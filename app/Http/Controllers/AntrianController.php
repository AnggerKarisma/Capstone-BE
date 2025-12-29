<?php

namespace App\Http\Controllers;

use App\Models\Antrian;
use App\Models\Poli;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Events\AntrianDipanggil;
use Illuminate\Http\JsonResponse;

class AntrianController extends Controller
{
    public function getAntrianDashboard(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'poli_id' => 'nullable|exists:polis,poli_id',
            'tanggal' => 'required|date',
        ]);

        $tanggal = $validated['tanggal'];
        
        if ($request->has('poli_id') && $request->poli_id != null){
            $poliId = $request->poli_id;
            $data = $this->getAntrianByPoli($poliId, $tanggal);

            return response()->json([
                'success' => true,
                'mode' => 'single',
                'data' => $data,
            ]);
        } 

        $allPolis = Poli::all();
        
        $dashboardData = $allPolis->map(function($poli) use ($tanggal){
            $antrianData = $this->getAntrianByPoli($poli->poli_id, $tanggal);
            
            return array_merge([
                'poli_id'   => $poli->poli_id,
                'nama_poli' => $poli->poli_name, 
            ], $antrianData);
        });

        return response()->json([
            'success' => true,
            'mode' => 'all',
            'data' => $dashboardData,
        ]);
    }

    private function getAntrianByPoli($poliId, $tanggal)
    {
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
            ->where('status', ['menunggu','dipanggil'])
            ->orderBy('nomor_antrian', 'asc')
            ->get();

        $sudahSelesai = Antrian::with(['reservation.user'])
            ->where('poli_id', $poliId)
            ->where('tanggal_antrian', $tanggal)
            ->where('status', 'selesai')
            ->orderByDesc('waktu_selesai')
            ->get();

        return [
            'sedang_dipanggil' => $sedangDipanggil,
            'sisa_antrian' => $sisaAntrian,
            'daftar_tunggu' => $daftarTunggu,
            'sudah_selesai' => $sudahSelesai,
        ];
    }

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
            if ($sedangDipanggil->reservation) {
                $sedangDipanggil->reservation->update([
                    'status' => 'completed'
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

        if($antrian->reservation){
            $antrian->reservation->update([
                'status' => 'completed'
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Antrian ' . $antrian->nomor_antrian . ' selesai.',
            'data' => $antrian,
        ]);
    }

    public function tandaiTerlewat(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'antrian_id' => 'required|exists:antrians,id',
        ]);

        $antrian = Antrian::find($validated['antrian_id']);

        if (!$antrian || !in_array($antrian->status, ['menunggu', 'dipanggil'])) {
            return response()->json([
                'success' => false,
                'message' => 'Antrian tidak valid untuk dilewati.',
            ], 400);
        }

        $antrian->update([
            'status' => 'terlewat',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Antrian ' . $antrian->nomor_antrian . ' ditandai terlewat.',
            'data' => $antrian,
        ]);
    }

    public function batalkanAntrian(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'antrian_id' => 'required|exists:antrians,id',
            'alasan'     => 'nullable|string'
        ]);

        $antrian = Antrian::find($validated['antrian_id']);

        if (!$antrian) {
            return response()->json([
                'success' => false,
                'message' => 'Antrian tidak ditemukan.',
            ], 404);
        }

        DB::transaction(function () use ($antrian) {
            $antrian->update([
                'status' => 'batal'
            ]);
            
            if ($antrian->reservation) {
                $antrian->reservation->update(['status' => 'cancelled']);
            }
        });

        return response()->json([
            'success' => true,
            'message' => 'Antrian ' . $antrian->nomor_antrian . ' berhasil dibatalkan.',
            'data' => $antrian,
        ]);
    }
}
