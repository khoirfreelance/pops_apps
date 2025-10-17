<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Bride extends Model
{
    protected $table = 'pernikahan';
    const UPDATED_AT = 'modified_at';

    protected $fillable = [
        'id_catin',
        'tgl_daftar',
        'tgl_rencana_menikah',
        'rencana_tinggal',
        'catatan',
    ];

    /**
     * Relasi catin — menunjuk ke calon pengantin perempuan
     */
    public function catin()
    {
        return $this->belongsTo(Catin::class, 'id_catin');
    }
}
