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
            'nama_dokter' => 'required|string|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $dokter = Dokter::create($request->only((new \App\Models\Dokter)->getFillable()));
        return response()->json($dokter, 201);
    }

    public function show(Dokter $dokter)
    {
        return $dokter;
    }

    public function update(Request $request, $dokter_id)
    {
        $dokter = Dokter::find($dokter_id);
        if (!$dokter) {
            return response()->json(['message' => 'Dokter tidak ditemukan'], 404);
        }

        $dokter->update($request->all());
        return response()->json($dokter);
    }

    public function destroy(Dokter $dokter)
    {
        $dokter->delete();
        return response()->json(['message' => 'Dokter berhasil dihapus']);
    }
}
