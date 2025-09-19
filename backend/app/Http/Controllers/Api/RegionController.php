<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Wilayah;
use Illuminate\Http\Request;

class RegionController extends Controller
{
    public function getProvinsi()
    {
        $provinsi = Wilayah::select('provinsi as nama')
            ->distinct()
            ->orderBy('nama')
            ->get();

        return response()->json($provinsi);
    }

    public function getKota(Request $request)
    {
        $kota = Wilayah::select('kota as nama')
            ->where('provinsi', $request->provinsi)
            ->distinct()
            ->orderBy('nama')
            ->get();

        return response()->json($kota);
    }

    public function getKecamatan(Request $request)
    {
        $kecamatan = Wilayah::select('kecamatan as nama')
            ->where('provinsi', $request->provinsi)
            ->where('kota', $request->kota)
            ->distinct()
            ->orderBy('nama')
            ->get();

        return response()->json($kecamatan);
    }

    public function getKelurahan(Request $request)
    {
        $kelurahan = Wilayah::select('kelurahan as nama')
            ->where('provinsi', $request->provinsi)
            ->where('kota', $request->kota)
            ->where('kecamatan', $request->kecamatan)
            ->distinct()
            ->orderBy('kelurahan')
            ->get();

        return response()->json($kelurahan);
    }
}
