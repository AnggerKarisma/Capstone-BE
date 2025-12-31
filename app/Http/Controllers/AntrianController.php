<?php

namespace App\Http\Controllers;

use App\Models\Antrian;
use App\Models\Poli;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
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

    public function getMyQueues(Request $request): JsonResponse
    {
        // Use $request->user() instead of Auth::user() - more reliable with Sanctum
        $user = $request->user();
        
        if (!$user) {
            \Log::channel('single')->error('❌ USER NOT AUTHENTICATED');
            return response()->json([
                'success' => false,
                'message' => 'User not authenticated'
            ], 401);
        }

        // User model uses 'userid' as primary key, not 'id'
        $userId = $user->userid;
        
        \Log::channel('single')->info('🔍 MY QUEUES CALLED', [
            'user_id' => $userId,
            'user_type' => get_class($user)
        ]);
        
        $validated = $request->validate([
            'tanggal' => 'nullable|date',
        ]);

        $tanggal = $validated['tanggal'] ?? Carbon::today()->toDateString();

        \Log::channel('single')->info('📅 Querying antrians', [
            'user_id' => $userId,
            'tanggal' => $tanggal
        ]);

        // Ambil antrian user berdasarkan reservation mereka
        $myQueues = Antrian::with(['poli', 'dokter', 'reservation'])
            ->whereHas('reservation', function($query) use ($userId) {
                $query->where('booked_user_id', $userId);
            })
            ->where('tanggal_antrian', $tanggal)
            ->whereIn('status', ['menunggu', 'dipanggil'])
            ->orderBy('nomor_antrian', 'asc')
            ->get()
            ->map(function($antrian) use ($tanggal) {
                // Hitung posisi antrian
                $daftarTunggu = Antrian::where('poli_id', $antrian->poli_id)
                    ->where('tanggal_antrian', $tanggal)
                    ->whereIn('status', ['menunggu', 'dipanggil'])
                    ->orderBy('nomor_antrian', 'asc')
                    ->get();

                $position = $daftarTunggu->search(function($item) use ($antrian) {
                    return $item->id === $antrian->id;
                }) + 1;

                // Ambil nomor yang sedang dipanggil
                $sedangDipanggil = Antrian::where('poli_id', $antrian->poli_id)
                    ->where('tanggal_antrian', $tanggal)
                    ->where('status', 'dipanggil')
                    ->orderByDesc('waktu_panggil')
                    ->first();

                // Hitung estimasi waktu tunggu (10 menit per pasien)
                $patientsAhead = $position - 1;
                $estimatedMinutes = $patientsAhead * 10;

                // Extract nomor antrian yang bersih (ambil 3 digit terakhir)
                $cleanNomor = substr($antrian->nomor_antrian, -3);
                
                return [
                    'antrian_id' => $antrian->id,
                    'nomor_antrian' => $cleanNomor,
                    'nomor_antrian_full' => $antrian->nomor_antrian,
                    'status' => $antrian->status,
                    'poli_id' => $antrian->poli_id,
                    'poli_name' => $antrian->poli->poli_name ?? 'N/A',
                    'dokter_name' => $antrian->dokter->nama_dokter ?? 'Belum ditentukan',
                    'tanggal_antrian' => $antrian->tanggal_antrian,
                    'posisi_antrian' => $position,
                    'total_antrian' => $daftarTunggu->count(),
                    'nomor_sekarang' => $sedangDipanggil ? substr($sedangDipanggil->nomor_antrian, -3) : null,
                    'estimasi_waktu_menit' => $estimatedMinutes,
                ];
            });

        // Jika tidak ada antrian hari ini, cari antrian terbaru user
        if ($myQueues->isEmpty()) {
            Log::info('⚠️ No queues found for date, using fallback', ['tanggal' => $tanggal]);
            
            $latestQueue = Antrian::with(['poli', 'dokter', 'reservation'])
                ->whereHas('reservation', function($query) use ($userId) {
                    $query->where('booked_user_id', $userId);
                })
                ->whereIn('status', ['menunggu', 'dipanggil'])
                ->orderByDesc('tanggal_antrian')
                ->orderBy('nomor_antrian', 'asc')
                ->first();
            
            Log::info('🔍 Fallback result', [
                'found' => $latestQueue ? 'yes' : 'no',
                'tanggal_antrian' => $latestQueue?->tanggal_antrian
            ]);

            if ($latestQueue) {
                $latestDate = $latestQueue->tanggal_antrian;
                
                // Hitung posisi untuk tanggal antrian terbaru
                $daftarTunggu = Antrian::where('poli_id', $latestQueue->poli_id)
                    ->where('tanggal_antrian', $latestDate)
                    ->whereIn('status', ['menunggu', 'dipanggil'])
                    ->orderBy('nomor_antrian', 'asc')
                    ->get();

                $position = $daftarTunggu->search(function($item) use ($latestQueue) {
                    return $item->id === $latestQueue->id;
                }) + 1;

                $sedangDipanggil = Antrian::where('poli_id', $latestQueue->poli_id)
                    ->where('tanggal_antrian', $latestDate)
                    ->where('status', 'dipanggil')
                    ->orderByDesc('waktu_panggil')
                    ->first();

                $patientsAhead = $position - 1;
                $estimatedMinutes = $patientsAhead * 10;

                // Extract clean nomor antrian
                $cleanNomor = substr($latestQueue->nomor_antrian, -3);
                
                $myQueues = collect([[
                    'antrian_id' => $latestQueue->id,
                    'nomor_antrian' => $cleanNomor,
                    'nomor_antrian_full' => $latestQueue->nomor_antrian,
                    'status' => $latestQueue->status,
                    'poli_id' => $latestQueue->poli_id,
                    'poli_name' => $latestQueue->poli->poli_name ?? 'N/A',
                    'dokter_name' => $latestQueue->dokter->nama_dokter ?? 'Belum ditentukan',
                    'tanggal_antrian' => $latestQueue->tanggal_antrian,
                    'posisi_antrian' => $position,
                    'total_antrian' => $daftarTunggu->count(),
                    'nomor_sekarang' => $sedangDipanggil ? substr($sedangDipanggil->nomor_antrian, -3) : null,
                    'estimasi_waktu_menit' => $estimatedMinutes,
                ]]);
            }
        }

        Log::info('✅ Returning queues', ['count' => $myQueues->count()]);
        
        return response()->json([
            'success' => true,
            'data' => $myQueues,
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
