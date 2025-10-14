<?php

namespace App\Http\Controllers;

use App\Models\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

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
        $request->validate([
            'Nama' => 'required|string|max:100',
            'Email' => 'required|email|unique:admins,Email',
            'Password' => 'required|string|min:6',
        ]);

        $admin = Admin::create([
            'Nama' => $request->Nama,
            'Email' => $request->Email,
            'Password' => Hash::make($request->Password),
        ]);

        return response()->json($admin, 201);
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
            return response()->json(['message' => 'Admin tidak ditemukan'], 404);
        }

        $request->validate([
            'Nama' => 'string|max:100',
            'Email' => 'email|unique:admins,Email,' . $id . ',adminID',
            'Password' => 'string|min:6',
        ]);

        $admin->update([
            'Nama' => $request->Nama ?? $admin->Nama,
            'Email' => $request->Email ?? $admin->Email,
            'Password' => $request->Password ? Hash::make($request->Password) : $admin->Password,
        ]);

        return response()->json($admin);
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
