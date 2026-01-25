<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DampinganKeluarga extends Model
{
    protected $table = 'dampingan_keluarga';
    const UPDATED_AT = 'modified_at';

    protected $fillable = [
        'id_pendampingan',
        'id_keluarga',
        'id_tpk',
        'jenis',
    ];

    public function tpk()
    {
        return $this->belongsTo(TPK::class, 'id_tpk', 'id');
    }

    public function keluarga()
    {
        return $this->belongsTo(Keluarga::class, 'id_keluarga', 'id');
    }

    public function pendampingan()
    {
        return $this->belongsTo(Pendampingan::class, 'id_pendampingan', 'id');
    }

}
