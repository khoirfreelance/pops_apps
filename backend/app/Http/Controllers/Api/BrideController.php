<?php

namespace App\Http\Controllers\Api;

use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Models\Bride;
use App\Models\AnggotaKeluarga;
use App\Models\Pendampingan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Log;
use Illuminate\Support\Facades\Auth;

class BrideController extends Controller
{
    /**
     * Tampilkan semua calon pengantin.
     * GET /api/bride
     */
    public function index()
    {
        $data = Bride::all();
        return response()->json($data);
    }

    /**
     * Simpan data calon pengantin baru.
     * POST /api/bride
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'bride.nik' => 'required|string',
            'bride.nama' => 'required|string',
            'bride.no_kk' => 'nullable|string',
            'bride.tgl_lahir' => 'nullable|date',
            'bride.pendidikan' => 'nullable|string',
            'bride.status' => 'nullable|string',
            'bride.pekerjaan' => 'nullable|string',
            'bride.bb' => 'nullable|numeric',
            'bride.tb' => 'nullable|numeric',
            'bride.lila' => 'nullable|numeric',
            'bride.hb' => 'nullable|string',

            'groom.nik' => 'required|string',
            'groom.nama' => 'required|string',
            'groom.no_kk' => 'nullable|string',
            'groom.tgl_lahir' => 'nullable|date',
            'groom.pendidikan' => 'nullable|string',
            'groom.status' => 'nullable|string',
            'groom.pekerjaan' => 'nullable|string',
            'groom.bb' => 'nullable|numeric',
            'groom.tb' => 'nullable|numeric',
        ]);

        DB::beginTransaction();

        try {
            /**
             * 1️⃣ Simpan ke tb_anggota_keluarga (calon pengantin perempuan)
             */
            $anggotaP = AnggotaKeluarga::create([
                'id_keluarga'       => null,
                'nik'               => $validated['bride']['nik'],
                'nama'              => $validated['bride']['nama'],
                'status_hubungan'   => 'Calon Istri',
                'pendidikan'        => $validated['bride']['pendidikan'] ?? null,
                'pekerjaan'         => $validated['bride']['pekerjaan'] ?? null,
                'status_perkawinan' => $validated['bride']['status'] ?? null,
            ]);

            if (!empty($validated['bride']['no_kk'])) {
                // optional: simpan ke tabel kk / keluarga bila ada relasi
                // Keluarga::firstOrCreate(['no_kk' => $validated['bride']['no_kk']]);
            }

            /**
             * 2️⃣ Simpan ke tb_anggota_keluarga (calon pengantin laki-laki)
             */
            $anggotaL = AnggotaKeluarga::create([
                'id_keluarga'       => null,
                'nik'               => $validated['groom']['nik'],
                'nama'              => $validated['groom']['nama'],
                'status_hubungan'   => 'Calon Suami',
                'pendidikan'        => $validated['groom']['pendidikan'] ?? null,
                'pekerjaan'         => $validated['groom']['pekerjaan'] ?? null,
                'status_perkawinan' => $validated['groom']['status'] ?? null,
            ]);

            /**
             * 3️⃣ Simpan ke tb_catin untuk masing-masing
             */
            $catinP = Bride::create([
                'id_user'          => $anggotaP->id,
                'peran'            => 'istri',
                'tgl_daftar'       => now(),
                'status_pernikahan'=> 'belum menikah',
            ]);

            $catinL = Bride::create([
                'id_user'          => $anggotaL->id,
                'id_pasangan'      => $catinP->id, // relasi ke istri
                'peran'            => 'suami',
                'tgl_daftar'       => now(),
                'status_pernikahan'=> 'belum menikah',
            ]);

            // Update pasangan perempuan agar punya id_pasangan juga
            $catinP->update(['id_pasangan' => $catinL->id]);

            /**
             * 4️⃣ Simpan data kesehatan ke tb_pendampingan (masing-masing)
             */
            Pendampingan::create([
                'jenis'        => 'catin',
                'id_subjek'    => $catinP->id,
                'tanggal'      => now(),
                'bb'           => $validated['bride']['bb'] ?? null,
                'tb'           => $validated['bride']['tb'] ?? null,
                'lila'         => $validated['bride']['lila'] ?? null,
                'hb'           => $validated['bride']['hb'] ?? null,
                'id_petugas'   => auth()->id(),
            ]);

            Pendampingan::create([
                'jenis'        => 'catin',
                'id_subjek'    => $catinL->id,
                'tanggal'      => now(),
                'bb'           => $validated['groom']['bb'] ?? null,
                'tb'           => $validated['groom']['tb'] ?? null,
                'id_petugas'   => auth()->id(),
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Data calon pengantin berhasil disimpan',
                'data' => [
                    'istri' => $catinP,
                    'suami' => $catinL
                ]
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal menyimpan data',
                'error' => $th->getMessage()
            ], 500);
        }
    }



    /**
     * Tampilkan detail satu calon pengantin.
     * GET /api/bride/{id}
     */
    public function show($id)
    {
        $bride = Bride::with('pasangan')->find($id);

        if (!$bride) {
            return response()->json([
                'message' => 'Data calon pengantin tidak ditemukan',
            ], 404);
        }

        return response()->json($bride, 200);
    }
}
