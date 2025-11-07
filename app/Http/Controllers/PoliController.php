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
    public function show(Poli $poli)
    {
        return $poli;
    }

    // POST: tambah poli (oleh SuperAdmin)
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'poli_id' => 'required|string|max:10|unique:poli', // 
            'poli_name' => 'required|string|max:50', // 
            // Tambahkan validasi lain jika perlu
            'tipe_layanan' => 'nullable|char|max:1', // 
            'tipe_poli' => 'nullable|string|max:5', // 
            'aktif' => 'nullable|string|max:5', // 
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $poli = Poli::create($validator->validated());
        return response()->json($poli, 201);
    }

    // PUT: update poli (oleh SuperAdmin)
    public function update(Request $request, Poli $poli)
    {
        $validator = Validator::make($request->all(), [
            'poli_name' => 'sometimes|required|string|max:50', // 
            // Tambahkan validasi lain
            'tipe_layanan' => 'nullable|char|max:1', // 
            'tipe_poli' => 'nullable|string|max:5', // 
            'aktif' => 'nullable|string|max:5', // 
            'kepala' => 'nullable|string|max:60', // 
            'update_date' => 'nullable|date', // 
            'update_by' => 'nullable|string|max:20', // 
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $poli->update($validator->validated());
        return response()->json($poli);
    }

    // DELETE: hapus poli (oleh SuperAdmin)
    public function destroy(Poli $poli)
    {
        $poli->delete();
        return response()->json(['message' => 'Poli berhasil dihapus']);
    }
}