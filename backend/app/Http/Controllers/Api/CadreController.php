<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Cadre;
use App\Models\TPK;
use App\Models\Posyandu;
use App\Models\User;
use App\Models\Log;
use App\Models\Wilayah;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class CadreController extends Controller
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
                'nik'           => $cadre->user->nik ?? null,
                'status'        => $cadre->user->status === 1 ? 'Aktif':'Non aktif',
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
            'context'  => 'Kader / Pengguna',
            'activity' => 'view',
            'timestamp'=> now(),
        ]);

        return response()->json($data);
    }

    public function store(Request $request)
    {
        $isPendingUser = empty($request->nik) ? 1 : 0;
        $isTPK = empty($request->no_tpk);

        if (!$isTPK) {
            // simpan TPK
            $tpk = \App\Models\TPK::firstOrCreate([
                'no_tpk' => $request->no_tpk ?: null,
                'id_wilayah' => $wilayah->id,
            ]);
        }

        // simpan wilayah
        $wilayah = \App\Models\Wilayah::firstOrCreate([
            'provinsi' => $request->provinsi,
            'kota' => $request->kota,
            'kecamatan' => $request->kecamatan,
            'kelurahan' => $request->kelurahan,
        ]);

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
            'status' => $request->no_tpk ? 'kader':'non-kader',
        ]);

        Log::create([
            'id_user'  => Auth::id(),
            'context'  => 'Kader/Pengguna',
            'activity' => 'store',
            'timestamp'=> now(),
        ]);

        return response()->json([
            'message' => 'Cadre berhasil ditambahkan',
            'data' => $cadre->load('user', 'tpk')
        ]);
    }

    public function pendingData()
    {
        $cadres = Cadre::with(['tpk', 'user', 'posyandu'])
        ->wherehas('user', function ($q) {
                $q->where('is_pending', 1);
            })
        ->get();

        $data = $cadres->map(function ($cadre) {
            return [
                'id'            => $cadre->id,
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

    public function show($id)
    {
        $cadre = Cadre::with(['tpk', 'user', 'posyandu.wilayah'])
            ->where('id', $id)
            ->firstOrFail();

        $data = [
            'id'            => $cadre->id,
            'no_tpk'        => $cadre->tpk->no_tpk ?? null,
            'nama'          => $cadre->user->name ?? null,
            'nik'           => $cadre->user->nik ?? null,
            'status'        => $cadre->status ?? null,
            'phone'         => $cadre->user->phone ?? null,
            'email'         => $cadre->user->email ?? null,
            'role'          => $cadre->user->role ?? null,
            'unit_posyandu' => $cadre->posyandu->nama_posyandu ?? null,

            // ambil dari relasi wilayah
            'provinsi'      => $cadre->posyandu->wilayah->provinsi ?? null,
            'kota'          => $cadre->posyandu->wilayah->kota ?? null,
            'kecamatan'     => $cadre->posyandu->wilayah->kecamatan ?? null,
            'kelurahan'     => $cadre->posyandu->wilayah->kelurahan ?? null,
        ];

        return response()->json($data);
    }

    public function updateProfile(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'name'  => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
        ]);

        $user->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Profile berhasil diperbarui',
            'data' => [
                'nik'   => $user->nik,
                'name'  => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'role'  => $user->role,
            ]
        ]);
    }

    public function deactive($email)
    {
        User::where('email', $email)->update(['status' => 0]);

        return response()->json([
            'message' => 'Kader/Pengguna berhasil dinonaktifkan'
        ]);
    }

    public function active($email)
    {
        User::where('email', $email)->update(['status' => 1]);

        return response()->json([
            'message' => 'Kader/Pengguna berhasil diaktifkan'
        ]);
    }

    public function delete($email)
    {
        $user = User::where('email', $email)->firstOrFail();

        $user->update([
            'status' => 2, // 2 = deleted/non aktif
            'name' => 'Anonim ' . $user->id,
            'email' => 'deleted_' . $user->email,
            'phone' => null,
            'deleted_at' => now(),
        ]);

        return response()->json([
            'message' => 'Kader/Pengguna berhasil dihapus'
        ]);
    }

    public function wilayahByUser()
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthorized'
            ], 401);
        }

        // join users -> wilayah
        $wilayah = Wilayah::query()
            ->join('users', 'users.id_wilayah', '=', 'wilayah.id')
            ->where('users.id', $user->id)
            ->select(
                'wilayah.id',
                'wilayah.provinsi',
                'wilayah.kota',
                'wilayah.kecamatan',
                'wilayah.kelurahan'
            )
            ->first();

        if (!$wilayah) {
            return response()->json([
                'status' => false,
                'message' => 'Wilayah tidak ditemukan'
            ], 404);
        }

        return response()->json([
            'status'     => true,
            'id_wilayah' => $wilayah->id,
            'provinsi'   => $wilayah->provinsi,
            'kota'       => $wilayah->kota,
            'kecamatan' => $wilayah->kecamatan,
            'kelurahan' => $wilayah->kelurahan,
        ]);
    }

    public function me(Request $request)
    {
        $user = $request->user();

        return response()->json([
            'success' => true,
            'data' => [
                'nik'               => $user->nik,
                'name'              => $user->name,
                'email'             => $user->email,
                'email_verified_at' => $user->email_verified_at,
                'phone'             => $user->phone,
                'role'              => $user->role,
                'status'            => $user->status,
                'is_pending'        => $user->is_pending,
                'deleted_at'        => $user->deleted_at,
            ]
        ]);
    }

    public function changePassword(Request $request)
    {
        $user = $request->user();

        $request->validate([
            'current_password' => 'required',
            'password'         => 'required|min:8|confirmed',
        ]);

        // cek password lama
        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Password lama tidak sesuai'
            ], 422);
        }

        $user->update([
            'password' => Hash::make($request->password),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Password berhasil diubah'
        ]);
    }
}
