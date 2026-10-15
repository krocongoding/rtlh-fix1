<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Region extends Model
{
    /**
     * Kode wilayah Kota Cirebon (BPS).
     * Digunakan untuk membatasi cakupan data hanya ke Kota Cirebon.
     */
    const KOTA_CIREBON_CODE = '32.74';

    protected $fillable = [
        'parent_id',
        'code',
        'name',
        'type',
        'level',
        'is_active',
    ];

    /**
     * Scope: hanya region di bawah Kota Cirebon (kecamatan & kelurahan).
     * Meng-filter berdasarkan kode yang diawali '32.74'.
     */
    public function scopeKotaCirebon($query)
    {
        return $query->where('code', 'like', self::KOTA_CIREBON_CODE . '.%');
    }

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function parent()
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    public function houses()
    {
        return $this->hasMany(House::class);
    }
}
