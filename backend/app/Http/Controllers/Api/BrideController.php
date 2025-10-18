<?php

namespace App\Http\Controllers\Api;

use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Models\Bride;
use App\Models\Catin;
use App\Models\Pendampingan;
use Illuminate\Http\Request;
use App\Models\Log;
use Illuminate\Support\Facades\Auth;

class BrideController extends Controller
{
    /**
     * Tampilkan semua data pernikahan lengkap dengan pasangan dan pendampingan
     */
    public function index(Request $request)
    {
        $query = Bride::with([
            'catin',
            'catin.pasangan',
            'catin.pendampingan', // ✅ relasi baru ditambahkan
            'catin' => function ($q) {
                $q->select('id', 'id_pasangan', 'nama', 'nik', 'peran', 'pekerjaan', 'tgl_lahir', 'usia', 'no_hp');
            },
            'catin.pasangan' => function ($q) {
                $q->select('id', 'id_pasangan', 'nama', 'nik', 'peran', 'pekerjaan', 'tgl_lahir', 'usia', 'no_hp');
            },
            'catin.pendampingan' => function ($q) {
                $q->select(
                    'id',
                    'jenis',
                    'id_subjek',
                    'tgl_pendampingan',
                    'dampingan_ke',
                    'catatan',
                    'bb',
                    'tb',
                    'lk',
                    'lila',
                    'lika',
                    'hb',
                    'usia',
                    'anemia',
                    'kek',
                    'terpapar_rokok',
                    'jamban_sehat',
                    'punya_jaminan',
                    'keluarga_teredukasi',
                    'mendapatkan_bantuan',
                    'riwayat_penyakit',
                    'ket_riwayat_penyakit',
                    'id_petugas',
                    'created_at'
                );
            }
        ]);

        // Filter opsional
        if ($request->has('nama')) {
            $query->whereHas('catin', function ($q) use ($request) {
                $q->where('nama', 'like', "%{$request->nama}%");
            });
        }

        if ($request->has('tgl_rencana_menikah')) {
            $query->whereDate('tgl_rencana_menikah', $request->tgl_rencana_menikah);
        }

        return response()->json($query->orderByDesc('id')->get());
    }

    /**
     * Simpan data pernikahan baru
     * Sekaligus buat 2 data catin (pria & perempuan)
     */
    public function store(Request $request)
    {
        return DB::transaction(function () use ($request) {
            // simpan catin perempuan
            $catinP = Catin::create([
                'nama' => $request->input('nama_perempuan'),
                'nik' => $request->input('nik_perempuan'),
                'pekerjaan' => $request->input('pekerjaan_perempuan'),
                'tgl_lahir' => $request->input('tgl_lahir_perempuan'),
                'usia' => $request->input('usia_perempuan'),
                'no_hp' => $request->input('hp_perempuan'),
                'peran' => 'istri'
            ]);

            // simpan catin pria
            $catinL = Catin::create([
                'nama' => $request->input('nama_pria'),
                'nik' => $request->input('nik_pria'),
                'pekerjaan' => $request->input('pekerjaan_pria'),
                'tgl_lahir' => $request->input('tgl_lahir_pria'),
                'usia' => $request->input('usia_pria'),
                'no_hp' => $request->input('hp_pria'),
                'peran' => 'suami',
                'id_pasangan' => $catinP->id
            ]);

            // update pasangan perempuan
            $catinP->update(['id_pasangan' => $catinL->id]);

            // simpan data pernikahan
            $bride = Bride::create([
                'id_catin' => $catinP->id,
                'tgl_daftar' => now(),
                'tgl_rencana_menikah' => $request->tgl_rencana_menikah,
                'rencana_tinggal' => $request->rencana_tinggal,
                'catatan' => $request->catatan,
            ]);

            $pendampingan = Pendampingan::create([
                'jenis' => 'Calon Pengantin',
                'id_subjek' => $catinP->id,
                'tgl_pendampingan' => $request->tgl_pendampingan,
                'dampingan_ke' => $request->dampingan_ke,
                'catatan'=> $request->catatan,
                'bb' => $request->bb,
                'tb'=> $request->tb,
                'lila'=> $request->lila,
                'hb'=> $request->hb,
                'usia'=> $request->usia_perempuan,
                'anemia'=> $request->status_hb,
                'kek'=> $request->status_gizi,
                'terpapar_rokok'=> $request->catin_terpapar_rokok,
                'punya_jaminan'=> $request->fasilitas_rujukan,
                'keluarga_teredukasi'=> $request->edukasi,
                'mendapatkan_bantuan'=> $request->pmt,
                'riwayat_penyakit'=> $request->punya_riwayat_penyakit,
                'ket_riwayat_penyakit'=> $request->riwayat_penyakit,
                'id_petugas'=> Auth::id(),
            ]);

            Log::create([
                'id_user'  => Auth::id(),
                'context'  => 'calon pengantin',
                'activity' => 'create',
                'timestamp'=> now(),
            ]);

            return response()->json([
                'message' => 'Data pernikahan berhasil disimpan',
                'data' => [
                    'pernikahan' => $bride,
                    'catin_perempuan' => $catinP,
                    'catin_pria' => $catinL
                ]
            ]);
        });
    }

