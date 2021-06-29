<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

    /* Mutators */
    public function setPaidAtAttribute($value)
    {
        $this->attributes['paid_at'] = \Carbon\Carbon::parse($value);
    }

}
