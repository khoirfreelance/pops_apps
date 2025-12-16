<?php

namespace App\Imports;

use App\Models\Kunjungan;
use App\Models\Wilayah;
use App\Models\Posyandu;
use App\Models\Keluarga;
use App\Models\Log;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithCustomCsvSettings;

class ChildrenImportKunjungan implements
    ToModel,
    WithHeadingRow,
    WithChunkReading,
    WithCustomCsvSettings
{
    protected array $wilayahUser = [];
    public function __construct(private int $userId) {
        $this->loadWilayahUser();
    }

    public function chunkSize(): int
    {
        return 200;
    }

    public function getCsvSettings(): array
    {
        return [
            'delimiter' => ',',
            'input_encoding' => 'UTF-8',
        ];
    }

    public function model(array $row)
    {
        return DB::transaction(function () use ($row) {

            // =========================
            // 1. Parse tanggal
            // =========================
            $tglLahir = $this->convertDate($row['tgl_lahir'] ?? null);
            $tglUkur  = $this->convertDate($row['tanggal_pengukuran'] ?? null);

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
                'provinsi' => $this->wilayahUser['provinsi'],
                'kota' => $this->wilayahUser['kota'],
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
                'provinsi' => $this->wilayahUser['provinsi'],
                'kota' => $this->wilayahUser['kota'],
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

    private function convertDate($date)
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

    /** Hitung umur (bulan) */
    private function hitungUmurBulan($tglLahir, $tglUkur)
    {
        if (!$tglLahir || !$tglUkur)
            return null;

        $lahir = new \DateTime($tglLahir);
        $ukur = new \DateTime($tglUkur);
        $diff = $lahir->diff($ukur);

        return $diff->y * 12 + $diff->m + ($diff->d / 30);
    }
    private function normalizeDecimal($value)
    {
        if ($value === null || $value === '') {
            return null;
        }

        // ganti koma menjadi titik
        $value = str_replace(',', '.', $value);

        return is_numeric($value) ? (float) $value : null;
    }

    private function statusBB($status){
        $beratBadan = [
            "Sangat Kurang" => "Severely Underweight",
            "Kurang" => "Underweight",
            "Normal" => "Normal",
            "Risiko Berat Badan Lebih" => "Risiko BB Lebih",
            "BB Lebih" => "BB Lebih",
        ];
        return $beratBadan[$status] ?? null;
    }

    private function statusTB($status){
        $tinggiBadan = [
            "Sangat Pendek" => "Severely Stunted",
            "Pendek" => "Stunted",
            "Normal" => "Normal",
            "Tinggi" => "Tinggi",
        ];
        return $tinggiBadan[$status] ?? null;
    }

    private function statusBBTB($status){
        $bbTb = [
            "Gizi Buruk" => "Severely Wasted",
            "Gizi Kurang" => "Wasted",
            "Gizi Baik" => "Normal",
            "Risiko Gizi Lebih" => "Possible Risk of Overweight",
            "Gizi Lebih" => "Overweight",
            "Obesitas" => "Obese",
        ];
        return $bbTb[$status] ?? null;
    }

    private function normalizeNaikBeratBadan($value)
    {
        if (is_null($value)) {
            return null;
        }

        $value = strtolower(trim($value));

        if (in_array($value, ['yes', 'ya', '1', 'true', 'T'])) {
            return true;
        } elseif (in_array($value, ['no', 'tidak', '0', 'false', 'F'])) {
            return false;
        }

        return null;
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
}
