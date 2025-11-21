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
            'tanggal_diperiksa' => 'required|date',
        ], [
            'reservasi_id.unique' => 'Rekam medis untuk reservasi ini sudah ada.'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        try {
            DB::beginTransaction();

            $rekamMedis = RekamMedis::create($validator->validated());

            $reservasi = Reservation::find($request->reservasi_id);
            if ($reservasi) {
                $reservasi->status = 'selesai'; 
                $reservasi->save();
            }

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

    public function show(RekamMedis $rekamMedi)
    {
        return $rekamMedi->load('reservasi');
    }

    public function update(Request $request, RekamMedis $rekamMedi)
    {
        $validator = Validator::make($request->all(), [
            'no_medrec' => 'sometimes|required|string',
            'gejala' => 'nullable|string',
            'diagnosis' => 'nullable|string',
            'tindakan' => 'nullable|string',
            'tanggal_diperiksa' => 'sometimes|required|date',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $rekamMedi->update($validator->validated());

        return response()->json([
            'message' => 'Rekam medis berhasil diupdate.',
            'data' => $rekamMedi
        ]);
    }

    public function destroy(RekamMedis $rekamMedi)
    {
        $rekamMedi->delete();
        return response()->json(['message' => 'Rekam medis berhasil dihapus.'], 200);
    }
}