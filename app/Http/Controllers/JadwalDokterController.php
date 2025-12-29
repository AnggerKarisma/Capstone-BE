<?php

namespace App\Http\Controllers;

use App\Models\JadwalDokter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Exception;

class JadwalDokterController extends Controller
{
    /**
     * Helper: query dasar dengan relasi yang dibutuhkan
     */
    protected function baseQuery()
    {
        return JadwalDokter::with(['dokter', 'poli']);
    }

    /**
     * Helper: query berdasarkan composite key (dokter_id + poli_id)
     */
    protected function queryByDokterPoli($dokterId, $poliId)
    {
        return $this->baseQuery()
            ->where('dokter_id', $dokterId)
            ->where('poli_id', $poliId);
    }

    /**
     * Helper: ubah string kosong jadi null, dan buang field yang tidak perlu
     */
    protected function cleanInput(array $input): array
    {
        // buang field yang tidak boleh di-mass assign
        unset($input['_token'], $input['_method'], $input['dokter'], $input['poli']);

        return array_map(function ($value) {
            return $value === "" ? null : $value;
        }, $input);
    }

    /**
     * Helper: tambahkan audit fields (last_update, last_update_by)
     */
    protected function applyAuditFields(array $data): array
    {
        $data['last_update'] = now();

        $user = Auth::user();
        $data['last_update_by'] = $user
            ? ($user->name ?? $user->Nama ?? 'system')
            : 'system';

        return $data;
    }

    /**
     * GET /api/jadwal-dokter
     * Untuk admin: ambil semua jadwal
     */
    public function index()
    {
        try {
            $jadwal = $this->baseQuery()->get();

            return response()->json([
                'success' => true,
                'data'    => $jadwal,
            ]);
        } catch (Exception $e) {
            Log::error('Error getting jadwal: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mengambil data jadwal',
            ], 500);
        }
    }

    /**
     * GET /api/public/jadwal-dokter/{poli_id}
     * Untuk FE publik: ambil semua jadwal dokter di satu poli
     * FE kamu EXPECT array langsung, jadi di sini kita return array saja.
     */
    public function getByPoli($poli_id)
    {
        try {
            $jadwal = $this->baseQuery()
                ->where('poli_id', $poli_id)
                ->get();

            // FE: setDokters(Array.isArray(res.data) ? res.data : []);
            return response()->json($jadwal);
        } catch (Exception $e) {
            Log::error('Error getByPoli: ' . $e->getMessage(), [
                'poli_id' => $poli_id,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mengambil jadwal dokter',
            ], 500);
        }
    }

    /**
     * GET /api/jadwal-dokter/{dokter_id}/{poli_id}
     * Detail 1 jadwal
     */
    public function show($dokter_id, $poli_id)
    {
        try {
            $jadwal = $this->queryByDokterPoli($dokter_id, $poli_id)->first();

            if (!$jadwal) {
                return response()->json([
                    'success' => false,
                    'message' => 'Jadwal tidak ditemukan',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data'    => $jadwal,
            ]);
        } catch (Exception $e) {
            Log::error('Error getting jadwal detail: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mengambil detail jadwal',
            ], 500);
        }
    }

    /**
     * POST /api/jadwal-dokter
     * Tambah jadwal baru
     */
    public function store(Request $request)
    {
        try {
            $request->validate([
                'dokter_id' => 'required|exists:dokters,dokter_id',
                'poli_id'   => 'required|exists:polis,poli_id',
            ]);

            $exists = JadwalDokter::where('dokter_id', $request->dokter_id)
                ->where('poli_id', $request->poli_id)
                ->exists();

            if ($exists) {
                return response()->json([
                    'success' => false,
                    'message' => 'Jadwal untuk dokter dan poli ini sudah ada',
                ], 422);
            }

            $data = $this->cleanInput($request->all());
            $data = $this->applyAuditFields($data);

            JadwalDokter::create($data);

            $jadwal = $this->queryByDokterPoli($request->dokter_id, $request->poli_id)->first();

            return response()->json([
                'success' => true,
                'message' => 'Jadwal dokter berhasil ditambahkan',
                'data'    => $jadwal,
            ], 201);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors'  => $e->errors(),
            ], 422);
        } catch (Exception $e) {
            Log::error('Error creating jadwal: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat menyimpan jadwal',
            ], 500);
        }
    }

    /**
     * PUT /api/jadwal-dokter/{dokter_id}/{poli_id}
     * Update jadwal
     */
    public function update(Request $request, $dokter_id, $poli_id)
    {
        try {
            $query = $this->queryByDokterPoli($dokter_id, $poli_id);
            $jadwal = $query->first();

            if (!$jadwal) {
                return response()->json([
                    'success' => false,
                    'message' => 'Jadwal tidak ditemukan',
                ], 404);
            }

            $data = $this->cleanInput($request->all());
            $data['dokter_id'] = $dokter_id;
            $data['poli_id']   = $poli_id;
            $data = $this->applyAuditFields($data);

            $query->update($data);

            $updatedData = $this->queryByDokterPoli($dokter_id, $poli_id)->first();

            return response()->json([
                'success' => true,
                'message' => 'Jadwal dokter berhasil diperbarui',
                'data'    => $updatedData,
            ]);
        } catch (Exception $e) {
            Log::error('Error updating jadwal: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * DELETE /api/jadwal-dokter/{dokter_id}/{poli_id}
     * Hapus jadwal
     */
    public function destroy($dokter_id, $poli_id)
    {
        $jadwal = $this->queryByDokterPoli($dokter_id, $poli_id)->first();

        if (!$jadwal) {
            return response()->json([
                'success' => false,
                'message' => 'Jadwal tidak ditemukan',
            ], 404);
        }

        $jadwal->delete();

        return response()->json([
            'success' => true,
            'message' => 'Jadwal dokter berhasil dihapus',
        ]);
    }
}
