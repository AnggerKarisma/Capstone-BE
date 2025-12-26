<?php

namespace App\Http\Controllers;

use App\Models\RekamMedis;
use App\Models\Reservation;
use App\Models\Antrian;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class RekamMedisController extends Controller
{
    public function index()
    {
        return RekamMedis::with('reservasi.user')
            ->orderByDesc('tanggal_diperiksa')
            ->get();
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'reservasi_id'      => 'required|exists:reservations,reservid',
            'no_medrec'         => 'nullable|string|max:50',
            'gejala'            => 'nullable|string',
            'diagnosis'         => 'nullable|string',
            'tindakan'          => 'nullable|string',
            'resep_obat'        => 'nullable|string',
        ]);

        try {
            DB::beginTransaction();

            $reservasi = Reservation::with('user')->findOrFail($request->reservasi_id);

            $tanggalDiperiksa = Carbon::now();

            $noMedrec = $request->no_medrec;
            if (empty($noMedrec)) {
                $lastId = RekamMedis::max('rekam_medis_id') ?? 0;
                $noMedrec = 'RM-' . date('Y') . '-' . str_pad($lastId + 1, 5, '0', STR_PAD_LEFT);
            }

            $rm = RekamMedis::create([
                'reservasi_id'      => $reservasi->reservid,
                'no_medrec'         => $noMedrec,
                'gejala'            => $request->gejala,
                'diagnosis'         => $request->diagnosis,
                'tindakan'          => $request->tindakan,
                'resep_obat'        => $request->resep_obat,
                'tanggal_diperiksa' => $tanggalDiperiksa,
            ]);

            $reservasi->update(['status' => 'confirmed']); 

            $antrian = Antrian::where('reservation_id', $reservasi->reservid)->first();
            if ($antrian) {
                $antrian->update([
                    'status' => 'selesai',
                    'waktu_selesai' => Carbon::now(),
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Rekam medis berhasil disimpan. Pasien selesai diperiksa.',
                'data' => $rm->load('reservasi.user'), // Load data user untuk respon balik
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal menyimpan rekam medis: ' . $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, RekamMedis $rekamMedis)
    {
        $validated = $request->validate([
            'no_medrec'         => 'nullable|string|max:50',
            'diagnosis'         => 'nullable|string',
            'tanggal_diperiksa' => 'nullable|date',
        ]);

        $rekamMedis->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Rekam medis berhasil diperbarui.',
            'data' => $rekamMedis,
        ]);
    }

    public function destroy(RekamMedis $rekamMedis)
    {
        $rekamMedis->delete();

        return response()->json([
            'success' => true,
            'message' => 'Rekam medis berhasil dihapus.',
        ]);
    }
}
