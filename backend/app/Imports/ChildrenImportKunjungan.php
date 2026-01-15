<?php

namespace App\Imports;

use App\Models\Child;
use App\Models\User;
use App\Models\Wilayah;
use App\Models\Log;
use App\Models\Keluarga;
use App\Models\AnggotaKeluarga;
use Illuminate\Support\Collection;
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

    public function startRow(): int
    {
        return 2; // skip header
    }

    public function collection(Collection $rows)
    {

        try {
            foreach ($rows as $index => $row) {

                // ❌ STOP kalau struktur tidak sesuai
                if (count($row) < 45) {
                    throw new Exception("Format CSV tidak valid di baris " . ($index + 2));
                }

                // dd($this->wilayahUser['provinsi']);

                $data = [
                    'petugas' => strtoupper($row[1]),
                    'tgl_pendampingan' => $this->convertDate($row[2]),
                    'nama_anak' => strtoupper($row[3]),
                    'nik_anak' => $row[4],
                    'jk' => $this->normalizeJenisKelamin($row[5]),
                    'usia' => ltrim(trim($row[6]), "0"),

                    'nama_ayah' => strtoupper($row[7]),
                    'nik_ayah' => $row[8],
                    'pekerjaan_ayah' => strtoupper($row[9]),
                    'usia_ayah' => $row[10],

                    'nama_ibu' => strtoupper($row[11]),
                    'nik_ibu' => $row[12],
                    'pekerjaan_ibu' => strtoupper($row[13]),
                    'usia_ibu' => $row[14],

                    'anak_ke' => $row[15],

                    'riwayat_4t' => $row[16],
                    'riwayat_kb' => $row[17],
                    'alat_kontrasepsi' => $row[18],

                    'provinsi' => strtoupper($this->wilayahUser['provinsi']),
                    'kota' => strtoupper($this->wilayahUser['kota']),
                    'kecamatan' => strtoupper($row[19]),
                    'kelurahan' => strtoupper($row[20]),
                    'rt' => ltrim($row[21], "0"),
                    'rw' => ltrim($row[22], "0"),

                    'bb_lahir' => $this->normalizeBeratGramToKg($row[23]),
                    'tb_lahir' => $this->normalizePanjangMToCM($row[24]),
                    'bb' => $this->normalizeBeratGramToKg($row[25]),
                    'tb' => $this->normalizePanjangMToCM($row[26]),

                    'status_gizi' => strtoupper($row[27]),
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

                $wilayah = Wilayah::firstOrCreate([
                    'provinsi' => $data['provinsi'],
                    'kota' => $data['kota'],
                    'kecamatan' => $data['kecamatan'],
                    'kelurahan' => $data['kelurahan'],
                ]);

                $noKK = $data['nik_ayah'] ?? $data['nik_ibu'] ?? $data['nik_anak'] ?? null;

                if ($noKK) {
                    $keluarga = Keluarga::firstOrCreate(
                        [
                            'no_kk' => $noKK,
                        ],
                        [
                            'alamat' => 'desa ' . $data['kelurahan'] . ', kec. ' . $data['kecamatan'] . ', kota/kab. ' . $data['kota'],
                            'rt' => $data['rt'] ?? null,
                            'rw' => $data['rw'] ?? null,
                            'id_wilayah' => $wilayah->id,
                            'is_pending' => true,
                        ]
                    );
                    $anggota = [
                        'id_keluarga' => $keluarga->id,
                        'nama' => $data['nama_anak'],
                        'nik' => $data['nik_anak'],
                        'jenis_kelamin' => $this->normalizeJenisKelamin($data['jk']),
                        'status_hubungan' => 'Anak',
                        'is_pending' => true,
                    ];

                    $anggota_ayah = [
                        'id_keluarga' => $keluarga->id,
                        'nama' => $data['nama_ayah'],
                        'nik' => $data['nik_ayah'],
                        'jenis_kelamin' => 'L',
                        'status_hubungan' => 'Kepala Keluarga',
                        'is_pending' => true,
                    ];

                    $anggota_ibu = [
                        'id_keluarga' => $keluarga->id,
                        'nama' => $data['nama_ibu'],
                        'nik' => $data['nik_ibu'],
                        'jenis_kelamin' => 'P',
                        'status_hubungan' => 'Istri',
                        'is_pending' => true,
                    ];

                    if ($data['nik_ayah']) {
                        AnggotaKeluarga::firstOrCreate(["nik" => $anggota_ayah['nik']], $anggota_ayah);
                    }else{
                        AnggotaKeluarga::create($anggota_ayah);
                    }

                    if ($data['nik_ibu']) {
                        AnggotaKeluarga::firstOrCreate(["nik" => $anggota_ibu['nik']], $anggota_ibu);
                    }else{
                        AnggotaKeluarga::create($anggota_ibu);
                    }

                    if ($data['nik_anak']) {
                        AnggotaKeluarga::firstOrCreate(["nik" => $anggota['nik']], $anggota);
                    }else{
                        AnggotaKeluarga::create($anggota);
                    }

                }


                // ✅ Log aktivitas
                Log::create([
                    'id_user' => \Auth::id(),
                    'context' => 'Pendampingan Anak',
                    'activity' => 'Import pendampingan anak ' . ($row['nama_anak'] ?? '-'),
                    'timestamp' => now(),
                ]);
            }

        } catch (Exception $e) {
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
