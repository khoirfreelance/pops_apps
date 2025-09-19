<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Config; // model untuk tb_config
use Illuminate\Support\Facades\Auth;

class ConfigController extends Controller
{
    public function index()
    {
        $config = Config::where('name', 'site_config')
                        ->first();

        if (!$config) {
            return response()->json(null);
        }

        // decode string JSON jadi array
        $value = json_decode($config->value, true);

        return response()->json($value); // langsung kirim object logo/background
    }

    public function store(Request $request)
    {
        // Upload file ke storage
        $logoPath = null;
        if ($request->hasFile('logo') && $request->file('logo')->isValid()) {
            $logoPath = $request->file('logo')->store('logos', 'public');
        }

        $bgPath = null;
        if ($request->hasFile('background') && $request->file('background')->isValid()) {
            $bgPath = $request->file('background')->store('backgrounds', 'public');
        }

        // Bentuk data JSON yang akan disimpan ke kolom `value`
        $data = [
            'logo'         => $logoPath ? asset('storage/' . $logoPath) : null,
            'background'   => $bgPath ? asset('storage/' . $bgPath) : null,
            'logoWidth'    => $request->input('logoWidth'),
            'colorTheme'   => $request->input('colorTheme'),
            'footerColumn' => $request->input('footerColumn'),
            'maintenance'  => $request->input('maintenance'),
        ];

        // Simpan/update di tb_config, pakai JSON di kolom value
        Config::updateOrCreate(
            [
                'name'    => 'site_config',
                'id_user' => Auth::id(), // supaya spesifik per user
            ],
            [
                'value'       => json_encode($data),
                'modified_at' => now(),
            ]
        );

        return response()->json([
            'message' => 'Konfigurasi tersimpan',
            'data'    => $data
        ]);
    }
}
