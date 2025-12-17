<?php

namespace App\Imports;

use App\Models\Child;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Exception;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithStartRow;

class ChildrenImportPendampingan implements ToCollection, WithStartRow
{
    public function startRow(): int
    {
        return 2; // skip header
    }

    public function collection(Collection $rows)
    {
        DB::beginTransaction();

        try {
            foreach ($rows as $index => $row) {

                // ❌ STOP kalau struktur tidak sesuai
                if (count($row) < 45) {
                    throw new Exception("Format CSV tidak valid di baris " . ($index + 2));
                }

                $data = [
                    'petugas' => $row[1],
                    'tgl_pendampingan' => $this->convertDate($row[2]),
                    'nama_anak' => $row[3],
                    'nik_anak' => $row[4],
                    'jk' => $row[5],
                    'usia' => $row[6],

                    'nama_ayah' => $row[7],
                    'nik_ayah' => $row[8],
                    'pekerjaan_ayah' => $row[9],
                    'usia_ayah' => $row[10],

                    'nama_ibu' => $row[11],
                    'nik_ibu' => $row[12],
                    'pekerjaan_ibu' => $row[13],
                    'usia_ibu' => $row[14],

                    'anak_ke' => $row[15],
                    'riwayat_4t' => $row[16],
                    'riwayat_kb' => $row[17],
                    'alat_kontrasepsi' => $row[18],

                    'kecamatan' => $row[19],
                    'kelurahan' => $row[20],
                    'rt' => $row[21],
                    'rw' => $row[22],

                    'bb_lahir' => $row[23],
                    'tb_lahir' => $row[24],
                    'bb' => $row[25],
                    'tb' => $row[26],

                    'status_gizi' => $row[27],
                    'lila' => $row[28],
                    'lika' => $row[29],

                    'asi' => $row[30],
                    'imunisasi' => $row[31],
                    'diasuh_oleh' => $row[32],
                    'rutin_posyandu' => $row[33],

                    'riwayat_penyakit_bawaan' => $row[34],
                    'penyakit_bawaan' => $row[35],
                    'riwayat_penyakit_6bulan' => $row[36],
                    'penyakit_6bulan' => $row[37],

                    'terpapar_asap_rokok' => $row[38],
                    'penggunaan_jamban_sehat' => $row[39],
                    'penggunaan_sab' => $row[40],
                    'apabila_ada_penyakit' => $row[41],
                    'memiliki_jaminan' => $row[42],
                    'kie' => $row[43],
                    'mendapatkan_bantuan' => $row[44],
                ];

                // Z-Score
                $z_bbu = $this->hitungZScore('BB/U', $data['jk'], $data['usia'], $data['bb']);
                $z_tbu = $this->hitungZScore('TB/U', $data['jk'], $data['usia'], $data['tb']);
                $z_bbtb = $this->hitungZScore('BB/TB', $data['jk'], $data['tb'], $data['bb']);

                $data = array_merge($data, [
                    'zs_bb_u' => $z_bbu,
                    'bb_u' => $this->statusBBU($z_bbu),
                    'zs_tb_u' => $z_tbu,
                    'tb_u' => $this->statusTBU($z_tbu),
                    'zs_bb_tb' => $z_bbtb,
                    'bb_tb' => $this->statusBBTB($z_bbtb),
                ]);

                Child::create($data);
            }

            DB::commit();

        } catch (Exception $e) {
            DB::rollBack();
            throw $e; // ⬅️ penting supaya Excel::import gagal
        }
    }

    private function convertDate($date)
    {
        if (!$date) {
            return null;
        }
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



}
