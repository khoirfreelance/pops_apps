<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Posyandu;

class PosyanduController extends Controller
{
    public function getPosyandu()
    {
        $posyandu = Posyandu::select('nama_posyandu as nama')
            ->distinct()
            ->orderBy('nama')
            ->get();

        return response()->json($posyandu);
    }
}
