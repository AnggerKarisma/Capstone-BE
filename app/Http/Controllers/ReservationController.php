<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Reservation;
use App\Models\Poli;
use App\Models\PenanggungJawab;
use App\Models\Antrian;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
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
        $input['booked_user_id'] = Auth::id();

        $reservation = Reservation::create($input);
        return response()->json([
            'success' => true,
            'message' => 'Reservasi berhasil dibuat',
            'data' => $reservation
        ],201);
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

    $tanggalReservasi = $request->tanggal_reservasi;
    $poliId = $request->poli_id;

    $jumlahAntrianSebelumnya = Reservation::where('poli_id', $poliId)
        ->where('tanggal_reservasi', $tanggalReservasi)
        ->where('status', 'confirmed')
        ->count();
    $nomorAntrian = $jumlahAntrianSebelumnya + 1;

    $poli = Poli::findOrFail($poliId);
    $kodePoli = 'P'. $poli->poli_id;
    $nomorAntrianLengkap = $kodePoli . '-' . str_replace('-','', $tanggalReservasi) . '-' . str_pad($nomorAntrian, 3, '0', STR_PAD_LEFT);

        $reservation->verif_adminID = Auth::id();
        $reservation->poli_id = $poliId;
        $reservation->dokter_id = $request->dokter_id;
        $reservation->tanggal_reservasi = $tanggalReservasi;
        $reservation->nomor_antrian = $nomorAntrianLengkap;
        $reservation->status = 'confirmed';
        $reservation->save();

        Antrian::create([
            'reservation_id' => $reservation->reservid,
            'poli_id' => $poliId,
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
