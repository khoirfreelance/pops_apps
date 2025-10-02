<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Cadre;
use App\Models\TPK;
use App\Models\User;
use App\Models\Log;
use Illuminate\Support\Facades\Auth;

class MemberController extends Controller
{
    public function index()
    {
        $cadres = Cadre::with(['tpk', 'user'])
        ->wherehas('user', function ($q) {
                $q->where('is_pending', 0);
            })
        ->get();

        $data = $cadres->map(function ($cadre) {
            return [
                'id'            => $cadre->id,
                'no_tpk'        => $cadre->tpk->no_tpk ?? null,
                'nama'          => $cadre->user->name ?? null,
                'action'        => null,
            ];
        });

        \App\Models\Log::create([
            'id_user'  => \Auth::id(),
            'context'  => 'Anggota TPK',
            'activity' => 'view',
            'timestamp'=> now(),
        ]);

        return response()->json($data);
    }

    public function store(Request $request)
    {
        // simpan wilayah
        $wilayah = \App\Models\Wilayah::firstOrCreate([
            'provinsi' => $request->provinsi,
            'kota' => $request->kota,
            'kecamatan' => $request->kecamatan,
            'kelurahan' => $request->kelurahan,
        ]);

        // simpan TPK
        $tpk = \App\Models\TPK::firstOrCreate([
            'no_tpk' => $request->no_tpk ?: null,
            'id_wilayah' => $wilayah->id,
        ]);

        Log::create([
            'id_user'  => Auth::id(),
            'context'  => 'Anggota TPK',
            'activity' => 'store',
            'timestamp'=> now(),
        ]);

        return response()->json([
            'message' => 'Cadre berhasil ditambahkan'
        ]);
    }

    public function getTPK()
    {
        $tpk = TPK::select('no_tpk')
            ->distinct()
            ->orderBy('no_tpk')
            ->get();

        return response()->json($tpk);
    }

    public function getUser()
    {
        $user = User::select('nik','name')
            ->where('is_pending',0)
            ->distinct()
            ->orderBy('nik')
            ->get();

        return response()->json($user);
    }
}
