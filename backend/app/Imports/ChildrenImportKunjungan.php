<?php

namespace App\Imports;

use App\Models\Child;
use App\Models\User;
use App\Models\Wilayah;
use App\Models\Log;
use App\Models\Keluarga;
use App\Models\Log;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Exception;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithStartRow;

class ChildrenImportPendampingan implements ToCollection, WithStartRow
{
    protected array $wilayahUser = [];
    public function __construct(private int $userId)
    {
        $this->loadWilayahUser();
    }

    // Irul Additional
    protected function detectDelimiter(string $path): string
    {
        $handle = fopen($path, 'r');
        $firstLine = fgets($handle);
        fclose($handle);

        $comma = substr_count($firstLine, ',');
        $semicolon = substr_count($firstLine, ';');

        return $semicolon > $comma ? ';' : ',';
    }

    public function getCsvSettings(): array
    {
        $path = request()->file('file')->getRealPath();

        return [
            'delimiter' => $this->detectDelimiter($path),
            'input_encoding' => 'UTF-8',
        ];
    }
    /* public function getCsvSettings(): array
    {
        return [
            'delimiter' => ',',
            'input_encoding' => 'UTF-8',
        ];
    } */

    public function model(array $row)
    {
        //dd($row);
        return DB::transaction(function () use ($row) {

            // =========================
            // 1. Parse tanggal
            // =========================
            $tglLahir = $this->convertDate($row['tgl_lahir'] ?? null);
            $tglUkur  = $this->convertDate($row['tanggal_pengukuran'] ?? null);
            //dump($tglLahir, $tglUkur);
            // =========================
            // 2. Hitung usia & status
            // =========================
            $usia = $this->hitungUmurBulan($tglLahir, $tglUkur);

            $z_bbu  =  $this->normalizeDecimal($row["zs_bbu"] ?? null);
            $z_tbu  =  $this->normalizeDecimal($row["zs_tbu"]?? null);
            $z_bbtb = $this->normalizeDecimal( $row["zs_bbtb"]??null);

            // =========================
            // 3. Status gizi
            // =========================

            $status_bbu  = $this->statusBB($row["bbu"]);
            $status_tbu  = $this->statusTB($row["tbu"]);
            $status_bbtb = $this->statusBBTB($row["bbtb"]);

            $naikBB = $this->normalizeNaikBeratBadan($row["naik_berat_badan"]);

            // =========================
            // 4. Simpan Kunjungan
            // =========================
            $kunjungan = Kunjungan::create([
                'nik' => $row['nik'],
                'nama_anak' => $this->normalizeText($row['nama'] ?? null),
                'jk' => $this->normalizeText($row['jk'] ?? null),
                'tgl_lahir' => $tglLahir,
                'bb_lahir' =>  $this->normalizeDecimal($row['bb_lahir']??null),
                'tb_lahir' => $this->normalizeDecimal($row['tb_lahir']??null),
                'nama_ortu' => $this->normalizeText($row['nama_ortu'] ?? null),
                'alamat' => $this->normalizeText($row['alamat']),
                'rt' => $row['rt'],
                'rw' => $row['rw'],
                'puskesmas' => $this->normalizeText($row['pukesmas']),
                'posyandu' => $this->normalizeText($row['posyandu']),
                'tgl_pengukuran' => $tglUkur,
                'usia_saat_ukur' => $usia,
                'bb' =>  $this->normalizeDecimal($row['berat']??null),
                'tb' =>  $this->normalizeDecimal($row['tinggi']??null),
                'lila' => $this->normalizeDecimal($row['lila'] ?? null),
                'provinsi' => $this->normalizeText($row['prov']),
                'kota' => $this->normalizeText($row['kabkota']),
                //'provinsi' => $this->wilayahUser['provinsi'],
                //'kota' => $this->wilayahUser['kota'],
                'kecamatan' => $this->normalizeText($row['kec']),
                'kelurahan' => $this->normalizeText($row['desakel']),

                'bb_u' => $this->normalizeStatus($status_bbu),
                'zs_bb_u' => $z_bbu,
                'tb_u' => $this->normalizeStatus($status_tbu),
                'zs_tb_u' => $z_tbu,
                'bb_tb' => $this->normalizeStatus($status_bbtb),
                'zs_bb_tb' => $z_bbtb,
                'naik_berat_badan' => $naikBB,
            ]);

            // =========================
            // 5. Wilayah & Posyandu
            // =========================
            $wilayah = Wilayah::firstOrCreate([
                /* 'provinsi' => $this->wilayahUser['provinsi'],
                'kota' => $this->wilayahUser['kota'], */
                'provinsi' => $this->normalizeText($row['prov']),
                'kota' => $this->normalizeText($row['kabkota']),
                'kecamatan' => $this->normalizeText($row['kec']),
                'kelurahan' => $this->normalizeText($row['desakel']),
            ]);

            Posyandu::firstOrCreate([
                'nama_posyandu' => $this->normalizeText($row['posyandu']),
                'id_wilayah' => $wilayah->id,
                'rt' => $row['rt'],
                'rw' => $row['rw'],
            ]);

            // =========================
            // 6. Keluarga
            // =========================
            if (!empty($row['no_kk'] ?? null)) {
                Keluarga::firstOrCreate(
                    ['no_kk' => $row['no_kk']],
                    [
                        'alamat' => $this->normalizeText($row['alamat']),
                        'rt' => $row['rt'],
                        'rw' => $row['rw'],
                        'id_wilayah' => $wilayah->id,
                        'is_pending' => false,
                    ]
                );
            }

            // =========================
            // 7. Log
            // =========================
            Log::create([
                'id_user' => $this->userId,
                'context' => 'Anak',
                'activity' => 'Import data anak ' . ($row['nama'] ?? '-'),
                'timestamp' => now(),
            ]);

            return $kunjungan;
        });
    }

