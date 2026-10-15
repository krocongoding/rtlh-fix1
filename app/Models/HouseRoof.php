<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HouseRoof extends Model
{
    protected $fillable = [
        'house_id',
        'frame_material_id',
        'frame_condition_id',
        'material_id',
        'condition_id',
    ];

    public function house()
    {
        return $this->belongsTo(House::class);
    }

    public function frameMaterial(): BelongsTo
    {
        return $this->belongsTo(MasterValue::class, 'frame_material_id');
    }

    public function frameCondition(): BelongsTo
    {
        return $this->belongsTo(MasterValue::class, 'frame_condition_id');
    }

    public function material(): BelongsTo
    {
        return $this->belongsTo(MasterValue::class, 'material_id');
    }

    public function condition(): BelongsTo
    {
        return $this->belongsTo(MasterValue::class, 'condition_id');
    }
}
