<?php

namespace App\Http\Controllers;

use App\Models\RekamMedis;
use App\Models\Reservation; 
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class RekamMedisController extends Controller
{
    public function index()
    {
        $rekamMedis = RekamMedis::with('reservasi')->get();
        return response()->json($rekamMedis);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'reservasi_id' => 'required|integer|exists:reservations,reservid|unique:rekam_medis',
            'no_medrec' => 'required|string',
            'gejala' => 'nullable|string',
            'diagnosis' => 'nullable|string',
            'tindakan' => 'nullable|string',
            'resep_obat' => 'nullable|string',
            'tanggal_diperiksa' => 'required|date',
        ], [
            'reservasi_id.unique' => 'Rekam medis untuk reservasi ini sudah ada.'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        try {
            DB::beginTransaction();

            $reservasi = Reservation::find($request->reservasi_id);

            // Tidak boleh buat rekam medis jika belum dikonfirmasi
            if ($reservasi->status !== 'confirmed') {
                return response()->json([
                    'message' => 'Reservasi belum dikonfirmasi sehingga belum bisa dibuat rekam medis.'
                ], 409);
            }

            $rekamMedis = RekamMedis::create($validator->validated());

            $reservasi->status = 'confirmed';
            $reservasi->save();

            DB::commit();

            return response()->json([
                'message' => 'Rekam medis berhasil disimpan dan reservasi telah diselesaikan.',
                'data' => $rekamMedis->load('reservasi') 
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Gagal menyimpan rekam medis: ' . $e->getMessage()], 500);
        }
    }

    public function show(RekamMedis $rekamMedis)
    {
        return $rekamMedis->load('reservasi');
    }

    public function update(Request $request, RekamMedis $rekamMedis)
    {
        $validator = Validator::make($request->all(), [
            'no_medrec' => 'sometimes|required|string',
            'gejala' => 'nullable|string',
            'diagnosis' => 'nullable|string',
            'tindakan' => 'nullable|string',
            'resep_obat' => 'nullable|string',
            'tanggal_diperiksa' => 'sometimes|required|date',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $rekamMedis->update($validator->validated());

        return response()->json([
            'message' => 'Rekam medis berhasil diupdate.',
            'data' => $rekamMedis->load('reservasi')
        ]);
    }

    public function destroy(RekamMedis $rekamMedis)
    {
        $rekamMedis->delete();
        return response()->json(['message' => 'Rekam medis berhasil dihapus.'], 200);
    }
}