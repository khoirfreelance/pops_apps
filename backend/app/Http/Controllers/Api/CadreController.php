<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Cadre;
use App\Models\TPK;
use App\Models\Posyandu;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class CadreController extends Controller
{
    public function index()
    {
        $cadres = Cadre::with(['tpk', 'user'])->get();
        return response()->json($cadres);
    }

    public function store(Request $request)
    {
        $request->validate([
            'no_tpk' => 'required|numeric',
            'nik' => 'required|unique:users,nik',
            'nama' => 'required|string',
            'email' => 'required|email|unique:users,email',
            'phone' => 'nullable|string',
            'role' => 'required|string',
            'unit_posyandu' => 'required|numeric',
            //'password' => 'required|min:6|confirmed', // confirm_password
        ]);

        // simpan user
        $user = User::create([
            'nik' => $request->nik,
            'name' => $request->nama,
            'email' => $request->email,
            'phone' => $request->phone,
            'role' => $request->role,
            'status' => $request->status,
            'id_posyandu' => $request->unit_posyandu,
            'password' => Hash::make($request->password),
        ]);

        // simpan cadre
        $cadre = Cadre::create([
            'id_tpk' => $request->no_tpk,
            'id_user' => $user->id,
            'jabatan' => $request->role,
            'status' => 'aktif'
        ]);

        return response()->json([
            'message' => 'Cadre berhasil ditambahkan',
            'data' => $cadre->load('user', 'tpk')
        ]);
    }
}
