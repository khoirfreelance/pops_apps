<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Child;
use App\Models\Kunjungan;
use App\Models\Wilayah;
use App\Models\Posyandu;
use App\Models\Log;
use App\Models\Intervensi;
use App\Models\Keluarga;
use App\Models\AnggotaKeluarga;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class ChildrenController extends Controller
{

    public function index(Request $request)
    {
        // Normalisasi format periode
        if (!empty($filters['periodeAwal'])) {
            try {
                $filters['periodeAwal'] = \Carbon\Carbon::parse($filters['periodeAwal'])->startOfMonth()->format('Y-m-d');
            } catch (\Exception $e) {
                $filters['periodeAwal'] = null;
            }
        }

        if (!empty($filters['periodeAkhir'])) {
            try {
                $filters['periodeAkhir'] = \Carbon\Carbon::parse($filters['periodeAkhir'])->endOfMonth()->format('Y-m-d');
            } catch (\Exception $e) {
                $filters['periodeAkhir'] = null;
            }
        }

        $user = Auth::user();

        // ✅ 1. Cari data anggota_tpk user
        $anggotaTPK = \App\Models\Cadre::where('id_user', $user->id)->first();

        if (!$anggotaTPK) {
            return response()->json(['message' => 'User tidak terdaftar dalam anggota TPK'], 404);
        }

        // ✅ 2. Ambil posyandu dan wilayah
        $posyandu = $anggotaTPK->posyandu;
        $wilayah = $posyandu?->wilayah;

        if (!$wilayah) {
            return response()->json(['message' => 'Wilayah tidak ditemukan untuk user ini'], 404);
        }

        // ✅ 3. Dapatkan kelurahan user
        $filterKelurahan = $wilayah->kelurahan ?? null;

        // ✅ 4. Ambil semua filter dari request
        $filters = $request->only([
            'periodeAwal', 'periodeAkhir', 'posyandu', 'rw', 'rt',
            'bbu', 'tbu', 'bbtb', 'stagnan', 'intervensi'
        ]);

        // ✅ 5. KUNJUNGAN (utama)
        $kunjungan = Kunjungan::query()
            ->when($filterKelurahan, fn($q) => $q->where('kelurahan', $filterKelurahan))
            ->when($filters['periodeAwal'] ?? null, fn($q, $val) => $q->whereDate('tgl_pengukuran', '>=', $val))
            ->when($filters['periodeAkhir'] ?? null, fn($q, $val) => $q->whereDate('tgl_pengukuran', '<=', $val))
            ->when($filters['posyandu'] ?? null, fn($q, $val) => $q->whereIn('posyandu', (array) $val))
            ->when($filters['rw'] ?? null, fn($q, $val) => $q->whereIn('rw', (array) $val))
            ->when($filters['rt'] ?? null, fn($q, $val) => $q->whereIn('rt', (array) $val))
            ->when($filters['bbu'] ?? null, fn($q, $val) => $q->whereIn('bb_u', (array) $val))
            ->when($filters['tbu'] ?? null, fn($q, $val) => $q->whereIn('tb_u', (array) $val))
            ->when($filters['bbtb'] ?? null, fn($q, $val) => $q->whereIn('bb_tb', (array) $val))
            ->when(isset($filters['stagnan']), fn($q) => $q->where('naik_berat_badan', $filters['stagnan'] ? 0 : 1))
            ->get();

        // ✅ 6. PENDAMPINGAN
        $pendampingan = Child::query()
            ->when($filterKelurahan, fn($q) => $q->where('kelurahan', $filterKelurahan))
            ->when($filters['periodeAwal'] ?? null, fn($q, $val) => $q->whereDate('tgl_pendampingan', '>=', $val))
            ->when($filters['periodeAkhir'] ?? null, fn($q, $val) => $q->whereDate('tgl_pendampingan', '<=', $val))
            ->when($filters['rw'] ?? null, fn($q, $val) => $q->whereIn('rw', (array) $val))
            ->when($filters['rt'] ?? null, fn($q, $val) => $q->whereIn('rt', (array) $val))
            ->when($filters['bbu'] ?? null, fn($q, $val) => $q->whereIn('bb_u', (array) $val))
            ->when($filters['tbu'] ?? null, fn($q, $val) => $q->whereIn('tb_u', (array) $val))
            ->when($filters['bbtb'] ?? null, fn($q, $val) => $q->whereIn('bb_tb', (array) $val))
            ->when(isset($filters['stagnan']), fn($q) => $q->where('naik_berat_badan', $filters['stagnan'] ? 0 : 1))
            ->get();

        // ✅ 7. INTERVENSI
        $intervensi = Intervensi::query()
            ->when($filterKelurahan, fn($q) => $q->where('desa', $filterKelurahan))
            ->when($filters['posyandu'] ?? null, fn($q, $val) => $q->whereIn('posyandu', (array) $val))
            ->when($filters['rw'] ?? null, fn($q, $val) => $q->whereIn('rw', (array) $val))
            ->when($filters['rt'] ?? null, fn($q, $val) => $q->whereIn('rt', (array) $val))
            ->when($filters['periodeAwal'] ?? null, fn($q, $val) => $q->whereDate('tgl_intervensi', '>=', $val))
            ->when($filters['periodeAkhir'] ?? null, fn($q, $val) => $q->whereDate('tgl_intervensi', '<=', $val))
            ->when($filters['intervensi'] ?? null, fn($q, $val) => $q->whereIn('kategori', (array) $val))
            ->get();

        $grouped = [];

        // 1️⃣ KUNJUNGAN — sumber utama data anak
        foreach ($kunjungan as $item) {
            $nik = $item->nik ?? $item->nik_ortu ?? '';
            if (!$nik) continue;

            if (!isset($grouped[$nik])) {
                $grouped[$nik] = [
                    'id' => $item->id,
                    'nama' => $item->nama_anak ?? '-',
                    'nik' => $item->nik ?? '',
                    'jk' => $item->jk ?? '-',
                    'provinsi' => $item->provinsi ?? '-',
                    'kota' => $item->kota ?? '-',
                    'kecamatan' => $item->kecamatan ?? '-',
                    'kelurahan' => $item->kelurahan ?? '-',
                    'rw' => $item->rw ?? '-',
                    'rt' => $item->rt ?? '-',
                    'kelahiran' => [],
                    'keluarga' => [],
                    'pendampingan' => [],
                    'posyandu' => [],
                    'intervensi' => []
                ];
            }

            $grouped[$nik]['posyandu'][] = [
                'posyandu' => $item->posyandu,
                'tgl_ukur' => $item->tgl_pengukuran,
                'usia' => $item->usia_saat_ukur,
                'bbu' => $item->bb_u,
                'tbu' => $item->tb_u,
                'bbtb' => $item->bb_tb,
                'bb' => $item->bb,
                'tb' => $item->tb,
                'bb_naik' => $item->naik_berat_badan,
            ];

            $grouped[$nik]['kelahiran'][] = [
                'tgl_lahir' => $item->tgl_lahir ?? '-',
                'bb_lahir' => $item->bb_lahir ?? '-',
                'pb_lahir' => $item->tb_lahir ?? '-',
            ];

            $grouped[$nik]['keluarga'][] = [
                'nama_ayah' => $item->peran === 'Ayah' ? $item->nama_ortu : '-',
                'nama_ibu' => $item->peran === 'Ibu' ? $item->nama_ortu : '-',
                'pekerjaan_ayah' => '-',
                'pekerjaan_ibu' => '-',
                'usia_ayah' => '-',
                'usia_ibu' => '-',
                'anak_ke' => '-',
            ];
        }

        // 2️⃣ PENDAMPINGAN
        foreach ($pendampingan as $item) {
            $nik = $item->nik_anak ?? $item->nik_ibu ?? '';
            if (!$nik) continue;

            $grouped[$nik] ??= [
                'id' => $item->id,
                'nama' => $item->nama_anak ?? '-',
                'nik' => $item->nik_anak ?? '',
                'jk' => $item->jk ?? '-',
                'provinsi' => $item->provinsi ?? '-',
                'kota' => $item->kota ?? '-',
                'kecamatan' => $item->kecamatan ?? '-',
                'kelurahan' => $item->kelurahan ?? '-',
                'rw' => $item->rw ?? '-',
                'rt' => $item->rt ?? '-',
                'kelahiran' => [],
                'keluarga' => [],
                'pendampingan' => [],
                'posyandu' => [],
                'intervensi' => []
            ];

            $grouped[$nik]['pendampingan'][] = [
                'kader' => $item->petugas ?? '-',
                'tanggal' => $item->tgl_pendampingan ?? '-',
            ];

            if (empty($grouped[$nik]['keluarga'])) {
                $grouped[$nik]['keluarga'][] = [
                    'nama_ayah' => $item->nama_ayah ?? '-',
                    'nama_ibu' => $item->nama_ibu ?? '-',
                    'pekerjaan_ayah' => $item->pekerjaan_ayah ?? '-',
                    'pekerjaan_ibu' => $item->pekerjaan_ibu ?? '-',
                    'usia_ayah' => $item->usia_ayah ?? '-',
                    'usia_ibu' => $item->usia_ibu ?? '-',
                    'anak_ke' => $item->anak_ke ?? '-',
                ];
            }

            if (empty($grouped[$nik]['kelahiran'])) {
                $grouped[$nik]['kelahiran'][] = [
                    'tgl_lahir' => $item->tgl_lahir ?? '-',
                    'bb_lahir' => $item->bb_lahir ?? '-',
                    'pb_lahir' => $item->tb_lahir ?? '-',
                ];
            }
        }

        // 3️⃣ INTERVENSI
        foreach ($intervensi as $item) {
            $nik = $item->nik_subjek ?? $item->nik_wali ?? '';
            if (!$nik) continue;

            $grouped[$nik] ??= [
                'id' => $item->id,
                'nama' => $item->nama_subjek ?? '-',
                'nik' => $item->nik_subjek ?? '',
                'jk' => $item->jk ?? '-',
                'provinsi' => $item->desa ?? '-',
                'kota' => '-',
                'kecamatan' => '-',
                'kelurahan' => '-',
                'rw' => $item->rw ?? '-',
                'rt' => $item->rt ?? '-',
                'kelahiran' => [],
                'keluarga' => [],
                'pendampingan' => [],
                'posyandu' => [],
                'intervensi' => []
            ];

            $grouped[$nik]['intervensi'][] = [
                'jenis' => $item->kategori ?? '-',
                'tgl_intervensi' => $item->tgl_intervensi ?? '-',
                'bantuan' => $item->bantuan ?? '-',
            ];
        }

        $data_anak = array_values($grouped);

        return response()->json([
            'status' => 'success',
            'message' => 'Data anak berhasil dimuat',
            'data_anak' => $data_anak
        ]);
    }

    public function status(Request $request)
    {
        try {

            $user = Auth::user();

            // ✅ Ambil wilayah user lewat anggota_tpk → posyandu → wilayah
            $anggotaTPK = \App\Models\Cadre::where('id_user', $user->id)->first();

            if (!$anggotaTPK) {
                return response()->json(['message' => 'User tidak terdaftar dalam anggota TPK'], 404);
            }

            $posyandu = $anggotaTPK->posyandu;
            $wilayah = $posyandu?->wilayah;

            if (!$wilayah) {
                return response()->json(['message' => 'Wilayah tidak ditemukan untuk user ini'], 404);
            }

            $filterKelurahan = $wilayah->kelurahan;

            // ✅ Ambil data anak dari beberapa sumber
            $filters = $request->only([
            'periodeAwal', 'periodeAkhir', 'posyandu', 'rw', 'rt',
            'bbu', 'tbu', 'bbtb', 'stagnan', 'intervensi'
        ]);

        // ✅ 5. KUNJUNGAN (utama)
        $kunjungan = Kunjungan::query()
            ->when($filterKelurahan, fn($q) => $q->where('kelurahan', $filterKelurahan))
            ->when($filters['periodeAwal'] ?? null, fn($q, $val) => $q->whereDate('tgl_pengukuran', '>=', $val))
            ->when($filters['periodeAkhir'] ?? null, fn($q, $val) => $q->whereDate('tgl_pengukuran', '<=', $val))
            ->when($filters['posyandu'] ?? null, fn($q, $val) => $q->whereIn('posyandu', (array) $val))
            ->when($filters['rw'] ?? null, fn($q, $val) => $q->whereIn('rw', (array) $val))
            ->when($filters['rt'] ?? null, fn($q, $val) => $q->whereIn('rt', (array) $val))
            ->when($filters['bbu'] ?? null, fn($q, $val) => $q->whereIn('bb_u', (array) $val))
            ->when($filters['tbu'] ?? null, fn($q, $val) => $q->whereIn('tb_u', (array) $val))
            ->when($filters['bbtb'] ?? null, fn($q, $val) => $q->whereIn('bb_tb', (array) $val))
            ->when(isset($filters['stagnan']), fn($q) => $q->where('naik_berat_badan', $filters['stagnan'] ? 0 : 1))
            ->get();

        // ✅ 6. PENDAMPINGAN
        $pendampingan = Child::query()
            ->when($filterKelurahan, fn($q) => $q->where('kelurahan', $filterKelurahan))
            ->when($filters['periodeAwal'] ?? null, fn($q, $val) => $q->whereDate('tgl_pendampingan', '>=', $val))
            ->when($filters['periodeAkhir'] ?? null, fn($q, $val) => $q->whereDate('tgl_pendampingan', '<=', $val))
            ->when($filters['rw'] ?? null, fn($q, $val) => $q->whereIn('rw', (array) $val))
            ->when($filters['rt'] ?? null, fn($q, $val) => $q->whereIn('rt', (array) $val))
            ->when($filters['bbu'] ?? null, fn($q, $val) => $q->whereIn('bb_u', (array) $val))
            ->when($filters['tbu'] ?? null, fn($q, $val) => $q->whereIn('tb_u', (array) $val))
            ->when($filters['bbtb'] ?? null, fn($q, $val) => $q->whereIn('bb_tb', (array) $val))
            ->when(isset($filters['stagnan']), fn($q) => $q->where('naik_berat_badan', $filters['stagnan'] ? 0 : 1))
            ->get();

        // ✅ 7. INTERVENSI
        $intervensi = Intervensi::query()
            ->when($filterKelurahan, fn($q) => $q->where('desa', $filterKelurahan))
            ->when($filters['posyandu'] ?? null, fn($q, $val) => $q->whereIn('posyandu', (array) $val))
            ->when($filters['rw'] ?? null, fn($q, $val) => $q->whereIn('rw', (array) $val))
            ->when($filters['rt'] ?? null, fn($q, $val) => $q->whereIn('rt', (array) $val))
            ->when($filters['periodeAwal'] ?? null, fn($q, $val) => $q->whereDate('tgl_intervensi', '>=', $val))
            ->when($filters['periodeAkhir'] ?? null, fn($q, $val) => $q->whereDate('tgl_intervensi', '<=', $val))
            ->when($filters['intervensi'] ?? null, fn($q, $val) => $q->whereIn('kategori', (array) $val))
            ->get();

            // Gabungkan (utamakan kunjungan)
            $data = $kunjungan->isNotEmpty() ? $kunjungan : ($pendampingan->isNotEmpty() ? $pendampingan : $intervensi);

            if ($data->isEmpty()) {
            $defaultCounts = [
                'Stunting' => 0,
                'Wasting' => 0,
                'Underweight' => 0,
                'Normal' => 0,
                'Overweight' => 0,
                'BB Stagnan' => 0,
            ];

            $result = [];
            foreach ($defaultCounts as $title => $value) {
                $color = match ($title) {
                    'Stunting' => 'danger',
                    'Wasting' => 'warning',
                    'Underweight' => 'violet',
                    'Normal' => 'success',
                    'BB Stagnan' => 'info',
                    'Overweight' => 'dark',
                    default => 'secondary'
                };
                $result[] = [
                    'title' => $title,
                    'value' => 0,
                    'percent' => '0%',
                    'color' => $color
                ];
            }

            return response()->json([
                'total' => 0,
                'counts' => $result,
                'kelurahan' => $filterKelurahan ?? '-',
            ]);
        }


            // ✅ Filter tambahan dari frontend
            if ($request->filled('posyandu')) $data = $data->where('posyandu', $request->posyandu);
            if ($request->filled('rw')) $data = $data->where('rw', $request->rw);
            if ($request->filled('rt')) $data = $data->where('rt', $request->rt);

            // ✅ Filter periode (opsional)
            if ($request->filled('periodeAwal') && $request->filled('periodeAkhir')) {
                $periodeAwal = $this->parseBulanTahun($request->periodeAwal);
                $periodeAkhir = $this->parseBulanTahun($request->periodeAkhir, true);

                $data = $data->filter(function ($item) use ($periodeAwal, $periodeAkhir) {
                    $tgl = $item->tgl_pengukuran ?? $item->tgl_pendampingan ?? $item->tgl_intervensi ?? null;
                    if (!$tgl) return false;

                    return $tgl >= $periodeAwal->format('Y-m-d') && $tgl <= $periodeAkhir->format('Y-m-d');
                });
            }


            // ✅ Tetap tampilkan meski NIK kosong
            $data = $data->map(function ($item) {
                return [
                    'nama_anak' => $item->nama_anak ?? $item->nama_subjek ?? null,
                    'nik_anak' => $item->nik_anak ?? $item->nik_subjek ?? '',
                    'bbu' => $item->bb_u ?? null,
                    'tbu' => $item->tb_u ?? null,
                    'bbtb' => $item->bb_tb ?? null,
                    'naik_berat_badan' => $item->naik_berat_badan ?? null,
                    'kelurahan' => $item->kelurahan ?? null,
                ];
            });

            $total = $data->count();

            // ✅ Hitung status gizi
            $count = [
                'Stunting' => 0,
                'Wasting' => 0,
                'Underweight' => 0,
                'Normal' => 0,
                'Overweight' => 0,
                'BB Stagnan' => 0,
            ];

            foreach ($data as $anak) {
                $bbu = strtolower($anak['bbu'] ?? '');
                $tbu = strtolower($anak['tbu'] ?? '');
                $bbtb = strtolower($anak['bbtb'] ?? '');
                $naikBB = $anak['naik_berat_badan'];

                if (str_contains($tbu, 'severely stunted') || str_contains($tbu, 'stunted')) $count['Stunting']++;
                if (str_contains($bbtb, 'severely wasted') || str_contains($bbtb, 'wasted')) $count['Wasting']++;
                if (str_contains($bbu, 'underweight') || str_contains($bbu, 'severely underweight')) $count['Underweight']++;
                if ($bbu === 'normal' && $tbu === 'normal' && $bbtb === 'normal') $count['Normal']++;
                if (str_contains($bbtb, 'possible risk of overweight') || str_contains($bbtb, 'overweight') || str_contains($bbtb, 'obesitas')) $count['Overweight']++;
                if (is_null($naikBB)) $count['BB Stagnan']++;
            }

            // ✅ Format hasil untuk frontend
            $result = [];
            foreach ($count as $title => $value) {
                $percent = $total > 0 ? round(($value / $total) * 100, 1) : 0;
                $color = match ($title) {
                    'Stunting' => 'danger',
                    'Wasting' => 'warning',
                    'Underweight' => 'violet',
                    'Normal' => 'success',
                    'BB Stagnan' => 'info',
                    'Overweight' => 'dark',
                    default => 'secondary'
                };
                $result[] = [
                    'title' => $title,
                    'value' => $value,
                    'percent' => "{$percent}%",
                    'color' => $color
                ];
            }

            return response()->json([
                'total' => $total,
                'counts' => $result,
                'kelurahan' => $filterKelurahan,
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Gagal mengambil data status gizi anak',
                'error' => $e->getMessage(),
            ], 500);
        }
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

    public function kunjungan()
    {
        // Ambil semua data anak
        $data = Kunjungan::orderBy('nik')->get();

        return response()->json([
            'success' => true,
            'data' => $data
        ]);
    }

    public function import_kunjungan(Request $request)
    {
        // Validasi file
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
            if (empty($data[0])) continue; // skip baris kosong

            if (!$header) {
                $header = $data;
                continue;
            }

            $count++;

            $rows[] = [
                'nik' => $data[0] ?? null,
                'nama_anak' => $data[1] ?? null,
                'jk' => $data[2] ?? null,
                'tgl_lahir' => $data[3] ?? null,
                'bb_lahir' => $data[4] ?? null,
                'tb_lahir' => $data[5] ?? null,
                'nama_ortu' => $data[6] ?? null,
                'peran'  => $data[7] ?? null,
                'nik_ortu'  => $data[8] ?? null,
                'alamat' => $data[9] ?? null,
                'provinsi' => $data[10] ?? null,
                'kota' => $data[11] ?? null,
                'kecamatan' => $data[12] ?? null,
                'kelurahan' => $data[13] ?? null,
                'rw' => $data[14] ?? null,
                'rt' => $data[15] ?? null,
                'puskesmas' => $data[16] ?? null,
                'posyandu' => $data[17] ?? null,
                'tgl_pengukuran' => $this->convertDate($data[18] ?? null),
                'bb' => $data[19] ?? null,
                'tb' => $data[20] ?? null,
                'lila' => $data[21] ?? null,
                'diasuh_oleh' => $data[22] ?? null,
                'asi' => $data[23] ?? null,
                'imunisasi' => $data[24] ?? null,
                'rutin_posyandu' => $data[25] ?? null,
                'penyakit_bawaan' => $data[26] ?? null,
                'penyakit_6bulan' => $data[27] ?? null,
                'terpapar_asap_rokok' => $data[28] ?? null,
                'penggunaan_jamban_sehat' => $data[29] ?? null,
                'penggunaan_sab' => $data[30] ?? null,
                'memiliki_jaminan' => $data[31] ?? null,
                'kie' => $data[32] ?? null,
                'mendapatkan_bantuan' => $data[33] ?? null,
                'kpsp' => $data[34] ?? null,
                'catatan' => $data[36] ?? null,
                'no_kk' => $data[35] ?? null,
                'petugas' => $data[37] ?? null,
            ];
        }

        fclose($handle);

        // ✅ Simpan batch (gunakan chunk)
        if (!empty($rows)) {
            foreach (array_chunk($rows, 100) as $chunk) {
                foreach ($chunk as $row) {
                    // Hitung usia dalam bulan
                    $usia = $this->hitungUmurBulan($row['tgl_lahir'], $row['tgl_pengukuran']);

                    // Hitung z-score dummy (bisa diganti nanti pakai WHO standard)
                    $z_bbu = $this->hitungZScore('BB/U', $row['jk'], $usia, $row['bb']);
                    $z_tbu = $this->hitungZScore('TB/U', $row['jk'], $usia, $row['tb']);
                    $z_bbtb = $this->hitungZScore('BB/TB', $row['jk'], $row['tb'], $row['bb']);

                    // Tentukan status berdasarkan z-score
                    $status_bbu = $this->statusBBU($z_bbu);
                    $status_tbu = $this->statusTBU($z_tbu);
                    $status_bbtb = $this->statusBBTB($z_bbtb);

                    // Cek apakah berat naik
                    $naikBB = $this->cekNaikBB($row['nik'], $row['bb'], $row['tgl_pengukuran'], 'kunjungan');

                    // Gabungkan hasil perhitungan ke $row
                    $row = array_merge($row, [
                        'tgl_pengukuran'   => $row['tgl_pengukuran'], // samakan nama
                        'usia_saat_ukur'   => $usia,
                        'bb_u'             => $status_bbu,
                        'zs_bb_u'          => $z_bbu,
                        'tb_u'             => $status_tbu,
                        'zs_tb_u'          => $z_tbu,
                        'bb_tb'            => $status_bbtb,
                        'zs_bb_tb'         => $z_bbtb,
                        'naik_berat_badan' => $naikBB,
                    ]);

                    // Simpan anak
                    $child = Kunjungan::create($row);

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

                    // ✅ Simpan keluarga & anggota bila ada no_kk
                    if (!empty($row['no_kk'])) {
                        $keluarga = Keluarga::firstOrCreate(
                            ['no_kk' => $row['no_kk']],
                            [
                                'alamat' => $row['alamat'] ?? null,
                                'rt' => $row['rt'] ?? null,
                                'rw' => $row['rw'] ?? null,
                                'id_wilayah' => $wilayah->id,
                                'is_pending' => false,
                            ]
                        );
                    }

                    // ✅ Simpan log
                    \App\Models\Log::create([
                        'id_user'   => \Auth::id(),
                        'context'   => 'Anak',
                        'activity'  => 'Import data anak ' . ($row['nama_anak'] ?? '-'),
                        'timestamp' => now(),
                    ]);
                }
            }
        }

        return response()->json([
            'message' => "Berhasil import {$count} data anak",
            'data' => $rows,
            'count' => $count,
        ], 200);
    }

    public function import_pendampingan(Request $request)
    {
        // 🔍 Validasi file
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
            if (empty($data[0])) continue; // skip baris kosong

            if (!$header) {
                $header = $data;
                continue;
            }

            // Pastikan kolom tanggal pendampingan valid
            if (!isset($data[1]) || !preg_match('/\d{4}-\d{2}-\d{2}/', $data[1])) {
                continue; // skip baris yang bukan data
            }

            $count++;

            $rows[] = [
                'petugas' => $data[0] ?? null,
                'tgl_pendampingan' => $this->convertDate($data[1] ?? null),
                'nama_anak' => $data[2] ?? null,
                'nik_anak' => $data[3] ?? null,
                'jk' => $data[4] ?? null,
                'tgl_lahir' => $data[5] ?? null,
                'nama_ayah' => $data[6] ?? null,
                'nik_ayah' => $data[7] ?? null,
                'pekerjaan_ayah' => $data[8] ?? null,
                'usia_ayah' => $data[9] ?? null,
                'nama_ibu' => $data[10] ?? null,
                'nik_ibu' => $data[11] ?? null,
                'pekerjaan_ibu' => $data[12] ?? null,
                'usia_ibu' => $data[13] ?? null,
                'anak_ke' => $data[14] ?? null,
                'riwayat_4t' => $data[15] ?? null,
                'riwayat_kb' => $data[16] ?? null,
                'alat_kontrasepsi' => $data[17] ?? null,
                'provinsi' => $data[18] ?? null,
                'kota' => $data[19] ?? null,
                'kecamatan' => $data[20] ?? null,
                'kelurahan' => $data[21] ?? null,
                'rw' => $data[22] ?? null,
                'rt' => $data[23] ?? null,
                'bb_lahir' => $data[24] ?? null,
                'tb_lahir' => $data[25] ?? null,
                'bb' => $data[26] ?? null,
                'tb' => $data[27] ?? null,
                'lila' => $data[28] ?? null,
                'lika' => $data[29] ?? null,
                'asi' => $data[30] ?? null,
                'imunisasi' => $data[31] ?? null,
                'diasuh_oleh' => $data[32] ?? null,
                'rutin_posyandu' => $data[33] ?? null,
                'riwayat_penyakit_bawaan' => $data[34] ?? null,
                'penyakit_bawaan' => $data[35] ?? null,
                'riwayat_penyakit_6bulan' => $data[36] ?? null,
                'penyakit_6bulan' => $data[37] ?? null,
                'terpapar_asap_rokok' => $data[38] ?? null,
                'penggunaan_jamban_sehat' => $data[39] ?? null,
                'penggunaan_sab' => $data[40] ?? null,
                'apabila_ada_penyakit' => $data[41] ?? null,
                'memiliki_jaminan' => $data[42] ?? null,
                'kie' => $data[43] ?? null,
                'mendapatkan_bantuan' => $data[44] ?? null,
                'catatan' => $data[45] ?? null,
                'no_kk' => $data[46] ?? null,
            ];
        }

        fclose($handle);

        // ✅ Simpan batch (gunakan chunk)
        if (!empty($rows)) {
            foreach (array_chunk($rows, 100) as $chunk) {
                foreach ($chunk as $row) {

                    // Hitung usia berdasarkan tgl_lahir dan tgl_pendampingan
                    $usia = $this->hitungUmurBulan($row['tgl_lahir'], $row['tgl_pendampingan']);

                    // Hitung z-score dummy (bisa diganti nanti pakai WHO standard)
                    $z_bbu = $this->hitungZScore('BB/U', $row['jk'], $usia, $row['bb']);
                    $z_tbu = $this->hitungZScore('TB/U', $row['jk'], $usia, $row['tb']);
                    $z_bbtb = $this->hitungZScore('BB/TB', $row['jk'], $row['tb'], $row['bb']);

                    // Tentukan status berdasarkan z-score
                    $status_bbu = $this->statusBBU($z_bbu);
                    $status_tbu = $this->statusTBU($z_tbu);
                    $status_bbtb = $this->statusBBTB($z_bbtb);

                    // Gabungkan hasil perhitungan
                    $row = array_merge($row, [
                        'usia'     => $usia,
                        'zs_bb_u'  => $z_bbu,
                        'bb_u'     => $status_bbu,
                        'zs_tb_u'  => $z_tbu,
                        'tb_u'     => $status_tbu,
                        'zs_bb_tb' => $z_bbtb,
                        'bb_tb'    => $status_bbtb,
                    ]);

                    // Simpan data pendampingan anak
                    $child = \App\Models\Child::create($row);

                    // ✅ Simpan wilayah & posyandu
                    $wilayah = \App\Models\Wilayah::firstOrCreate([
                        'provinsi' => $row['provinsi'],
                        'kota' => $row['kota'],
                        'kecamatan' => $row['kecamatan'],
                        'kelurahan' => $row['kelurahan'],
                    ]);

                    // ✅ Simpan keluarga bila ada no_kk
                    if (!empty($row['no_kk'])) {
                        $keluarga = \App\Models\Keluarga::firstOrCreate(
                            ['no_kk' => $row['no_kk']],
                            [
                                'alamat' => 'desa '.$row['kelurahan'].', kec. '.$row['kecamatan'].', kota/kab. '.$row['kota'],
                                'rt' => $row['rt'] ?? null,
                                'rw' => $row['rw'] ?? null,
                                'id_wilayah' => $wilayah->id,
                                'is_pending' => false,
                            ]
                        );
                    }

                    // ✅ Log aktivitas
                    \App\Models\Log::create([
                        'id_user'   => \Auth::id(),
                        'context'   => 'Pendampingan Anak',
                        'activity'  => 'Import pendampingan anak ' . ($row['nama_anak'] ?? '-'),
                        'timestamp' => now(),
                    ]);
                }
            }
        }

        return response()->json([
            'message' => "Berhasil import {$count} data pendampingan anak",
            'count' => $count,
        ], 200);
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

        while (($row = fgetcsv($handle, 1000, ',')) !== false) {
            // Lewati baris kosong atau header
            if ($count === 0 && str_contains(strtolower($row[0]), 'petugas')) {
                $count++;
                continue;
            }
            if (empty($row[0]) && empty($row[3])) continue;

            Intervensi::create([
                'petugas'           => $row[0] ?? null,
                'tgl_intervensi'    => isset($row[1]) ? date('Y-m-d', strtotime($row[1])) : null,
                'desa'              => $row[2] ?? null,
                'nama_subjek'         => $row[3] ?? null,
                'nik_subjek'          => $row[4] ?? null,
                'status_subjek'       => 'anak',
                'jk'                => $row[5] ?? null,
                'tgl_lahir'         => isset($row[6]) ? date('Y-m-d', strtotime($row[6])) : null,
                'nama_wali'    => $row[7] ?? null,
                'nik_wali'     => $row[8] ?? null,
                'status_wali'  => $row[9] ?? null,
                'rt'                => $row[10] ?? null,
                'rw'                => $row[11] ?? null,
                'posyandu'          => $row[12] ?? null,
                'umur_subjek'   => floor($this->hitungUmurBulan($row[6], $row[1])). 'Bulan',
                'bantuan'           => $row[13] ?? null,
                'kategori'          => $row[14] ?? null,
            ]);

            $count++;
        }

        fclose($handle);

        return response()->json(['message' => "Berhasil impor {$count} data intervensi."]);
    }

    /** Hitung umur (bulan) */
    private function hitungUmurBulan($tglLahir, $tglUkur)
    {
        if (!$tglLahir || !$tglUkur) return null;

        $lahir = new \DateTime($tglLahir);
        $ukur  = new \DateTime($tglUkur);
        $diff  = $lahir->diff($ukur);

        return $diff->y * 12 + $diff->m + ($diff->d / 30);
    }

    /** Hitung Z-Score sesuai jenis pengukuran */
    private function hitungZScore($tipe, $jk, $usiaOrTb, $bb)
    {
        $sex = ($jk == 'L' || $jk == 'l' || $jk == 1) ? 1 : 2;

        switch ($tipe) {
            case 'BB/U':
                $usia = round($usiaOrTb);
                $row = \DB::table('who_weight_for_age')
                    ->where('sex', $sex)
                    ->where('age_month', $usia)
                    ->first();
                break;

            case 'TB/U':
                $usia = round($usiaOrTb);
                $row = \DB::table('who_height_for_age')
                    ->where('sex', $sex)
                    ->where('age_month', $usia)
                    ->first();
                break;

            case 'BB/TB':
                $tb = round($usiaOrTb);
                $row = \DB::table('who_weight_for_height')
                    ->where('sex', $sex)
                    ->where('height_cm', $tb)
                    ->first();
                break;

            default:
                return null;
        }

        if (!$row) return null;

        return $this->hitungZScoreLMS($bb, $row->L, $row->M, $row->S);
    }

    private function hitungZScoreLMS($nilai, $L, $M, $S)
    {
        if ($L == 0) {
            $z = log($nilai / $M) / $S;
        } else {
            $z = (pow(($nilai / $M), $L) - 1) / ($L * $S);
        }

        // Bulatkan ke 2 angka di belakang koma
        return round($z, 2);
    }

    /** Dummy status berdasar z-score */
    private function statusBBU($z)
    {
        if (is_null($z)) return null;
        if ($z < -3) return 'Severely Underweight';
        if ($z < -2) return 'Underweight';
        if ($z <= 1) return 'Normal';
        if ($z <= 2) return 'Risiko BB Lebih';
        return 'Overweight';
    }

    private function statusTBU($z)
    {
        if (is_null($z)) return null;
        if ($z < -3) return 'Severely Stunted';
        if ($z < -2) return 'Stunted';
        if ($z <= 2) return 'Normal';
        return 'Tinggi';
    }

    private function statusBBTB($z)
    {
        if (is_null($z)) return null;
        if ($z < -3) return 'Severely Wasted';
        if ($z < -2) return 'Wasted';
        if ($z <= 1) return 'Normal';
        if ($z <= 2) return 'Possible risk of Overweight';
        if ($z <= 3) return 'Overweight';
        return 'Obesitas';
    }

    /** Cek apakah berat badan naik dibanding pengukuran terakhir */
    private function cekNaikBB($nik, $bbSekarang, $tglUkur, $tipe = 'kunjungan')
    {
        // Pilih model berdasarkan tipe
        $model = match ($tipe) {
            'pendampingan' => \App\Models\Child::class,
            default => \App\Models\Kunjungan::class,
        };

        // Cek data terakhir berdasarkan NIK dan tanggal
        $last = $model::where('nik', $nik)
            ->where('tgl_pengukuran', '<', $tglUkur)
            ->orderBy('tgl_pengukuran', 'desc')
            ->first();

        // Jika tidak ada data sebelumnya → null
        if (!$last) return null;

        // Bandingkan berat badan
        return $bbSekarang > $last->bb;
    }

    /** Konversi tanggal dari dd/mm/yyyy ke
     * yyy-mm-dd */
    private function convertDate($date)
    {
        if (!$date) return null;
        $parts = preg_split('/[\/\-]/', $date);
        if (count($parts) === 3) {
            return strlen($parts[2]) === 4
                ? "{$parts[2]}-{$parts[1]}-{$parts[0]}"
                : "{$parts[0]}-{$parts[1]}-{$parts[2]}";
        }
        return null;
    }

    public function tren(Request $request)
    {
        $user = Auth::user();

        // 1. Ambil data anggota TPK
        $anggotaTPK = \App\Models\Cadre::where('id_user', $user->id)->first();
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

        $query = Kunjungan::query();
        if ($filterKelurahan) {
            $query->where('kelurahan', $filterKelurahan);
        }

        // 4. Filter manual (opsional) dari UI
        if ($request->filled('posyandu')) $query->where('posyandu', $request->posyandu);
        if ($request->filled('rw'))       $query->where('rw', $request->rw);
        if ($request->filled('rt'))       $query->where('rt', $request->rt);

        // 5. Tentukan periode current & previous
        if ($request->filled('periode')) {
            $bulanIni = $this->parseBulanTahun($request->periode)->format('Y-m');
            $bulanLalu = $this->parseBulanTahun($request->periode)->subMonth()->format('Y-m');
        } else {
            // default: bulan ini - 1
            $bulanIni = now()->subMonth()->format('Y-m');
            $bulanLalu = now()->subMonths(2)->format('Y-m');
        }

        // 6. Ambil seluruh data Kunjungan yg relevan
        $data = $query->get();

        // 7. Kirim ke buildTrend dgn bulan yang sudah ditentukan
        $tren = [
            'bb'   => $this->buildTrend($data, 'bbu', [
                'Berat Badan Sangat Kurang (Severely Underweight)',
                'Berat Badan Kurang (Underweight)',
                'Berat Badan Normal',
                'Risiko Berat Badan Lebih',
            ], $bulanIni, $bulanLalu),

            'tb'   => $this->buildTrend($data, 'tbu', [
                'Sangat Pendek (Severely Stunted)',
                'Pendek (Stunted)',
                'Normal',
                'Tinggi'
            ], $bulanIni, $bulanLalu),

            'bbtb' => $this->buildTrend($data, 'bbtb', [
                'Gizi Buruk (Severely Wasted)',
                'Gizi Kurang (Wasted)',
                'Gizi Baik (Normal)',
                'Berisiko Gizi Lebih (Possible Risk of Overweight)',
                'Gizi Lebih (Overweight)',
                'Obesitas (Obese)',
            ], $bulanIni, $bulanLalu),
        ];

        return response()->json($tren);
    }

    private function buildTrend($data, $field, $categories, $currentMonth, $previousMonth)
    {
        // Filter per bulan
        $currentData = $data->filter(fn($i) =>
            \Carbon\Carbon::parse($i->tgl_pengukuran)->format('Y-m') === $currentMonth
        );

        $previousData = $data->filter(fn($i) =>
            \Carbon\Carbon::parse($i->tgl_pengukuran)->format('Y-m') === $previousMonth
        );

        // Hitung kategori
        $countCategories = function ($collection) use ($field, $categories) {
            $result = [];
            foreach ($categories as $cat) {
                $result[$cat] = $collection->where($field, $cat)->count();
            }
            return $result;
        };

        $currentCounts  = $countCategories($currentData);
        $previousCounts = $countCategories($previousData);

        // Hitung total
        $totalCurrent  = array_sum($currentCounts);
        $totalPrevious = array_sum($previousCounts);

        $diffPercent = $totalPrevious == 0
            ? 0
            : round((($totalCurrent - $totalPrevious) / $totalPrevious) * 100, 2);

        return [
            "month" => [
                "current"  => $currentMonth,
                "previous" => $previousMonth,
            ],
            "data" => [
                "current"  => $currentCounts,
                "previous" => $previousCounts,
            ],
            "total" => [
                "current"       => $totalCurrent,
                "previous"      => $totalPrevious,
                "diff_percent"  => $diffPercent
            ]
        ];
    }



}
