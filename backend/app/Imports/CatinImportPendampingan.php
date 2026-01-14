<?php

namespace App\Imports;

use App\Models\Catin;
use App\Models\Cadre;
use App\Models\Wilayah;
use App\Models\User;
use App\Models\Posyandu;
use App\Models\Log;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\WithCustomCsvSettings;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithStartRow;

class CatinImportPendampingan implements
    ToCollection,
    WithStartRow,
    WithCustomCsvSettings
{
    protected array $wilayahUser = [];
    protected string $posyanduUser = '';

    public function __construct(private int $userId)
    {
        $this->loadWilayahUser();
    }

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

    public function startRow(): int
    {
        return 2; // skip header
    }

    public function collection(Collection $rows)
    {
        try {
            foreach ($rows as $row) {
                $this->processRow($row->toArray());
            }
        } catch (\Exception $e) {
            throw $e;
        }
    }

    private function processRow(array $row): void
    {
        $berat = $row[19] != null ? $this->normalizeDecimal($row[19]) : null;
        $tinggi = $row[20] != null ? $this->normalizeDecimal($row[20]) : null;
        $lila = $row[24] != null ? $this->normalizeDecimal($row[24]) : null;
        $hb = $row[23] != null ? $this->normalizeDecimal($row[23]) : null;
        $usia_perempuan = $row[6] != null ? (int) $row[6] : null;
        DB::transaction(function () use ($row, $berat, $tinggi, $lila, $hb, $usia_perempuan) {
            $catin = Catin::create(attributes: [
                'nama_petugas' => $row[1] ?? null,
                'tanggal_pendampingan' => $this->convertDate($row[2] ?? null),

                'nama_perempuan' => $row[3] ?? null,
                'nik_perempuan' => $row[4] ?? null,
                'pekerjaan_perempuan' => $row[5] ?? null,
                'usia_perempuan' => $usia_perempuan ?? 0,
                'hp_perempuan' => $row[7] ?? null,

                'nama_laki' => $row[8] ?? null,
                'nik_laki' => $row[9] ?? null,
                'pekerjaan_laki' => $row[10] ?? null,
                'usia_laki' => $row[11] ?? 0,
                'hp_laki' => $row[12] ?? null,

                'pernikahan_ke' => $row[13] ?? null,

                'provinsi' => $this->wilayahUser['provinsi'] ?? null,
                'kota' => $this->wilayahUser['kota'] ?? null,
                'kecamatan' => $row[14] ?? null,
                'kelurahan' => $row[15] ?? null,
                'rw' => $row[16] ?? null,
                'rt' => $row[17] ?? null,
                'posyandu' => $this->posyanduUser ?? null,

                'tanggal_pemeriksaan' => $row[18] != null ? $this->convertDate($row[18]) : null,
                'berat_perempuan' => $berat,
                'tinggi_perempuan' => $tinggi,
                'hb_perempuan' => $hb,
                'lila_perempuan' => $lila,
                'terpapar_rokok' => $this->toBool($row[26] ?? null),
                'mendapat_ttd' => $this->toBool($row[27] ?? null),
                'menggunakan_jamban' => $this->toBool($row[28] ?? null),
                'sumber_air_bersih' => $this->toBool($row[29] ?? null),
                'punya_riwayat_penyakit' => $this->toBool($row[30] ?? null),
                'riwayat_penyakit' => $row[31] ?? null,
                'mendapat_fasilitas_rujukan' => $this->toBool($row[31] ?? null),
                'mendapat_kie' => $this->toBool($row[33] ?? null),
                'mendapat_bantuan_pmt' => $this->toBool($row[33] ?? null),

                'tanggal_rencana_menikah' => $this->convertDate($row[35] ?? null),
                'rencana_tinggal' => $row[36] ?? null,

                'imt' => $this->hitungIMT($berat, $tinggi),
                'status_kek' => $this->statusKEK($lila),
                'status_hb' => $this->statusHB($hb),
                'status_risiko' => $this->statusRisiko($usia_perempuan),

            ]);

            $wilayah = Wilayah::firstOrCreate([
                'provinsi' => $this->wilayahUser['provinsi'],
                'kota' => $this->wilayahUser['kota'],
                'kecamatan' => $row['14'] ?? null,
                'kelurahan' => $row['15'] ?? null,
            ]);

            // Takeout cause posyandu get from cadre of user doing the import
            // Posyandu::firstOrCreate([
            //     'nama_posyandu' => $row['posyandu'] ?? '-',
            //     'id_wilayah' => $wilayah->id,
            //     'rt' => ltrim($row['rt'], "0") ?? null,
            //     'rw' => ltrim($row['rw'], "0") ?? null,
            // ]);

            Log::create([
                'id_user' => $this->userId,
                'context' => 'Catin',
                'activity' => 'Import data calon pengantin ' . ($row['nama_perempuan'] ?? '-'),
                'timestamp' => now(),
            ]);
        });
    }

    /* =========================
     * Helper functions
     * ========================= */

    private function toBool($val)
    {
        return in_array(strtolower(trim($val)), ['ya', 'y', 'true', '1']) ? true : false;
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

    protected function loadWilayahUser(): void
    {
        $user = User::find($this->userId);
        $this->posyanduUser = 'UNKNOWN';

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
        $cadre = Cadre::where('id_user', $this->userId)->first();

        if ($cadre && $cadre->posyandu) {
            $this->posyanduUser = $cadre->posyandu->nama_posyandu;
        }

        $this->wilayahUser = [
            'provinsi' => $zona->provinsi ?? null,
            'kota' => $zona->kota ?? null,
            'kecamatan' => $zona->kecamatan ?? null,
            'kelurahan' => $zona->kelurahan ?? null,
        ];
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

    private function statusKEK($lila)
    {
        if (is_null($lila))
            return null;
        return $lila < 23.5 ? 'KEK' : 'Normal';
    }

    private function statusHB($hb)
    {
        if (is_null($hb))
            return null;
        return $hb < 12 ? 'Anemia' : 'Normal';
    }

    private function statusRisiko($usia_perempuan)
    {
        if (is_null($usia_perempuan))
            return null;
        return ($usia_perempuan < 20 || $usia_perempuan > 35) ? 'Berisiko' : 'Normal';
    }

    private function normalizeDecimal(?string $value): ?float
    {
        if ($value === null) {
            return null;
        }

        $value = trim($value);
        if ($value === '') {
            return null;
        }

        // jika ada koma dan titik, asumsikan:
        // titik = ribuan, koma = desimal (ID/EU)
        if (str_contains($value, ',') && str_contains($value, '.')) {
            $value = str_replace('.', '', $value);
            $value = str_replace(',', '.', $value);
        }
        // jika hanya koma → desimal
        elseif (str_contains($value, ',')) {
            $value = str_replace(',', '.', $value);
        }

        if (!is_numeric($value)) {
            return null;
        }

        return (float) $value;
    }



}
