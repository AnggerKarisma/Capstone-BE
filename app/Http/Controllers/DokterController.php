<?php

namespace App\Http\Controllers;

use App\Models\Dokter;
use Illuminate\Http\Request;

class DokterController extends Controller
{
    public function index()
    {
        return response()->json(Dokter::all());
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama' => 'required|string|max:255',
            'spesialis' => 'required|string|max:255',
            'aktif' => 'required|boolean',
            'jenisKelamin' => 'required|in:Laki-laki,Perempuan',
            'SIP' => 'required|string|unique:dokters,SIP',
            'SIPdate' => 'required|date',
        ]);

        $dokter = Dokter::create($request->all());
        return response()->json($dokter, 201);
    }

    public function show($id)
    {
        $dokter = Dokter::findOrFail($id);
        return response()->json($dokter);
    }

    public function update(Request $request, $id)
    {
        $dokter = Dokter::findOrFail($id);

        $request->validate([
            'nama' => 'sometimes|required|string|max:255',
            'spesialis' => 'sometimes|required|string|max:255',
            'aktif' => 'sometimes|required|boolean',
            'jenisKelamin' => 'sometimes|required|in:Laki-laki,Perempuan',
            'SIP' => 'sometimes|required|string|unique:dokters,SIP,' . $id . ',dokterId',
            'SIPdate' => 'sometimes|required|date',
        ]);

        $dokter->update($request->all());
        return response()->json($dokter);
    }

    public function destroy($id)
    {
        $dokter = Dokter::findOrFail($id);
        $dokter->delete();

        return response()->json(['message' => 'Dokter berhasil dihapus']);
    }
}
