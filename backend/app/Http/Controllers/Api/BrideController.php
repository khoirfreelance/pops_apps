<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Bride;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Log;
use Illuminate\Support\Facades\Auth;

class BrideController extends Controller
{
    /**
     * Tampilkan semua calon pengantin.
     * GET /api/bride
     */
    public function index()
    {
        $data = Bride::all();
        return response()->json($data);
    }

    /**
     * Simpan data calon pengantin baru.
     * POST /api/bride
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'bride.nik' => 'required|string',
            'bride.nama' => 'required|string',
            'groom.nik' => 'nullable|string',
            'groom.nama' => 'nullable|string',
        ]);

        $bride = Bride::create($data['bride']);

        if (!empty($data['groom']['nik'])) {
            $groom = BridePartner::create(array_merge(
                $data['groom'],
                ['bride_id' => $bride->id]
            ));
        }

        return response()->json(['success' => true, 'bride' => $bride]);
    }


    /**
     * Tampilkan detail satu calon pengantin.
     * GET /api/bride/{id}
     */
    public function show($id)
    {
        $bride = Bride::with('pasangan')->find($id);

        if (!$bride) {
            return response()->json([
                'message' => 'Data calon pengantin tidak ditemukan',
            ], 404);
        }

        return response()->json($bride, 200);
    }
}
