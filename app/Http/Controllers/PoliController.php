<?php

namespace App\Http\Controllers;

use App\Models\Poli;
use Illuminate\Http\Request;

class PoliController extends Controller
{
    // GET: semua poli
    public function index()
    {
        return response()->json(Poli::all());
    }

    // GET: poli berdasarkan ID
    public function show($id)
    {
        $poli = Poli::find($id);
        if (!$poli) {
            return response()->json(['message' => 'Poli tidak ditemukan'], 404);
        }
        return response()->json($poli);
    }

    // POST: tambah poli (oleh SuperAdmin)
    public function store(Request $request)
    {
        $request->validate([
            'nama' => 'required|string|max:100',
            'tipeLayanan' => 'required|string|max:100',
            'tipePoli' => 'required|string|max:100',
        ]);

        // Ambil superAdminID dari user yang sedang login
        $poli = Poli::create([
            'nama' => $request->nama,
            'tipeLayanan' => $request->tipeLayanan,
            'tipePoli' => $request->tipePoli,
            'superAdminID' => $request->user()->adminID, // ← Ambil dari user yang login
        ]);

        return response()->json($poli, 201);
    }

    // PUT: update poli (oleh SuperAdmin)
    public function update(Request $request, $id)
    {
        $poli = Poli::find($id);
        if (!$poli) {
            return response()->json(['message' => 'Poli tidak ditemukan'], 404);
        }

        $poli->update($request->all());
        return response()->json($poli);
    }

    // DELETE: hapus poli (oleh SuperAdmin)
    public function destroy($id)
    {
        $poli = Poli::find($id);
        if (!$poli) {
            return response()->json(['message' => 'Poli tidak ditemukan'], 404);
        }

        $poli->delete();
        return response()->json(['message' => 'Poli berhasil dihapus']);
    }
}