    /**
     * Tampilkan detail pernikahan berdasarkan ID
     */
    public function show($id)
    {
        $bride = Bride::with([
            'catin',
            'catin.pasangan'
        ])->findOrFail($id);

        return response()->json($bride);
    }

    /**
     * Update data pernikahan dan catin
     */
    public function update(Request $request, $id)
    {
        $bride = Bride::with('catin.pasangan')->findOrFail($id);

        return DB::transaction(function () use ($request, $bride) {
            $bride->update([
                'tgl_rencana_menikah' => $request->tgl_rencana_menikah,
                'rencana_tinggal' => $request->rencana_tinggal,
                'catatan' => $request->catatan,
            ]);

            // update catin perempuan
            $bride->catin->update([
                'nama' => $request->input('perempuan.nama'),
                'nik' => $request->input('perempuan.nik'),
                'pekerjaan' => $request->input('perempuan.pekerjaan'),
                'tgl_lahir' => $request->input('perempuan.tgl_lahir'),
                'usia' => $request->input('perempuan.usia'),
                'no_hp' => $request->input('perempuan.no_hp'),
            ]);

            // update catin pria
            $bride->catin->pasangan->update([
                'nama' => $request->input('pria.nama'),
                'nik' => $request->input('pria.nik'),
                'pekerjaan' => $request->input('pria.pekerjaan'),
                'tgl_lahir' => $request->input('pria.tgl_lahir'),
                'usia' => $request->input('pria.usia'),
                'no_hp' => $request->input('pria.no_hp'),
            ]);

            return response()->json(['message' => 'Data berhasil diperbarui']);
        });
    }

    /**
     * Cek jumlah pendampingan berdasarkan NIK pasangan catin
     */
    public function checkDampinganKe(Request $request)
    {
        $nikP = $request->query('nik_perempuan');
        $nikL = $request->query('nik_pria');

        if (!$nikP || !$nikL) {
            return response()->json(['error' => 'NIK belum lengkap'], 400);
        }

        // Ambil data catin perempuan dan pria
        $catinP = Catin::where('nik', $nikP)->first();
        $catinL = Catin::where('nik', $nikL)->first();

        if (!$catinP || !$catinL) {
            return response()->json([
                'exists' => false,
                'dampingan_ke' => 1,
                'message' => 'Belum ada data pasangan di database'
            ]);
        }

        // Pastikan mereka pasangan valid
        $isCouple = (
            ($catinP->id_pasangan === $catinL->id) ||
            ($catinL->id_pasangan === $catinP->id)
        );

        if (!$isCouple) {
            return response()->json([
                'exists' => false,
                'dampingan_ke' => 1,
                'message' => 'Data ditemukan tapi bukan pasangan valid'
            ]);
        }

        // Hitung total pendampingan sebelumnya
        $total = Pendampingan::where('jenis', 'catin')
            ->whereIn('id_subjek', [$catinP->id, $catinL->id])
            ->count();

        return response()->json([
            'exists' => true,
            'dampingan_ke' => $total + 1,
            'message' => 'Pasangan ditemukan'
        ]);
    }

    public function search(Request $request)
    {
        $nik = $request->nik;

        if (!$nik) {
            return response()->json(['message' => 'NIK wajib diisi.'], 400);
        }

        $query = Bride::with([
            'catin',
            'catin.pasangan',
            'catin.pendampingan',
            'catin' => function ($q) {
                $q->select('id', 'id_pasangan', 'nama', 'nik', 'peran', 'pekerjaan', 'tgl_lahir', 'usia', 'no_hp');
            },
            'catin.pasangan' => function ($q) {
                $q->select('id', 'id_pasangan', 'nama', 'nik', 'peran', 'pekerjaan', 'tgl_lahir', 'usia', 'no_hp');
            },
            'catin.pendampingan' => function ($q) {
                $q->select(
                    'id',
                    'jenis',
                    'id_subjek',
                    'tgl_pendampingan',
                    'dampingan_ke',
                    'catatan',
                    'bb',
                    'tb',
                    'lk',
                    'lila',
                    'lika',
                    'hb',
                    'usia',
                    'anemia',
                    'kek',
                    'terpapar_rokok',
                    'jamban_sehat',
                    'punya_jaminan',
                    'keluarga_teredukasi',
                    'mendapatkan_bantuan',
                    'riwayat_penyakit',
                    'ket_riwayat_penyakit',
                    'id_petugas',
                    'created_at'
                );
            }
        ])
        ->whereHas('catin', function ($q) use ($nik) {
            $q->where('nik', $nik);
        })
        ->orWhereHas('catin.pasangan', function ($q) use ($nik) {
            $q->where('nik', $nik);
        });

        $result = $query->first();

         // ❌ kalau gak ada hasil, kirim 404
        if (!$result) {
            return response()->json(['message' => 'Data tidak ditemukan.'], 404);
        }

        // ✅ kalau ada, kirim data
        return response()->json($result);
    }

}
