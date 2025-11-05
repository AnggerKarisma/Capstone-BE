<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\profile;
use Illuminate\Support\Facades\Auth;
use Illuminate\validation\Rule;

class ProfileController extends Controller
{
    public function show()
    {
        $user = Auth::user();
        $profile = $user->profile;

        if (!$profile) {
            return response()->json(['message' => 'Profile tidak ditemukan. silahkan lengkapi profil anda'], 404);
        }

        return response()->json([
            'message' => 'Profile berhasil diambil ',
            'data' => $profile
        ]);
    }

    public function store(Request $request)
    {
        $user = Auth::user();

        $validatedData = $request->validate([
            'lokasi' => 'nullable|string|max:255',
            'jenis_kelamin' => ['nullable', Rule::in(['laki-laki', 'Perempuan'])],
            'noKTP' => ['nullable', 'string', 'digits:16', Rule::unique('profiles')->ignore($user->profile->id ?? null, 'id')],
            'suku' => 'nullable|string|max:100',
            'tempat_lahir' => 'nullable|string|max:100',
            'tanggal_lahir' => 'nullable|date',
            'status_keluarga' => 'nullable|string|max:100',
            'nama_keluarga' => 'nullable|string|max:255',
            'agama' => 'nullable|string|max:50',
            'status_perkawinan' => 'nullable|string|max:50',
            'pendidikan_terakhir' => 'nullable|string|max:100',
            'alamat' => 'nullable|string|max:500',
            'provinsi' => 'nullable|string|max:100',
            'kota/kabupaten' => 'nullable|string|max:100',
            'kecamatan' => 'nullable|string|max:100',
            'kelurahan' => 'nullable|string|max:100',
            'nomor_telepon' => 'nullable|string|max:15|unique:profiles,nomor_telepon',
            'nomor_pegawai' => ['nullable', 'string', 'max:50', Rule::unique('profiles')->ignore($user->profile->id ?? null),
        ],
        ]);

        $profile = $user->profile()->updateOrCreate(
            ['user_id' => $user->userid],
            $validatedData
        );

        return response()->json([
            'message' => 'Profile berhasil dibuat',
            'data' => $profile
        ], 200);
    }
}
