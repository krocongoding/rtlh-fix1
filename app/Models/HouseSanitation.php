<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HouseSanitation extends Model
{
    protected $table = 'house_sanitation';

    protected $fillable = [
        'house_id',
        'water_source_id',
        'wastewater_disposal_id',
        'toilet_available',
        'toilet_type_id',
        'fecal_disposal_type_id',
        'water_fecal_distance',
    ];

    protected function casts(): array
    {
        return [
            'toilet_available' => 'boolean',
        ];
    }

    public function house(): BelongsTo
    {
        return $this->belongsTo(House::class);
    }

    public function waterSource(): BelongsTo
    {
        return $this->belongsTo(
            MasterValue::class,
            'water_source_id'
        );
    }

    public function wastewaterDisposal(): BelongsTo
    {
        return $this->belongsTo(
            MasterValue::class,
            'wastewater_disposal_id'
        );
    }

    public function toiletType(): BelongsTo
    {
        return $this->belongsTo(
            MasterValue::class,
            'toilet_type_id'
        );
    }

    public function fecalDisposalType(): BelongsTo
    {
        return $this->belongsTo(
            MasterValue::class,
            'fecal_disposal_type_id'
        );
    }
}