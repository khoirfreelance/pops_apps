<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StatKeluarga extends Model
{
    protected $table = 'stat_keluarga';
    protected $fillable = ['id_wilayah', 'id_petugas', 'count_keluarga'];

    public function wilayah()
    {
        return $this->belongsTo(Wilayah::class, 'id_wilayah', 'id');
    }

    public function petugas()
    {
        return $this->belongsTo(User::class, 'id_petugas', 'id');
    }
}
