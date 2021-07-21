<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StripeAccount extends Model
{
    use HasFactory;

    protected $guarded = [];

    /*scopes*/
    public function scopeActive($query)
    {
        return $query->where('charges_enabled', 1);
    }

    /* get relationships */
    public function websites()
    {
        return $this->hasMany(Website::class);
    }
}
