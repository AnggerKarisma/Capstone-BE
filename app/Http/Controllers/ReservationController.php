<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Reservation;
use App\Models\Poli;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Carbon\Carbon;

class ReservationController extends Controller
{
    public function index()
    {
        $query = Reservation::with(['user', 'poli', 'jadwalDokter']);

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
            'poli_id' => 'required|exists:polis,poliID',
            'tanggal_reservasi' => 'required|date|after_or_equal:today',
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
        $reservation->load(['user', 'admin', 'poli', 'jadwalDokter']);

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
            'poli_id' => 'required|exists:polis,poliID',
            'jadwal_dokter_id' => 'required|exists:jadwal_dokters,jadwaldokterid',
            'tanggal_reservasi' => 'required|date|after_or_equal:today',
            'status' => 'required|in:confirmed,cancelled',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'data tidak valid',
                'errors' => $validator->errors()
            ], 422);
        }

    $tanggal = Carbon::parse($request->tanggal_reservasi)->toDateString();
    $poliId = $request->poli_id;

    $jumlahAntrianSebelumnya = Reservation::where('poli_id', $poliId)
        ->where('tanggal_reservasi', $tanggal)
        ->where('status', 'confirmed')
        ->count();
    $nomorAntrian = $jumlahAntrianSebelumnya + 1;

    $poli = Poli::findOrFail($poliId);
    $kodePoli = $poli->kode ?? 'P';
    $nomorAntrian = $kodePoli . '-' . str_replace('-','', $tanggal) . '-' . str_pad($nomorAntrianBaru, 3, '0', STR_PAD_LEFT);

        $reservation->verif_adminID = Auth::id();
        $reservation->poli_id = $poliId;
        $reservation->jadwal_dokter_id = $request->jadwal_dokter_id;
        $reservation->tanggal_reservasi = $tanggal;
        $reservation->nomor_antrian = $nomorAntrian;
        $reservation->status = 'confirmed';
        $reservation->save();

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
