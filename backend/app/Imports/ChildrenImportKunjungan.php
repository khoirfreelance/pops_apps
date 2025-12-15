<?php

namespace App\Imports;

use App\Models\Kunjungan;
use App\Models\Wilayah;
use App\Models\Posyandu;
use App\Models\Keluarga;
use App\Models\Log;
use Carbon\Carbon;
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
    public function __construct(private int $userId) {}

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


            /**
             * Status Berat Badan	
             * Sangat Kurang	Severely Underweight
             * Kurang	Underweight
             * Normal	Normal
             * Risiko Berat Badan Lebih	Risiko BB Lebih
             * 	
             * Status Tinggi Badan	
             * Sangat Pendek	Severely Stunted
             * Pendek	Stunted
             * Normal	Normal
             * Tinggi	Tinggi
             * 	
             * Status BB/TB	
             * Gizi Buruk	Severely Wasted
             * Gizi Baik	Wasted
             * Berisiko Gizi Lebih	Possible Risk of Overweight
             * Gizi Lebih	Overweight
             * Obesitas	Obese   
             * */
            $status_bbu  = $row["bbu"];
            $status_tbu  = $row["tbu"];
            $status_bbtb = $row["bbtb"];

            $naikBB = $row["naik_berat_badan"];

            // =========================
            // 4. Simpan Kunjungan
            // =========================
            $kunjungan = Kunjungan::create([
                'nik' => $row['nik'],
                'nama_anak' => $row['nama'],
                'jk' => $row['jk'],
                'tgl_lahir' => $tglLahir,
                'bb_lahir' =>  $this->normalizeDecimal($row['bb_lahir']??null),
                'tb_lahir' => $row['tb_lahir'],
                'nama_ortu' => $row['nama_ortu'],
                'alamat' => $row['alamat'],
                'rt' => $row['rt'],
                'rw' => $row['rw'],
                'puskesmas' => $row['pukesmas'],
                'posyandu' => $row['posyandu'],
                'tgl_pengukuran' => $tglUkur,
                'usia_saat_ukur' => $usia,
                'bb' =>  $this->normalizeDecimal($row['berat']??null),
                'tb' =>  $this->normalizeDecimal($row['tinggi']??null),
                'lila' => $row['lila'] ?? null,

                'bb_u' => $status_bbu,
                'zs_bb_u' => $z_bbu,
                'tb_u' => $status_tbu,
                'zs_tb_u' => $z_tbu,
                'bb_tb' => $status_bbtb,
                'zs_bb_tb' => $z_bbtb,
                'naik_berat_badan' => $naikBB,
            ]);

            // =========================
            // 5. Wilayah & Posyandu
            // =========================
            $wilayah = Wilayah::firstOrCreate([
                'provinsi' => $row['prov'],
                'kota' => $row['kab_kota'],
                'kecamatan' => $row['kec'],
                'kelurahan' => $row['desa_kel'],
            ]);

            Posyandu::firstOrCreate([
                'nama_posyandu' => $row['posyandu'],
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
                        'alamat' => $row['alamat'],
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

}
