<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use App\Models\Keluarga;
use App\Models\AnggotaKeluarga;
use Illuminate\Http\Request;
use Carbon\Carbon;

class FamilyController extends Controller
{
    public function index()
    {
        $keluargas = Keluarga::with('kepala')
                    ->where('is_pending', 0)
                    ->get();

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
                'tgl_lahir'       => $k->kepala && $k->kepala->tanggal_lahir
                                    ? $k->kepala->tempat_lahir.', '. Carbon::parse($k->kepala->tanggal_lahir)->format('d-m-Y')
                                    : null,
                'pendidikan'      => $k->kepala ? $k->kepala->pendidikan : null,
                'status_hubungan' => $k->kepala ? $k->kepala->status_hubungan : null,
                'is_pending' => $k->kepala ? $k->kepala->is_pending : null,

                // jumlah anggota keluarga
                'jml_anggota'     => $k->anggota ? $k->anggota
                                                    ->where('is_pending', 0)
                                                    ->count() : 0,
            ];
        });

        return response()->json($data->values());
    }

    public function store(Request $request)
    {
        // cek apakah no_kk sudah ada
        $keluarga = Keluarga::where('no_kk', $request->no_kk)->first();

        // cek is_pending untuk keluarga
        $isPendingKeluarga = empty($request->no_kk) ? 1 : 0;

        // cek is_pending untuk anggota
        $isPendingAnggota = empty($request->nik) ? 1 : 0;
        $status = $isPendingKeluarga || $isPendingAnggota == 0 ? 'Pending':'Saved';

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
                'is_pending' => $isPendingKeluarga,
            ]);
        }

        // tambah anggota keluarga (bisa kepala/istri/anak dsb.)
        $keluarga->anggota()->create([
            'nik' => $request->nik,
            'no_kk' => $request->no_kk,
            'nama' => $request->nama,
            'tempat_lahir' => $request->tempat_lahir,
            'tanggal_lahir' => $request->tgl_lahir,
            'jenis_kelamin' => $request->gender,
            'pendidikan' => $request->pendidikan,
            'pekerjaan' => $request->pekerjaan,
            'status_hubungan' => $request->status_hubungan,
            'agama' => $request->agama,
            'status_perkawinan' => $request->status_perkawinan,
            'kewarganegaraan' => $request->kewarganegaraan,
            'is_pending' => $isPendingAnggota,
        ]);

        return response()->json(['message' => 'Data berhasil disimpan', 'status' => $status]);
    }

    public function checkNoKK(Request $request)
    {
        $keluarga = Keluarga::with(['kepala', 'anggota', 'wilayah'])
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
                    // expand wilayah
                    'provinsi'  => $keluarga->wilayah?->provinsi,
                    'kota'      => $keluarga->wilayah?->kota,
                    'kecamatan' => $keluarga->wilayah?->kecamatan,
                    'kelurahan' => $keluarga->wilayah?->kelurahan,
                ],
            ]);
        }

        return response()->json(['exists' => false]);
    }

    public function detail($no_kk)
    {
        $keluarga = Keluarga::with(['wilayah', 'anggota'])
                    ->where('no_kk', $no_kk)
                    ->where('is_pending', '0')
                    ->firstOrFail();

        return response()->json([
            'id'       => $keluarga->id,
            'no_kk'    => $keluarga->no_kk,
            'alamat'   => $keluarga->alamat,
            'rt'       => $keluarga->rt,
            'rw'       => $keluarga->rw,

            // wilayah
            'wilayah' => [
                'provinsi'  => $keluarga->wilayah?->provinsi,
                'kota'      => $keluarga->wilayah?->kota,
                'kecamatan' => $keluarga->wilayah?->kecamatan,
                'kelurahan' => $keluarga->wilayah?->kelurahan,
            ],

            // anggota keluarga (kepala + lainnya)
            'anggota' => $keluarga->anggota->map(function ($a) {
                return [
                    'id'               => $a->id,
                    'nik'              => $a->nik,
                    'nama'             => $a->nama,
                    'tempat_lahir'     => $a->tempat_lahir,
                    'tanggal_lahir'    => $a->tanggal_lahir,
                    'jenis_kelamin'    => $a->jenis_kelamin,
                    'pendidikan'       => $a->pendidikan,
                    'pekerjaan'        => $a->pekerjaan,
                    'status_hubungan'  => $a->status_hubungan,
                    'agama'            => $a->agama,
                    'status_perkawinan'=> $a->status_perkawinan,
                    'kewarganegaraan'  => $a->kewarganegaraan,
                ];
            }),
        ]);
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:csv,txt|max:2048'
        ]);

        $file = $request->file('file');
        $path = $file->getRealPath();
        $data = array_map('str_getcsv', file($path));

        // anggap baris pertama header CSV
        $header = array_map('trim', $data[0]);
        unset($data[0]);

        DB::beginTransaction();
        try {
            foreach ($data as $row) {
                $row = array_combine($header, $row);

                // cek pending keluarga
                $isPendingKeluarga = empty($row['no_kk']) ? 1 : 0;

                // simpan ke tabel keluarga
                $keluarga = Keluarga::firstOrCreate(
                    ['no_kk' => $row['no_kk']],
                    [
                        'alamat' => $row['alamat'] ?? null,
                        'rt'     => $row['rt'] ?? null,
                        'rw'     => $row['rw'] ?? null,
                        'id_wilayah' => $row['id_wilayah'] ?? null,
                        'is_pending' => $isPendingKeluarga,
                    ]
                );

                // cek pending anggota
                $isPendingAnggota = empty($row['nik']) ? 1 : 0;

                // simpan ke tabel anggota_keluarga
                AnggotaKeluarga::updateOrCreate(
                    ['nik' => $row['nik']],
                    [
                        'id_keluarga' => $keluarga->id,
                        'nama'        => $row['nama'] ?? null,
                        'tanggal_lahir' => $row['tanggal_lahir'] ?? null,
                        'pendidikan'  => $row['pendidikan'] ?? null,
                        'status_hubungan' => $row['status_hubungan'] ?? null,
                        'agama' => $row['agama'] ?? null,
                        'pekerjaan' => $row['pekerjaan'] ?? null,
                        'status_perkawinan' => $row['status_perkawinan'] ?? null,
                        'kewarganegaraan' => $row['kewarganegaraan'] ?? null,
                        'jenis_kelamin' => $row['jenis_kelamin'] ?? null,
                        'is_pending' => $isPendingAnggota,
                    ]
                );

                $status = $isPendingKeluarga || $isPendingAnggota == 0 ? 'Pending':'Saved';
            }

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Data berhasil diimport', 'status'=> $status]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function pendingData()
    {
        $keluargas = Keluarga::with('kepala')
                    ->where('is_pending', 1)
                    ->get();

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
                'tgl_lahir'       => $k->kepala && $k->kepala->tanggal_lahir
                                    ? $k->kepala->tempat_lahir.', '. Carbon::parse($k->kepala->tanggal_lahir)->format('d-m-Y')
                                    : null,
                'pendidikan'      => $k->kepala ? $k->kepala->pendidikan : null,
                'status_hubungan' => $k->kepala ? $k->kepala->status_hubungan : null,
                'is_pending' => $k->kepala ? $k->kepala->is_pending : null,

                // jumlah anggota keluarga
                'jml_anggota'     => $k->anggota ? $k->anggota
                                                    ->where('is_pending', 1)
                                                    ->count() : 0,
            ];
        });

        return response()->json($data->values());
    }

    public function pending($id)
    {
        $keluarga = Keluarga::with(['wilayah', 'anggota'])
                    ->where('id', $id)
                    ->where('is_pending', '1')
                    ->firstOrFail();

        return response()->json([
            'id'       => $keluarga->id,
            'no_kk'    => $keluarga->no_kk,
            'alamat'   => $keluarga->alamat,
            'rt'       => $keluarga->rt,
            'rw'       => $keluarga->rw,

            // wilayah
            'provinsi'  => $keluarga->wilayah?->provinsi,
            'kota'      => $keluarga->wilayah?->kota,
            'kecamatan' => $keluarga->wilayah?->kecamatan,
            'kelurahan' => $keluarga->wilayah?->kelurahan,

            // anggota keluarga (kepala + lainnya)
            'anggota' => $keluarga->anggota->map(function ($a) {
                return [
                    'id'               => $a->id,
                    'nik'              => $a->nik,
                    'nama'             => $a->nama,
                    'tempat_lahir'     => $a->tempat_lahir,
                    'tanggal_lahir'    => $a->tanggal_lahir,
                    'jenis_kelamin'    => $a->jenis_kelamin,
                    'pendidikan'       => $a->pendidikan,
                    'pekerjaan'        => $a->pekerjaan,
                    'status_hubungan'  => $a->status_hubungan,
                    'agama'            => $a->agama,
                    'status_perkawinan'=> $a->status_perkawinan,
                    'kewarganegaraan'  => $a->kewarganegaraan,
                ];
            }),
        ]);
    }

    public function update(Request $request, $id)
    {
        // cek apakah no_kk sudah ada
        $keluarga = Keluarga::findOrFail($id);

        // cek is_pending untuk keluarga
        $isPendingKeluarga = empty($request->no_kk) ? 1 : 0;

        // cek is_pending untuk anggota
        $isPendingAnggota = empty($request->nik) ? 1 : 0;
        $status = $isPendingKeluarga || $isPendingAnggota == 0 ? 'Pending':'Saved';

        // simpan wilayah
        $wilayah = \App\Models\Wilayah::firstOrCreate([
            'provinsi' => $request->provinsi,
            'kota' => $request->kota,
            'kecamatan' => $request->kecamatan,
            'kelurahan' => $request->kelurahan,
        ]);

        // buat keluarga baru
        $keluarga->update([
            'no_kk' => $request->no_kk,
            'alamat' => $request->alamat,
            'rt' => $request->rt,
            'rw' => $request->rw,
            'id_wilayah' => $wilayah->id,
            'is_pending' => $isPendingKeluarga,
        ]);

        // tambah anggota keluarga (bisa kepala/istri/anak dsb.)
        $keluarga->anggota()->update([
            'nik' => $request->nik,
            'nama' => $request->nama,
            'tempat_lahir' => $request->tempat_lahir,
            'tanggal_lahir' => $request->tgl_lahir,
            'jenis_kelamin' => $request->gender,
            'pendidikan' => $request->pendidikan,
            'pekerjaan' => $request->pekerjaan,
            'status_hubungan' => $request->status_hubungan,
            'agama' => $request->agama,
            'status_perkawinan' => $request->status_perkawinan,
            'kewarganegaraan' => $request->kewarganegaraan,
            'is_pending' => $isPendingAnggota,
        ]);

        return response()->json(['message' => 'Data berhasil diubah', 'status' => $status]);
    }
}

