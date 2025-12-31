<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Reservation;
use App\Models\Poli;
use App\Models\PenanggungJawab;
use App\Models\Antrian;
use App\Models\Dokter;
use App\Models\User;
use App\Notifications\ReservationStatusUpdated;
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

        if ($request->has('status') && in_array($request->status, ['pending', 'confirmed', 'cancelled'])) {
            $query->where('status', $request->status);
        }

        $reservations = $query->latest()->paginate(10);

        return response()->json([
            'success' => true,
            'message' => 'Daftar reservasi berhasil diambil',
            'data'    => $reservations,
        ]);
    }

    public function store(Request $request)
    {
        // ---------- VALIDASI DASAR ----------
        $rules = [
            'poli_id'             => 'required|exists:polis,poli_id',
            'tanggal_reservasi'   => 'required|date|after_or_equal:today',
            'keluhan'             => 'required|string|max:1000',
            'is_self'             => 'required|boolean',
            'penanggung_jawab_id' => 'nullable|exists:penanggung_jawabs,pjId',
            'dokter_id'           => 'nullable|exists:dokters,dokter_id',
        ];

        $isSelf = $request->boolean('is_self');

        if (!$isSelf) {
            $rules = array_merge($rules, [
                'nama'                 => 'required|string|max:255',
                'email'                => 'required|string|email|max:255',
                'jenis_kelamin'        => 'required|in:Laki-laki,Perempuan',
                'tempat_lahir'         => 'required|string|max:100',
                'tanggal_lahir'        => 'required|date',
                'nomor_ktp'            => 'required|string|digits:16',
                'nomor_whatsapp'       => 'required|string|max:15',
                'penjaminan'           => 'required|in:asuransi,cash',
                'status_keluarga'      => 'nullable|string|max:100',
                'nama_keluarga'        => 'nullable|string|max:255',
                'status_perkawinan'    => 'nullable|string|max:50',
                'suku'                 => 'nullable|string|max:100',
                'agama'                => 'nullable|string|max:50',
                'pendidikan_terakhir'  => 'nullable|string|max:100',
                'alamat'               => 'nullable|string|max:500',
                'provinsi'             => 'nullable|string|max:100',
                'kota/kabupaten'       => 'nullable|string|max:100',
                'kecamatan'            => 'nullable|string|max:100',
                'kelurahan'            => 'nullable|string|max:100',
                'nomor_pegawai'        => 'nullable|string|max:50',
                'nama_asuransi'        => 'required_if:penjaminan,asuransi|nullable|string|max:100',
                'nomor_asuransi'       => 'required_if:penjaminan,asuransi|nullable|string|max:50',
            ]);
        }

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors'  => $validator->errors(),
            ], 422);
        }

        // ---------- AMBIL USER YANG LOGIN ----------
        $actor = $request->user();  // Sanctum
        if (!$actor) {
            return response()->json([
                'success' => false,
                'message' => 'User tidak terautentik, silakan login ulang.',
            ], 401);
        }

        if (!($actor instanceof User)) {
            return response()->json([
                'success' => false,
                'message' => 'Reservasi hanya boleh dibuat oleh pasien.',
            ], 403);
        }

        $user  = $actor;
        $input = $request->all();

        // ---------- JIKA RESERVASI UNTUK DIRI SENDIRI ----------
        if ($isSelf) {
            $profile = $user->profile;

            if (!$profile) {
                return response()->json([
                    'success' => false,
                    'message' => 'Profil pengguna tidak ditemukan. Silakan lengkapi profil terlebih dahulu.',
                ], 400);
            }

            if (empty($profile->noKTP) || empty($profile->tanggal_lahir)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Nomor KTP dan tanggal lahir pada profil tidak ditemukan. Silakan lengkapi profil terlebih dahulu.',
                ], 400);
            }

            $input['nama']                = $user->name;
            $input['email']               = $user->email;
            $input['nomor_whatsapp']      = $profile->nomor_telepon ?? $user->nomor_telepon;
            $input['jenis_kelamin']       = $profile->jenis_kelamin;
            $input['tempat_lahir']        = $profile->tempat_lahir ?? '-';
            $input['tanggal_lahir']       = $profile->tanggal_lahir;
            $input['nomor_ktp']           = $profile->noKTP;
            $input['status_keluarga']     = $profile->status_keluarga;
            $input['nama_keluarga']       = $profile->nama_keluarga;
            $input['status_perkawinan']   = $profile->status_perkawinan;
            $input['suku']                = $profile->suku;
            $input['agama']               = $profile->agama;
            $input['pendidikan_terakhir'] = $profile->pendidikan_terakhir;
            $input['alamat']              = $profile->alamat;
            $input['provinsi']            = $profile->provinsi;
            $input['kota/kabupaten']      = $profile->{'kota/kabupaten'} ?? null;
            $input['kecamatan']           = $profile->kecamatan;
            $input['kelurahan']           = $profile->kelurahan;
            $input['nomor_pegawai']       = $profile->nomor_pegawai;

            if (!isset($input['penjaminan'])) {
                $input['penjaminan']    = $profile->penjaminan ?? 'cash';
                $input['nama_asuransi'] = $profile->nama_asuransi;
                $input['nomor_asuransi']= $profile->nomor_asuransi;
            }
        }

        // ---------- SET FIELD SISTEM ----------
        $input['booked_user_id'] = $user->userid;
        $input['status']         = 'pending';

        if (empty($input['penanggung_jawab_id'])) {
            $input['penanggung_jawab_id'] = null;
        }
        if (empty($input['dokter_id'])) {
            $input['dokter_id'] = null;
        }

        // ---------- PANGGIL ML & REKOMENDASI ----------
        $rekomendasiAI = [];
        $isMatch       = false;

        try {
            $usia = 30;
            $jk   = 'L';

            if ($user->profile) {
                if ($user->profile->tanggal_lahir) {
                    $usia = Carbon::parse($user->profile->tanggal_lahir)->age;
                }
                $jk = ($user->profile->jenis_kelamin == 'Perempuan') ? 'P' : 'L';
            }

            $response = Http::timeout(5)->post('http://127.0.0.1:5000/predict', [
                'keluhan'          => $request->keluhan,
                'usia'             => $usia,
                'jenis_kelamin'    => $jk,
                'riwayat_penyakit' => '',
            ]);

            if ($response->successful()) {
                $mlResult      = $response->json();
                $rekomendasiAI = $mlResult['rekomendasi_poli'] ?? null;

                $poliUser = Poli::find($request->poli_id);

                if ($poliUser && !empty($rekomendasiAI)) {
                    $namaPoliUser = strtoupper($poliUser->poli_name); 
                    foreach($rekomendasiAI as $saran) {
                        $saran = strtoupper($saran); 
                        $coreUser = trim(str_replace(['KLINIK', 'POLI'], '', $namaPoliUser));
                        $coreAI   = trim(str_replace(['KLINIK', 'POLI'], '', $saran));

                        if (str_contains($coreUser, $coreAI) || str_contains($coreAI, $coreUser)) {
                            $isMatch = true;
                            break; 
                        }
                    }
                }
            }
        } catch (\Exception $e) {
            Log::error("Gagal koneksi ke ML: " . $e->getMessage());
        }

        // NONAKTIFKAN validasi ketat - biarkan user bebas memilih poli
        // Log saja jika tidak sesuai rekomendasi AI
        if (!$isMatch && !empty($rekomendasiAI)) {
            Log::info("User memilih poli berbeda dari rekomendasi AI", [
                'pilihan_user' => $poliUser->poli_name ?? '-',
                'rekomendasi_ai' => $rekomendasiAI
            ]);
        }
        
        $input['rekomendasi_ai'] = $rekomendasiAI;
        $input['sesuai_ai']      = $isMatch ? 1 : 0;

        // ---------- SIMPAN KE DATABASE ----------
        try {
            $reservation = Reservation::create($input);

            return response()->json([
                'success'     => true,
                'message'     => 'Reservasi berhasil dibuat',
                'data'        => $reservation,
                'ai_analysis' => [
                    'suggestion' => $rekomendasiAI,
                    'is_match'   => $isMatch,
                ],
            ], 201);
        } catch (\Exception $e) {
            Log::error("Gagal membuat reservasi: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal membuat reservasi',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    public function getRecommendation(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'keluhan' => 'required|string|max:1000',
            'is_self' => 'boolean', 
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false, 
                'message' => 'Data tidak lengkap', 
                'errors' => $validator->errors()
            ], 422);
        }

        $user = Auth::user();
        $isSelf = $request->has('is_self') ? $request->boolean('is_self') : true;
        $usia = 30; 
        $jk = 'L';
        $riwayat = ''; 

        if ($isSelf) {
            if ($user->profile) {
                if ($user->profile->tanggal_lahir) {
                    $usia = Carbon::parse($user->profile->tanggal_lahir)->age;
                }
                if ($user->profile->jenis_kelamin) {
                    $jk = ($user->profile->jenis_kelamin == 'Perempuan') ? 'P' : 'L';
                }
            }
        } else {
            if ($request->filled('tanggal_lahir')) {
                $usia = Carbon::parse($request->tanggal_lahir)->age;
            }
            if ($request->filled('jenis_kelamin')) {
                $jk = ($request->jenis_kelamin == 'Perempuan') ? 'P' : 'L';
            }
            if ($request->filled('riwayat_penyakit')) {
                $riwayat = $request->riwayat_penyakit;
            }
        }

        try {
            $payload = [
                'keluhan' => $request->keluhan,
                'usia' => (int) $usia,        
                'jenis_kelamin' => $jk,      
                'riwayat_penyakit' => $riwayat 
            ];

            Log::info('Sending to ML:', $payload);

            $response = Http::timeout(10)->post('http://127.0.0.1:5000/predict', $payload);

            if ($response->successful()) {
                $mlResult = $response->json();

                $rekomendasiList = $mlResult['rekomendasi_poli'] ?? []; 
                $confidence = $mlResult['confidence_score'] ?? 0;

                $poliOptions = [];

                foreach ($rekomendasiList as $namaPoliAI) {
                    $keyword = trim(str_replace(['POLI', 'KLINIK'], '', strtoupper($namaPoliAI)));

                    $poliDb = Poli::where('poli_name', 'LIKE', '%' . $keyword . '%')->first();

                    if ($poliDb) {
                        $poliOptions[] = [
                            'poli_id' => $poliDb->poli_id,
                            'poli_name' => $poliDb->poli_name,
                            'source' => 'AI Recommendation'
                        ];
                    }
                }

                return response()->json([
                    'success' => true,
                    'message' => 'Rekomendasi berhasil didapatkan',
                    'data' => [
                        'rekomendasi_list' => $poliOptions, // List poli (Max 3) untuk ditampilkan di Frontend
                        'confidence' => $confidence,
                        'analisis' => $rekomendasiList[0] ?? '-' // Top 1 prediction text
                    ]
                ]);

            } else {
                return response()->json(['success' => false, 'message' => 'AI Service Error'], 502);
            }

        } catch (\Exception $e) {
            Log::error("ML Error: " . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Gagal menghubungi AI'], 500);
        }
    }

    public function show(Reservation $reservation)
    {
        $this->authorize('view', $reservation);
        $reservation->load(['user', 'admin', 'poli', 'dokter', 'penanggungJawab']);

        return response()->json([
            'success' => true,
            'message' => 'Detail reservasi berhasil diambil',
            'data'    => $reservation,
        ]);
    }

    public function verify(Request $request, Reservation $reservation)
    {
        $this->authorize('verify', $reservation);
        
        // Validasi status yang dikirim
        $validator = Validator::make($request->all(), [
            'status' => 'required|in:confirmed,cancelled',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Status tidak valid',
                'errors'  => $validator->errors(),
            ], 422);
        }

        if ($reservation->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'Reservasi sudah diverifikasi atau dibatalkan',
            ], 409);
        }

        $newStatus = $request->status;

        // Jika reject/cancel, langsung update status tanpa generate antrian
        if ($newStatus === 'cancelled') {
            try {
                $reservation->status = 'cancelled';
                $reservation->verif_adminID = Auth::id();
                $reservation->save();

                // Kirim notifikasi ke user
                $user = $reservation->user;
                if ($user) {
                    $user->notify(new ReservationStatusUpdated($reservation));
                }

                return response()->json([
                    'success' => true,
                    'message' => 'Reservasi berhasil dibatalkan',
                    'data'    => $reservation,
                ]);
            } catch (\Exception $e) {
                Log::error('Gagal membatalkan reservasi: ' . $e->getMessage());
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal membatalkan reservasi: ' . $e->getMessage(),
                ], 500);
            }
        }

        // Jika confirm, validasi data dan generate antrian
        $dataToValidate = [
            'poli_id'           => $reservation->poli_id,
            'dokter_id'         => $reservation->dokter_id,
            'tanggal_reservasi' => $reservation->tanggal_reservasi,
        ];

        $validator = Validator::make($dataToValidate, [
            'poli_id'           => 'required|exists:polis,poli_id',
            'dokter_id'         => 'required|exists:dokters,dokter_id',
            'tanggal_reservasi' => 'required|date|after_or_equal:today',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Data reservasi tidak valid untuk diverifikasi',
                'errors'  => $validator->errors(),
            ], 422);
        }

        try {
            return DB::transaction(function () use ($reservation) {
                $tanggalReservasi = $reservation->tanggal_reservasi;
                $poli_id          = $reservation->poli_id;
                $dokter_id        = $reservation->dokter_id;

                // Hitung jumlah antrian confirmed di poli & tanggal yang sama
                $jumlahAntrian = Reservation::where('poli_id', $poli_id)
                    ->where('tanggal_reservasi', $tanggalReservasi)
                    ->where('status', 'confirmed')
                    ->lockForUpdate()
                    ->count();

                $nomorAntrian = $jumlahAntrian + 1;

                $poli     = Poli::findOrFail($poli_id);
                $kodePoli = $poli->poli_id;
                $dateStr  = str_replace('-', '', $tanggalReservasi);

                $nomorAntrianLengkap = sprintf("%s-%s-%03d", $kodePoli, $dateStr, $nomorAntrian);

                $reservation->verif_adminID     = Auth::id();
                $reservation->poli_id           = $poli_id;
                $reservation->dokter_id         = $dokter_id;
                $reservation->tanggal_reservasi = $tanggalReservasi;
                $reservation->nomor_antrian     = $nomorAntrianLengkap;
                $reservation->status            = 'confirmed';
                $reservation->save();

                Antrian::create([
                    'reservation_id' => $reservation->reservid,
                    'poli_id'        => $poli_id,
                    'dokter_id'      => $dokter_id,
                    'nomor_antrian'  => $nomorAntrianLengkap,
                    'tanggal_antrian'=> $tanggalReservasi,
                    'status'         => 'menunggu',
                ]);
                
                $user = $reservation->user;
                if ($user) {
                    $user->notify(new ReservationStatusUpdated($reservation));
                }

                return response()->json([
                    'success' => true,
                    'message' => 'Reservasi berhasil diverifikasi',
                    'data'    => $reservation,
                ]);
            });
        } catch (\Exception $e) {
            Log::error('Gagal memproses verifikasi: ' . $e->getMessage());

            if ($user) {
                $user->notify(new ReservationStatusUpdated($reservation));
            }

            return response()->json([
                'success' => false,
                'message' => 'Gagal memproses verifikasi: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function cancel(Reservation $reservation)
    {
        $this->authorize('cancel', $reservation);

        if ($reservation->status === 'cancelled' || $reservation->status === 'confirmed') {
            return response()->json([
                'success' => false,
                'message' => 'Reservasi tidak dapat dibatalkan',
            ], 409);
        }

        $reservation->status = 'cancelled';
        $reservation->save();

        return response()->json([
            'success' => true,
            'message' => 'Reservasi berhasil dibatalkan',
            'data'    => $reservation,
        ]);
    }

    public function update(Request $request, Reservation $reservation)
    {
        
        $validator = Validator::make($request->all(), [
            'poli_id'           => 'nullable|exists:polis,poli_id',
            'dokter_id'         => 'nullable|exists:dokters,dokter_id',
            'tanggal_reservasi' => 'nullable|date|after_or_equal:today',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        try {
            return DB::transaction(function () use ($request, $reservation) {
        
                $oldPoli = $reservation->poli_id;
                $newPoli = $request->poli_id ?? $oldPoli;
                $reservation->update([
                    'poli_id'           => $newPoli,
                    'dokter_id'         => $request->dokter_id ?? $reservation->dokter_id,
                    'tanggal_reservasi' => $request->tanggal_reservasi ?? $reservation->tanggal_reservasi,
                ]);

                if ($reservation->status === 'confirmed') {
                    $antrian = Antrian::where('reservation_id', $reservation->reservid)->first();
                    
                    if ($antrian) {
                        if ($oldPoli !== $newPoli) {
                            $poli = Poli::findOrFail($newPoli);
                            $kodePoli = $poli->poli_id;
                            $dateStr = str_replace('-', '', $reservation->tanggal_reservasi);
                            $oldNumberSequence = substr($antrian->nomor_antrian, -3); 
                            
                            $newNomorAntrian = sprintf("%s-%s-%s", $kodePoli, $dateStr, $oldNumberSequence);
                            
                            $antrian->update([
                                'poli_id'       => $newPoli,
                                'dokter_id'     => $request->dokter_id ?? $antrian->dokter_id,
                                'nomor_antrian' => $newNomorAntrian,
                                'tanggal_antrian' => $reservation->tanggal_reservasi
                            ]);
                            $reservation->update(['nomor_antrian' => $newNomorAntrian]);
                        } else {
                            $antrian->update([
                                'dokter_id'       => $request->dokter_id ?? $antrian->dokter_id,
                                'tanggal_antrian' => $reservation->tanggal_reservasi
                            ]);
                        }
                    }
                }

                return response()->json([
                    'success' => true,
                    'message' => 'Data reservasi berhasil diperbarui',
                    'data'    => $reservation
                ]);
            });

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal update reservasi: ' . $e->getMessage()
            ], 500);
        }
    }
}
