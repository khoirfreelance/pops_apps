<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Bride extends Model
{
    protected $table = 'catin';
    const UPDATED_AT = 'modified_at';

    protected $fillable = [
        'id_pasangan',
        'id_user', // relasi ke tb_anggota_keluarga
        'peran', // 'suami' atau 'istri'
        'tgl_daftar',
        'rencana_tgl_nikah',
        'tempat_nikah',
        'status_pernikahan',
    ];

    /**
     * Relasi ke tabel anggota_keluarga
     * id_user → id di tabel anggota_keluarga
     */
    public function anggotaKeluarga()
    {
        return $this->belongsTo(AnggotaKeluarga::class, 'id_user');
    }

    /**
     * Relasi pasangan — menunjuk ke calon pengantin lainnya (suami/istri)
     */
    public function pasangan()
    {
        return $this->belongsTo(Bride::class, 'id_pasangan');
    }

    /**
     * Relasi terbalik — mencari siapa yang menunjuk dirinya sebagai pasangan
     */
    public function linkedBy()
    {
        return $this->hasOne(Bride::class, 'id_pasangan');
    }

    /**
     * Accessor untuk nama pasangan
     */
    public function getNamaPasanganAttribute()
    {
        return $this->pasangan?->nama ?? $this->linkedBy?->nama ?? '-';
    }

    /**
     * Scope pencarian umum
     */
    public function scopeFilter($query, $filters)
    {
        if (!empty($filters['nama'])) {
            $query->where('nama', 'like', "%{$filters['nama']}%");
        }

        if (!empty($filters['peran'])) {
            $query->where('peran', $filters['peran']);
        }

        if (!empty($filters['status_pernikahan'])) {
            $query->where('status_pernikahan', $filters['status_pernikahan']);
        }

        return $query;
    }
}
