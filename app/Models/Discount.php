<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Discount extends Model
{
    use HasFactory;

    protected $fillable = [
        'used_by',
        'code',
        'value',
        'type',
        'status',
        'per_user',
        'limit',
        'description',
        'start_date',
        'end_date'
    ];

    public function isActive()
    {
        if ($this->start_date && $this->end_date)
            return $this->status == 'active' && $this->start_date >= Carbon::now() && $this->end_date <= Carbon::now();
        return $this->status == 'active';
    }

    public function use(): HasMany
    {
        return $this->hasMany(DiscountUse::class);
    }

    public function checkUseLimitNotFull(): bool
    {
        if ($this->limit == 0)
            return true;
        return $this->use->count() <= $this->limit;
    }
}
