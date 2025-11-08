<?php

namespace App\Http\Controllers;

use App\Models\Antrian;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Events\AntrianDipanggil;

class AntrianController extends Controller
{
    public function getAntrianDashboard(Request $request)
    {
        $request->validate([
            'poli_id' => 'required|exists:polis,poli_id',
            'tanggal' => 'required|date',
        ]);

        $poli_id = $request->poli_id;
        $tanggal = $request->tanggal;

        $sedangDipanggil = Antrian::where('poli_id', $poli_id)
            ->where('tanggal_antrian', $tanggal)
            ->where('status', 'dipanggil')
            ->orderBy('waktu_panggil', 'desc')
            ->first();

        $sisaAntrian = Antrian::where('poli_id', $poli_id)
            ->where('tanggal_antrian', $tanggal)
            ->where('status', 'menunggu')
            ->count();
            
        $daftarTunggu = Antrian::where('poli_id', $poli_id)
            ->where('tanggal_antrian', $tanggal)
            ->whereIn('status', ['menunggu', 'dipanggil'])
            ->orderBy('nomor_antrian', 'asc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'sedang_dipanggil' => $sedangDipanggil,
                'sisa_antrian' => $sisaAntrian,
                'daftar_tunggu' => $daftarTunggu, // Untuk list di dashboard Admin
            ]
        ]);
    }

    /**
     * (Untuk Admin) Memanggil nomor antrian berikutnya.
     */
    public function panggilBerikutnya(Request $request)
    {
        $request->validate([
            'poli_id' => 'required|exists:polis,poli_id',
        ]);

        $poli_id = $request->poli_id;
        $admin_id = Auth::id(); // Admin yang sedang bertugas
        $tanggal = Carbon::today();

        return DB::transaction(function () use ($poli_id, $admin_id, $tanggal) {
            
            // 1. Selesaikan antrian yang 'dipanggil' oleh admin ini
            $masihDipanggil = Antrian::where('poli_id', $poli_id)
                ->where('tanggal_antrian', $tanggal)
                ->where('status', 'dipanggil')
                ->where('admin_id', $admin_id) 
                ->first();

            if ($masihDipanggil) {
                $masihDipanggil->update([
                    'status' => 'selesai',
                    'waktu_selesai' => now(),
                ]);
            }

            // 2. Ambil antrian 'menunggu' berikutnya
            $antrianBaru = Antrian::where('poli_id', $poli_id)
                ->where('tanggal_antrian', $tanggal)
                ->where('status', 'menunggu')
                ->orderBy('nomor_antrian', 'asc')
                ->first();

            if (!$antrianBaru) {
                return response()->json(['success' => false, 'message' => 'Tidak ada antrian lagi'], 404);
            }

            // 3. Update status antrian baru
            $antrianBaru->update([
                'status' => 'dipanggil',
                'waktu_panggil' => now(),
                'admin_id' => $admin_id,
            ]);

            $sisaAntrian = Antrian::where('poli_id', $poli_id)
                ->where('tanggal_antrian', $tanggal)
                ->where('status', 'menunggu')
                ->count();
            
            broadcast(new AntrianDipanggil($antrianBaru, $sisaAntrian))->toOthers();

            return response()->json([
                'success' => true,
                'message' => 'Antrian ' . $antrianBaru->nomor_antrian . ' dipanggil',
                'data' => $antrianBaru
            ]);
        });
    }
}