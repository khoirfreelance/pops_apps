<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\FooterSetting;
use Illuminate\Support\Facades\Storage;

class FooterSettingController extends Controller
{
    /* =========================
       PUBLIC
    ========================== */
    public function show()
    {
        $footer = FooterSetting::first();

        return response()->json([
            'data' => $footer
        ]);
    }

    /* =========================
       ADMIN SAVE
    ========================== */
    public function store(Request $request)
    {
        $footer = FooterSetting::first();

        if (!$footer) {
            $footer = new FooterSetting();
        }

        // upload logo
        if ($request->hasFile('logo')) {
            $path = $request->file('logo')->store('footer', 'public');

            $footer->logo_path = $path;
            $footer->logo_url  = asset('storage/' . $path);
        }

        $footer->save();

        return response()->json([
            'message' => 'Footer saved',
            'data' => $footer
        ]);
    }
}
