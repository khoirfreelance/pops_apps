<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RW extends Model
{
    protected $table = 'rw';
    protected $fillable = ['id_wilayah', 'id_petugas', 'count_rw'];

    public function wilayah()
    {
        return $this->belongsTo(Wilayah::class, 'id_wilayah', 'id');
    }

    public function petugas()
    {
        return $this->belongsTo(User::class, 'id_petugas', 'id');
    }
}
