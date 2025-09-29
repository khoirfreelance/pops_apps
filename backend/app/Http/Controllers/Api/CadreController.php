<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Cadre;
use App\Models\TPK;
use App\Models\Posyandu;
use App\Models\User;
use App\Models\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class CadreController extends Controller
{
    public function index()
    {
        $cadres = Cadre::with(['tpk', 'user'])->get();

        $data = $cadres->map(function ($cadre) {
            return [
                'no_tpk'        => $cadre->tpk->no_tpk ?? null,
                'nama'          => $cadre->user->name ?? null,
                'nik'           => $cadre->user->nik ?? null,
                'status'        => $cadre->status ?? null,
                'phone'         => $cadre->user->phone ?? null,
                'email'         => $cadre->user->email ?? null,
                'role'          => $cadre->user->role ?? null,
                'unit_posyandu' => $cadre->posyandu->nama_posyandu ?? null, // kalau Cadre punya relasi posyandu
                // kamu bisa tambahkan field action sendiri, misal tombol edit/delete
                'action'        => null,
            ];
        });

        \App\Models\Log::create([
            'id_user'  => \Auth::id(),
            'context'  => 'Pengguna',
            'activity' => 'view',
            'timestamp'=> now(),
        ]);

        return response()->json($data);
    }

    public function store(Request $request)
    {
        $isPendingUser = empty($request->nik) ? 1 : 0;
        $isTPK = empty($request->no_tpk);

        // simpan wilayah
        $wilayah = \App\Models\Wilayah::firstOrCreate([
            'provinsi' => $request->provinsi,
            'kota' => $request->kota,
            'kecamatan' => $request->kecamatan,
            'kelurahan' => $request->kelurahan,
        ]);

        if (!$isTPK) {
            // simpan TPK
            $tpk = \App\Models\TPK::firstOrCreate([
                'no_tpk' => $request->no_tpk ?: null,
                'id_wilayah' => $wilayah->id,
            ]);
        }

        // simpan Posyandu
        $posyandu = \App\Models\Posyandu::firstOrCreate([
            'nama_posyandu' => $request->unit_posyandu,
            'id_wilayah'  => $wilayah->id,
        ]);

        // simpan user
        $user = User::create([
            'nik' => $request->nik?: null,
            'name' => $request->nama,
            'email' => $request->email,
            'email_verified_at' => NOW(),
            'phone' => $request->phone,
            'role' => $request->role,
            'status' => $request->status,
            'is_pending' => $isPendingUser,
            'password' => Hash::make($request->password),
        ]);

        // simpan cadre
        $cadre = Cadre::create([
            'id_tpk' => $request->no_tpk ?: null,
            'id_user' => $user->id,
            'id_posyandu' => $posyandu->id,
            'status' => 'aktif'
        ]);

        Log::create([
            'id_user'  => Auth::id(),
            'context'  => 'Pengguna',
            'activity' => 'store',
            'timestamp'=> now(),
        ]);

        return response()->json([
            'message' => 'Cadre berhasil ditambahkan',
            'data' => $cadre->load('user', 'tpk')
        ]);
    }
}
