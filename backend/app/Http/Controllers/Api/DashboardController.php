<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
//use Illuminate\Http\Request;
use App\Models\Keluarga;
use App\Models\Posyandu;
use App\Models\TPK;
use App\Models\User;
use App\Models\Catin;
//use App\Models\Pendampingan;
use App\Models\Pregnancy;
use App\Models\Child;
use App\Models\Kunjungan;
//use App\Models\Bride;
use App\Models\Wilayah;
use Illuminate\Support\Facades\Auth;
//use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{

    public function stats()
    {
        $user = Auth::user();
        $isSuperAdmin = $user->role === 'Super Admin';
        $wilayah = Wilayah::find($user->id_wilayah);

        //dd($user->role,' and ',$user->id_wilayah);
        // ambil semua nilai RT dan RW dari masing-masing tabel
        if ($isSuperAdmin) {
            $rts = collect()
                ->merge(Pregnancy::whereNotNull('rt')->pluck('rt'))
                ->merge(Catin::whereNotNull('rt')->pluck('rt'))
                ->merge(Kunjungan::whereNotNull('rt')->pluck('rt'))
                ->merge(Child::whereNotNull('rt')->pluck('rt'));

            $rws = collect()
                ->merge(Pregnancy::whereNotNull('rw')->pluck('rw'))
                ->merge(Catin::whereNotNull('rw')->pluck('rw'))
                ->merge(Kunjungan::whereNotNull('rw')->pluck('rw'))
                ->merge(Child::whereNotNull('rw')->pluck('rw'));
        }else{
            $rts = collect()
                ->merge(Pregnancy::whereNotNull('rt')
                    ->where('kelurahan', $wilayah->kelurahan)
                    ->pluck('rt'))
                ->merge(Catin::whereNotNull('rt')
                    ->where('kelurahan', $wilayah->kelurahan)
                    ->pluck('rt'))
                ->merge(Kunjungan::whereNotNull('rt')
                    ->where('kelurahan', $wilayah->kelurahan)
                    ->pluck('rt'))
                ->merge(Child::whereNotNull('rt')
                    ->where('kelurahan', $wilayah->kelurahan)
                    ->pluck('rt'));

            $rws = collect()
                ->merge(Pregnancy::whereNotNull('rw')
                    ->where('kelurahan', $wilayah->kelurahan)
                    ->pluck('rw'))
                ->merge(Catin::whereNotNull('rw')
                    ->where('kelurahan', $wilayah->kelurahan)
                    ->pluck('rw'))
                ->merge(Kunjungan::whereNotNull('rw')
                    ->where('kelurahan', $wilayah->kelurahan)
                    ->pluck('rw'))
                ->merge(Child::whereNotNull('rw')
                    ->where('kelurahan', $wilayah->kelurahan)
                    ->pluck('rw'));
        }

        // hitung yang unik
        $uniqueRt = $rts->unique()->count();
        $uniqueRw = $rws->unique()->count();

        if ($isSuperAdmin) {
            $anakDariPendampingan = Child::whereRaw(
                    'TIMESTAMPDIFF(MONTH, tgl_lahir, CURDATE()) <= 60'
                )
                ->distinct('nik_anak')
                ->count('nik_anak');

            $anakDariKunjungan = Kunjungan::whereRaw(
                    'TIMESTAMPDIFF(MONTH, tgl_lahir, CURDATE()) <= 60'
                )
                ->distinct('nik')
                ->count('nik');

        }else {
            $anakDariPendampingan = Child::whereRaw(
                    'TIMESTAMPDIFF(MONTH, tgl_lahir, CURDATE()) <= 60 AND kelurahan = "'.$wilayah->kelurahan.'"'
                )
                ->distinct('nik_anak')
                ->count('nik_anak');

            $anakDariKunjungan = Kunjungan::whereRaw(
                    'TIMESTAMPDIFF(MONTH, tgl_lahir, CURDATE()) <= 60 AND kelurahan = "'.$wilayah->kelurahan.'"'
                )
                ->distinct('nik')
                ->count('nik');
        }

        $anak = $anakDariPendampingan + $anakDariKunjungan;

        if ($isSuperAdmin) {
            return response()->json([
                'rw' => $uniqueRw,
                'rt' => $uniqueRt,
                'keluarga' => Keluarga::count(),
                'tpk' => TPK::count(),
                'ibu_hamil' => Pregnancy::count(),
                'posyandu' => Posyandu::select('nama_posyandu')->groupBy('nama_posyandu','id_wilayah')->get()->count(),
                'bidan' => User::where('role', '=', 'Bidan')->count(),
                'catin' => Catin::count(),
                'anak' => $anakDariKunjungan,
            ]);
        }else {
            return response()->json([
                'rw' => $uniqueRw,
                'rt' => $uniqueRt,
                'keluarga' => Keluarga::where('id_wilayah', $wilayah->id)->count(),
                'tpk' => TPK::where('id_wilayah', $wilayah->id)->count(),
                'ibu_hamil' => Pregnancy::where('kelurahan', $wilayah->kelurahan)->count(),
                'posyandu' => Posyandu::select('nama_posyandu')->where('id_wilayah', $wilayah->id)->groupBy('nama_posyandu','id_wilayah')->get()->count(),
                'bidan' => User::where('role', '=', 'Bidan')->count(),
                'catin' => Catin::where('kelurahan', $wilayah->kelurahan)->count(),
                'anak' => $anakDariKunjungan,
            ]);
        }
    }

    public function getPosyanduWilayah($id)
    {
        $posyandus = Posyandu::where('id_wilayah', $id)
            ->select('nama_posyandu', 'rw', 'rt')
            ->get();

        if ($posyandus->isEmpty()) {
            return response()->json(['message' => 'Posyandu tidak ditemukan'], 404);
        }

        // Grouping berdasarkan nama_posyandu
        $grouped = $posyandus->groupBy('nama_posyandu')->map(function ($items, $nama) {
            return [
                'nama_posyandu' => $nama,
                'rw' => $items->pluck('rw')->unique()->filter()->values(),
                'rt' => $items->pluck('rt')->unique()->filter()->values(),
            ];
        })->values();

        return response()->json($grouped);
    }

}
