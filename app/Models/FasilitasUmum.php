<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FasilitasUmum extends Model
{
    protected $table = 'fasilitas_umum';

    protected $fillable = ['source_no', 'nama', 'kategori', 'latitude', 'longitude', 'is_manual'];

    protected function casts(): array
    {
        return [
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
            'is_manual' => 'boolean',
        ];
    }
}