<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    // GET: Ambil semua user (beserta profilenya)
    public function index()
    {
        $users = User::with('profile')->orderByDesc('created_at')->get();

        return response()->json([
            'success' => true,
            'message' => 'List semua user berhasil diambil',
            'data' => $users
        ], 200);
    }

    // PUT: Update data user oleh Superadmin
    public function update(Request $request, $id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json(['success' => false, 'message' => 'User tidak ditemukan'], 404);
        }

        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
      
            'email' => [
                'required', 
                'email', 
                Rule::unique('users', 'email')->ignore($id, 'userid') 
            ],

            'nomor_telepon' => [
                'required', 
                'string', 
                Rule::unique('users', 'nomor_telepon')->ignore($id, 'userid')
            ],
            
            'password' => 'nullable|string|min:6'
        ]);

        // Update data dasar
        $user->name = $request->name;
        $user->email = $request->email;
        $user->nomor_telepon = $request->nomor_telepon;

        // Jika password diisi, update passwordnya
        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }

        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'Data user berhasil diperbarui',
            'data' => $user
        ], 200);
    }

    // DELETE: Hapus user
    public function destroy($id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json(['success' => false, 'message' => 'User tidak ditemukan'], 404);
        }

        $user->delete();

        return response()->json([
            'success' => true,
            'message' => 'User berhasil dihapus permanen'
        ], 200);
    }
    
    // GET: Ambil detail 1 user
    public function show($id)
    {
        $user = User::with('profile')->find($id);
        
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'User tidak ditemukan'], 404);
        }

        return response()->json(['success' => true, 'data' => $user], 200);
    }
}