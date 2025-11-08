<?php

namespace App\Http\Controllers;

use App\Models\PenanggungJawab;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class PenanggungJawabController extends Controller
{

    public function index()
    {
        return response()->json(PenanggungJawab::all());
    }


    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nama' => 'required|string|max:255',
            'nomor_whatsapp' => 'required|string|max:20|unique:penanggung_jawabs,nomor_whatsapp',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $penanggungJawab = PenanggungJawab::create($request->all());
        
        return response()->json([
            'success' => true,
            'message' => 'Penanggung jawab berhasil dibuat',
            'data' => $penanggungJawab
        ], 201);
    }

    public function show(PenanggungJawab $penanggungJawab)
    {
        return response()->json(['success' => true, 'data' => $penanggungJawab]);
    }


    public function update(Request $request, PenanggungJawab $penanggungJawab)
    {
        $validator = Validator::make($request->all(), [
            'nama' => 'sometimes|required|string|max:255',
            'nomor_whatsapp' => 'sometimes|required|string|max:20|unique:penanggung_jawabs,nomor_whatsapp,' . $penanggungJawab->PjId . ',PjId',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $penanggungJawab->update($request->all());
        
        return response()->json([
            'success' => true,
            'message' => 'Penanggung jawab berhasil diupdate',
            'data' => $penanggungJawab
        ]);
    }

    
    public function destroy(PenanggungJawab $penanggungJawab)
    {
        try {
            $penanggungJawab->delete();
            return response()->json(['success' => true, 'message' => 'Penanggung Jawab berhasil dihapus.']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Gagal menghapus: Penanggung jawab mungkin masih terikat data lain.'], 409);
        }
    }
}