    /* ======================
     * Helper (reuse existing)
     * ====================== */

    private function normalizeText($value)
    {
        return $value ? strtoupper(trim($value)) : null;
    }

    private function normalizeStatus($value)
    {
        return $value ? ucwords(trim($value)) : null;
    }

    // Irul Custom ConvertDate
    private function convertDate($date)
    {
        if (!$date) {
            return null;
        }

        $date = trim($date);

        // ✅ Format yang diizinkan
        $acceptedFormats = [
            'd/m/Y',
            'd-m-Y',
            'Y/m/d',
            'Y-m-d',
        ];

        // =========================
        // 1️⃣ Cek format eksplisit
        // =========================
        foreach ($acceptedFormats as $format) {
            $dt = \DateTime::createFromFormat($format, $date);
            if ($dt && $dt->format($format) === $date) {
                return $dt->format('Y-m-d');
            }
        }

        // =========================
        // 2️⃣ Fallback manual (CSV jelek)
        // =========================
        $parts = preg_split('/[\/\-]/', $date);

        if (count($parts) === 3) {
            if (strlen($parts[0]) === 4) {
                [$y, $m, $d] = $parts;
            } else {
                [$d, $m, $y] = $parts;
            }

            if (checkdate((int)$m, (int)$d, (int)$y)) {
                return sprintf('%04d-%02d-%02d', $y, $m, $d);
            }
        }

        // =========================
        // ❌ FORMAT TIDAK DITERIMA
        // =========================
        throw new \Exception(
            "Format tanggal <strong>{$date}</strong> tidak diterima.<br>"
            . "Format yang diperbolehkan adalah:<br>"
            . "<ul class='text-start'>"
            . "<li><strong>DD/MM/YYYY</strong> (contoh: 25/12/2024)</li>"
            . "<li><strong>DD-MM-YYYY</strong> (contoh: 25-12-2024)</li>"
            . "<li><strong>YYYY/MM/DD</strong> (contoh: 2024/12/25)</li>"
            . "<li><strong>YYYY-MM-DD</strong> (contoh: 2024-12-25)</li>"
            . "</ul>"
        );
    }

