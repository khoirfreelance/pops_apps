<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RT extends Model
{
    protected $table = 'rt';
    protected $fillable = ['id_wilayah', 'id_petugas', 'count_rt'];

    public function wilayah()
    {
        return $this->belongsTo(Wilayah::class, 'id_wilayah', 'id');
    }

    public function petugas()
    {
        return $this->belongsTo(User::class, 'id_petugas', 'id');
    }
}
