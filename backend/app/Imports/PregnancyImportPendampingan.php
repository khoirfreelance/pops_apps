<?php

namespace App\Imports;

use App\Models\{Pregnancy, Wilayah, Log, User, Cadre};
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\{
  ToCollection,
  WithStartRow,
  WithCustomCsvSettings
};

class PregnancyImportPendampingan implements
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

  public function startRow(): int
  {
    return 2; // skip header
  }

  public function getCsvSettings(): array
  {
    $path = request()->file('file')->getRealPath();

    return [
      'delimiter' => $this->detectDelimiter($path),
      'input_encoding' => 'UTF-8',
    ];
  }

  protected function detectDelimiter(string $path): string
  {
    $line = fgets(fopen($path, 'r'));
    return substr_count($line, ';') > substr_count($line, ',') ? ';' : ',';
  }

  public function collection(Collection $rows): void
  {
    foreach ($rows as $row) {
      $this->processRow($row->toArray());
    }
  }

  protected function processRow(array $row): void
  {
    DB::transaction(function () use ($row) {

      $berat = $this->normalizeDecimal($row[21] ?? null);
      // HARD VALIDATION (WAJIB)
      if ($berat !== null && ($berat < 20 || $berat > 999)) {
        $berat = null;
      }
      $tinggi = $this->meterToCm($this->normalizeDecimal($row[22] ?? null));
      // HARD VALIDATION (WAJIB)
      if ($tinggi !== null && ($tinggi < 50 || $tinggi > 999)) {
        $tinggi = null;
      }

      
      $hb = $this->normalizeDecimal($row[24] ?? null);
      // HARD VALIDATION (WAJIB)
      if ($hb !== null && ($hb < 5 || $hb > 999)) {
        $hb = null;
      }
      $lila = $this->normalizeDecimal($row[26] ?? null);
      // HARD VALIDATION (WAJIB)
      if ($lila !== null && ($lila < 10 || $lila > 999)) {
        $lila = null;
      }
      $usia = isset($row[5]) ? (int) $row[5] : null;
      // HARD VALIDATION (WAJIB)
      if ($usia !== null && ($usia < 10 || $usia > 999)) {
        $usia = null;
      }

      $imt = $this->hitungIMT($berat, $tinggi);

      $pregnancy = Pregnancy::create([
        'nama_petugas' => $row[1] ?? null,
        'tanggal_pendampingan' => $this->convertDate($row[2] ?? null),

        'nama_ibu' => $row[3] ?? null,
        'nik_ibu' => $row[4] ?? null,
        'usia_ibu' => $usia,

        'nama_suami' => $row[6] ?? null,
        'nik_suami' => $row[7] ?? null,
        'pekerjaan_suami' => $row[8] ?? null,
        'usia_suami' => isset($row[9]) ? (int) $row[9] : null,

        'kehamilan_ke' => $row[10] ?? null,
        'jumlah_anak' => $row[11] ?? null,
        'status_kehamilan' => $row[12] ?? null,
        'riwayat_4t' => $row[13] ?? null,

        'riwayat_penggunaan_kb' => $row[14] ?? null,
        'riwayat_ber_kontrasepsi' => $row[15] ?? null,

        'provinsi' => $this->wilayahUser['provinsi'],
        'kota' => $this->wilayahUser['kota'],
        'kecamatan' => $row[16] ?? null,
        'kelurahan' => $row[17] ?? null,
        'rt' => $row[18] ?? null,
        'rw' => $row[19] ?? null,

        'tanggal_pemeriksaan_terakhir' => $this->convertDate($row[20] ?? null),
        'berat_badan' => $berat,
        'tinggi_badan' => $tinggi,
        'kadar_hb' => $hb,
        'lila' => $lila,

        'imt' => $imt,
        'status_gizi_hb' => $hb !== null ? ($hb < 11 ? 'Anemia' : 'Normal') : null,
        'status_gizi_lila' => $lila !== null ? ($lila < 23.5 ? 'KEK' : 'Normal') : null,
        'status_risiko_usia' => ($usia < 20 || $usia > 35) ? 'Berisiko' : 'Normal',

        'riwayat_penyakit' => $row[28] ?? null,
        'usia_kehamilan_minggu' => (int) ($row[29] ?? 0),
        'taksiran_berat_janin' => $row[30] ?? null,
        'tinggi_fundus' => $row[31] ?? null,
        'hpl' => $this->convertDate($row[32] ?? null),

        'terpapar_asap_rokok' => $this->toBool($row[33] ?? null),
        'mendapat_ttd' => $this->toBool($row[34] ?? null),
        'menggunakan_jamban' => $this->toBool($row[35] ?? null),
        'menggunakan_sab' => $this->toBool($row[36] ?? null),
        'fasilitas_rujukan' => $this->toBool($row[37] ?? null),
        'riwayat_keguguran_iufd' => $this->toBool($row[38] ?? null),
        'mendapat_kie' => $this->toBool($row[39] ?? null),
        'mendapat_bantuan_sosial' => $this->toBool($row[40] ?? null),

        'rencana_tempat_melahirkan' => $row[41] ?? null,
        'rencana_asi_eksklusif' => $row[42] ?? null,
        'rencana_tinggal_setelah' => $row[43] ?? null,
        'rencana_kontrasepsi' => $row[44] ?? null,

        'posyandu' => $this->posyanduUser ?? null,
      ]);

      Wilayah::firstOrCreate([
        'provinsi' => $this->wilayahUser['provinsi'],
        'kota' => $this->wilayahUser['kota'],
        'kecamatan' => $pregnancy->kecamatan,
        'kelurahan' => $pregnancy->kelurahan,
      ]);

      // Takeout cause posyandu get from cadre of user doing the import
      // Posyandu::firstOrCreate([
      //     'nama_posyandu' => $pregnancy->posyandu ?? '-',
      //     'rt' => $pregnancy->rt,
      //     'rw' => $pregnancy->rw,
      // ]);

      Log::create([
        'id_user' => Auth::id(),
        'context' => 'Ibu Hamil',
        'activity' => 'Import data kehamilan ibu ' . ($row[3] ?? '-'),
        'timestamp' => now(),
      ]);
    });
  }

  /* ================= HELPERS ================= */

  private function meterToCm(?float $value): ?float
  {
    if (!$value)
      return null;
    return $value < 3 ? $value * 100 : $value;
  }

  private function hitungIMT($berat, $tinggi): ?float
  {
    if (!$berat || !$tinggi)
      return null;
    return round($berat / pow($tinggi / 100, 2), 1);
  }

  private function normalizeDecimal(?string $value): ?float
  {
    if (!$value)
      return null;
    $value = str_replace(',', '.', trim($value));
    return is_numeric($value) ? (float) $value : null;
  }

  private function convertDate($date): ?string
  {
    try {
      return $date ? Carbon::parse(str_replace('/', '-', $date))->format('Y-m-d') : null;
    } catch (\Exception) {
      return null;
    }
  }

  private function toBool($val): bool
  {
    return in_array(strtolower(trim((string) $val)), ['ya', 'y', '1', 'true']);
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
}
