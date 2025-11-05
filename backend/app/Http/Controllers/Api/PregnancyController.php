<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Pregnancy;
use Carbon\Carbon;
use App\Models\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

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
                            'posyandu' => $g->posyandu,
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

    public function show($nik_ibu)
    {
        try {
            // ✅ Ambil semua data ibu berdasarkan NIK
            $records = Pregnancy::where('nik_ibu', $nik_ibu)->orderByDesc('tanggal_pemeriksaan_terakhir')->get();

            if ($records->isEmpty()) {
                return response()->json([
                    'message' => 'Data ibu hamil tidak ditemukan',
                    'nik_ibu' => $nik_ibu,
                    'data' => null
                ], 404);
            }

            $latest = $records->first();
            $alamat =

            // ✅ Format profil utama ibu hamil
            $ibu = [
                'nik' => $latest->nik_ibu,
                'nama' => $latest->nama_ibu ?? '-',
                'nama_suami' => $latest->nama_suami ?? '-',
                'nik_suami' => $latest->nik_suami ?? '-',
                'usia_suami' => $latest->usia_suami ?? '-',
                'usia' => $latest->usia_ibu ?? '-',
                'kelurahan' => $latest->kelurahan ?? '-',
                'rw' => $latest->rw ?? '-',
                'rt' => $latest->rt ?? '-',
                'kecamatan' => $latest->kecamatan ?? '-',
                'provinsi' => $latest->provinsi ?? '-',
                'kota' => $latest->kota ?? '-',
                'status_gizi' => $latest->status_kehamilan ?? '-',
            ];

            // ✅ Riwayat Pemeriksaan (3 terakhir)
            $riwayatPemeriksaan = $records->take(5)->map(function ($item) {
                return [
                    'tanggal' => optional($item->tanggal_pemeriksaan_terakhir)->format('Y-m-d'),
                    'anemia' => $item->status_gizi_hb ?? '-',
                    'kek' => $item->status_gizi_lila ?? '-',
                    'risiko' => $item->status_kehamilan ?? '-',
                    'berat_badan' => $item->berat_badan ?? '-',
                    'tinggi_badan' => $item->tinggi_badan ?? '-',
                    'imt' => $item->imt ?? '-',
                    'lila' => $item->lila ?? '-',
                    'kadar_hb' => $item->kadar_hb ?? '-',
                    'usia_kehamilan_minggu' => $item->usia_kehamilan_minggu ?? '-',
                ];
            });

            // ✅ Riwayat Intervensi dummy (bisa dihubungkan tabel lain jika sudah ada)
            $riwayatIntervensi = collect([
                [
                    'tanggal' => '2025-01-10',
                    'kader' => 'Kader A',
                    'intervensi' => 'Pemberian Tablet Tambah Darah',
                ],
                [
                    'tanggal' => '2025-02-15',
                    'kader' => 'Kader B',
                    'intervensi' => 'Pemeriksaan LILA & Konseling Gizi',
                ],
            ]);

            // ✅ Data Kehamilan (semua record)
            $dataKehamilan = $records->map(function ($item) {
                return [
                    'tgl_pendampingan' => optional($item->tanggal_pendampingan)->format('Y-m-d'),
                    'kehamilan_ke' => $item->kehamilan_ke ?? '-',
                    'risiko' => $item->status_kehamilan ?? '-',
                    'tb' => $item->tinggi_badan ?? '-',
                    'bb' => $item->berat_badan ?? '-',
                    'lila' => $item->lila ?? '-',
                    'kek' => $item->status_gizi_lila ?? '-',
                    'hb' => $item->kadar_hb ?? '-',
                    'anemia' => $item->status_gizi_hb ?? '-',
                    'asap_rokok' => $item->terpapar_asap_rokok ?? '-',
                    'bantuan_sosial' => $item->mendapat_bantuan_sosial ?? '-',
                    'jamban_sehat' => $item->menggunakan_jamban ?? '-',
                    'sumber_air_bersih' => $item->menggunakan_sab ?? '-',
                    'keluhan' => $item->keluhan ?? '-',
                    'intervensi' => $item->intervensi ?? '-',
                ];
            });

            return response()->json([
                'ibu' => $ibu,
                'riwayat_pemeriksaan' => $riwayatPemeriksaan,
                'riwayat_intervensi' => $riwayatIntervensi,
                'kehamilan' => $dataKehamilan,
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Gagal mengambil detail ibu hamil',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function tren(Request $request)
    {
        try {
            $user = Auth::user();

            // wilayah user default filter
            $wilayah = [
                'kelurahan' => $user->kelurahan ?? null,
                'kecamatan' => $user->kecamatan ?? null,
                'kota'      => $user->kota ?? null,
                'provinsi'  => $user->provinsi ?? null,
            ];

            $query = Pregnancy::query();

            // default wilayah user
            if (!empty($wilayah['kelurahan'])) {
                $query->where('kelurahan', $wilayah['kelurahan']);
            }

            // tambahan filter request
            foreach (['kelurahan', 'posyandu', 'rw', 'rt'] as $f) {
                if ($request->filled($f)) {
                    $query->where($f, $request->$f);
                }
            }

            // periode utama
            if ($request->filled('periodeAwal') && $request->filled('periodeAkhir')) {
                $awal = Carbon::parse($request->periodeAwal)->startOfDay();
                $akhir = Carbon::parse($request->periodeAkhir)->endOfDay();
            } else {
                $akhir = Carbon::now()->endOfDay();
                $awal = (clone $akhir)->subMonths(1)->startOfMonth(); // default bulan ini vs bulan lalu
            }

            // periode pembanding (bulan sebelumnya)
            $awalPrev = (clone $awal)->subMonth();
            $akhirPrev = (clone $awal)->subDay();

            // ambil data untuk periode utama dan sebelumnya
            $current = $query->clone()
                ->whereBetween('tanggal_pemeriksaan_terakhir', [$awal, $akhir])
                ->get();

            $previous = $query->clone()
                ->whereBetween('tanggal_pemeriksaan_terakhir', [$awalPrev, $akhirPrev])
                ->get();

            if ($current->isEmpty()) {
                return response()->json([
                    'message' => 'Tidak ada data bumil pada periode ini.',
                    'dataTable_bumil' => [],
                    'total' => 0,
                ], 200);
            }

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
                    'Risiko Tinggi' => $risti,
                ];
            };

            $currCount = $countStatus($current);
            $prevCount = $countStatus($previous);

            // hasil rekap per status
            $statuses = ['KEK', 'Anemia', 'Risiko Tinggi'];
            $dataTable = [];

            foreach ($statuses as $status) {
                $jumlah = $currCount[$status];
                $total = $currCount['total'];
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

            return response()->json([
                'total' => $currCount['total'],
                'dataTable_bumil' => $dataTable,
                'periode' => [
                    'current' => [$awal->toDateString(), $akhir->toDateString()],
                    'previous' => [$awalPrev->toDateString(), $akhirPrev->toDateString()],
                ],
            ]);
        } catch (\Exception $e) {
            \Log::error('summaryStatusBumil error: ' . $e->getMessage());
            return response()->json([
                'message' => 'Gagal menghitung status bumil',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function intervensiSummary(Request $request)
    {
        try {
            $user = Auth::user();

            // wilayah user default filter
            $wilayah = [
                'kelurahan' => $user->kelurahan ?? null,
                'kecamatan' => $user->kecamatan ?? null,
                'kota'      => $user->kota ?? null,
                'provinsi'  => $user->provinsi ?? null,
            ];

            $query = Pregnancy::query();

            // default wilayah user
            if (!empty($wilayah['kelurahan'])) {
                $query->where('kelurahan', $wilayah['kelurahan']);
            }

            // additional filters
            if ($request->filled('kelurahan')) {
                $query->where('kelurahan', $request->kelurahan);
            }
            if ($request->filled('posyandu')) {
                $query->where('posyandu', $request->posyandu);
            }
            if ($request->filled('rw')) {
                $query->where('rw', $request->rw);
            }
            if ($request->filled('rt')) {
                $query->where('rt', $request->rt);
            }

            // Periode handling:
            // Priority: periodeAwal + periodeAkhir (ISO) > periode (YYYY-MM or "NamaBulan YYYY") > default last 12 months
            if ($request->filled('periodeAwal') && $request->filled('periodeAkhir')) {
                $awal = Carbon::parse($request->periodeAwal)->startOfDay();
                $akhir = Carbon::parse($request->periodeAkhir)->endOfDay();
            } elseif ($request->filled('periode')) {
                $periode = trim($request->periode);
                // If format YYYY-MM
                if (preg_match('/^\d{4}-\d{2}$/', $periode)) {
                    $awal = Carbon::createFromFormat('Y-m', $periode)->startOfMonth();
                    $akhir = (clone $awal)->endOfMonth();
                } else {
                    // Try parse "NamaBulan YYYY" in Indonesian or english
                    // Try mapping Indonesian month names:
                    $bulanMap = [
                        'januari'=>1,'februari'=>2,'maret'=>3,'april'=>4,'mei'=>5,'juni'=>6,
                        'juli'=>7,'agustus'=>8,'september'=>9,'oktober'=>10,'november'=>11,'desember'=>12
                    ];
                    $parts = preg_split('/\s+/', strtolower($periode));
                    if (count($parts) >= 2) {
                        $monthName = $parts[0];
                        $year = intval($parts[1]);
                        $month = $bulanMap[$monthName] ?? null;
                        if ($month) {
                            $awal = Carbon::create($year, $month, 1)->startOfMonth();
                            $akhir = (clone $awal)->endOfMonth();
                        }
                    }
                }
            }

            // default 12 months terakhir jika tidak ter-set
            if (!isset($awal) || !isset($akhir)) {
                $akhir = Carbon::now()->endOfDay();
                $awal = (clone $akhir)->subMonths(11)->startOfMonth(); // last 12 months (include current month)
            }

            $query->whereBetween('tanggal_pemeriksaan_terakhir', [$awal->toDateString(), $akhir->toDateString()]);

            // Ambil data (descending latest pemeriksaan)
            $rows = $query->orderByDesc('tanggal_pemeriksaan_terakhir')->get();

            if ($rows->isEmpty()) {
                return response()->json([
                    'message' => 'Tidak ada data kehamilan ditemukan.',
                    'dataTable_bumil' => [],
                    'total' => 0,
                ], 200);
            }

            // Group by nik_ibu and prepare result per ibu
            $grouped = $rows->groupBy('nik_ibu')->map(function ($group) use ($awal, $akhir) {
                $latest = $group->sortByDesc('tanggal_pemeriksaan_terakhir')->first();

                // riwayat ascending
                $riwayat = $group->sortBy('tanggal_pemeriksaan_terakhir')->map(function ($g) {
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
                        'posyandu' => $g->posyandu,
                        'intervensi' => $g->intervensi ?? null,
                    ];
                })->values();

                // DETEKSI status KEK/Anemia/Risiko dari latest record
                $kek = $this->detectKek($latest);
                $anemia = $this->detectAnemia($latest);
                $risti = $this->detectRisti($latest);

                // DETEKSI apakah sudah mendapat intervensi:
                $kek_interv = $this->detectIntervensiFor($group, 'kek');
                $anemia_interv = $this->detectIntervensiFor($group, 'anemia');
                $risti_interv = $this->detectIntervensiFor($group, 'risti');

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
                    'posyandu' => $latest->posyandu,
                    'tanggal_pemeriksaan_terakhir' => $latest->tanggal_pemeriksaan_terakhir,
                    'riwayat_pemeriksaan' => $riwayat,
                    // flags sesuai frontend expectation (Ya / Tidak)
                    'kek' => $kek ? 'Ya' : 'Tidak',
                    'kek_intervensi' => $kek_interv ? 'Ya' : 'Tidak',
                    'anemia' => $anemia ? 'Ya' : 'Tidak',
                    'anemia_intervensi' => $anemia_interv ? 'Ya' : 'Tidak',
                    'risti' => $risti ? 'Ya' : 'Tidak',
                    'risti_intervensi' => $risti_interv ? 'Ya' : 'Tidak',
                ];
            });

            return response()->json([
                'total' => $grouped->count(),
                'dataTable_bumil' => $grouped->values()->all(),
            ], 200);
        } catch (\Exception $e) {
            \Log::error('intervensiSummary error: '.$e->getMessage());
            return response()->json([
                'message' => 'Gagal mengambil data intervensi bumil',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    // ---------- Helper detection methods ----------

    protected function detectKek($row)
    {
        // cek kolom status_gizi_lila atau lila value
        if (isset($row->status_gizi_lila) && $row->status_gizi_lila) {
            $s = strtolower($row->status_gizi_lila);
            return Str::contains($s, ['kek', 'kurang energi kronis', 'kurang']);
        }
        // fallback: jika LILA numeric threshold (mis. <23 untuk wanita hamil)
        if (isset($row->lila) && is_numeric($row->lila)) {
            return floatval($row->lila) < 23.0;
        }
        return false;
    }

    protected function detectAnemia($row)
    {
        if (isset($row->status_gizi_hb) && $row->status_gizi_hb) {
            $s = strtolower($row->status_gizi_hb);
            return Str::contains($s, ['anemia', 'rendah', 'hb rendah']);
        }
        if (isset($row->kadar_hb) && is_numeric($row->kadar_hb)) {
            return floatval($row->kadar_hb) < 11.0; // cutoff umum anemia pada ibu hamil
        }
        return false;
    }

    protected function detectRisti($row)
    {
        if (isset($row->status_kehamilan) && $row->status_kehamilan) {
            $s = strtolower($row->status_kehamilan);
            return Str::contains($s, ['risiko', 'berisiko', 'high risk', 'risti']);
        }
        return false;
    }

    /**
     * Detect apakah intervensi untuk tipe tertentu sudah ada.
     * Tipe: 'kek' | 'anemia' | 'risti'
     * Checks:
     *  - boolean column like kek_intervensi / anemia_intervensi / risti_intervensi
     *  - or 'intervensi' text/json fields containing keywords
     */
    protected function detectIntervensiFor($group, $type)
    {
        foreach ($group as $row) {
            // direct boolean column check
            $col = $type.'_intervensi';
            if (isset($row->$col)) {
                if ($row->$col === 'Ya' || $row->$col === 1 || $row->$col === true) {
                    return true;
                }
            }

            // check 'intervensi' text/json
            if (isset($row->intervensi) && $row->intervensi) {
                $interv = $row->intervensi;
                if (is_string($interv)) {
                    $s = strtolower($interv);
                    if ($type === 'kek' && Str::contains($s, ['pmt', 'supplement', 'kek', 'gizi'])) return true;
                    if ($type === 'anemia' && Str::contains($s, ['tablet', 'fe', 'iron', 'anemia', 'hb'])) return true;
                    if ($type === 'risti' && Str::contains($s, ['rujukan', 'risky', 'high risk', 'rst'])) return true;
                } else {
                    // if JSON/array
                    try {
                        $arr = (array) $interv;
                        $flat = json_encode($arr);
                        $s = strtolower($flat);
                        if ($type === 'kek' && Str::contains($s, ['pmt','kek','pemberian makanan'])) return true;
                        if ($type === 'anemia' && Str::contains($s, ['fe','tablet','iron','anemia'])) return true;
                        if ($type === 'risti' && Str::contains($s, ['rujukan','rujuk'])) return true;
                    } catch (\Throwable $t) {
                        // ignore
                    }
                }
            }

            // also check columns like 'kek_intervensi' spelled various ways
            $altCols = [
                'kek' => ['kek_intervensi','intervensi_kek','intervensi_kek_ya'],
                'anemia' => ['anemia_intervensi','intervensi_anemia','intervensi_anemia_ya'],
                'risti' => ['risti_intervensi','intervensi_risti','intervensi_risiko']
            ];
            foreach ($altCols[$type] ?? [] as $c) {
                if (isset($row->$c) && ($row->$c === 'Ya' || $row->$c === 1 || $row->$c === true)) return true;
            }
        }
        return false;
    }

    public function indikatorBulanan(Request $request)
    {
        try {
            $query = Pregnancy::query();

            // 🔹 Filter opsional
            if ($request->kelurahan) $query->where('kelurahan', $request->kelurahan);
            if ($request->posyandu) $query->where('posyandu', $request->posyandu);
            if ($request->rw) $query->where('rw', $request->rw);
            if ($request->rt) $query->where('rt', $request->rt);

            $data = $query->get(['tgl_pendampingan', 'status_gizi']);

            // 🔹 Buat list 12 bulan terakhir
            $months = collect(range(0, 11))
                ->map(fn($i) => now()->subMonths(11 - $i)->format('M Y'))
                ->values();

            $indikatorList = ['KEK', 'Anemia', 'Berisiko', 'Normal'];
            $result = [];
            foreach ($indikatorList as $indikator) {
                $result[$indikator] = array_fill(0, 12, 0);
            }

            foreach ($data as $item) {
                if (!$item->tgl_pendampingan) continue;

                $monthKey = \Carbon\Carbon::parse($item->tgl_pendampingan)->format('M Y');
                $idx = $months->search($monthKey);
                if ($idx === false) continue;

                $status = strtolower($item->status_gizi ?? '');
                if (str_contains($status, 'kek')) {
                    $result['KEK'][$idx]++;
                } elseif (str_contains($status, 'anemia')) {
                    $result['Anemia'][$idx]++;
                } elseif (str_contains($status, 'berisiko')) {
                    $result['Berisiko'][$idx]++;
                } else {
                    $result['Normal'][$idx]++;
                }
            }

            return response()->json([
                'labels' => $months,
                'indikator' => $result
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'error' => 'Gagal memuat data indikator',
                'message' => $th->getMessage(),
            ], 500);
        }
    }


}
