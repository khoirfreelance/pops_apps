<?php

namespace App\Imports;

use App\Models\Catin;
use App\Models\Wilayah;
use App\Models\User;
use App\Models\Posyandu;
use App\Models\Log;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithCustomCsvSettings;

class CatinImport implements 
    ToModel, 
    WithHeadingRow, 
    WithCustomCsvSettings
{
    protected array $wilayahUser = [];

    public function __construct(private int $userId)
    {
        $this->loadWilayahUser();
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
            dump($row);

            $catin = Catin::create([
                'nama_petugas' => $row['nama_petugas'] ?? null,
                'tanggal_pendampingan' => $this->convertDate($row['tanggal_pendampingan'] ?? null),

                'nama_perempuan' => $row['nama_perempuan'] ?? null,
                'nik_perempuan' => $row['nik_perempuan'] ?? null,
                'usia_perempuan' => $row['usia_perempuan'] ?? 0,
            ]);

            $wilayah = Wilayah::firstOrCreate([
                'provinsi' => $this->wilayahUser['provinsi'],
                'kota' => $this->wilayahUser['kota'],
                'kecamatan' => $row['kecamatan'] ?? null,
                'kelurahan' => $row['kelurahan'] ?? null,
            ]);

            Posyandu::firstOrCreate([
                'nama_posyandu' => $row['posyandu'] ?? '-',
                'id_wilayah' => $wilayah->id,
                'rt' => ltrim($row['rt'], "0") ?? null,
                'rw' => ltrim($row['rw'], "0") ?? null,
            ]);

            Log::create([
                'id_user' => $this->userId,
                'context' => 'Catin',
                'activity' => 'Import data calon pengantin ' . ($row['nama_perempuan'] ?? '-'),
                'timestamp' => now(),
            ]);

            return $catin;
        });
    }

    /* =========================
     * Helper functions
     * ========================= */

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
