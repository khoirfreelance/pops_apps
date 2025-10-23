<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Keluarga;
use App\Models\Posyandu;
use App\Models\TPK;
use App\Models\User;
use App\Models\Catin;
/* use App\Models\Rw;
use App\Models\Rt;
use App\Models\IbuHamil;


use App\Models\Bidan;

use App\Models\Anak;
*/

class DashboardController extends Controller
{
    public function stats()
    {
        return response()->json([
            'rw' => 40,
            'rt' => 60,
            'keluarga' => Keluarga::count(),
            'tpk' => TPK::count(), // contoh data statis
            'ibu_hamil' => 30,//IbuHamil::count(),
            'posyandu' => Posyandu::count(),
            'bidan' => User::where('role', '=', 'Bidan')->count(),
            'catin' => Catin::count(),
            'anak' => 45,//Anak::where('usia', '<=', 5)->count(),
        ]);
    }
}
