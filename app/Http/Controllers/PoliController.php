<?php

namespace App\Http\Controllers;

use App\Models\Poli;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PoliController extends Controller
{
    // GET: semua poli
    public function index()
    {
        return response()->json(Poli::all());
    }

    // GET: poli berdasarkan ID
    public function show($poli_id)
    {
        $poli = Poli::find($poli_id);
        if (!$poli) {
            return response()->json(['message' => 'Poli tidak ditemukan'], 404);
        }
        return response()->json($poli);
    }

    // POST: tambah poli (oleh SuperAdmin)
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'poli_id' => 'required|string|max:10|unique:polis',
            'poli_name' => 'required|string|max:50',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $poli = Poli::create($request->only((new \App\Models\Poli)->getFillable()));
        return response()->json($poli, 201);
    }

    // PUT: update poli (oleh SuperAdmin)
    public function update(Request $request, $poli_id)
    {
        $poli = Poli::find($poli_id);
        if (!$poli) {
            return response()->json(['message' => 'Poli tidak ditemukan'], 404);
        }

        $poli->update($request->all());
        return response()->json($poli);
    }

    // DELETE: hapus poli (oleh SuperAdmin)
    public function destroy($poli_id)
    {
        $poli = Poli::find($poli_id);
        if (!$poli) {
            return response()->json(['message' => 'Poli tidak ditemukan'], 404);
        }

        $poli->delete();
        return response()->json(['message' => 'Poli berhasil dihapus']);
    }
}