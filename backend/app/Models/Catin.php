<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Catin extends Model
{
    protected $table = 'catin';
    const UPDATED_AT = 'modified_at';

    protected $fillable = [
        'id_pasangan',
        'peran',
        'nama',
        'nik',
        'pekerjaan',
        'tgl_lahir',
        'usia',
        'no_hp',
    ];

    /**
     * Relasi pasangan — menunjuk ke calon pengantin lainnya (suami/istri)
     */
    public function pasangan()
    {
        return $this->belongsTo(Catin::class, 'id_pasangan', 'id');
    }
}
