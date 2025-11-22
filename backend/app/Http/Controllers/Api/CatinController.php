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
//use PhpOffice\PhpSpreadsheet\IOFactory;

class CatinController extends Controller
{
    public function index(Request $request)
    {
        $filterKelurahan = $request->kelurahan;

        $query = Catin::query();

        if ($filterKelurahan) {
            $query->where('kelurahan', $filterKelurahan);
        }
        if ($request->filled('posyandu')) $query->where('posyandu', $request->posyandu);
        if ($request->filled('rw')) $query->where('rw', $request->rw);
        if ($request->filled('rt')) $query->where('rt', $request->rt);

        // 5️⃣ Filter periode
        if ($request->filled('periodeAwal') && $request->filled('periodeAkhir')) {

            $periodeAwal  = $this->parseBulanTahun($request->periodeAwal, false);
            $periodeAkhir = $this->parseBulanTahun($request->periodeAkhir, true);

            $query->whereBetween('tanggal_pendampingan', [
                $periodeAwal,
                $periodeAkhir
            ]);

        } else {

            // DEFAULT 1 TAHUN TERAKHIR
            $tanggalAkhir = Carbon::now()->endOfMonth();
            $tanggalAwal  = Carbon::now()->subYear()->startOfMonth();

            $query->whereBetween('tanggal_pendampingan', [
                $tanggalAwal,
                $tanggalAkhir
            ]);
        }

        // --- Ambil semua record dulu ---
        $all = $query->orderBy('tanggal_pemeriksaan')->get();

        /*
        |--------------------------------------------------------------------------
        | FILTER USIA
        |--------------------------------------------------------------------------
        */
        if ($request->filled('usia') && is_array($request->usia)) {
            $all = $all->filter(function ($item) use ($request) {

                $usia = $item->usia_perempuan ?? null;
                if (!$usia) return false;

                foreach ($request->usia as $range) {
                    $range = trim($range);

                    if ($range === '<20' && $usia < 20) return true;
                    if ($range === '>40' && $usia > 40) return true;

                    if (str_contains($range, '-')) {
                        [$min, $max] = explode('-', $range);
                        if ($usia >= (int)$min && $usia <= (int)$max) return true;
                    }
                }

                return false;
            });
        }

        /*
        |--------------------------------------------------------------------------
        | FILTER STATUS (KEK, Anemia, Risiko ≠ Normal)
        |--------------------------------------------------------------------------
        */
        if ($request->filled('status') && is_array($request->status)) {

            $statuses = array_map('strtolower', $request->status);

            $all = $all->filter(function ($item) use ($statuses) {

                foreach ($statuses as $status) {

                    if (str_contains($status, 'kek')) {
                        if (stripos($item->status_kek, 'kek') !== false) return true;
                    }

                    if (str_contains($status, 'anemia')) {
                        if (stripos($item->status_hb, 'anemia') !== false) return true;
                    }

                    if (str_contains($status, 'risiko')) {
                        if (
                            empty($item->status_risiko) ||
                            stripos($item->status_risiko, 'normal') === false
                        ) {
                            return true;
                        }
                    }
                }

                return false;
            });
        }

        /*
        |--------------------------------------------------------------------------
        | GROUP BY NIK PEREMPUAN
        |--------------------------------------------------------------------------
        */
        $grouped = $all->groupBy('nik_perempuan')->map(function ($items) {

            $main = $items->first();

            return [
                // informasi utama
                'nama_perempuan'  => $main->nama_perempuan,
                'nik_perempuan'   => $main->nik_perempuan,
                'usia_perempuan'  => $main->usia_perempuan,
                'hp_perempuan'    => $main->hp_perempuan,
                'kerja_perempuan' => $main->pekerjaan_perempuan,

                'nama_laki'       => $main->nama_laki,
                'nik_laki'        => $main->nik_laki,
                'usia_laki'       => $main->usia_laki,
                'hp_laki'         => $main->hp_laki,
                'kerja_laki'      => $main->pekerjaan_laki,

                'status_risiko'   => $main->status_risiko,
                'provinsi'        => $main->provinsi,
                'kota'            => $main->kota,
                'kecamatan'       => $main->kecamatan,
                'kelurahan'       => $main->kelurahan,
                'rw'              => $main->rw,
                'rt'              => $main->rt,
                'posyandu'        => $main->posyandu,

                'tgl_pernikahan'  => $main->tanggal_rencana_menikah,
                'tgl_kunjungan'   => $main->tanggal_pendampingan,

                // RIWAYAT PEMERIKSAAN
                'riwayat_pemeriksaan' => $items->map(function ($d) {
                    return [
                        'tanggal_pemeriksaan' => $d->tanggal_pemeriksaan,
                        'berat_perempuan'     => $d->berat_perempuan,
                        'tinggi_perempuan'    => $d->tinggi_perempuan,
                        'imt_perempuan'       => $d->imt_perempuan,
                        'hb_perempuan'        => $d->hb_perempuan,
                        'status_hb'           => $d->status_hb,
                        'lila_perempuan'      => $d->lila_perempuan,
                        'status_kek'          => $d->status_kek,
                        'riwayat_penyakit'    => $d->riwayat_penyakit,
                        'terpapar_rokok'      => $d->terpapar_rokok,
                        'menggunakan_jamban'  => $d->menggunakan_jamban,
                        'sumber_air_bersih'   => $d->sumber_air_bersih
                    ];
                })->values(),

                // RIWAYAT PENDAMPINGAN
                'riwayat_pendampingan' => $items->whereNotNull('tanggal_pendampingan')->map(function ($d) {
                    return [
                        'tanggal_pendampingan' => $d->tanggal_pendampingan,
                        'nama_petugas'          => $d->nama_petugas,
                    ];
                })->values(),
            ];

        })->values();

        return response()->json([
            'success' => true,
            'data' => $grouped
        ]);
    }

