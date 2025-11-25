<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Pregnancy;
use App\Models\Intervensi;
use App\Models\Cadre;
use Carbon\Carbon;
use App\Models\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class PregnancyController extends Controller
{
    public function getData(Request $request)
    {
        try {
            $user = Auth::user();

            $wilayah = [
                'kelurahan' => $user->kelurahan ?? null,
                'kecamatan' => $user->kecamatan ?? null,
                'kota' => $user->kota ?? null,
                'provinsi' => $user->provinsi ?? null,
            ];

            $data = Pregnancy::get();

            $dataRaw = $data;

            $data = $data->groupBy('nik_ibu')->map(function ($group) {
                return $group->sortByDesc('tanggal_pemeriksaan_terakhir')->first();
            });

            if (!empty($wilayah['kelurahan'])) {
                $data = $data->filter(function ($item) use ($wilayah) {
                    return $item->kelurahan === $wilayah['kelurahan'];
                });
            }

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
                };

                $start = $parsePeriode($request->periodeAwal)->startOfMonth()->format('Y-m-d');
                $end = $parsePeriode($request->periodeAkhir)->endOfMonth()->format('Y-m-d');
                // dd($start, $end);
                $data = $data->filter(function ($item) use ($start, $end) {
                    // dd($item->tanggal_pemeriksaan_terakhir >= $start && $item->tanggal_pemeriksaan_terakhir <= $end);
                    return $item->tanggal_pemeriksaan_terakhir >= $start && $item->tanggal_pemeriksaan_terakhir <= $end;
                });
            }

            if ($request->filled('status') && is_array($request->status)) {
                $data = $data->filter(function ($q) use ($request) {
                    foreach ($request->status as $status) {
                        $statusLower = strtolower($status);
                        if (str_contains($statusLower, 'kek')) {
                            if (empty($q->status_gizi_lila)) {
                                return false;
                            }
                            return $q->status_gizi_lila == 'KEK';
                        } else if (str_contains($statusLower, 'anemia')) {
                            if (empty($q->status_gizi_hb)) {
                                return false;
                            }
                            return $q->status_gizi_hb == 'Anemia';
                        } else if (str_contains($statusLower, 'risiko')) {
                            if (empty($q->status_risiko_usia)) {
                                return false;
                            }
                            return $q->status_risiko_usia == 'Berisiko';
                        }
                    }
                });
            }

            if ($request->filled('usia') && is_array($request->usia)) {
                $data = $data->filter(function ($q) use ($request) {
                    foreach ($request->usia as $range) {
                        $range = trim($range);
                        if (empty($q->usia_ibu)) {
                            return false;
                        }
                        if ($range === '<20') {
                            if ($q->usia_ibu < 20) {
                                return true;
                            }
                        } elseif ($range === '>40') {
                            if ($q->usia_ibu > 40) {
                                return true;
                            }
                        } elseif (str_contains($range, '-')) {
                            [$min, $max] = explode('-', $range);
                            if ($q->usia_ibu >= (int) $min && $q->usia_ibu <= (int) $max) {
                                return true;
                            }
                        }
                    }
                    return false;
                });
            }

            if ($request->filled('posyandu')){
                $data = $data->filter(function ($q) use ($request) {
                    if ($request->filled('rw')){
                        if ($request->filled('rt')){
                            return strtolower($q->posyandu) == strtolower($request->posyandu) && 
                                strtolower($q->rw) == strtolower($request->rw) && 
                                strtolower($q->rt) == strtolower($request->rt);
                        }
                        return strtolower($q->posyandu) == strtolower($request->posyandu) && 
                                strtolower($q->rw) == strtolower($request->rw);
                    }
                    return strtolower($q->posyandu) == strtolower($request->posyandu);
                });
            }

            if ($data->isEmpty()) {
                return response()->json([
                    'message' => 'Tidak ada data kehamilan ditemukan.',
                    'data' => [],
                ], 200);
            }
            $intervensiList = Intervensi::where('status_subjek', 'bumil')->orderBy('tgl_intervensi')->get();
            // ✅ Group data per ibu
            $groupedData = $data->map(function ($group) use ($intervensiList, $dataRaw) {
                $intervensi = $intervensiList->Where('nik_subjek', $group->nik_ibu)->map(function ($item) {
                    return [
                        'kader' => $item->petugas,
                        'tanggal' => $item->tgl_intervensi,
                        'intervensi' => $item->kategori,
                    ];
                })->values();

                $riwayat = $dataRaw->Where('nik_ibu', $group->nik_ibu)->sortBy('tanggal_pemeriksaan_terakhir')->map(function ($g) {
                    return [
                        'tanggal_pemeriksaan_terakhir' => $g->tanggal_pemeriksaan_terakhir,
                        'berat_badan' => $g->berat_badan,
                        'tinggi_badan' => $g->tinggi_badan,
                        'imt' => $g->imt,
                        'kadar_hb' => $g->kadar_hb,
                        'status_gizi_hb' => $g->status_gizi_hb,
                        'lila' => $g->lila,
                        'status_risiko_usia' => $g->status_risiko_usia,
                        'status_gizi_lila' => $g->status_gizi_lila,
                        'usia_kehamilan_minggu' => $g->usia_kehamilan_minggu,
                        'posyandu' => $g->posyandu,
                    ];
                })->values();

                return [
                    'nama_ibu' => $group->nama_ibu,
                    'nik_ibu' => $group->nik_ibu,
                    'usia_ibu' => $group->usia_ibu,
                    'nama_suami' => $group->nama_suami,
                    'nik_suami' => $group->nik_suami,
                    'kehamilan_ke' => $group->kehamilan_ke,
                    'jumlah_anak' => $group->jumlah_anak,
                    'status_risiko_usia' => $group->status_risiko_usia,
                    'status_gizi_hb' => $group->status_gizi_hb,
                    'status_gizi_lila' => $group->status_gizi_lila,
                    'provinsi' => $group->provinsi,
                    'kota' => $group->kota,
                    'kecamatan' => $group->kecamatan,
                    'kelurahan' => $group->kelurahan,
                    'rt' => $group->rt,
                    'rw' => $group->rw,
                    'tanggal_pendampingan' => $group->tanggal_pendampingan,
                    'riwayat_pemeriksaan' => $riwayat,
                    'hpl' => $group->first()->hpl,
                    'intervensi' => $intervensi ?? null,
                ];
            });
            if ($request->filled('intervensi') && is_array($request->intervensi)) {
                $groupedData = $groupedData->filter(function ($q) use ($request) {
                    foreach ($request->intervensi as $val) {
                        $jenisIntervensi = ['MBG', 'KIE', 'PMT', 'Bansos'];
                        if ($val === "Belum Mendapatkan Intervensi") {
                            if (empty($q['intervensi']) || $q['intervensi']->isEmpty()) {
                                return true;
                            }
                        } else {
                            if (in_array($val, $jenisIntervensi) && !empty($q['intervensi']) && $q['intervensi']->isNotEmpty()) {
                                $found = false;
                                $q['intervensi']->each(function ($intervensiItem) use ($val, &$found) {
                                    if (Str::lower($intervensiItem['intervensi']) === Str::lower($val)) {
                                        $found = true;
                                    }
                                });
                                return $found;
                            }
                            if ($val == "Bantuan Lainnya"){
                                $found = false;
                                $q['intervensi']->each(function ($intervensiItem) use (&$found) {
                                    if (!in_array(Str::lower($intervensiItem['intervensi']), ['mbg', 'kie', 'pmt', 'bansos'])) {
                                        $found = true;
                                    }
                                });
                                return $found;
                            }
                        }
                    }
                    return false;
                });
            }

            if ($groupedData->isEmpty()) {
                return response()->json([
                    'message' => 'Tidak ada data kehamilan ditemukan.',
                    'data' => [],
                ], 200);
            }
            return $groupedData;
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Gagal mengambil data kehamilan',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function index(Request $request)
    {
        $data = $this->getData($request);

        if ($data instanceof \Illuminate\Http\JsonResponse) {
            return $data;
        }

        $total = $data->count();


        $count = [
            'Anemia' => 0,
            'KEK' => 0,
            'Berisiko' => 0,
            'Normal' => 0,
            'Total Bumil' => $data->count(),
        ];

        foreach ($data as $row) {
            $hbStatus = strtoupper($row['status_gizi_hb'] ?? '');
            $lilaStatus = strtoupper($row['status_gizi_lila'] ?? '');
            $riskStatus = strtoupper($row['status_risiko_usia'] ?? '');

            if ($hbStatus === 'ANEMIA')
                $count['Anemia']++;
            if ($lilaStatus === 'KEK')
                $count['KEK']++;
            if ($riskStatus === 'BERISIKO')
                $count['Berisiko']++;

            // Normal = semua normal
            if ($hbStatus === 'NORMAL' && $lilaStatus === 'NORMAL' && $riskStatus === 'NORMAL') {
                $count['Normal']++;
            }
        }


        foreach ($count as $title => $value) {

            $color = match ($title) {
                'KEK' => 'danger',
                'Anemia' => 'warning',
                'Berisiko' => 'violet',
                'Normal' => 'success',
                'Total Bumil' => 'secondary'
            };

            $percent = $total ? round(($value / $total) * 100, 1) : 0;

            $result[] = [
                'title' => $title,
                'value' => $value,
                'percent' => "{$percent}%",
                'color' => $color,
            ];
        }

        return response()->json([
            'total' => $total,
            'data' => $data->values(),
            'counts' => $result
        ], 200);
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
            if (empty($data[0]))
                continue;

            if (!$header) {
                $header = $data;
                continue;
            }

            $count++;

            // ✅ Ambil nilai mentah
            $usia_ibu = floatval($data[4] ?? 0);
            $tinggi_badan = $this->sanitizeNumeric($data[23] ?? 0);
            $hb = $this->sanitizeNumeric($data[25] ?? 0);
            $lila = $this->sanitizeNumeric($data[27] ?? 0);
            $riwayat_4t = strtolower(trim($data[12] ?? ''));

            // ✅ 1. Status Gizi LILA
            // Berdasarkan Kemenkes: KEK jika LILA < 23.5 cm
            $status_lila = null;
            if ($lila > 0) {
                $status_lila = ($lila < 23.5) ? 'KEK' : 'Normal';
            }

            // ✅ 2. Status Gizi Hb
            // Berdasarkan WHO: anemia jika Hb < 11 g/dL
            $status_hb = null;
            if ($hb > 0) {
                $status_hb = ($hb < 11) ? 'Anemia' : 'Normal';
            }

            // ✅ 3. Status Risiko Usia (berdasarkan 4T)
            $status_risiko_final = ($usia_ibu < 20 || $usia_ibu > 35)
                ? 'Berisiko'
                : 'Normal';

            $tinggi_badan = floatval(preg_replace('/[^0-9.]/', '', $data[23] ?? 0));

            // Jika nilainya terlalu besar (misal 15512 cm, harusnya 155)
            if ($tinggi_badan > 300) {
                $tinggi_badan = $tinggi_badan / 100; // ubah ke cm realistis
            }

            // Hindari nilai tidak wajar
            if ($tinggi_badan < 50 || $tinggi_badan > 250) {
                $tinggi_badan = null;
            }

            $imt = $this->hitungIMT($data[22], $tinggi_badan);

            // ✅ Mapping kolom CSV ke field sesuai model Pregnancy
            $rows[] = [
                'nama_petugas' => $data[0] ?? null,
                'tanggal_pendampingan' => $this->convertDate($data[1] ?? null),
                'nama_ibu' => $data[2] ?? null,
                'nik_ibu' => $data[3] ?? null,
                'usia_ibu' => $usia_ibu ?: null,
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
                'tinggi_badan' => $tinggi_badan,
                'kadar_hb' => $hb,
                'status_gizi_hb' => $status_hb,
                'lila' => $lila,
                'status_gizi_lila' => $status_lila,
                'status_risiko_usia' => $usia_ibu < 20 || $usia_ibu > 35 ? 'Berisiko' : 'Normal',
                'riwayat_penyakit' => $data[29] ?? null,
                'usia_kehamilan_minggu' => intval($data[30] ?? 0),
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
                'rencana_tempat_melahirkan' => $data[32] ?? null,
                'rencana_asi_eksklusif' => $data[43] ?? null,
                'rencana_tinggal_setelah' => $data[44] ?? null,
                'rencana_kontrasepsi' => $data[45] ?? null,
                'posyandu' => $data[46] ?? null,
                'imt' => $imt
            ];
        }

        fclose($handle);

        // ✅ Simpan ke DB
        if (!empty($rows)) {
            foreach ($rows as $row) {
                Pregnancy::create($row);

                $wilayah = \App\Models\Wilayah::firstOrCreate([
                    'provinsi' => $row['provinsi'],
                    'kota' => $row['kota'],
                    'kecamatan' => $row['kecamatan'],
                    'kelurahan' => $row['kelurahan'],
                ]);

                \App\Models\Posyandu::firstOrCreate([
                    'nama_posyandu' => $row['posyandu'] ?? '-',
                    'id_wilayah' => $wilayah->id,
                    'rt' => $row['rt'] ?? null,
                    'rw' => $row['rw'] ?? null,
                ]);

                Log::create([
                    'id_user' => Auth::id(),
                    'context' => 'Ibu Hamil',
                    'activity' => 'Import data kehamilan ibu ' . ($row['nama_ibu'] ?? '-'),
                    'timestamp' => now(),
                ]);
            }
        }

        return response()->json([
            'message' => "Berhasil import {$count} data kehamilan",
            'count' => $count,
            'row' => $rows
        ], 200);


    }

    // Tambahkan fungsi helper di dalam class
    private function sanitizeNumeric($value)
    {
        // Hapus semua karakter kecuali angka, koma, titik
        $clean = preg_replace('/[^0-9.,]/', '', $value);

        // Ganti koma jadi titik
        $clean = str_replace(',', '.', $clean);

        // Hapus titik ganda atau salah posisi
        $clean = preg_replace('/\.{2,}/', '.', $clean);

        return is_numeric($clean) ? floatval($clean) : null;
    }

    public function import_intervensi(Request $request)
    {
        if (!$request->hasFile('file')) {
            return response()->json(['message' => 'Tidak ada file yang diunggah'], 400);
        }

        $file = $request->file('file');
        $path = $file->getRealPath();
        $handle = fopen($path, 'r');

        $rows = [];
        $count = 0;

        while (($row = fgetcsv($handle, 1000, ',', '"')) !== false) {
            // Lewati baris kosong atau header
            if ($count === 0 && str_contains(strtolower($row[0]), 'petugas')) {
                $count++;
                continue;
            }
            if (empty($row[0]) && empty($row[3]))
                continue;

            if (count($row) < 16) {
                \Log::warning('Baris CSV tidak lengkap:', $row);
                continue;
            }

            Intervensi::create([
                'petugas' => $row[0] ?? null,
                'tgl_intervensi' => isset($row[1]) ? date('Y-m-d', strtotime($row[1])) : null,
                'desa' => $row[2] ?? null,
                'nama_subjek' => $row[3] ?? null,
                'nik_subjek' => $row[4] ?? null,
                'status_subjek' => 'bumil',
                'jk' => $row[5] ?? null,
                'tgl_lahir' => isset($row[6]) ? date('Y-m-d', strtotime($row[6])) : null,
                'umur_subjek' => !empty($row[7]) ? $row[7] : floor($this->hitungUmurTahun(trim($row[6]), trim($row[1]))) . ' Tahun',
                'nama_wali' => $row[8] ?? null,
                'nik_wali' => $row[9] ?? null,
                'status_wali' => $row[10] ?? null,
                'rt' => $row[11] ?? null,
                'rw' => $row[12] ?? null,
                'posyandu' => $row[13] ?? null,
                'bantuan' => $row[14] ?? null,
                'kategori' => $row[15] ?? null,
            ]);

            $count++;
        }

        fclose($handle);

        return response()->json(['message' => "Berhasil impor {$count} data intervensi.", 'data' => $rows]);
    }

    /** Hitung umur (tahun) */
    private function hitungUmurTahun($tglLahir, $tglUkur)
    {
        if (!$tglLahir || !$tglUkur)
            return null;
        return Carbon::parse($tglLahir)->diffInYears(Carbon::parse($tglUkur));
    }

    private function convertDate($date)
    {
        if (!$date)
            return null;
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

            // =====================================
            // 1. Ambil wilayah user
            // =====================================
            $anggotaTPK = Cadre::where('id_user', $user->id)->first();

            if (!$anggotaTPK) {
                return response()->json(['message' => 'User tidak terdaftar dalam anggota TPK'], 404);
            }

            $posyandu = $anggotaTPK->posyandu;
            $wilayah = $posyandu?->wilayah;

            if (!$wilayah) {
                return response()->json(['message' => 'Wilayah tidak ditemukan'], 404);
            }

            // =====================================
            // 2. Tentukan periode (default H-1 bulan)
            // =====================================
            if ($request->filled('periode')) {
                $periode = Carbon::createFromFormat('Y-m', $request->periode);
                $periodeAkhir = $periode->copy()->endOfMonth();
                $periodeAwal = $periode->copy()->startOfMonth();
            } else {
                $periode = now()->subMonths(1);
                $periodeAkhir = $periode->copy()->endOfMonth();
                $periodeAwal = $periode->copy()->startOfMonth();
            }


            $data = Pregnancy::get();

            $data = $data->groupBy('nik_ibu')->map(function ($group) {
                return $group->sortByDesc('tanggal_pemeriksaan_terakhir')->first();
            });

            $dataRaw = $data;

            if (!empty($wilayah->kelurahan)) {
                $data = $data->filter(function ($item) use ($wilayah) {
                    return strtolower($item->kelurahan) === strtolower($wilayah->kelurahan);
                });
            }

            $data = $data->filter(function ($item) use ($periodeAwal, $periodeAkhir) {
                return $item->tanggal_pemeriksaan_terakhir >= $periodeAwal->format('Y-m-d') &&
                    $item->tanggal_pemeriksaan_terakhir <= $periodeAkhir->format('Y-m-d');
            });


            if ($request->posyandu) {
                $data = $data->filter(function ($item) use ($request) {
                    return strtolower($item->posyandu) === strtolower($request->posyandu);
                });
            }

            if ($request->rw) {
                $data = $data->filter(function ($item) use ($request) {
                    return strtolower($item->rw) === strtolower($request->rw);
                });
            }

            if ($request->rt) {
                $data = $data->filter(function ($item) use ($request) {
                    return strtolower($item->rt) === strtolower($request->rt);
                });
            }


            if ($data->isEmpty()) {
                return response()->json([
                    'total' => 0,
                    'counts' => [
                        ['title' => 'Anemia', 'value' => 0, 'percent' => '0%', 'color' => 'warning', 'trend' => []],
                        ['title' => 'KEK', 'value' => 0, 'percent' => '0%', 'color' => 'danger', 'trend' => []],
                        ['title' => 'Berisiko', 'value' => 0, 'percent' => '0%', 'color' => 'violet', 'trend' => []],
                        ['title' => 'Normal', 'value' => 0, 'percent' => '0%', 'color' => 'success', 'trend' => []],
                        ['title' => 'Total Bumil', 'value' => 0, 'percent' => '0%', 'color' => 'secondary', 'trend' => []],
                    ],
                    'kelurahan' => $wilayah->kelurahan,
                ]);
            }

            $total = $data->count();

            // =====================================
            // 5. Hitung status berdasarkan FIELD BARU
            // =====================================
            $count = [
                'Anemia' => 0,
                'KEK' => 0,
                'Berisiko' => 0,
                'Normal' => 0,
                'Total Bumil' => $total,
            ];

            foreach ($data as $row) {

                $hbStatus = strtoupper($row->status_gizi_hb ?? '');
                $lilaStatus = strtoupper($row->status_gizi_lila ?? '');
                $riskStatus = strtoupper($row->status_risiko_usia ?? '');

                if ($hbStatus === 'ANEMIA')
                    $count['Anemia']++;
                if ($lilaStatus === 'KEK')
                    $count['KEK']++;
                if ($riskStatus === 'BERISIKO')
                    $count['Berisiko']++;

                // Normal = semua normal
                if ($hbStatus === 'NORMAL' && $lilaStatus === 'NORMAL' && $riskStatus === 'NORMAL') {
                    $count['Normal']++;
                }
            }

            // =====================================
            // 6. TREND 3 BULAN TERAKHIR
            // =====================================
            $trendCount = [];

            $monthsToTrend = 6;
            foreach ($count as $status => $v) {

                $trend = collect();

                for ($i = ($monthsToTrend - 1); $i >= 0; $i--) {

                    $tgl = $periode->copy();
                    $tgl->subMonths($i);
                    $awal = $tgl->copy()->startOfMonth()->format('Y-m-d');
                    $akhir = $tgl->copy()->endOfMonth()->format('Y-m-d');

                    $monthData = $dataRaw->filter(function ($item) use ($awal, $akhir) {
                        return $item->tanggal_pemeriksaan_terakhir >= $awal &&
                            $item->tanggal_pemeriksaan_terakhir <= $akhir;
                    });
                    $groupedMonth = $monthData->groupBy('nik_ibu')->map(function ($group) {
                        return $group->sortByDesc('tanggal_pemeriksaan_terakhir')->first();
                    });

                    $totalMonth = $groupedMonth->count();
                    $jumlah = 0;

                    foreach ($groupedMonth as $row) {
                        $hbStatus = strtoupper($row->status_gizi_hb ?? '');
                        $lilaStatus = strtoupper($row->status_gizi_lila ?? '');
                        $riskStatus = strtoupper($row->status_risiko_usia ?? '');

                        if ($status === 'Anemia' && $hbStatus === 'ANEMIA')
                            $jumlah++;
                        if ($status === 'KEK' && $lilaStatus === 'KEK')
                            $jumlah++;
                        if ($status === 'Berisiko' && $riskStatus === 'BERISIKO')
                            $jumlah++;
                        if (
                            $hbStatus == 'NORMAL'
                            && $lilaStatus == 'NORMAL'
                            && $riskStatus == 'NORMAL'
                        ) {
                            $jumlah++;
                        }

                        if ($status === 'Total Bumil') {
                            $jumlah = $totalMonth;
                        }
                    }

                    $persen = $totalMonth ? round(($jumlah / $totalMonth) * 100, 1) : 0;

                    $trend->push([
                        'bulan' => $tgl->format('M'),
                        'persen' => $persen,
                        'jumlah' => $jumlah,
                        'total' => $totalMonth,
                    ]);
                }

                $trendCount[$status] = $trend;
            }

            // =====================================
            // 7. Format output
            // =====================================
            $result = [];

            foreach ($count as $title => $value) {

                $color = match ($title) {
                    'KEK' => 'danger',
                    'Anemia' => 'warning',
                    'Berisiko' => 'violet',
                    'Normal' => 'success',
                    'Total Bumil' => 'secondary'
                };

                $percent = $total ? round(($value / $total) * 100, 1) : 0;

                $result[] = [
                    'title' => $title,
                    'value' => $value,
                    'percent' => "{$percent}%",
                    'color' => $color,
                    'trend' => $trendCount[$title],
                ];
            }

            return response()->json([
                'total' => $total,
                'counts' => $result,
                'kelurahan' => $wilayah->kelurahan,
                'final' => $data->values(),
                'groupMonth' => $groupedMonth
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Gagal mengambil data status bumil',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * 🔹 Helper untuk parsing "Juni 2025" → Carbon date
     */
    private function parseBulanTahun(string $periode, bool $isAkhir = false): Carbon
    {
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

        $parts = explode(' ', strtolower(trim($periode)));
        if (count($parts) === 2 && isset($bulanMap[$parts[0]])) {
            [$namaBulan, $tahun] = $parts;
            $bulan = $bulanMap[$namaBulan];
            $date = Carbon::createFromDate($tahun, $bulan, 1);
            return $isAkhir ? $date->endOfMonth() : $date->startOfMonth();
        }

        // fallback: coba parse langsung format "YYYY-MM" atau "YYYY-MM-DD"
        return Carbon::parse($periode);
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
                    'status_gizi' => $latest->status_risiko_usia ?? '-',
                ];

            // ✅ Riwayat Pemeriksaan (3 terakhir)
            $riwayatPemeriksaan = $records->take(5)->map(function ($item) {
                return [
                    'tanggal' => optional($item->tanggal_pemeriksaan_terakhir)->format('Y-m-d'),
                    'anemia' => $item->status_gizi_hb ?? '-',
                    'kek' => $item->status_gizi_lila ?? '-',
                    'risiko' => $item->status_risiko_usia ?? '-',
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
                    'tanggal' => '',
                    'kader' => '',
                    'intervensi' => '',
                ],
            ]);

            // ✅ Data Kehamilan (semua record)
            $dataKehamilan = $records->map(function ($item) {
                return [
                    'tgl_pendampingan' => optional($item->tanggal_pendampingan)->format('Y-m-d'),
                    'kehamilan_ke' => $item->kehamilan_ke ?? '-',
                    'risiko' => $item->status_risiko_usia ?? '-',
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

            if (!$user) {
                return response()->json(['message' => 'User tidak ditemukan'], 404);
            }

            // 1️⃣ Wilayah default dari user
            $wilayah = [
                'kelurahan' => $user->kelurahan ?? null,
                'kecamatan' => $user->kecamatan ?? null,
                'kota' => $user->kota ?? null,
                'provinsi' => $user->provinsi ?? null,
            ];

            $query = Pregnancy::query();

            // Filter default wilayah user
            if (!empty($wilayah['kelurahan'])) {
                $query->where('kelurahan', $wilayah['kelurahan']);
            }

            // 2️⃣ Filter tambahan request
            foreach (['kelurahan', 'posyandu', 'rw', 'rt'] as $f) {
                if ($request->filled($f)) {
                    $query->where($f, $request->$f);
                }
            }

            // 3️⃣ Tentukan current & previous month
            if ($request->filled('periode')) {
                $periode = Carbon::createFromFormat('Y-m', $request->periode)->startOfMonth();

                $currentMonth = $periode->format('Y-m');
                $previousMonth = $periode->copy()->subMonth()->format('Y-m');
            } else {
                $currentMonth = now()->subMonth()->format('Y-m');
                $previousMonth = now()->subMonths(2)->format('Y-m');
            }

            // 4️⃣ Ambil data all (tanpa filter bulan)
            $allData = $query->get();

            // 5️⃣ Filter current & previous berdasarkan bulan
            $current = $allData->filter(
                fn($i) =>
                Carbon::parse($i->tanggal_pemeriksaan_terakhir)->format('Y-m') === $currentMonth
            );

            $previous = $allData->filter(
                fn($i) =>
                Carbon::parse($i->tanggal_pemeriksaan_terakhir)->format('Y-m') === $previousMonth
            );

            // 6️⃣ Fungsi hitung status
            $countStatus = function ($rows) {
                $total = $rows->count();

                return [
                    'total' => $total,
                    'KEK' => $rows->filter(fn($r) => $this->detectKek($r))->count(),
                    'Anemia' => $rows->filter(fn($r) => $this->detectAnemia($r))->count(),
                    'Risiko Usia' => $rows->filter(fn($r) => $this->detectRisti($r))->count(),
                ];
            };

            $currCount = $countStatus($current);
            $prevCount = $countStatus($previous);

            // 7️⃣ Susun tabel seperti gizi anak
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

            // Jika kosong, isi default
            if (($currCount['total'] ?? 0) === 0) {
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
                'dataTable_bumil' => $dataTable,
                'periode' => [
                    'current' => $currentMonth,
                    'previous' => $previousMonth,
                ],
            ], 200);

        } catch (\Exception $e) {
            \Log::error('tren bumil error: ' . $e->getMessage());
            return response()->json([
                'message' => 'Gagal menghitung tren bumil',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function case(Request $request)
    {
        $user = Auth::user();

        // 1. Ambil data anggota TPK
        $anggotaTPK = Cadre::where('id_user', $user->id)->first();
        if (!$anggotaTPK) {
            return response()->json(['message' => 'User tidak terdaftar dalam anggota TPK'], 404);
        }

        // 2. Ambil posyandu & wilayah
        $posyandu = $anggotaTPK->posyandu;
        $wilayah = $posyandu?->wilayah;
        if (!$wilayah) {
            return response()->json(['message' => 'Wilayah tidak ditemukan untuk user ini'], 404);
        }

        // 3. Default filter kelurahan user
        $filterKelurahan = $wilayah->kelurahan ?? null;

        $query = Pregnancy::query();
        if ($filterKelurahan) {
            $query->where('kelurahan', $filterKelurahan);
        }

        // 4. Filter manual (opsional) dari UI
        if ($request->filled('posyandu'))
            $query->where('posyandu', $request->posyandu);
        if ($request->filled('rw'))
            $query->where('rw', $request->rw);
        if ($request->filled('rt'))
            $query->where('rt', $request->rt);

        // 5. Tentukan periode current & previous
        if ($request->filled('periode') && strlen($request->periode) >= 7) {
            $tanggal = Carbon::createFromFormat('Y-m', $request->periode);
            $bulanIni = $tanggal->format('Y-m');
            $bulanLalu = $tanggal->subMonth()->format('Y-m');
        } else {
            $bulanIni = now()->subMonth()->format('Y-m');
            $bulanLalu = now()->subMonths(2)->format('Y-m');
        }


        // 6. Ambil seluruh data Kunjungan yg relevan
        $data = $query->get();

        // ========== 7. Hitung CASUS berdasarkan NIK IBU (unique) ==========
        $nik_case = $data->filter(function ($item) {
            return
                ($item->status_gizi_hb && $item->status_gizi_hb !== 'Normal') ||
                ($item->status_gizi_lila && $item->status_gizi_lila !== 'Normal') ||
                ($item->status_risiko_usia && $item->status_risiko_usia !== null && $item->status_risiko_usia !== 'Normal');
        })->pluck('nik_ibu')->unique();

        // total kasus = jumlah ibu bermasalah
        $totalCase = $nik_case->count();

        // Jika kamu masih butuh grouping kategori, sesuaikan ke unique nik:
        $ane_kek = $data->filter(function ($item) {
            return $item->status_gizi_hb !== 'Normal'
                || $item->status_gizi_lila !== 'Normal';
        })->pluck('nik_ibu')->unique()->count();

        $ris_ane = $data->filter(function ($item) {
            return $item->status_risiko_usia !== null && $item->status_risiko_usia !== 'Normal'
                && $item->status_gizi_hb !== 'Normal';
        })->pluck('nik_ibu')->unique()->count();

        $ris_kek = $data->filter(function ($item) {
            return $item->status_risiko_usia !== null && $item->status_risiko_usia !== 'Normal'
                && $item->status_gizi_lila !== 'Normal';
        })->pluck('nik_ibu')->unique()->count();


        $totalCase = $ane_kek + $ris_ane + $ris_kek;
        return response()->json([
            'status' => 'success',
            'message' => 'Data anak berhasil dimuat',
            'grouping' => [
                'ane_kek' => $ane_kek,
                'ris_kek' => $ris_kek,
                'ris_ane' => $ris_ane,
            ],
            'totalCase' => $totalCase
        ]);

    }

    public function intervensiSummary(Request $request)
    {
        try {
            $user = Auth::user();

            // 🔹 Wilayah user default
            $wilayah = [
                'kelurahan' => $user->kelurahan ?? null,
                'kecamatan' => $user->kecamatan ?? null,
                'kota' => $user->kota ?? null,
                'provinsi' => $user->provinsi ?? null,
            ];

            $data = Pregnancy::get();

            $dataRaw = $data;

            $data = $data->groupBy('nik_ibu')->map(function ($group) {
                return $group->sortByDesc('tanggal_pemeriksaan_terakhir')->first();
            });

            // 🔹 Filter wilayah (prioritas: request > user)
            foreach (['kelurahan', 'posyandu', 'rw', 'rt'] as $f) {
                if ($request->filled($f)) {
                    $data = $data->filter(function ($item) use ($request, $f) {
                        return strtolower($item->kelurahan) == strtolower($request[$f]);
                    });
                } elseif (!empty($wilayah[$f] ?? null)) {
                    $data = $data->where(strtolower($f), strtolower($wilayah[$f]));
                }
            }

            // 🔹 Penentuan periode
            if ($request->filled('periodeAwal') && $request->filled('periodeAkhir')) {
                $awal = Carbon::parse($request->periodeAwal)->startOfDay();
                $akhir = Carbon::parse($request->periodeAkhir)->endOfDay();
            } elseif ($request->filled('periode')) {
                $periode = trim($request->periode);
                if (preg_match('/^\d{4}-\d{2}$/', $periode)) {
                    $awal = Carbon::createFromFormat('Y-m', $periode)->startOfMonth();
                    $akhir = (clone $awal)->endOfMonth();
                } else {
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
                        'desember' => 12
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

            if (!isset($awal) || !isset($akhir)) {
                $periode = Carbon::now()->subMonth();
                $akhir = (clone $periode)->endOfMonth();
                $awal = (clone $periode)->startOfMonth();
            }
            $data = $data->filter(function ($item) use ($awal, $akhir) {
                return $item->tanggal_pemeriksaan_terakhir >= $awal && $item->tanggal_pemeriksaan_terakhir <= $akhir;
            });

            // 🔹 Ambil data
            $rows = $data;

            if ($rows->isEmpty()) {
                return response()->json([
                    'message' => 'Tidak ada data kehamilan ditemukan.',
                    'dataTable_bumil' => [],
                    'total' => 0,
                ]);
            }

            // 🔹 Group by nik_ibu
            $grouped = $rows->map(function ($latest) use ($dataRaw) {
                $riwayat = $dataRaw->where('nik_ibu', $latest->nik_ibu)->sortBy('tanggal_pemeriksaan_terakhir')->map(function ($g) {
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

                // 🔹 Deteksi kondisi (case-insensitive)
                $isKek = str_contains(strtolower(trim($latest->status_gizi_lila ?? '')), 'kek');
                $isAnemia = str_contains(strtolower(trim($latest->status_gizi_hb ?? '')), 'anemia');
                $isRisti = str_contains(strtolower(trim($latest->status_risiko_usia ?? '')), 'berisiko');

                // 🔹 Deteksi intervensi
                $kekInterv = $this->detectIntervensiFor($latest, 'kek');
                $anemiaInterv = $this->detectIntervensiFor($latest, 'anemia');
                $ristiInterv = $this->detectIntervensiFor($latest, 'risti');

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
                    'kek' => $isKek ? 'Ya' : 'Tidak',
                    'kek_intervensi' => $kekInterv ? 'Ya' : 'Tidak',
                    'anemia' => $isAnemia ? 'Ya' : 'Tidak',
                    'anemia_intervensi' => $anemiaInterv ? 'Ya' : 'Tidak',
                    'risti' => $isRisti ? 'Ya' : 'Tidak',
                    'risti_intervensi' => $ristiInterv ? 'Ya' : 'Tidak',
                ];
            });

            return response()->json([
                'total' => $grouped->count(),
                'dataTable_bumil' => $grouped->values()->all(),
            ]);
        } catch (\Throwable $e) {
            \Log::error('intervensiSummary error: ' . $e->getMessage());
            return response()->json([
                'message' => 'Gagal mengambil data intervensi bumil',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

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
            return Str::contains($s, ['anemia', 'rendah', 'hb rendah', 'ya']);
        }
        if (isset($row->kadar_hb) && is_numeric($row->kadar_hb)) {
            return floatval($row->kadar_hb) < 11.0; // cutoff umum anemia pada ibu hamil
        }
        return false;
    }

    protected function detectRisti($row)
    {
        if (isset($row->status_risiko_usia) && $row->status_risiko_usia) {
            $s = strtolower($row->status_risiko_usia);
            return Str::contains($s, ['berisiko']);
        }
        return false;
    }

    protected function detectIntervensiFor($group, $type)
    {
        foreach ($group as $row) {
            // direct boolean column check
            $col = $type . '_intervensi';
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
                    if ($type === 'kek' && Str::contains($s, ['pmt', 'kie', 'bansos', 'mbg', 'lainnya']))
                        return true;
                    if ($type === 'anemia' && Str::contains($s, ['pmt', 'kie', 'bansos', 'mbg', 'lainnya']))
                        return true;
                    if ($type === 'risti' && Str::contains($s, ['pmt', 'kie', 'bansos', 'mbg', 'lainnya']))
                        return true;
                } else {
                    // if JSON/array
                    try {
                        $arr = (array) $interv;
                        $flat = json_encode($arr);
                        $s = strtolower($flat);
                        if ($type === 'kek' && Str::contains($s, ['pmt', 'kie', 'bansos', 'mbg', 'lainnya']))
                            return true;
                        if ($type === 'anemia' && Str::contains($s, ['pmt', 'kie', 'bansos', 'mbg', 'lainnya']))
                            return true;
                        if ($type === 'risti' && Str::contains($s, ['pmt', 'kie', 'bansos', 'mbg', 'lainnya']))
                            return true;
                    } catch (\Throwable $t) {
                        // ignore
                    }
                }
            }

            // also check columns like 'kek_intervensi' spelled various ways
            $altCols = [
                'kek' => ['kek_intervensi', 'intervensi_kek', 'intervensi_kek_ya'],
                'anemia' => ['anemia_intervensi', 'intervensi_anemia', 'intervensi_anemia_ya'],
                'risti' => ['risti_intervensi', 'intervensi_risti', 'intervensi_risiko']
            ];
            foreach ($altCols[$type] ?? [] as $c) {
                if (isset($row->$c) && ($row->$c === 'Ya' || $row->$c === 1 || $row->$c === true))
                    return true;
            }
        }
        return false;
    }


    public function intervensi(Request $request)
    {
        $user = Auth::user();

        // 1️⃣ Ambil data anggota TPK
        $anggotaTPK = Cadre::where('id_user', $user->id)->first();
        if (!$anggotaTPK) {
            return response()->json(['message' => 'User tidak terdaftar dalam anggota TPK'], 404);
        }

        // 2️⃣ Ambil posyandu & wilayah
        $posyandu = $anggotaTPK->posyandu;
        $wilayah = $posyandu?->wilayah;
        if (!$wilayah) {
            return response()->json(['message' => 'Wilayah tidak ditemukan untuk user ini'], 404);
        }

        $filterKelurahan = $wilayah->kelurahan ?? null;

        // ==========================
        // Tentukan periode (Y-m)
        // ==========================
        if ($request->filled('periode')) {
            $tanggal = Carbon::createFromFormat('Y-m', $request->periode);
        } else {
            $tanggal = now()->subMonth(); // default bulan berjalan -1
        }

        // ==========================
        // A. QUERY KUNJUNGAN BUMIL
        // ==========================
        $data = Pregnancy::get();

        $data = $data->groupBy('nik_ibu')->map(function ($group) {
            return $group->sortByDesc('tanggal_pemeriksaan_terakhir')->first();
        });


        if ($filterKelurahan)
            $data = $data->filter(function ($item) use ($filterKelurahan) {
                return strtolower($item->kelurahan) == strtolower($filterKelurahan);
            });


        if ($request->filled('posyandu'))
            $data = $data->filter(function ($item) use ($request) {
                return strtolower($item->posyandu) == strtolower($request->posyandu);
            });
        if ($request->filled('rw'))
            $data = $data->filter(function ($item) use ($request) {
                return $item->rw == $request->rw;
            });
        if ($request->filled('rt'))
            $data = $data->filter(function ($item) use ($request) {
                return $item->rt == $request->rt;
            });

        $data = $data->filter(function ($item) use ($tanggal) {
            $tgl = Carbon::parse($item->tanggal_pendampingan);
            return $tgl->year == $tanggal->year && $tgl->month == $tanggal->month;
        });


        $dataGanda = $data->filter(function ($item) {
            $ganda = 0;
            if ($item->status_gizi_lila == 'KEK')
                $ganda++;   // KEK
            if ($item->status_gizi_hb == 'Anemia')
                $ganda++;       // Anemia
            if ($item->status_risiko_usia == 'Berisiko')
                $ganda++; // Berisiko
            return $ganda >= 2; // minimal 2 parameter bermasalah → status ganda
        });

        $nikCase = $dataGanda->pluck('nik_ibu')->unique();

        // ==========================
        // B. QUERY INTERVENSI BUMIL
        // ==========================
        $dataIntervensi = Intervensi::where('status_subjek', 'bumil')
            ->whereIn('nik_subjek', $nikCase->toArray())->get();

        // Filter periode
        $dataIntervensi = $dataIntervensi->filter(function ($item) use ($tanggal) {
            $tglIntervensi = Carbon::parse($item->tgl_intervensi);
            $tglFilter = Carbon::create($tanggal->year, $tanggal->month, 1)->endOfMonth();

            return $tglIntervensi <= $tglFilter;
        });


        $dataAll = $dataGanda->map(function ($item) use ($dataIntervensi) {
            $intervensiUntukIbu = $dataIntervensi->filter(function ($interv) use ($item) {
                return $interv->nik_subjek == $item->nik_ibu;
            });

            return [
                'nik' => $item->nik_ibu,
                'nama' => $item->nama_ibu,
                'kelurahan' => $item->kelurahan,
                'posyandu' => $item->posyandu,
                'rt' => $item->rt,
                'rw' => $item->rw,
                'umur' => $item->usia_ibu,
                'data_kunjungan' => $item,
                'data_intervensi' => $intervensiUntukIbu->values(),
            ];
        });

        return response()->json([
            'status' => 'success',
            'message' => 'Data intervensi & kunjungan bumil berhasil dimuat',

            'grouping' => [
                'total_case' => $dataGanda->count(),
                'punya_keduanya' => $dataIntervensi->count(),
                'hanya_kunjungan' => $dataGanda->count() - $dataIntervensi->count(),
            ],

            'detail' => [
                'punya_keduanya' => $dataAll->filter(function ($item) {
                    return $item['data_intervensi']->isNotEmpty();
                })->values(),
                'hanya_kunjungan' => $dataAll->filter(function ($item) {
                    return $item['data_intervensi']->isEmpty();
                })->values(),
            ],
        ]);
    }

    public function indikatorBulanan(Request $request)
    {
        try {
            $query = Pregnancy::query();

            $query->where('kelurahan', $request->kelurahan);

            foreach (['posyandu', 'rw', 'rt'] as $f) {
                if ($request->filled($f))
                    $query->where($f, $request->$f);
            }

            $startDate = now()->subMonths(11)->startOfMonth();
            $endDate = now()->endOfMonth();

            $query->whereBetween('tanggal_pemeriksaan_terakhir', [$startDate, $endDate]);

            $data = $query->get([
                'nik_ibu',
                'tanggal_pemeriksaan_terakhir',
                'status_gizi_lila',
                'status_gizi_hb',
                'status_risiko_usia'
            ]);

            $data = $data->groupBy('nik_ibu')->map(function ($group) {
                return $group->sortByDesc('tanggal_pemeriksaan_terakhir')->first();
            });

            if ($data->isEmpty()) {
                return response()->json([
                    'labels' => [],
                    'indikator' => [],
                ]);
            }

            $months = collect(range(0, 11))
                ->map(fn($i) => now()->subMonths(11 - $i)->format('M Y'))
                ->values();

            $indikatorList = ['KEK', 'Anemia', 'Berisiko'];
            $result = [];
            foreach ($indikatorList as $indikator) {
                $result[$indikator] = array_fill(0, 12, 0);
            }

            // Group per bulan, ambil semua record
            $groupedByMonth = $data->groupBy(function ($item) {
                return Carbon::parse($item->tanggal_pemeriksaan_terakhir)->format('Y-m');
            });

            // Hitung
            foreach ($groupedByMonth as $monthKey => $rows) {
                $label = Carbon::createFromFormat('Y-m', $monthKey)->format('M Y');
                $idx = $months->search($label);
                if ($idx === false)
                    continue;

                $result['KEK'][$idx] = $rows->filter(
                    fn($i) =>
                    str_contains(strtolower($i->status_gizi_lila ?? ''), 'kek')
                )->count();

                $result['Anemia'][$idx] = $rows->filter(
                    fn($i) =>
                    str_contains(strtolower($i->status_gizi_hb ?? ''), 'anemia')
                )->count();

                $result['Berisiko'][$idx] = $rows->filter(
                    fn($i) =>
                    str_contains(strtolower($i->status_risiko_usia ?? ''), 'berisiko')
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


    public function indikatorBulanan_old(Request $request)
    {
        try {
            $query = Pregnancy::query();

            // 🔹 Filter opsional
            foreach (['kelurahan', 'posyandu', 'rw', 'rt'] as $f) {
                if ($request->filled($f)) {
                    $query->where($f, $request->$f);
                }
            }

            // 🔹 Ambil kolom relevan
            $data = $query->get([
                'tanggal_pemeriksaan_terakhir',
                'status_gizi_lila',
                'status_gizi_hb',
                'status_risiko_usia'
            ]);

            // 🔹 Buat 12 bulan terakhir (misal: "Nov 2024", "Dec 2024", ..., "Oct 2025")
            $months = collect(range(0, 11))
                ->map(fn($i) => now()->subMonths(11 - $i)->format('M Y'))
                ->values();

            $indikatorList = ['KEK', 'Anemia', 'Berisiko', 'Normal'];
            $result = [];
            foreach ($indikatorList as $indikator) {
                $result[$indikator] = array_fill(0, 12, 0);
            }

            if ($data->isEmpty()) {
                return response()->json([
                    'labels' => $months,
                    'indikator' => $result,
                ]);
            }

            foreach ($data as $item) {
                if (!$item->tanggal_pemeriksaan_terakhir)
                    continue;

                $monthKey = Carbon::parse($item->tanggal_pemeriksaan_terakhir)->format('M Y');
                $idx = $months->search($monthKey);
                if ($idx === false)
                    continue;

                $isKEK = str_contains(strtolower(trim($item->status_gizi_lila ?? '')), 'kek');
                $isAnemia = str_contains(strtolower(trim($item->status_gizi_hb ?? '')), 'anemia');
                $isBerisiko = str_contains(strtolower(trim($item->status_risiko_usia ?? '')), 'berisiko');

                if ($isKEK) {
                    $result['KEK'][$idx]++;
                } elseif ($isAnemia) {
                    $result['Anemia'][$idx]++;
                } elseif ($isBerisiko) {
                    $result['Berisiko'][$idx]++;
                } else {
                    $result['Normal'][$idx]++;
                }
            }

            return response()->json([
                'labels' => $months,
                'indikator' => $result,
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'error' => 'Gagal memuat data indikator',
                'message' => $th->getMessage(),
            ], 500);
        }
    }

    private function hitungIMT($berat, $tinggi)
    {
        if (empty($berat) || empty($tinggi))
            return null;

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

}
