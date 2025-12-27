<?php

namespace App\Http\Controllers;

use App\Models\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AdminController extends Controller
{
    // GET: tampilkan semua admin
    public function index()
    {
        $admins = Admin::all();
        return response()->json($admins);
    }

    // POST: tambah admin baru
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:admins',
            'password' => 'required|string|min:8',
            'role' => 'required|in:superadmin,admin',
            'poli_id' => 'nullable|exists:polis,poli_id', 
        ]);

        if ($validator->fails()) {
            return response()->json(['success'=>false, 'errors'=>$validator->errors()], 422);
        }

        $admin = Admin::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password),
            'role' => $request->role,
            'poli_id' => $request->poli_id, 
        ]);

        return response()->json(['success'=>true, 'data'=>$admin], 201);
    }

    // GET: tampilkan detail admin tertentu
    public function show($id)
    {
        $admin = Admin::find($id);
        if (!$admin) {
            return response()->json(['message' => 'Admin tidak ditemukan'], 404);
        }
        return response()->json($admin);
    }

    // PUT: update admin
    public function update(Request $request, $id)
    {
        $admin = Admin::find($id);
        
        if (!$admin) {
            return response()->json([
                'success' => false,
                'message' => 'Admin tidak ditemukan'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'Nama'     => 'nullable|string|max:100', 
            'Email'    => 'nullable|email|unique:admins,Email,' . $id . ',adminID',
            'Password' => 'nullable|string|min:6',
            'role'     => 'nullable|in:superadmin,admin', 
            'poli_id'  => 'nullable|exists:polis,poli_id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors'  => $validator->errors()
            ], 422);
        }

        // Siapkan data untuk update
        $dataToUpdate = [
            'Nama'    => $request->Nama ?? $admin->Nama,
            'Email'   => $request->Email ?? $admin->Email,
            'role'    => $request->role ?? $admin->role,
            'poli_id' => $request->has('poli_id') ? $request->poli_id : $admin->poli_id,
        ];

        if ($request->filled('Password')) {
            $dataToUpdate['Password'] = Hash::make($request->Password);
        }

        try {
            $admin->update($dataToUpdate);

            return response()->json([
                'success' => true,
                'message' => 'Data admin berhasil diperbarui',
                'data'    => $admin
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengupdate admin: ' . $e->getMessage()
            ], 500);
        }
    }

    // DELETE: hapus admin
    public function destroy($id)
    {
        $admin = Admin::find($id);
        if (!$admin) {
            return response()->json(['message' => 'Admin tidak ditemukan'], 404);
        }

        $admin->delete();
        return response()->json(['message' => 'Admin berhasil dihapus']);
    }
}
