<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Keluarga;
use App\Models\Posyandu;
use App\Models\TPK;
use App\Models\User;
use App\Models\Catin;
use App\Models\RT;
use App\Models\RW;
use App\Models\StatKeluarga;
use App\Models\Pregnancy;
use App\Models\Child;
use App\Models\Kunjungan;
use App\Models\Wilayah;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{

    public function stats()
    {
        $user = Auth::user();
        $isSuperAdmin = $user->role === 'Super Admin';
        $wilayah = Wilayah::find($user->id_wilayah);

        // ===== RT, RW, Keluarga dari tabel rekap =====
        if ($isSuperAdmin) {
            $ids = $this->canonicalWilayahIds();

            $totalRt       = RT::whereIn('id_wilayah', $ids)->sum('count_rt');
            $totalRw       = RW::whereIn('id_wilayah', $ids)->sum('count_rw');
            $totalKeluarga = StatKeluarga::whereIn('id_wilayah', $ids)->sum('count_keluarga');
        } else {
            $wilayahId = $this->resolveWilayahId($wilayah->id);

            $totalRt       = RT::where('id_wilayah', $wilayahId)->sum('count_rt');
            $totalRw       = RW::where('id_wilayah', $wilayahId)->sum('count_rw');
            $totalKeluarga = StatKeluarga::where('id_wilayah', $wilayahId)->sum('count_keluarga');
        }

        // ===== Anak <= 5 tahun =====
        if ($isSuperAdmin) {
            $anakDariKunjungan = Kunjungan::whereRaw('TIMESTAMPDIFF(MONTH, tgl_lahir, CURDATE()) <= 60')
                ->distinct('nik')
                ->count('nik');
        } else {
            $anakDariKunjungan = Kunjungan::whereRaw('TIMESTAMPDIFF(MONTH, tgl_lahir, CURDATE()) <= 60')
                ->where('kelurahan', $wilayah->kelurahan)
                ->distinct('nik')
                ->count('nik');
        }

        if ($isSuperAdmin) {
            return response()->json([
                'rw'        => (int) $totalRw,
                'rt'        => (int) $totalRt,
                'keluarga'  => (int) $totalKeluarga,
                'tpk'       => TPK::count(),
                'ibu_hamil' => Pregnancy::count(),
                'posyandu' => Kunjungan::distinct()->count('posyandu'),
                'bidan'     => User::where('role', 'Bidan')->count(),
                'catin'     => Catin::count(),
                'anak'      => $anakDariKunjungan,
            ]);
        }

        $kelurahan = Wilayah::where('id', $wilayah->id)->value('kelurahan');

        $posyandu = Kunjungan::where('kelurahan', strtoupper($kelurahan))
            ->whereDate('tgl_pengukuran', '>=', now()->subYear())
            ->distinct()
            ->orderBy('posyandu')
            ->pluck('posyandu');

        return response()->json([
            'rw'        => (int) $totalRw,
            'rt'        => (int) $totalRt,
            'keluarga'  => (int) $totalKeluarga,
            'tpk'       => TPK::where('id_wilayah', $wilayah->id)->count(),
            'ibu_hamil' => Pregnancy::where('kelurahan', $wilayah->kelurahan)->count(),
            'posyandu'  => $posyandu->count(),
            'bidan'     => User::where('role', 'Bidan')->count(),
            'catin'     => Catin::where('kelurahan', $wilayah->kelurahan)->count(),
            'anak'      => $anakDariKunjungan,
        ]);
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

    // ================= RT =================

    public function getRT($id_wilayah = null)
    {
        $query = RT::with('wilayah');

        if ($id_wilayah) {
            $query->where('id_wilayah', $this->resolveWilayahId($id_wilayah));
        } else {
            $query->whereIn('id_wilayah', $this->canonicalWilayahIds());
        }

        return response()->json($query->get());
    }

    public function updateRT(Request $request)
    {
        $request->validate([
            'id_wilayah'  => 'required|exists:wilayah,id',
            'count_rt'    => 'required|integer|min:0',
        ]);

        $rt = RT::updateOrCreate(
            [
                'id_wilayah' => $request->id_wilayah,
                'id_petugas' => Auth::id(),
            ],
            [
                'count_rt' => $request->count_rt,
            ]
        );

        return response()->json([
            'message' => 'Data RT berhasil diupdate',
            'data' => $rt,
        ]);
    }

    // ================= RW =================

    public function getRW($id_wilayah = null)
    {
        $query = RW::with('wilayah');

        if ($id_wilayah) {
            $query->where('id_wilayah', $this->resolveWilayahId($id_wilayah));
        } else {
            $query->whereIn('id_wilayah', $this->canonicalWilayahIds());
        }

        return response()->json($query->get());
    }

    public function updateRW(Request $request)
    {
        $request->validate([
            'id_wilayah'  => 'required|exists:wilayah,id',
            'count_rw'    => 'required|integer|min:0',
        ]);

        $rw = RW::updateOrCreate(
            [
                'id_wilayah' => $request->id_wilayah,
                'id_petugas' => Auth::id(),
            ],
            [
                'count_rw' => $request->count_rw,
            ]
        );

        return response()->json([
            'message' => 'Data RW berhasil diupdate',
            'data' => $rw,
        ]);
    }

    /**
     * Id wilayah "kanonik": kelurahan tidak null/kosong,
     * dan jika redundan ambil id paling kecil (paling atas).
     */
    private function canonicalWilayahIds()
    {
        return Wilayah::query()
            ->whereNotNull('kelurahan')
            ->where('kelurahan', '!=', '')
            ->selectRaw('MIN(id) as id')
            ->groupBy('provinsi', 'kota', 'kecamatan', 'kelurahan')
            ->pluck('id');
    }

    /**
     * Ubah id wilayah apapun (termasuk duplikat) jadi id kanonik-nya.
     * Return null kalau wilayah tidak ada / kelurahan kosong.
     */
    private function resolveWilayahId($id)
    {
        $w = Wilayah::find($id);

        if (!$w || blank($w->kelurahan)) {
            return null;
        }

        return Wilayah::where('provinsi', $w->provinsi)
            ->where('kota', $w->kota)
            ->where('kecamatan', $w->kecamatan)
            ->where('kelurahan', $w->kelurahan)
            ->min('id');
    }
    // ================= Stat Keluarga =================

    public function getStatKeluarga($id_wilayah = null)
    {
        $query = StatKeluarga::with('wilayah');

        if ($id_wilayah) {
            $query->where('id_wilayah', $this->resolveWilayahId($id_wilayah));
        } else {
            $query->whereIn('id_wilayah', $this->canonicalWilayahIds());
        }

        return response()->json($query->get());
    }

    public function updateStatKeluarga(Request $request)
    {
        $request->validate([
            'id_wilayah'     => 'required|exists:wilayah,id',
            'count_keluarga' => 'required|integer|min:0',
        ]);

        $stat = StatKeluarga::updateOrCreate(
            [
                'id_wilayah' => $request->id_wilayah,
                'id_petugas' => Auth::id(),
            ],
            [
                'count_keluarga' => $request->count_keluarga,
            ]
        );

        return response()->json([
            'message' => 'Data Stat Keluarga berhasil diupdate',
            'data' => $stat,
        ]);
    }

}
