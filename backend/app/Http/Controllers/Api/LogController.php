<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Log;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class LogController extends Controller
{

    public function index()
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $logs = Log::with('user');

        // 🔥 filter 2 bulan terakhir
        $start = Carbon::now()->startOfMonth(); // awal bulan ini
        $end   = Carbon::now()->endOfMonth();   // akhir bulan ini

        $logs->whereBetween('timestamp', [$start, $end]);

        if ($user->role != 'SUPER ADMIN') {
            $logs->whereHas('user', function ($q) use ($user) {
                $q->where('id_wilayah', $user->id_wilayah);
            });
        }

        $logs = $logs->orderBy('timestamp', 'desc')->get();

        $data = $logs->map(function ($log) {
            return [
                'nama'      => $log->user ? $log->user->name : null,
                'nik'       => $log->user ? $log->user->nik : null,
                'context'   => $log->context,
                'activity'  => $log->activity,
                'timestamp' => $log->timestamp,
            ];
        });

        return response()->json($data->values());
    }
}
