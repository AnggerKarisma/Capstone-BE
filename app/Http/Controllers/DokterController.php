<?php

namespace App\Http\Controllers;

use App\Models\Dokter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class DokterController extends Controller
{
    public function index()
    {
        return response()->json(Dokter::all());
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'dokter_id' => 'required|string|max:10|unique:master_dokter',
            'nama_dokter' => 'required|string|max:100',
            'bidang_keahlian' => 'required|string|max:100',
            'tipe' => 'required|char|max:1',
            'aktif' => 'required|char|max:1',
            'flags' => 'required|string|max:10',
            'no_ktp' => 'required|string|max:16',
            'praktek' => 'nullable|string|max:100',
            'last_update' => 'nullable|date',
            'telpon_praktek' => 'nullable|string|max:50',
            'id_satu_sehat' => 'nullable|string|max:255',
            'jenis_kelamin' => 'nullable|char|max:1',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $dokter = Dokter::create($validator->validated());
        return response()->json($dokter, 201);
    }

    public function show(Dokter $dokter)
    {
        return $dokter;
    }

    public function update(Request $request, Dokter $dokter)
    {
        $validator = Validator::make($request->all(), [
            'nama_dokter' => 'sometimes|required|string|max:100',
            'bidang_keahlian' => 'sometimes|required|string|max:100',
            'tipe' => 'sometimes|required|char|max:1',
            'aktif' => 'sometimes|required|char|max:1',
            'flags' => 'sometimes|required|string|max:10',
            'no_ktp' => 'sometimes|required|string|max:16',
            'praktek' => 'nullable|string|max:100',
            'last_update' => 'nullable|date',
            'telpon_praktek' => 'nullable|string|max:50',
            'id_satu_sehat' => 'nullable|string|max:255',
            'jenis_kelamin' => 'nullable|char|max:1',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $dokter->update($validator->validated());
        return response()->json($dokter);
    }

    public function destroy(Dokter $dokter)
    {
        $dokter->delete();
        return response()->json(['message' => 'Dokter berhasil dihapus']);
    }
}
