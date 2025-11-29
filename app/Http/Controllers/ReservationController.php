<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Reservation;
use App\Models\Poli;
use App\Models\PenanggungJawab;
use App\Models\Antrian;
use App\Models\dokter;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Carbon\Carbon;


class ReservationController extends Controller
{
    public function index(Request $request)
    {
        $query = Reservation::with(['user', 'poli', 'dokter', 'penanggungJawab']);

        if ($request->has('status')&& in_array($request->status, ['pending', 'confirmed', 'cancelled'])) {
            $query->where('status', $request->status);
        }

        $reservations = $query->latest()->paginate(10);
        return response()->json([
            'success' => true,
            'message' => 'Daftar reservasi berhasil diambil',
            'data' => $reservations
        ]);
    }
    
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nama' => 'required|string|max:100',
            'email' => 'required|email|max:100',
            'tempat_lahir' => 'required|string|max:100',
            'tanggal_lahir' => 'required|date',
            'nomor_whatsapp' => 'required|string|max:15',
            'penjaminan' => 'required|in:asuransi,cash',
            'nomor_ktp' => 'required|string|size:16',
            'keluhan' => 'required|string|max:1000',
            'poli_id' => 'required|exists:polis,poli_id',
            'tanggal_reservasi' => 'required|date|after_or_equal:today',
            'penanggung_jawab_id' => 'nullable|exists:penanggung_jawabs,PjId',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 422);
        }

        $input = $request->all();
        $user = Auth::user(); 
        $input['booked_user_id'] = $user->userid;
        $input['status'] = 'pending';

        $rekomendasiAI = null;
        $isMatch = false;

        try {
            $usia = 30; 
            $jk = 'L';

            if ($user->profile) {
                if ($user->profile->tanggal_lahir) {
                    $usia = Carbon::parse($user->profile->tanggal_lahir)->age;
                }
                $jk = ($user->profile->jenis_kelamin == 'Perempuan') ? 'P' : 'L';
            }

            $response = Http::timeout(5)->post('http://127.0.0.1:5000/predict', [
                'keluhan' => $request->keluhan,
                'usia' => $usia,
                'jenis_kelamin' => $jk,
                'riwayat_penyakit' => '' 
            ]);

            if ($response->successful()) {
                $mlResult = $response->json();

                $rekomendasiAI = $mlResult['rekomendasi_poli'] ?? null;

                $poliUser = Poli::find($request->poli_id);
                if ($poliUser && $rekomendasiAI) {
                    $namaPoliUser = strtoupper($poliUser->poli_name);
                    $namaPoliAI = strtoupper($rekomendasiAI);

                    if (str_contains($namaPoliUser, $namaPoliAI) || str_contains($namaPoliAI, $namaPoliUser)) {
                        $isMatch = true;
                    }
                }
            }
        } catch (\Exception $e) {
            Log::error("Gagal koneksi ke ML: " . $e->getMessage());
        }

        $input['rekomendasi_ai'] = $rekomendasiAI;
        $input['sesuai_ai'] = $isMatch ? 1 : 0;

        $reservation = Reservation::create($input);

        return response()->json([
            'success' => true,
            'message' => 'Reservasi berhasil dibuat',
            'data' => $reservation,
            'ai_suggestion' => $rekomendasiAI 
        ], 201);
    }

    public function show(Reservation $reservation)
    {
        $this->authorize('view', $reservation);
        $reservation->load(['user', 'admin', 'poli', 'dokter', 'penanggungJawab']);

        return response()->json([
            'success' => true,
            'message' => 'Detail reservasi berhasil diambil',
            'data' => $reservation
        ]);
    }
    
    public function verify(Request $request, Reservation $reservation)
    {
        $this->authorize('verify', $reservation);
        if ($reservation->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'Reservasi sudah diverifikasi atau dibatalkan'
            ], 409);
        }
        $validator = Validator::make($request->all(), [
            'poli_id' => 'required|exists:polis,poli_id',
            'dokter_id' => 'required|exists:dokters,dokter_id',
            'tanggal_reservasi' => 'required|date|after_or_equal:today',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'data tidak valid',
                'errors' => $validator->errors()
            ], 422);
        }
        try {
            return DB::transaction(function () use ($request, $reservation) {
                $tanggalReservasi = $request->tanggal_reservasi;
                $poli_id = $request->poli_id;

                // Kunci baris untuk mencegah race condition (nomor antrian ganda)
                $jumlahAntrian = Reservation::where('poli_id', $poli_id)
                    ->where('tanggal_reservasi', $tanggalReservasi)
                    ->where('status', 'confirmed')
                    ->lockForUpdate() 
                    ->count();

                $nomorAntrian = $jumlahAntrian + 1;

                // Generate Format Nomor Antrian (Contoh: P01-20251127-001)
                $poli = Poli::findOrFail($poli_id);
                $kodePoli = $poli->poli_id; // Sesuaikan logic kode poli
                $dateStr = str_replace('-', '', $tanggalReservasi);
                $nomorAntrianLengkap = sprintf("%s-%s-%03d", $kodePoli, $dateStr, $nomorAntrian);

                // 3. Update Reservasi
                $reservation->verif_adminID = Auth::id(); // Siapa admin yang verifikasi
                $reservation->poli_id = $poli_id;
                $reservation->dokter_id = $request->dokter_id; // Simpan dokter yang dipilih
                $reservation->tanggal_reservasi = $tanggalReservasi;
                $reservation->nomor_antrian = $nomorAntrianLengkap;
                $reservation->status = 'confirmed';
                $reservation->save();

                // 4. Masukkan ke Tabel Antrian (Agar tampil di Dashboard Antrian)
                Antrian::create([
                    'reservation_id' => $reservation->reservid,
                    'poli_id' => $poli_id,
                    'dokter_id' => $request->dokter_id,
                    'nomor_antrian' => $nomorAntrianLengkap,
                    'tanggal_antrian' => $tanggalReservasi,
                    'status' => 'menunggu',
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Reservasi berhasil diverifikasi',
                    'data' => $reservation
                ]);
            });
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memproses verifikasi: ' . $e->getMessage()
            ], 500);
        }
    }

    public function cancel(Reservation $reservation)
    {
        $this->authorize('cancel', $reservation);
        if ($reservation->status === 'cancelled'|| $reservation->status === 'confirmed') {
            return response()->json([
                'success' => false,
                'message' => 'Reservasi tidak dapat dibatalkan'
            ], 409);
        }

        $reservation->status = 'cancelled';
        $reservation->save();

        return response()->json([
            'success' => true,
            'message' => 'Reservasi berhasil dibatalkan',
            'data' => $reservation
        ]);
    }
}
