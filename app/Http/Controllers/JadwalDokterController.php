<?php

namespace App\Http\Controllers;

use App\Models\JadwalDokter;
use App\Models\Dokter;
use App\Models\Poli;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Exception;

class JadwalDokterController extends Controller
{
    public function index()
    {
        try {
            $jadwal = JadwalDokter::with(['dokter', 'poli'])->get();
            return response()->json([
                'success' => true,
                'data' => $jadwal
            ]);
        } catch (Exception $e) {
            Log::error('Error getting jadwal: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mengambil data jadwal',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function show($dokter_id, $poliID)
    {
        try {
            $jadwal = JadwalDokter::where('dokter_id', $dokter_id)
                ->where('poliID', $poliID)
                ->with(['dokter', 'poli'])
                ->first();

            if (!$jadwal) {
                return response()->json([
                    'success' => false,
                    'message' => 'Jadwal tidak ditemukan'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $jadwal
            ]);
        } catch (Exception $e) {
            Log::error('Error getting jadwal detail: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $request->validate([
                'dokter_id' => 'required|exists:dokters,dokter_id',
                'poliID' => 'required|exists:polis,poliID',
            ]);

            $exists = JadwalDokter::where('dokter_id', $request->dokter_id)
                ->where('poliID', $request->poliID)
                ->exists();

            if ($exists) {
                return response()->json([
                    'success' => false,
                    'message' => 'Jadwal untuk dokter dan poli ini sudah ada'
                ], 422);
            }

            $data = $request->all();

            $data['last_update'] = now();
            $data['last_update_by'] = Auth::user()->name ?? 'system';

            $jadwal = JadwalDokter::create($data);

            $jadwal = JadwalDokter::where('dokter_id', $request->dokter_id)
                ->where('poliID', $request->poliID)
                ->first();

            return response()->json([
                'success' => true,
                'message' => 'Jadwal dokter berhasil ditambahkan',
                'data' => $jadwal
            ], 201);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $e->errors()
            ], 422);
        } catch (Exception $e) {
            Log::error('Error creating jadwal: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat menyimpan jadwal',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $dokter_id, $poliID)
    {
        try {
            $exists = JadwalDokter::where('dokter_id', $dokter_id)
                ->where('poliID', $poliID)
                ->exists();

            if (!$exists) {
                return response()->json([
                    'success' => false,
                    'message' => 'Jadwal tidak ditemukan'
                ], 404);
            }

            $data = $request->all();

            $data['dokter_id'] = $dokter_id;
            $data['poliID'] = $poliID;

            $data['last_update'] = now();
            $data['last_update_by'] = Auth::user()->name ?? 'system';

            JadwalDokter::where('dokter_id', $dokter_id)
                ->where('poliID', $poliID)
                ->update($data);

            $jadwal = JadwalDokter::where('dokter_id', $dokter_id)
                ->where('poliID', $poliID)
                ->first();

            return response()->json([
                'success' => true,
                'message' => 'Jadwal dokter berhasil diperbarui',
                'data' => $jadwal
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $e->errors()
            ], 422);
        } catch (Exception $e) {
            Log::error('Error updating jadwal: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat update jadwal',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy($dokter_id, $poliID)
    {
        $jadwal = JadwalDokter::where('dokter_id', $dokter_id)
            ->where('poliID', $poliID)
            ->first();

        if (!$jadwal) {
            return response()->json(['message' => 'Jadwal tidak ditemukan'], 404);
        }

        $jadwal->delete();

        return response()->json(['message' => 'Jadwal dokter berhasil dihapus']);
    }
}