    /* private function convertDate($date)
    {
        if (!$date) {
            return null;
        }

        $date = trim($date);

        // 1️⃣ Coba format yang jelas dulu
        $formats = [
            'd/m/Y',
            'd-m-Y',
            'Y/m/d',
            'Y-m-d',
        ];

        foreach ($formats as $format) {
            $dt = \DateTime::createFromFormat($format, $date);
            if ($dt && $dt->format($format) === $date) {
                return $dt->format('Y-m-d');
            }
        }

        // 2️⃣ Fallback: split manual (untuk CSV jelek)
        $parts = preg_split('/[\/\-]/', $date);

        if (count($parts) === 3) {
            // asumsi kalau bagian pertama 4 digit = tahun
            if (strlen($parts[0]) === 4) {
                [$y, $m, $d] = $parts;
            } else {
                [$d, $m, $y] = $parts;
            }

            if (checkdate((int)$m, (int)$d, (int)$y)) {
                return sprintf('%04d-%02d-%02d', $y, $m, $d);
            }
        }

        return null;
    }

    /* private function convertDate($date)
    {
        if (!$date)
            return null;
        $parts = preg_split('/[\/\-]/', $date);
        if (count($parts) === 3) {
            return strlen($parts[2]) === 4
                ? "{$parts[2]}-{$parts[1]}-{$parts[0]}"
                : "{$parts[0]}-{$parts[1]}-{$parts[2]}";
        }
        return null;
    }

    private function hitungZScore($tipe, $jk, $usiaOrTb, $bb)
    {
        $sex = ($jk == 'L' || $jk == 'l' || $jk == 1 || $jk == "LAKI-LAKI") ? 1 : 2;

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

        if (!$row) {
            return null;
        }

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


    private function statusBBU($z)
    {
        $result = null;
        if (is_null($z)) {
            $result = null;
        } elseif ($z < -3) {
            $result = 'Severely Underweight';
        } elseif ($z < -2) {
            $result = 'Underweight';
        } elseif ($z <= 1) {
            $result = 'Normal';
        } elseif ($z <= 2) {
            $result = 'Risiko BB Lebih';
        } else {
            $result = 'Overweight';
        }
        return $result;
    }

    private function statusTBU($z)
    {
        $result = null;
        if (is_null($z)) {
            $result = null;
        } elseif ($z < -3) {
            $result = 'Severely Stunted';
        } elseif ($z < -2) {
            $result = 'Stunted';
        } elseif ($z <= 2) {
            $result = 'Normal';
        } else {
            $result = 'Tinggi';
        }
        return $result;
    }

    private function statusBBTB($z)
    {
        $result = null;

        if ($z === null) {
            $result = null;
        } elseif ($z < -3) {
            $result = 'Severely Wasted';
        } elseif ($z < -2) {
            $result = 'Wasted';
        } elseif ($z <= 1) {
            $result = 'Normal';
        } elseif ($z <= 2) {
            $result = 'Risk of Overweight';
        } elseif ($z <= 3) {
            $result = 'Overweight';
        } else {
            $result = 'Obese';
        }

        return $result;
    }
    protected function loadWilayahUser(): void
    {
        $user = User::find($this->userId);

        if (!$user || !$user->id_wilayah) {
            $this->wilayahUser = [
                'provinsi' => null,
                'kota' => null,
                'kecamatan' => null,
                'kelurahan' => null,
            ];
            return;
        }

        $zona = Wilayah::find($user->id_wilayah);

        $this->wilayahUser = [
            'provinsi' => $zona->provinsi ?? null,
            'kota' => $zona->kota ?? null,
            'kecamatan' => $zona->kecamatan ?? null,
            'kelurahan' => $zona->kelurahan ?? null,
        ];
    }

    protected function normalizeBeratGramToKg($berat)
    {
        if (is_null($berat) || $berat === '') {
            return null;
        }

        $berat = floatval(str_replace(',', '.', trim($berat)));

        // Jika berat lebih dari 100, anggap dalam gram
        if ($berat > 100) {
            return round($berat / 1000, 2); // Konversi ke kg
        }

        return round($berat, 2); // Sudah dalam kg
    }

    protected function normalizePanjangMToCM($panjang)
    {
        if (is_null($panjang) || $panjang === '') {
            return null;
        }

        $panjang = floatval(str_replace(',', '.', trim($panjang)));

        // Jika panjang kurang dari atau sama dengan 1, anggap dalam meter
        if ($panjang <= 1) {
            return round($panjang * 100, 2); // Konversi ke cm
        }

        return round($panjang, 2); // Sudah dalam cm
    }

    protected function normalizeJenisKelamin($jk)
    {
        if (is_null($jk) || $jk === '') {
            return null;
        }

        $jk = strtoupper(trim($jk));

        if (in_array($jk, ['L', 'LAKI-LAKI', 'PRIA', '1'])) {
            return 'L';
        } elseif (in_array($jk, ['P', 'PEREMPUAN', 'WANITA', '2'])) {
            return 'P';
        }

        return null; // Nilai tidak valid
    }

}
