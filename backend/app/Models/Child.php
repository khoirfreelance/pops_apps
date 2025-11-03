<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Child extends Model
{
    use HasFactory;

    protected $fillable = [
        'nik',
        'nama',
        'jk',
        'tgl_lahir',
        'bb_lahir',
        'tb_lahir',
        'nama_ortu',
        'prov',
        'kab',
        'kec',
        'puskesmas',
        'desa',
        'posyandu',
        'rt',
        'rw',
        'alamat',
        'usia_saat_ukur',
        'tanggal_pengukuran',
        'berat',
        'tinggi',
        'lila',
        'bb_u',
        'zs_bb_u',
        'tb_u',
        'zs_tb_u',
        'bb_tb',
        'zs_bb_tb',
        'naik_berat_badan',
        'jml_vit_a',
        'kpsp',
        'kia',
        'detail',
    ];
}

