<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\profile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
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
        $profileId = $user->profile->id ?? null;

        $validator = Validator::make($request->all(), [
            'lokasi' => 'nullable|string|max:255',
            'jenis_kelamin' => ['nullable', Rule::in(['Laki-laki', 'Perempuan'])],
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
            'nomor_telepon' => ['nullable','string','max:15', Rule::unique('profiles','nomor_telepon')->ignore($profileId)],
            'nomor_pegawai' => ['nullable', 'string', 'max:50', Rule::unique('profiles')->ignore($profileId)],
            'penjaminan' => 'required|in:asuransi,cash',
            'nama_asuransi' => 'required_if:penjaminan,asuransi|nullable|string|max:100',
            'nomor_asuransi' => 'required_if:penjaminan,asuransi|nullable|string|max:50',
        ], [
            'nama_asuransi.required_if' => 'Nama asuransi wajib diisi jika penjaminan adalah asuransi.',
            'nomor_asuransi.required_if' => 'Nomor asuransi wajib diisi jika penjaminan adalah asuransi.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 422);
        }

        $data = $validator->validated();

        if(!isset($data['penjaminan'])){
            $data['penjaminan'] = 'cash';
        }

        $profile = $user->profile()->updateOrCreate(
            ['user_id' => $user->userid],
            $data
        );

        return response()->json([
            'success' => true,
            'message' => 'Profile berhasil dibuat',
            'data' => $profile
        ], 200);
    }
}
