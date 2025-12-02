<?php

namespace App\Http\Controllers;

use App\Models\Antrian;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Events\AntrianDipanggil;
use Illuminate\Http\JsonResponse;

class AntrianController extends Controller
{
    /** GET dashboard antrian */
    public function getAntrianDashboard(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'poli_id' => 'required|exists:polis,poli_id',
            'tanggal' => 'required|date',
        ]);

        $poliId  = $validated['poli_id'];
        $tanggal = $validated['tanggal'];

        // 🔥 now return reservation + user
        $sedangDipanggil = Antrian::with(['reservation.user'])
            ->where('poli_id', $poliId)
            ->where('tanggal_antrian', $tanggal)
            ->where('status', 'dipanggil')
            ->orderByDesc('waktu_panggil')
            ->first();

        $sisaAntrian = Antrian::where('poli_id', $poliId)
            ->where('tanggal_antrian', $tanggal)
            ->where('status', 'menunggu')
            ->count();

        $daftarTunggu = Antrian::with(['reservation.user'])
            ->where('poli_id', $poliId)
            ->where('tanggal_antrian', $tanggal)
            ->whereIn('status', ['menunggu', 'dipanggil'])
            ->orderBy('nomor_antrian', 'asc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'sedang_dipanggil' => $sedangDipanggil,
                'sisa_antrian' => $sisaAntrian,
                'daftar_tunggu' => $daftarTunggu,
            ],
        ]);
    }

    /** POST panggil berikutnya */
    public function panggilBerikutnya(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'poli_id' => 'required|exists:polis,poli_id',
        ]);

        $poliId = $validated['poli_id'];
        $adminId = Auth::id();
        $tanggal = Carbon::today()->toDateString();

        return DB::transaction(function () use ($poliId, $adminId, $tanggal) {
            $sedangDipanggil = Antrian::where('poli_id', $poliId)
                ->where('tanggal_antrian', $tanggal)
                ->where('status', 'dipanggil')
                ->where('admin_id', $adminId)
                ->lockForUpdate()
                ->first();

            if ($sedangDipanggil) {
                $sedangDipanggil->update([
                    'status'        => 'selesai',
                    'waktu_selesai' => now(),
                ]);
            }

            $antrianBaru = Antrian::with(['reservation.user'])
                ->where('poli_id', $poliId)
                ->where('tanggal_antrian', $tanggal)
                ->where('status', 'menunggu')
                ->orderBy('nomor_antrian', 'asc')
                ->lockForUpdate()
                ->first();

            if (!$antrianBaru) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tidak ada antrian lagi.',
                ], 404);
            }

            $antrianBaru->update([
                'status'        => 'dipanggil',
                'waktu_panggil' => now(),
                'admin_id'      => $adminId,
            ]);

            broadcast(new AntrianDipanggil(
                $antrianBaru,
                Antrian::where('poli_id', $poliId)
                    ->where('tanggal_antrian', $tanggal)
                    ->where('status', 'menunggu')
                    ->count()
            ))->toOthers();

            return response()->json([
                'success' => true,
                'message' => 'Antrian ' . $antrianBaru->nomor_antrian . ' dipanggil.',
                'data' => $antrianBaru,
            ]);
        });
    }

    /** POST selesaikan panggilan */
    public function selesaikanPanggilan(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'nomor_antrian' => 'required|exists:antrians,nomor_antrian',
        ]);

        $adminId = Auth::id();

        $antrian = Antrian::with(['reservation.user'])
            ->where('nomor_antrian', $validated['nomor_antrian'])
            ->where('status', 'dipanggil')
            ->where('admin_id', $adminId)
            ->first();

        if (!$antrian) {
            return response()->json([
                'success' => false,
                'message' => 'Antrian tidak ditemukan atau sudah selesai.',
            ], 404);
        }

        $antrian->update([
            'status'        => 'selesai',
            'waktu_selesai' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Antrian ' . $antrian->nomor_antrian . ' selesai.',
            'data' => $antrian,
        ]);
    }
}
