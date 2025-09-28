<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cadre extends Model
{
    protected $table = 'anggota_tpk';
    const UPDATED_AT = 'modified_at';

    protected $fillable = [
        'id_tpk',
        'id_user',
        'jabatan',
        'status'
    ];

    public function tpk()
    {
        return $this->belongsTo(TPK::class, 'id_tpk');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'id_user');
    }
}
