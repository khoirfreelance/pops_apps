<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AnggotaKeluarga extends Model
{
    protected $table = 'anggota_keluarga';
    const UPDATED_AT = 'modified_at';
    protected $fillable = ['id_keluarga','nik','nama','tanggal_lahir','jenis_kelamin',
        'status_hubungan','agama','pendidikan','pekerjaan','status_perkawinan','kewarganegaraan'];

    public function keluarga()
    {
        return $this->belongsTo(Keluarga::class, 'id_keluarga');
    }
}

