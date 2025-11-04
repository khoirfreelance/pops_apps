<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Pregnancy;
use Carbon\Carbon;
use App\Models\Log;
use Illuminate\Support\Facades\Auth;

class PregnancyController extends Controller
{
    public function index(Request $request)
    {
        try {
            $user = Auth::user();

            // ✅ Wilayah user
            $wilayah = [
                'kelurahan' => $user->kelurahan ?? null,
                'kecamatan' => $user->kecamatan ?? null,
                'kota' => $user->kota ?? null,
                'provinsi' => $user->provinsi ?? null,
            ];

            // ✅ Base query
            $query = Pregnancy::query();

            // Filter otomatis berdasarkan wilayah user
            if (!empty($wilayah['kelurahan'])) {
                $query->where('kelurahan', $wilayah['kelurahan']);
            }

            // ✅ Filter berdasarkan periode tanggal pemeriksaan
            if ($request->filled('periodeAwal') && $request->filled('periodeAkhir')) {
                $query->whereBetween('tanggal_pemeriksaan_terakhir', [
                    $request->periodeAwal,
                    $request->periodeAkhir,
                ]);
            }

            // ✅ Filter status KEK
            if ($request->filled('kek') && is_array($request->kek)) {
                $query->where(function ($q) use ($request) {
                    foreach ($request->kek as $val) {
                        $q->orWhere('status_gizi_lila', 'like', "%{$val}%");
                    }
                });
            }

            // ✅ Filter status Anemia
            if ($request->filled('anemia') && is_array($request->anemia)) {
                $query->where(function ($q) use ($request) {
                    foreach ($request->anemia as $val) {
                        $q->orWhere('status_gizi_hb', 'like', "%{$val}%");
                    }
                });
            }

            // ✅ Filter status Berisiko (status kehamilan)
            if ($request->filled('beresiko') && is_array($request->beresiko)) {
                $query->where(function ($q) use ($request) {
                    foreach ($request->beresiko as $val) {
                        $q->orWhere('status_kehamilan', 'like', "%{$val}%");
                    }
                });
            }

            // ✅ Filter usia ibu
            if ($request->filled('usia') && is_array($request->usia)) {
                $query->where(function ($q) use ($request) {
                    foreach ($request->usia as $range) {
                        if (str_contains($range, '-')) {
                            [$min, $max] = explode('-', $range);
                            $q->orWhereBetween('usia_ibu', [(int) $min, (int) $max]);
                        } elseif ($range === '40+') {
                            $q->orWhere('usia_ibu', '>=', 40);
                        }
                    }
                });
            }

            // ✅ Filter intervensi (misal ada kolom intervensi)
            if ($request->filled('intervensi') && is_array($request->intervensi)) {
                $query->where(function ($q) use ($request) {
                    foreach ($request->intervensi as $val) {
                        $q->orWhere('intervensi', 'like', "%{$val}%");
                    }
                });
            }

            // ✅ Ambil data dari DB
            $data = $query
                ->orderByDesc('tanggal_pemeriksaan_terakhir')
                ->get();

            if ($data->isEmpty()) {
                return response()->json([
                    'message' => 'Tidak ada data kehamilan ditemukan.',
                    'data' => [],
                ], 200);
            }

            // ✅ Group berdasarkan nik_ibu
            $grouped = $data->groupBy('nik_ibu')->map(function ($group) {
                $latest = $group->sortByDesc('tanggal_pemeriksaan_terakhir')->first();

                // kumpulkan riwayat pemeriksaan (ascending by tanggal)
                $riwayat = $group
                    ->sortBy('tanggal_pemeriksaan_terakhir')
                    ->map(function ($g) {
                        return [
                            'tanggal_pemeriksaan_terakhir' => $g->tanggal_pemeriksaan_terakhir,
                            'berat_badan' => $g->berat_badan,
                            'tinggi_badan' => $g->tinggi_badan,
                            'imt' => $g->imt,
                            'kadar_hb' => $g->kadar_hb,
                            'status_gizi_hb' => $g->status_gizi_hb,
                            'lila' => $g->lila,
                            'status_gizi_lila' => $g->status_gizi_lila,
                            'usia_kehamilan_minggu' => $g->usia_kehamilan_minggu,
                        ];
                    })
                    ->values();

                return [
                    'nama_ibu' => $latest->nama_ibu,
                    'nik_ibu' => $latest->nik_ibu,
                    'usia_ibu' => $latest->usia_ibu,
                    'nama_suami' => $latest->nama_suami,
                    'nik_suami' => $latest->nik_suami,
                    'kehamilan_ke' => $latest->kehamilan_ke,
                    'jumlah_anak' => $latest->jumlah_anak,
                    'status_kehamilan' => $latest->status_kehamilan,
                    'provinsi' => $latest->provinsi,
                    'kota' => $latest->kota,
                    'kecamatan' => $latest->kecamatan,
                    'kelurahan' => $latest->kelurahan,
                    'rt' => $latest->rt,
                    'rw' => $latest->rw,
                    'riwayat_pemeriksaan' => $riwayat,
                ];
            });

            // ✅ Response akhir
            return response()->json([
                'total' => $grouped->count(),
                'data' => $grouped->values(),
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Gagal mengambil data kehamilan',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function import(Request $request)
    {
        // ✅ Validasi file CSV
        $validator = Validator::make($request->all(), [
            'file' => 'required|mimes:csv,txt|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validasi gagal',
                'errors' => $validator->errors(),
            ], 422);
        }

        $file = $request->file('file');
        if (!$file->isValid()) {
            return response()->json(['message' => 'File tidak valid.'], 400);
        }

        $path = $file->getRealPath();
        $handle = fopen($path, 'r');

        $header = null;
        $rows = [];
        $count = 0;

        while (($data = fgetcsv($handle, 1000, ',')) !== false) {
            // Lewati baris kosong
            if (empty($data[0])) continue;

            // Baris pertama dianggap header
            if (!$header) {
                $header = $data;
                continue;
            }

            $count++;

            // ✅ Mapping kolom CSV ke field sesuai model Pregnancy
            $rows[] = [
                'nama_petugas' => $data[0] ?? null,
                'tanggal_pendampingan' => $this->convertDate($data[1] ?? null),
                'nama_ibu' => $data[2] ?? null,
                'nik_ibu' => $data[3] ?? null,
                'usia_ibu' => $data[4] ?? null,
                'nama_suami' => $data[5] ?? null,
                'nik_suami' => $data[6] ?? null,
                'pekerjaan_suami' => $data[7] ?? null,
                'usia_suami' => $data[8] ?? null,
                'kehamilan_ke' => $data[9] ?? null,
                'jumlah_anak' => $data[10] ?? null,
                'status_kehamilan' => $data[11] ?? null,
                'riwayat_4t' => $data[12] ?? null,
                'riwayat_penggunaan_kb' => $data[13] ?? null,
                'riwayat_ber_kontrasepsi' => $data[14] ?? null,
                'provinsi' => $data[15] ?? null,
                'kota' => $data[16] ?? null,
                'kecamatan' => $data[17] ?? null,
                'kelurahan' => $data[18] ?? null,
                'rt' => $data[19] ?? null,
                'rw' => $data[20] ?? null,
                'tanggal_pemeriksaan_terakhir' => $this->convertDate($data[21] ?? null),
                'berat_badan' => $data[22] ?? null,
                'tinggi_badan' => $data[23] ?? null,
                'imt' => $data[24] ?? null,
                'kadar_hb' => $data[25] ?? null,
                'status_gizi_hb' => $data[26] ?? null,
                'lila' => $data[27] ?? null,
                'status_gizi_lila' => $data[28] ?? null,
                'riwayat_penyakit' => $data[29] ?? null,
                'usia_kehamilan_minggu' => $data[30] ?? null,
                'taksiran_berat_janin' => $data[31] ?? null,
                'tinggi_fundus' => $data[32] ?? null,
                'hpl' => $this->convertDate($data[33] ?? null),
                'terpapar_asap_rokok' => $data[34] ?? null,
                'mendapat_ttd' => $data[35] ?? null,
                'menggunakan_jamban' => $data[36] ?? null,
                'menggunakan_sab' => $data[37] ?? null,
                'fasilitas_rujukan' => $data[38] ?? null,
                'riwayat_keguguran_iufd' => $data[39] ?? null,
                'mendapat_kie' => $data[40] ?? null,
                'mendapat_bantuan_sosial' => $data[41] ?? null,
                'rencana_tempat_melahirkan' => $data[42] ?? null,
                'rencana_asi_eksklusif' => $data[43] ?? null,
                'rencana_tinggal_setelah' => $data[44] ?? null,
                'rencana_kontrasepsi' => $data[44] ?? null,
            ];
        }

        fclose($handle);

        // ✅ Simpan ke DB (pakai insert batch)
        if (!empty($rows)) {
            foreach ($rows as $row) {
                Pregnancy::create($row);

                // ✅ Simpan atau ambil wilayah
                $wilayah = \App\Models\Wilayah::firstOrCreate([
                    'provinsi' => $row['provinsi'],
                    'kota' => $row['kota'],
                    'kecamatan' => $row['kecamatan'],
                    'kelurahan' => $row['kelurahan'],
                ]);

                // ✅ Log tiap baris (optional, bisa dihapus kalau terlalu banyak)
                Log::create([
                    'id_user'   => Auth::id(),
                    'context'   => 'Ibu Hamil',
                    'activity'  => 'Import data kehamilan ibu ' . ($row['nama_ibu'] ?? '-'),
                    'timestamp' => now(),
                ]);
            }
        }

        return response()->json([
            'message' => "Berhasil import {$count} data kehamilan",
            'count' => $count,
        ], 200);
    }

    private function convertDate($date)
    {
        if (!$date) return null;
        $date = str_replace('/', '-', trim($date));
        try {
            return Carbon::parse($date)->format('Y-m-d');
        } catch (\Exception $e) {
            return null;
        }
    }

    public function status(Request $request)
    {
        try {
            $user = Auth::user();

            // ✅ Wilayah user (opsional)
            $wilayah = [
                'kelurahan' => $user->kelurahan ?? null,
                'kecamatan' => $user->kecamatan ?? null,
                'kota' => $user->kota ?? null,
                'provinsi' => $user->provinsi ?? null,
            ];

            // ✅ Ambil data Pregnancy (filter per kelurahan jika tersedia)
            $query = Pregnancy::query();

            if (!empty($wilayah['kelurahan'])) {
                $query->where('kelurahan', $wilayah['kelurahan']);
            }

            $data = $query->get();

            if ($data->isEmpty()) {
                return response()->json([
                    'total' => 0,
                    'counts' => [],
                    'kelurahan' => $wilayah['kelurahan'] ?? '-',
                ]);
            }

            // ✅ Grouping berdasarkan nik_ibu
            $grouped = $data->groupBy('nik_ibu')->map(function ($group) {
                // Ambil record terakhir per ibu berdasarkan tanggal pemeriksaan
                return $group->sortByDesc('tanggal_pemeriksaan_terakhir')->first();
            });

            $groupedData = $grouped->values();
            $total = $groupedData->count();

            // ✅ Hitung status kesehatan dengan pencarian "contains" (tidak eksak)
            $count = [
                'KEK' => $groupedData->filter(fn($item) =>
                    stripos($item->status_gizi_lila, 'kek') !== false
                )->count(),

                'Anemia' => $groupedData->filter(fn($item) =>
                    stripos($item->status_gizi_hb, 'anemia') !== false
                )->count(),

                'Berisiko' => $groupedData->filter(fn($item) =>
                    stripos($item->status_kehamilan, 'berisiko') !== false
                )->count(),

                'Normal' => $groupedData->filter(fn($item) =>
                    stripos($item->status_kehamilan, 'normal') !== false
                )->count(),
            ];

            // ✅ Hitung persen dan format hasil
            $result = [];
            foreach ($count as $key => $val) {
                $percent = $total > 0 ? round(($val / $total) * 100, 1) : 0;
                $result[] = [
                    'title' => $key,
                    'value' => $val,
                    'percent' => $percent,
                    'color' => match($key) {
                        'KEK' => 'danger',
                        'Anemia' => 'warning',
                        'Berisiko' => 'violet',
                        'Normal' => 'success',
                        default => 'secondary'
                    }
                ];
            }

            // ✅ Response ke frontend
            return response()->json([
                'total' => $total,
                'counts' => $result,
                'kelurahan' => $wilayah['kelurahan'] ?? '-',
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Gagal mengambil data status kehamilan',
                'error' => $e->getMessage(),
            ], 500);
        }
    }



}
