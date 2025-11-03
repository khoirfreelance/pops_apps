<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Pregnancy;
use Carbon\Carbon;

class PregnancyController extends Controller
{
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
            // Lewati baris kosong
            if (empty($data[0])) continue;

            // Baris pertama dianggap header
            if (!$header) {
                $header = $data;
                continue;
            }

            $count++;

            // ✅ Mapping kolom CSV ke field sesuai model Pregnancy
            $rows[] = [
                'nama_petugas' => $data[0] ?? null,
                'tanggal_pendampingan' => $this->convertDate($data[1] ?? null),
                'nama_ibu' => $data[2] ?? null,
                'nik_ibu' => $data[3] ?? null,
                'usia_ibu' => $data[4] ?? null,
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
                'kecamatan' => $data[15] ?? null,
                'desa' => $data[16] ?? null,
                'rt' => $data[17] ?? null,
                'rw' => $data[18] ?? null,
                'tanggal_pemeriksaan_terakhir' => $this->convertDate($data[19] ?? null),
                'berat_badan' => $data[20] ?? null,
                'tinggi_badan' => $data[21] ?? null,
                'imt' => $data[22] ?? null,
                'kadar_hb' => $data[23] ?? null,
                'status_gizi_hb' => $data[24] ?? null,
                'lila' => $data[25] ?? null,
                'status_gizi_lila' => $data[26] ?? null,
                'riwayat_penyakit' => $data[27] ?? null,
                'usia_kehamilan_minggu' => $data[28] ?? null,
                'taksiran_berat_janin' => $data[29] ?? null,
                'tinggi_fundus' => $data[30] ?? null,
                'hpl' => $this->convertDate($data[31] ?? null),
                'terpapar_asap_rokok' => $data[32] ?? null,
                'mendapat_ttd' => $data[33] ?? null,
                'menggunakan_jamban' => $data[34] ?? null,
                'menggunakan_sab' => $data[35] ?? null,
                'fasilitas_rujukan' => $data[36] ?? null,
                'riwayat_keguguran_iufd' => $data[37] ?? null,
                'mendapat_kie' => $data[38] ?? null,
                'mendapat_bantuan_sosial' => $data[39] ?? null,
                'rencana_tempat_melahirkan' => $data[40] ?? null,
                'rencana_asi_eksklusif' => $data[41] ?? null,
                'rencana_tinggal_setelah' => $data[42] ?? null,
                'rencana_kontrasepsi' => $data[43] ?? null,
            ];
        }

        fclose($handle);

        // ✅ Simpan ke DB (pakai insert batch)
        if (!empty($rows)) {
            foreach (array_chunk($rows, 100) as $chunk) {
                Pregnancy::insert($chunk);
            }
        }

        return response()->json([
            'message' => "Berhasil import {$count} data kehamilan",
            'count' => $count,
        ], 200);
    }

    private function convertDate($date)
    {
        if (!$date) return null;
        $date = str_replace('/', '-', trim($date));
        try {
            return Carbon::parse($date)->format('Y-m-d');
        } catch (\Exception $e) {
            return null;
        }
    }
}
