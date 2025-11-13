<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Keluarga;
use App\Models\Posyandu;
use App\Models\TPK;
use App\Models\User;
use App\Models\Catin;
use App\Models\Pendampingan;
use App\Models\Pregnancy;
use App\Models\Child;
use App\Models\Kunjungan;
use App\Models\Bride;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function stats()
    {
        // ambil semua nilai RT dan RW dari masing-masing tabel
        $rts = collect()
            ->merge(Keluarga::whereNotNull('rt')->pluck('rt'))
            ->merge(Pendampingan::whereNotNull('rt')->pluck('rt'))
            ->merge(Posyandu::whereNotNull('rt')->pluck('rt'));

        $rws = collect()
            ->merge(Keluarga::whereNotNull('rw')->pluck('rw'))
            ->merge(Pendampingan::whereNotNull('rw')->pluck('rw'))
            ->merge(Posyandu::whereNotNull('rw')->pluck('rw'));

        // hitung yang unik
        $uniqueRt = $rts->unique()->count();
        $uniqueRw = $rws->unique()->count();

        $anakDariPendampingan = Child::select('nik_anak', DB::raw('MAX(usia) as usia_terbaru'))
            ->groupBy('nik_anak')
            ->having('usia_terbaru', '>', 60)
            ->count();

        $anakDariKunjungan = Kunjungan::select('nik', DB::raw('MAX(usia_saat_ukur) as usia_terbaru'))
            ->groupBy('nik')
            ->having('usia_terbaru', '>', 60)
            ->count();

        $anak = $anakDariPendampingan +1;//$anakDariKunjungan;

        return response()->json([
            'rw' => $uniqueRw,
            'rt' => $uniqueRt,
            'keluarga' => Keluarga::count(),
            'tpk' => TPK::count(),
            'ibu_hamil' => Pregnancy::count(),
            'posyandu' => Posyandu::select('nama_posyandu')->groupBy('nama_posyandu','id_wilayah')->get()->count(),
            'bidan' => User::where('role', '=', 'Bidan')->count(),
            'catin' => Bride::count(),
            'anak' => $anak,
        ]);
    }

    public function filters()
    {
        return response()->json([
            'kelurahan' => ['Bojonggede', 'Cipayung', 'Pabuaran'],
            'posyandu' => Posyandu::pluck('nama')->unique()->values(),
            'rw' => Keluarga::whereNotNull('rw')->pluck('rw')->unique()->values(),
            'rt' => Keluarga::whereNotNull('rt')->pluck('rt')->unique()->values(),
        ]);
    }

    public function getPosyanduWilayah($id)
    {
        $data = Posyandu::where('id', $id)
            ->select('id', 'nama_posyandu', 'rw', 'rt', 'id_wilayah')
            ->first();

        if (!$data) {
            return response()->json(['message' => 'Posyandu tidak ditemukan'], 404);
        }

        // Ambil semua RW & RT dari posyandu dengan wilayah yang sama
        $rw = Posyandu::where('id_wilayah', $data->id_wilayah)
            ->whereNotNull('rw')
            ->distinct()
            ->pluck('rw');

        $rt = Posyandu::where('id_wilayah', $data->id_wilayah)
            ->whereNotNull('rt')
            ->distinct()
            ->pluck('rt');

        return response()->json([
            'rw' => $rw,
            'rt' => $rt,
        ]);
    }

}
