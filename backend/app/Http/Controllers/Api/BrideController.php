<?php

namespace App\Http\Controllers;

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
        // Ambil semua data catin beserta pasangan (lazy eager load)
        $brides = Bride::with('pasangan')->get();

        return response()->json($brides, 200);
    }

    /**
     * Simpan data calon pengantin baru.
     * POST /api/bride
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'peran' => 'required|in:suami,istri',
            'id_pasangan' => 'nullable|exists:catin,id',
            'tgl_daftar' => 'required|date',
            'rencana_tgl_nikah' => 'nullable|date',
            'tempat_nikah' => 'nullable|string|max:255',
            'status_pernikahan' => 'nullable|string|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validasi gagal',
                'errors' => $validator->errors(),
            ], 422);
        }

        $bride = Bride::create($validator->validated());

        // Jika pasangan sudah ada, perbarui hubungan dua arah
        if ($bride->id_pasangan) {
            $pasangan = Bride::find($bride->id_pasangan);
            if ($pasangan && !$pasangan->id_pasangan) {
                $pasangan->id_pasangan = $bride->id;
                $pasangan->save();
            }
        }

        return response()->json([
            'message' => 'Data calon pengantin berhasil disimpan',
            'data' => $bride->load('pasangan'),
        ], 201);
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