    private function parseBulanTahun(string $periode, bool $akhirBulan = false): \Carbon\Carbon
    {
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

        $parts = explode(' ', trim(strtolower($periode))); // contoh: ["maret", "2025"]

        if (count($parts) === 2 && isset($bulanMap[$parts[0]])) {
            $bulan = $bulanMap[$parts[0]];
            $tahun = (int) $parts[1];

            $date = \Carbon\Carbon::createFromDate($tahun, $bulan, 1);
            return $akhirBulan ? $date->endOfMonth() : $date->startOfMonth();
        }

        // fallback jika format salah
        return \Carbon\Carbon::now();
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
                'usia_perempuan' => $data[5] ?? 0,
                'hp_perempuan' => $data[6] ?? null,

                'nama_laki' => $data[7] ?? null,
                'nik_laki' => $data[8] ?? null,
                'pekerjaan_laki' => $data[9] ?? null,
                'usia_laki' => $data[10] ?? 0,
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
                'hb_perempuan' => $data[24] ?? null,
                'lila_perempuan' => $data[23] ?? null,

                'terpapar_rokok' => $this->toBool($data[25] ?? null),
                'mendapat_ttd' => $this->toBool($data[26] ?? null),
                'menggunakan_jamban' => $this->toBool($data[27] ?? null),
                'sumber_air_bersih' => $this->toBool($data[28] ?? null),
                'sumber_air_bersih' => $this->toBool($data[28] ?? null),
                'punya_riwayat_penyakit' => $this->toBool($data[29] ?? null),
                'riwayat_penyakit' => $data[30] ?? null,
                'mendapat_fasilitas_rujukan' => $this->toBool($data[31] ?? null),
                'mendapat_kie' => $this->toBool($data[33] ?? null),
                'mendapat_bantuan_pmt' => $this->toBool($data[33] ?? null),

                'tanggal_rencana_menikah' => $this->convertDate($data[34] ?? null),
                'rencana_tinggal' => $data[35] ?? null,
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
                    'data' => $rows
                ], 500);
            }
        }

        return response()->json(['message' => 'Tidak ada data untuk diimport'], 400);
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
        return ($usia_perempuan < 21 || $usia_perempuan > 35) ? 'Berisiko' : 'Normal';
    }

    public function status(Request $request)
    {
        try {
            $filterKelurahan = $request->kelurahan ?? null;
            $query = Catin::query();

            // 3️⃣ Filter wilayah default
            if ($filterKelurahan) {
                $query->where('kelurahan', $filterKelurahan);
            }

            // 4️⃣ Filter tambahan
            if ($request->filled('posyandu')) $query->where('posyandu', $request->posyandu);
            if ($request->filled('rw')) $query->where('rw', $request->rw);
            if ($request->filled('rt')) $query->where('rt', $request->rt);

            // 5️⃣ Filter periode
            if ($request->ref === 'p') {

                // MODE RANGE (periodeAwal + periodeAkhir)
                if ($request->filled('periodeAwal') && $request->filled('periodeAkhir')) {

                    $periodeAwal  = $this->parseBulanTahun($request->periodeAwal, false);
                    $periodeAkhir = $this->parseBulanTahun($request->periodeAkhir, true);

                    $query->whereBetween('tanggal_pendampingan', [
                        $periodeAwal,
                        $periodeAkhir
                    ]);

                } else {

                    // DEFAULT 1 TAHUN TERAKHIR
                    $tanggalAkhir = Carbon::now()->endOfMonth();
                    $tanggalAwal  = Carbon::now()->subYear()->startOfMonth();

                    $query->whereBetween('tanggal_pendampingan', [
                        $tanggalAwal,
                        $tanggalAkhir
                    ]);
                }

            } else {

                // FILTER PER BULAN (dropdown)
                if ($request->filled('periode')) {

                    $periode = $this->parseBulanTahun($request->periode);

                    $query->whereBetween('tanggal_pendampingan', [
                        $periode->copy()->startOfMonth(),
                        $periode->copy()->endOfMonth()
                    ]);

                } else {

                    // DEFAULT BULAN BERJALAN - 1
                    $periodeDefault = Carbon::now()->subMonthNoOverflow();

                    $query->whereBetween('tanggal_pendampingan', [
                        $periodeDefault->copy()->startOfMonth(),
                        $periodeDefault->copy()->endOfMonth()
                    ]);
                }
            }


            // 6️⃣ Ambil data
            $data = $query->get();

            // ======================================================
            // 0️⃣ JIKA KOSONG
            // ======================================================
            if ($data->isEmpty()) {

                $keys = [
                    'Anemia',
                    'KEK',
                    'Risiko Usia',
                    'Total Berisiko',
                    'Total Catin'
                ];

                $result = [];
                foreach ($keys as $key) {
                    $result[] = [
                        'title' => $key,
                        'value' => 0,
                        'percent' => 0,
                        'color' => match ($key) {
                            'KEK' => 'danger',
                            'Anemia' => 'warning',
                            'Risiko Usia' => 'violet',
                            'Total Berisiko' => 'dark',
                            default => 'secondary'
                        },
                        'trend' => $this->catinTrend($key, $filterKelurahan) // 🔥 tetap ada tren walaupun kosong
                    ];
                }

                return response()->json([
                    'total' => 0,
                    'counts' => $result,
                    'kelurahan' => $wilayah->kelurahan ?? '-',
                ]);
            }

            // 7️⃣ Ambil data terakhir per NIK
            $grouped = $data->groupBy('nik_perempuan')->map(fn($g) =>
                $g->sortByDesc('tanggal_pendampingan')->first()
            );

            $groupedData = $grouped->values();
            $total = $groupedData->count();

            // 8️⃣ Filter usia
            if ($request->filled('usia') && is_array($request->usia)) {
                $groupedData = $groupedData->filter(function ($item) use ($request) {
                    foreach ($request->usia as $range) {
                        if ($range === '<20' && $item->usia_perempuan < 20) return true;
                        if ($range === '>40' && $item->usia_perempuan > 40) return true;

                        if (str_contains($range, '-')) {
                            [$min, $max] = explode('-', $range);
                            if ($item->usia_perempuan >= $min && $item->usia_perempuan <= $max) return true;
                        }
                    }
                    return false;
                });
            }

            // 9️⃣ Filter status
            if ($request->filled('status')) {
                $statuses = (array)$request->status;

                $groupedData = $groupedData->filter(function ($item) use ($statuses) {
                    foreach ($statuses as $status) {
                        $s = strtolower($status);

                        // "risiko" = selain normal
                        if ($s === 'Berisiko') {
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

            // 1️⃣0️⃣ Hitung status utama
            $count = [
                'Anemia' => $groupedData->filter(fn($i) => str_contains(strtolower($i->status_hb ?? ''), 'anemia'))->count(),
                'KEK' => $groupedData->filter(fn($i) => str_contains(strtolower($i->status_kek ?? ''), 'kek'))->count(),
                'Risiko Usia' => $groupedData->filter(fn($i) => str_contains(strtolower($i->status_risiko ?? ''), 'berisiko'))->count(),
                'Total Berisiko' => $groupedData->filter(fn($i) =>
                    str_contains(strtolower($i->status_risiko ?? ''), 'berisiko')
                    || str_contains(strtolower($i->status_kek ?? ''), 'kek')
                    || str_contains(strtolower($i->status_hb ?? ''), 'anemia')
                )->count(),
                'Total Catin' => $total,
            ];

            // 1️⃣1️⃣ Format hasil + TREND
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
                        'Risiko Usia' => 'violet',
                        'Total Berisiko' => 'dark',
                        default => 'secondary'
                    },
                    'trend' => $this->catinTrend($key, $filterKelurahan) // 🔥 TREND DI sini
                ];
            }

            return response()->json([
                'total' => $total,
                'counts' => $result,
                'kelurahan' => $wilayah->kelurahan ?? '-',
                //'filter'=> $filter
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Gagal mengambil data status Catin',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function tren(Request $request)
    {
        try {
            //$user = Auth::user();

            $filterKelurahan = $request->kelurahan;
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

            $periodeRaw = $request->periode;

            // Jika format "2025-03"
            if (preg_match('/^\d{4}-\d{2}$/', $periodeRaw)) {
                // parse manual
                $periode = Carbon::createFromFormat('Y-m-d', $periodeRaw . '-01');
            } else {
                // format "November 2025"
                $periode = Carbon::parse('01 ' . $periodeRaw);
            }

                $awal = $periode->copy()->startOfMonth();
                $akhir = $periode->copy()->endOfMonth();

            } else {
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
                $prevTotal = $prevCount['total'] ?? 0;
                $prevPersen = $prevTotal > 0 ? round(($prevJumlah / $prevTotal) * 100, 1) : 0;

                // tren dalam persentase
                $deltaPersen = $persen - $prevPersen;
                $tren = $deltaPersen === 0
                    ? '-'
                    : ($deltaPersen > 0 ? "{$deltaPersen}%" : "" . abs($deltaPersen) . "%");

                $trenClass = $deltaPersen > 0 ? 'text-danger' : ($deltaPersen < 0 ? 'text-success' : 'text-muted');
                $trenIcon = $deltaPersen > 0 ? 'fa-solid fa-caret-up' : ($deltaPersen < 0 ? 'fa-solid fa-caret-down' : 'fa-solid fa-minus');

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

    private function catinTrend($key, $kelurahan)
    {
        // Ambil 2 bulan terakhir
        $currentMonth = now()->format('Y-m');
        $previousMonth = now()->subMonth()->format('Y-m');

        // Ambil data catin berdasarkan kelurahan
        $data = Catin::query()
            ->when($kelurahan, fn($q) => $q->where('kelurahan', $kelurahan))
            ->get();

        if ($data->isEmpty()) {
            return [
                "month" => [
                    "current" => $currentMonth,
                    "previous" => $previousMonth,
                ],
                "data" => [
                    "current" => [$key => 0],
                    "previous" => [$key => 0],
                ],
                "total" => [
                    "current" => 0,
                    "previous" => 0,
                    "diff_percent" => 0
                ]
            ];
        }

        // ===========================
        // KATEGORI TREND CATIN
        // ===========================
        $categories = [
            "Anemia" => ["anemia", "anemia ringan", "anemia sedang", "anemia berat"],
            "KEK" => ["kek", "kurang energi kronis"],
            "Risiko Usia" => ["berisiko"], // pokoknya bukan normal
            "Total Berisiko" => ["*"], // akan dihitung terpisah
            "Total Catin" => ["*"],
        ];

        // Ambil kategori values
        $field = match ($key) {
            "Anemia" => "status_hb",
            "KEK" => "status_kek",
            "Risiko Usia", "Total Berisiko" => "status_risiko_usia",
            default => null
        };

        // Gunakan handler buildTrend
        return $this->buildCatinTrend($data, $key, $categories, $field, $currentMonth, $previousMonth);
    }

    private function buildCatinTrend($data, $key, $categories, $field, $currentMonth, $previousMonth)
    {
        // Filter per bulan
        $currentData = $data->filter(
            fn($i) =>
            Carbon::parse($i->tanggal_pendampingan)->format('Y-m') === $currentMonth
        );

        $previousData = $data->filter(
            fn($i) =>
            Carbon::parse($i->tanggal_pendampingan)->format('Y-m') === $previousMonth
        );

        // Hitung kategori
        $countCategory = function ($collection) use ($key, $categories, $field) {

            // Total Catin → jumlah semua
            if ($key === "Total Catin") {
                return $collection->count();
            }

            // Total Berisiko → bukan normal
            if ($key === "Total Berisiko") {
                return $collection->filter(function ($i) {
                    return
                        !str_contains(strtolower($i->status_risiko ?? ''), 'normal') ||
                        str_contains(strtolower($i->status_kek ?? ''), 'kek') ||
                        str_contains(strtolower($i->status_hb ?? ''), 'anemia');
                })->count();
            }

            // Kategori spesifik
            $values = $categories[$key] ?? [];

            return $collection->filter(function ($i) use ($field, $values) {
                if (!$field) return false;
                $v = strtolower($i->$field ?? '');
                foreach ($values as $pattern) {
                    if ($pattern === "*") return true;
                    if (str_contains($v, $pattern)) return true;
                }
                return false;
            })->count();
        };

        $current = $countCategory($currentData);
        $previous = $countCategory($previousData);

        $diffPercent = $previous == 0
            ? 0
            : round((($current - $previous) / $previous) * 100, 2);

        return [
            "month" => [
                "current" => $currentMonth,
                "previous" => $previousMonth,
            ],
            "data" => [
                "current" => [$key => $current],
                "previous" => [$key => $previous],
            ],
            "total" => [
                "current" => $current,
                "previous" => $previous,
                "diff_percent" => $diffPercent,
            ]
        ];
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
                    str_contains(strtolower($i->status_kek ?? ''), 'KEK')
                )->count();

                $result['Anemia'][$idx] = $rows->filter(fn($i) =>
                    str_contains(strtolower($i->status_hb ?? ''), 'Anemia')
                )->count();

                $result['Berisiko'][$idx] = $rows->filter(fn($i) =>
                    str_contains(strtolower($i->status_risiko ?? ''), 'berisiko')
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
