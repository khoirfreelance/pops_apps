<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Keluarga;
use App\Models\AnggotaKeluarga;
use Illuminate\Http\Request;

class FamilyController extends Controller
{
    public function index()
    {
        $keluargas = Keluarga::with('kepala')->get();

        $data = $keluargas->map(function ($k) {
            return [
                'id'      => $k->id,
                'no_kk'   => $k->no_kk,
                'alamat'  => $k->alamat,
                'rt'      => $k->rt,
                'rw'      => $k->rw,

                // data kepala keluarga langsung diurai
                'nama_kepala'     => $k->kepala ? $k->kepala->nama : null,
                'nik_kepala'      => $k->kepala ? $k->kepala->nik : null,
                'tgl_lahir'       => $k->kepala ? $k->kepala->tanggal_lahir : null,
                'pendidikan'      => $k->kepala ? $k->kepala->pendidikan : null,
                'status_hubungan' => $k->kepala ? $k->kepala->status_hubungan : null,
                /* 'kepala'  => $k->kepala ? [
                    'id'            => $k->kepala->id,
                    'nama'          => $k->kepala->nama,
                    'nik'           => $k->kepala->nik,
                    'tgl_lahir'     => $k->kepala->tanggal_lahir,
                    'pendidikan'    => $k->kepala->pendidikan,
                    'status_hubungan' => $k->kepala->status_hubungan,
                ] : null, */
            ];
        });

        return response()->json($data->values());
    }

    public function store(Request $request)
    {
        // cek apakah no_kk sudah ada
        $keluarga = Keluarga::where('no_kk', $request->no_kk)->first();

        if (!$keluarga) {
            // simpan wilayah
            $wilayah = \App\Models\Wilayah::firstOrCreate([
                'provinsi' => $request->provinsi,
                'kota' => $request->kota,
                'kecamatan' => $request->kecamatan,
                'kelurahan' => $request->kelurahan,
            ]);

            // buat keluarga baru
            $keluarga = Keluarga::create([
                'no_kk' => $request->no_kk,
                'alamat' => $request->alamat,
                'rt' => $request->rt,
                'rw' => $request->rw,
                'id_wilayah' => $wilayah->id,
            ]);
        }

        // tambah anggota keluarga (bisa kepala/istri/anak dsb.)
        $keluarga->anggota()->create([
            'nik' => $request->nik,
            'no_kk' => $request->no_kk,
            'nama' => $request->nama,
            'tmpt_lahir' => $request->tmpt_lahir,
            'tanggal_lahir' => $request->tgl_lahir,
            'jenis_kelamin' => $request->gender,
            'pendidikan' => $request->pendidikan,
            'pekerjaan' => $request->pekerjaan,
            'status_hubungan' => $request->status_hubungan,
            'agama' => $request->agama,
            'status_perkawinan' => $request->status_perkawinan,
            'kewarganegaraan' => $request->kewarganegaraan,
        ]);

        return response()->json(['message' => 'Data berhasil disimpan']);
    }


    public function checkNoKK(Request $request)
    {
        $keluarga = Keluarga::with(['kepala', 'anggota'])
            ->where('no_kk', $request->no_kk)
            ->first();

        if ($keluarga) {
            return response()->json([
                'exists' => true,
                'keluarga' => [
                    'id' => $keluarga->id,
                    'no_kk' => $keluarga->no_kk,
                    'alamat' => $keluarga->alamat,
                    'rt' => $keluarga->rt,
                    'rw' => $keluarga->rw,
                    'id_wilayah' => $keluarga->id_wilayah,
                    'kepala' => $keluarga->kepala?->nama,
                ],
            ]);
        }

        return response()->json(['exists' => false]);
    }

}

