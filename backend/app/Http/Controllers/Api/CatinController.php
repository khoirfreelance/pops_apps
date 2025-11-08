<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Support\Str;


use App\Models\Catin;
use App\Models\Wilayah;
use App\Models\Posyandu;
use App\Models\Log;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\IOFactory;

class CatinController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();

        $wilayah = [
            'kelurahan' => $user->kelurahan ?? null,
            'kecamatan' => $user->kecamatan ?? null,
            'kota' => $user->kota ?? null,
            'provinsi' => $user->provinsi ?? null,
        ];

        $query = Catin::query();

        // ✅ Filter wilayah otomatis
        if (!empty($wilayah['kelurahan'])) {
            $query->where('kelurahan', $wilayah['kelurahan']);
        }

        // ✅ Filter berdasarkan periode (format: "Juni 2025" dalam Bahasa Indonesia)
        if ($request->filled('periodeAwal') && $request->filled('periodeAkhir')) {
            // Daftar bulan dalam Bahasa Indonesia
            $bulanMap = [
                'januari' => 1,
                'februari' => 2,
                'maret' => 3,
                'april' => 4,
                'mei' => 5,
                'juni' => 6,
                'juli' => 7,
                'agustus' => 8,
                'september' => 9,
                'oktober' => 10,
                'november' => 11,
                'desember' => 12,
            ];

            // Fungsi bantu untuk konversi teks ke tanggal
            $parsePeriode = function ($periode) use ($bulanMap) {
                $parts = explode(' ', trim(strtolower($periode))); // ex: ["maret", "2025"]
                if (count($parts) === 2 && isset($bulanMap[$parts[0]])) {
                    $bulan = $bulanMap[$parts[0]];
                    $tahun = (int) $parts[1];
                    return Carbon::createFromDate($tahun, $bulan, 1);
                }
                // fallback: coba parse langsung
                return Carbon::createFromDate($tahun, $bulan, 1);
            };

            $start = $parsePeriode($request->periodeAwal)->startOfMonth()->format('Y-m-d');
            $end = $parsePeriode($request->periodeAkhir)->endOfMonth()->format('Y-m-d');

            $query->whereBetween('tanggal_pendampingan', [$start, $end]);
        }

        // ✅ Filter status (KEK, Anemia, Risiko ≠ Normal)
        if ($request->filled('status') && is_array($request->status)) {
            $query->where(function ($q) use ($request) {
                foreach ($request->status as $status) {
                    $statusLower = strtolower($status);
                    if (str_contains($statusLower, 'kek')) {
                        $q->orWhere('status_kek', 'like', '%kek%');
                    } elseif (str_contains($statusLower, 'anemia')) {
                        $q->orWhere('status_hb', 'like', '%anemia%');
                    } elseif (str_contains($statusLower, 'risiko')) {
                        $q->orWhere(function ($sub) {
                            $sub->where('status_risiko', 'not like', '%normal%')
                                ->orWhereNull('status_risiko');
                        });
                    }
                }
            });
        }

        // ✅ Filter usia ibu
        if ($request->filled('usia') && is_array($request->usia)) {
            $query->where(function ($q) use ($request) {
                foreach ($request->usia as $range) {
                    $range = trim($range);
                    if ($range === '<20') {
                        $q->orWhere('usia_perempuan', '<', 20);
                    } elseif ($range === '>40') {
                        $q->orWhere('usia_perempuan', '>', 40);
                    } elseif (str_contains($range, '-')) {
                        [$min, $max] = explode('-', $range);
                        $q->orWhereBetween('usia_perempuan', [(int)$min, (int)$max]);
                    }
                }
            });
        }

        // ✅ Ambil data
        $data = $query->orderByDesc('tanggal_pendampingan')->get();

        if ($data->isEmpty()) {
            return response()->json([
                'message' => 'Tidak ada data catin ditemukan.',
                'data' => [],
            ], 200);
        }

        // ✅ Group data per ibu
        $grouped = $data->groupBy('nik_perempuan')->map(function ($group) {
            $latest = $group->sortByDesc('tanggal_pendampingan')->first();
            $riwayat = $group->sortBy('tanggal_pendampingan')->map(function ($g) {
                return [
                    'tanggal_pendampingan' => $g->tanggal_pendampingan,
                    'berat_perempuan' => $g->berat_perempuan,
                    'tinggi_perempuan' => $g->tinggi_perempuan,
                    'imt' => $g->imt,
                    'hb_perempuan  ' => $g->hb_perempuan,
                    'status_hb' => $g->status_hb,
                    'lila_perempuan' => $g->lila_perempuan,
                    'status_kek' => $g->status_kek,
                    'posyandu' => $g->posyandu,
                    'status_risiko' => $g->status_risiko
                ];
            })->values();

            return [
                'nama_perempuan' => $latest->nama_perempuan,
                'nama_laki' => $latest->nama_laki,
                'usia_perempuan' => $latest->usia_perempuan,
                'usia_laki' => $latest->nik_laki,
                'pekerjaan_perempuan' => $latest->pekerjaan_perempuan,
                'pekerjaan_laki' => $latest->pekerjaan_laki,
                'posyandu' => $latest->posyandu,
                'rt' => $latest->rt,
                'rw' => $latest->rw,
                'tgl_menikah' => $latest->tgl_menikah,
                'riwayat_penyakit' => $latest->riwayat_penyakit,
                'sumber_air_bersih' => $latest->sumber_air_bersih,
                'jamban_sehat' => $latest->jamban_sehat,
                'riwayat_pemeriksaan' => $riwayat,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }

    public function show($id)
    {
        $data = Catin::find($id);

        if (!$data) {
            return response()->json(['success' => false, 'message' => 'Data tidak ditemukan'], 404);
        }

        return response()->json(['success' => true, 'data' => $data]);
    }

    public function import(Request $request)
    {
        // ✅ Validasi file Excel
        $validator = Validator::make($request->all(), [
            'file' => 'required|mimes:xlsx,xls,csv|max:5120', // bisa Excel atau CSV
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

        try {
            $spreadsheet = IOFactory::load($file->getRealPath());
            $sheet = $spreadsheet->getActiveSheet();
            $rows = [];
            $count = 0;

            $data = $sheet->toArray(null, true, true, true);
            $headerSkipped = false;

            foreach ($data as $row) {
                // Skip header
                if (!$headerSkipped) {
                    $headerSkipped = true;
                    continue;
                }

                if (empty($row['A'])) continue;
                $count++;

                $rows[] = [
                    'nama_petugas' => $row['A'] ?? null,
                    'tanggal_pendampingan' => $this->convertDate($row['B'] ?? null),

                    'nama_perempuan' => $row['C'] ?? null,
                    'nik_perempuan' => $row['D'] ?? null,
                    'pekerjaan_perempuan' => $row['E'] ?? null,
                    'usia_perempuan' => $row['F'] ?? null,
                    'hp_perempuan' => $row['G'] ?? null,

                    'nama_laki' => $row['H'] ?? null,
                    'nik_laki' => $row['I'] ?? null,
                    'pekerjaan_laki' => $row['J'] ?? null,
                    'usia_laki' => $row['K'] ?? null,
                    'hp_laki' => $row['L'] ?? null,

                    'pernikahan_ke' => $row['M'] ?? null,

                    'provinsi' => $row['N'] ?? null,
                    'kota' => $row['O'] ?? null,
                    'kecamatan' => $row['P'] ?? null,
                    'kelurahan' => $row['Q'] ?? null,
                    'rw' => $row['R'] ?? null,
                    'rt' => $row['S'] ?? null,
                    'posyandu' => $row['T'] ?? null,

                    'tanggal_pemeriksaan' => $this->convertDate($row['U'] ?? null),
                    'berat_perempuan' => $row['V'] ?? null,
                    'tinggi_perempuan' => $row['W'] ?? null,
                    'hb_perempuan' => $row['X'] ?? null,
                    'lila_perempuan' => $row['Y'] ?? null,

                    'terpapar_rokok' => $this->toBool($row['Z'] ?? null),
                    'mendapat_ttd' => $this->toBool($row['AA'] ?? null),
                    'menggunakan_jamban' => $this->toBool($row['AB'] ?? null),
                    'sumber_air_bersih' => $this->toBool($row['AC'] ?? null),
                    'punya_riwayat_penyakit' => $this->toBool($row['AD'] ?? null),
                    'riwayat_penyakit' => $row['AE'] ?? null,
                    'mendapat_fasilitas_rujukan' => $this->toBool($row['AF'] ?? null),
                    'mendapat_kie' => $this->toBool($row['AG'] ?? null),
                    'mendapat_bantuan_pmt' => $this->toBool($row['AH'] ?? null),

                    'tanggal_rencana_menikah' => $this->convertDate($row['AI'] ?? null),
                    'rencana_tinggal' => $row['AJ'] ?? null,
                ];
            }

            if (empty($rows)) {
                return response()->json(['message' => 'Tidak ada data untuk diimport'], 400);
            }

            // ✅ Simpan ke DB
            DB::beginTransaction();
            foreach ($rows as $row) {
                $imt = $this->hitungIMT($row['berat_perempuan'], $row['tinggi_perempuan']);
                $status_kek = $this->statusKEK($row['lila_perempuan']);
                $status_hb = $this->statusHB($row['hb_perempuan']);
                $status_risiko = $this->statusRisiko($row['usia_perempuan'], $row['usia_laki']);

                $catin = Catin::create(array_merge($row, [
                    'imt_perempuan' => $imt,
                    'status_kek' => $status_kek,
                    'status_hb' => $status_hb,
                    'status_risiko' => $status_risiko,
                ]));

                $wilayah = Wilayah::firstOrCreate([
                    'provinsi' => $row['provinsi'],
                    'kota' => $row['kota'],
                    'kecamatan' => $row['kecamatan'],
                    'kelurahan' => $row['kelurahan'],
                ]);

                Posyandu::firstOrCreate([
                    'nama_posyandu' => $row['posyandu'] ?? '-',
                    'id_wilayah' => $wilayah->id,
                    'rt' => $row['rt'] ?? null,
                    'rw' => $row['rw'] ?? null,
                ]);

                Log::create([
                    'id_user'   => Auth::id(),
                    'context'   => 'Catin',
                    'activity'  => 'Import data calon pengantin ' . ($row['nama_perempuan'] ?? '-'),
                    'timestamp' => now(),
                ]);
            }

            DB::commit();

            return response()->json([
                'message' => "Berhasil import {$count} data calon pengantin",
                'count' => $count,
            ], 200);

        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Gagal import data',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /* public function import(Request $request)
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
            if (empty($data[0])) continue;

            // baris pertama dianggap header
            if (!$header) {
                $header = $data;
                continue;
            }

            $count++;

            // ✅ Mapping kolom CSV ke field sesuai model Catin
            $rows[] = [
                'nama_petugas' => $data[0] ?? null,
                'tanggal_pendampingan' => $this->convertDate($data[1] ?? null),

                'nama_perempuan' => $data[2] ?? null,
                'nik_perempuan' => $data[3] ?? null,
                'pekerjaan_perempuan' => $data[4] ?? null,
                'usia_perempuan' => $data[5] ?? null,
                'hp_perempuan' => $data[6] ?? null,

                'nama_laki' => $data[7] ?? null,
                'nik_laki' => $data[8] ?? null,
                'pekerjaan_laki' => $data[9] ?? null,
                'usia_laki' => $data[10] ?? null,
                'hp_laki' => $data[11] ?? null,

                'pernikahan_ke' => $data[12] ?? null,

                'provinsi' => $data[13] ?? null,
                'kota' => $data[14] ?? null,
                'kecamatan' => $data[15] ?? null,
                'kelurahan' => $data[16] ?? null,
                'rw' => $data[17] ?? null,
                'rt' => $data[18] ?? null,
                'posyandu' => $data[19] ?? null,

                'tanggal_pemeriksaan' => $this->convertDate($data[20] ?? null),
                'berat_perempuan' => $data[21] ?? null,
                'tinggi_perempuan' => $data[22] ?? null,
                'hb_perempuan' => $data[23] ?? null,
                'lila_perempuan' => $data[24] ?? null,

                'terpapar_rokok' => $this->toBool($data[25] ?? null),
                'mendapat_ttd' => $this->toBool($data[26] ?? null),
                'menggunakan_jamban' => $this->toBool($data[27] ?? null),
                'sumber_air_bersih' => $this->toBool($data[28] ?? null),
                'punya_riwayat_penyakit' => $this->toBool($data[28] ?? null),
                'riwayat_penyakit' => $data[29] ?? null,
                'mendapat_fasilitas_rujukan' => $this->toBool($data[30] ?? null),
                'mendapat_kie' => $this->toBool($data[31] ?? null),
                'mendapat_bantuan_pmt' => $this->toBool($data[32] ?? null),

                'tanggal_rencana_menikah' => $this->convertDate($data[33] ?? null),
                'rencana_tinggal' => $data[34] ?? null,
            ];
        }

        fclose($handle);

        // ✅ Simpan ke DB
        if (!empty($rows)) {
            DB::beginTransaction();
            try {
                foreach ($rows as $row) {
                    // Perhitungan otomatis
                    $imt = $this->hitungIMT($row['berat_perempuan'], $row['tinggi_perempuan']);
                    $status_kek = $this->statusKEK($row['lila_perempuan']);
                    $status_hb = $this->statusHB($row['hb_perempuan']);
                    $status_risiko = $this->statusRisiko($row['usia_perempuan'], $row['usia_laki']);

                    $catin = Catin::create(array_merge($row, [
                        'imt_perempuan' => $imt,
                        'status_kek' => $status_kek,
                        'status_hb' => $status_hb,
                        'status_risiko' => $status_risiko,
                    ]));

                    // ✅ Simpan atau ambil wilayah & posyandu
                    $wilayah = Wilayah::firstOrCreate([
                        'provinsi' => $row['provinsi'],
                        'kota' => $row['kota'],
                        'kecamatan' => $row['kecamatan'],
                        'kelurahan' => $row['kelurahan'],
                    ]);

                    Posyandu::firstOrCreate([
                        'nama_posyandu' => $row['posyandu'] ?? '-',
                        'id_wilayah' => $wilayah->id,
                        'rt' => $row['rt'] ?? null,
                        'rw' => $row['rw'] ?? null,
                    ]);

                    Log::create([
                        'id_user'   => Auth::id(),
                        'context'   => 'Catin',
                        'activity'  => 'Import data calon pengantin ' . ($row['nama_perempuan'] ?? '-'),
                        'timestamp' => now(),
                    ]);
                }

                DB::commit();

                return response()->json([
                    'message' => "Berhasil import {$count} data calon pengantin",
                    'count' => $count,
                ], 200);
            } catch (\Throwable $e) {
                DB::rollBack();
                return response()->json([
                    'message' => 'Gagal import data',
                    'error' => $e->getMessage(),
                ], 500);
            }
        }

        return response()->json(['message' => 'Tidak ada data untuk diimport'], 400);
    } */

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

    private function toBool($val)
    {
        return in_array(strtolower(trim($val)), ['ya', 'y', 'true', '1']) ? true : false;
    }

    private function hitungIMT($berat, $tinggi)
    {
        if (empty($berat) || empty($tinggi)) return null;

        // kalau tinggi kemungkinan cm, valid range antara 100-200
        if ($tinggi < 50) {
            // kemungkinan user salah input (meter bukan cm)
            $tinggi = $tinggi * 100;
        }

        $tinggi_m = $tinggi / 100;
        $imt = round($berat / ($tinggi_m * $tinggi_m), 1);

        // batas aman IMT manusia biasanya 10-60
        return ($imt > 5 && $imt < 80) ? $imt : null;
    }

    private function statusKEK($lila)
    {
        if (is_null($lila)) return null;
        return $lila < 23.5 ? 'KEK' : 'Normal';
    }

    private function statusHB($hb)
    {
        if (is_null($hb)) return null;
        return $hb < 11 ? 'Anemia' : 'Normal';
    }

    private function statusRisiko($usia_perempuan)
    {
        if (is_null($usia_perempuan)) return null;
        return ($usia_perempuan < 21) ? 'Berisiko' : 'Normal';
    }

    public function status(Request $request)
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

            $query = Catin::query();

            // ✅ Filter berdasar wilayah user (default)
            if (!empty($wilayah['kelurahan'])) {
                $query->where('kelurahan', $wilayah['kelurahan']);
            }

            // ✅ Filter tambahan dari frontend
            if ($request->filled('posyandu')) $query->where('posyandu', $request->posyandu);
            if ($request->filled('rw')) $query->where('rw', $request->rw);
            if ($request->filled('rt')) $query->where('rt', $request->rt);

            if (!$request->filled('periode')) {
                if ($request->filled('periodeAwal') && $request->filled('periodeAkhir')) {
                    $periodeAwal = $this->parseBulanTahun($request->periodeAwal);
                    $periodeAkhir = $this->parseBulanTahun($request->periodeAkhir, true);

                    $query->whereBetween('tanggal_pendampingan', [
                        $periodeAwal->format('Y-m-d'),
                        $periodeAkhir->format('Y-m-d'),
                    ]);
                }
            }else {
                // filter dari dashboard
                if ($request->filled('periode')) {
                    $periode = $this->parseBulanTahun($request->periode);
                    $query->whereBetween('tanggal_pendampingan', [
                        $periode->format('Y-m-d'),
                    ]);
                }
            }

            // ✅ Ambil data
            $data = $query->get();

            if ($data->isEmpty()) {
                return response()->json([
                    'total' => 0,
                    'counts' => [],
                    'kelurahan' => $wilayah['kelurahan'] ?? '-',
                ]);
            }

            // ✅ Group by nik_perempuan, ambil record terakhir
            $grouped = $data->groupBy('nik_perempuan')->map(fn($group) =>
                $group->sortByDesc('tanggal_pendampingan')->first()
            );

            $groupedData = $grouped->values();
            $total = $groupedData->count();

            // ✅ Filter usia_perempuan
            if ($request->filled('usia_perempuan') && is_array($request->usia)) {
                $groupedData = $groupedData->filter(function ($item) use ($request) {
                    foreach ($request->usia as $range) {
                        $range = trim($range);
                        if ($range === '<20' && $item->usia_perempuan < 20) return true;
                        if ($range === '>40' && $item->usia_perempuan > 40) return true;
                        if (str_contains($range, '-')) {
                            [$min, $max] = explode('-', $range);
                            if ($item->usia_perempuan >= (int)$min && $item->usia_perempuan <= (int)$max) return true;
                        }
                    }
                    return false;
                });
            }

            // ✅ Filter intervensi
            if ($request->filled('intervensi') && is_array($request->intervensi)) {
                $groupedData = $groupedData->filter(function ($item) use ($request) {
                    foreach ($request->intervensi as $val) {
                        if (str_contains(strtolower($item->intervensi ?? ''), strtolower($val))) {
                            return true;
                        }
                    }
                    return false;
                });
            }

            // ✅ Filter by status
            if ($request->filled('status')) {
                $statuses = (array)$request->status;
                $groupedData = $groupedData->filter(function ($item) use ($statuses) {
                    foreach ($statuses as $status) {
                        $s = strtolower($status);
                        // ambil semua data selain "normal" kalau status = risiko
                        if ($s === 'risiko') {
                            return !str_contains(strtolower($item->status_risiko ?? ''), 'normal');
                        }

                        if (
                            str_contains(strtolower($item->status_risiko ?? ''), $s) ||
                            str_contains(strtolower($item->status_hb ?? ''), $s) ||
                            str_contains(strtolower($item->status_kek ?? ''), $s)
                        ) {
                            return true;
                        }
                    }
                    return false;
                });
                $total = $groupedData->count();
            }

            // ✅ Hitung status kesehatan
            $count = [
                'Anemia' => $groupedData->filter(fn($i) => str_contains(strtolower($i->status_hb ?? ''), 'anemia'))->count(),
                'KEK' => $groupedData->filter(fn($i) => str_contains(strtolower($i->status_kek ?? ''), 'kek'))->count(),
                'Berisiko' => $groupedData->filter(fn($i) => !str_contains(strtolower($i->status_risiko ?? ''), 'normal'))->count(),
                'Total Berisiko' => $groupedData->filter(fn($i) => !str_contains(strtolower($i->status_risiko ?? ''), 'normal') || str_contains(strtolower($i->status_kek ?? ''), 'kek') || str_contains(strtolower($i->status_hb ?? ''), 'anemia'))->count(),
                'Total Catin' => $total,
            ];

            // ✅ Format hasil
            $result = [];
            foreach ($count as $key => $val) {
                $percent = $total > 0 ? round(($val / $total) * 100, 1) : 0;
                $result[] = [
                    'title' => $key,
                    'value' => $val,
                    'percent' => $percent,
                    'color' => match ($key) {
                        'KEK' => 'danger',
                        'Anemia' => 'warning',
                        'Berisiko' => 'violet',
                        'Total Berisiko' => 'dark',
                        default => 'secondary'
                    }
                ];
            }

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

    public function tren(Request $request)
    {
        try {
            $user = Auth::user();

            // wilayah default dari user
            $wilayah = [
                'kelurahan' => $user->kelurahan ?? null,
                'kecamatan' => $user->kecamatan ?? null,
                'kota'      => $user->kota ?? null,
                'provinsi'  => $user->provinsi ?? null,
            ];

            $query = Catin::query();

            // Filter default dari user (jika ada)
            if (!empty($wilayah['kelurahan'])) {
                $query->where('kelurahan', $wilayah['kelurahan']);
            }

            // Filter tambahan dari request
            foreach (['kelurahan', 'posyandu', 'rw', 'rt'] as $f) {
                if ($request->filled($f)) {
                    $query->where($f, $request->$f);
                }
            }

            // Tentukan periode berdasarkan filter
            if ($request->filled('periode')) {
                // contoh: "November 2025"
                $periode = Carbon::parse('01 ' . $request->periode);
                $awal = $periode->copy()->startOfMonth();
                $akhir = $periode->copy()->endOfMonth();
            } else {
                // default bulan ini
                $awal = Carbon::now()->startOfMonth();
                $akhir = Carbon::now()->endOfMonth();
            }

            // periode pembanding (bulan sebelumnya)
            $awalPrev = $awal->copy()->subMonth()->startOfMonth();
            $akhirPrev = $awal->copy()->subMonth()->endOfMonth();

            // ambil data untuk periode utama dan sebelumnya
            $current = (clone $query)
                ->whereBetween('tanggal_pendampingan', [$awal, $akhir])
                ->get();

            $previous = (clone $query)
                ->whereBetween('tanggal_pendampingan', [$awalPrev, $akhirPrev])
                ->get();

            // fungsi bantu: hitung status (tanpa intervensi)
            $countStatus = function ($rows) {
                $total = $rows->count();
                $kek = $rows->filter(fn($r) => $this->detectKek($r))->count();
                $anemia = $rows->filter(fn($r) => $this->detectAnemia($r))->count();
                $risti = $rows->filter(fn($r) => $this->detectRisti($r))->count();

                return [
                    'total' => $total,
                    'KEK' => $kek,
                    'Anemia' => $anemia,
                    'Risiko Usia' => $risti,
                ];
            };

            $currCount = $countStatus($current);
            $prevCount = $countStatus($previous);

            $statuses = ['KEK', 'Anemia', 'Risiko Usia'];
            $dataTable = [];

            foreach ($statuses as $status) {
                $jumlah = $currCount[$status] ?? 0;
                $total = $currCount['total'] ?? 0;
                $persen = $total > 0 ? round(($jumlah / $total) * 100, 1) : 0;

                $prevJumlah = $prevCount[$status] ?? 0;
                $delta = $jumlah - $prevJumlah;
                $tren = $delta === 0 ? '-' : ($delta > 0 ? "Naik {$delta}" : "Turun " . abs($delta));

                $trenClass = $delta > 0 ? 'text-danger' : ($delta < 0 ? 'text-success' : 'text-muted');
                $trenIcon = $delta > 0 ? 'fa-solid fa-arrow-up' : ($delta < 0 ? 'fa-solid fa-arrow-down' : 'fa-solid fa-minus');

                $dataTable[] = [
                    'status' => $status,
                    'jumlah' => $jumlah,
                    'persen' => $persen,
                    'tren' => $tren,
                    'trenClass' => $trenClass,
                    'trenIcon' => $trenIcon,
                ];
            }

            // jika data kosong, kirim default 0
            if ($currCount['total'] === 0) {
                $dataTable = collect($statuses)->map(fn($s) => [
                    'status' => $s,
                    'jumlah' => 0,
                    'persen' => 0,
                    'tren' => '-',
                    'trenClass' => 'text-muted',
                    'trenIcon' => 'fa-solid fa-minus',
                ]);
            }

            return response()->json([
                'total' => $currCount['total'] ?? 0,
                'dataTable_catin' => $dataTable,
                'periode' => [
                    'current' => [$awal->toDateString(), $akhir->toDateString()],
                    'previous' => [$awalPrev->toDateString(), $akhirPrev->toDateString()],
                ],
            ], 200);

        } catch (\Exception $e) {
            \Log::error('tren catin error: ' . $e->getMessage());
            return response()->json([
                'message' => 'Gagal menghitung tren catin',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    protected function detectKek($row)
    {
        // cek kolom status_gizi_lila atau lila value
        if (isset($row->status_kek) && $row->status_kek) {
            $s = strtolower($row->status_kek);
            return Str::contains($s, ['kek']);
        }
        // fallback: jika LILA numeric threshold (mis. <23 untuk wanita hamil)
        if (isset($row->lila_perempuan) && is_numeric($row->lila_perempuan)) {
            return floatval($row->lila_perempuan) < 23.0;
        }
        return false;
    }

    protected function detectAnemia($row)
    {
        if (isset($row->status_hb) && $row->status_hb) {
            $s = strtolower($row->status_hb);
            return Str::contains($s, ['anemia']);
        }
        if (isset($row->hb_perempuan) && is_numeric($row->hb_perempuan)) {
            return floatval($row->hb_perempuan) < 11.0; // cutoff umum anemia pada ibu hamil
        }
        return false;
    }

    protected function detectRisti($row)
    {
        if (isset($row->status_risiko) && $row->status_risiko) {
            $s = strtolower($row->status_risiko);
            return Str::contains($s, ['risiko', 'berisiko', 'high risk', 'risti']);
        }
        return false;
    }

    public function indikatorBulanan(Request $request)
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

            $query = Catin::query();

            // ✅ Filter wilayah user
            if (!empty($wilayah['kelurahan'])) {
                $query->where('kelurahan', $wilayah['kelurahan']);
            }

            // ✅ Filter tambahan dari frontend
            foreach (['posyandu', 'rw', 'rt'] as $f) {
                if ($request->filled($f)) $query->where($f, $request->$f);
            }

            // ✅ Ambil data dalam 12 bulan terakhir
            $startDate = now()->subMonths(11)->startOfMonth();
            $endDate = now()->endOfMonth();

            $query->whereBetween('tanggal_pendampingan', [$startDate, $endDate]);

            $data = $query->get([
                'nik_perempuan',
                'tanggal_pendampingan',
                'status_kek',
                'status_hb',
                'status_risiko'
            ]);

            if ($data->isEmpty()) {
                return response()->json([
                    'labels' => [],
                    'indikator' => [],
                ]);
            }

            // ✅ Buat label bulan
            $months = collect(range(0, 11))
                ->map(fn($i) => now()->subMonths(11 - $i)->format('M Y'))
                ->values();

            // ✅ Siapkan struktur hasil
            $indikatorList = ['KEK', 'Anemia', 'Berisiko'];
            $result = [];
            foreach ($indikatorList as $indikator) {
                $result[$indikator] = array_fill(0, 12, 0);
            }

            // ✅ Grouping by bulan dan nik_perempuan → ambil record terakhir per orang per bulan
            $groupedByMonth = $data->groupBy(function ($item) {
                return \Carbon\Carbon::parse($item->tanggal_pendampingan)->format('Y-m');
            })->map(function ($rows) {
                return $rows->groupBy('nik_perempuan')->map(fn($g) =>
                    $g->sortByDesc('tanggal_pendampingan')->first()
                );
            });

            // ✅ Hitung total per bulan
            foreach ($groupedByMonth as $monthKey => $rows) {
                $label = \Carbon\Carbon::createFromFormat('Y-m', $monthKey)->format('M Y');
                $idx = $months->search($label);
                if ($idx === false) continue;

                $rows = $rows->values();

                $result['KEK'][$idx] = $rows->filter(fn($i) =>
                    str_contains(strtolower($i->status_kek ?? ''), 'kek')
                )->count();

                $result['Anemia'][$idx] = $rows->filter(fn($i) =>
                    str_contains(strtolower($i->status_hb ?? ''), 'anemia')
                )->count();

                $result['Berisiko'][$idx] = $rows->filter(fn($i) =>
                    !str_contains(strtolower($i->status_risiko ?? ''), 'normal')
                )->count();
            }

            return response()->json([
                'labels' => $months,
                'indikator' => $result,
            ]);

        } catch (\Throwable $th) {
            return response()->json([
                'error' => 'Gagal memuat data indikator bulanan',
                'message' => $th->getMessage(),
            ], 500);
        }
    }


}
