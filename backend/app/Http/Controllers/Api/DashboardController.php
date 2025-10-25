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

        return response()->json([
            'rw' => $uniqueRw,
            'rt' => $uniqueRt,
            'keluarga' => Keluarga::count(),
            'tpk' => TPK::count(),
            'ibu_hamil' => 30, // contoh statis
            'posyandu' => Posyandu::count(),
            'bidan' => User::where('role', '=', 'Bidan')->count(),
            'catin' => Catin::count(),
            'anak' => 45,
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
