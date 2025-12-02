<?php

namespace App\Http\Controllers;

use App\Models\RekamMedis;
use Illuminate\Http\Request;
use App\Models\Reservation;

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
            'tanggal_diperiksa' => 'required|date',
        ]);

        // Otomatis buat No Medrec jika kosong
        if (empty($validated['no_medrec'])) {
            $validated['no_medrec'] = 'RM-' . str_pad(RekamMedis::max('rekam_medis_id') + 1, 5, '0', STR_PAD_LEFT);
        }

        $rm = RekamMedis::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Rekam medis berhasil ditambahkan.',
            'data' => $rm,
        ], 201);
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
