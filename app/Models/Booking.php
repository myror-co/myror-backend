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

    public function setCheckinAttribute($value)
    {
        $this->attributes['checkin'] = \Carbon\Carbon::parse($value);
    }

    public function setCheckoutAttribute($value)
    {
        $this->attributes['checkout'] = \Carbon\Carbon::parse($value);
    }

    public function setCurrencyAttribute($value)
    {
        $this->attributes['currency'] = strtoupper($value);
    }

    /* Scope */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'COMPLETED');
    }

    public function scopeOfGateway($query, $gateway)
    {
        return $query->where('gateway', $gateway);
    }

    /* Accesor */
    public function listing()
    {
        return $this->belongsTo('App\Models\Listing');
    }

}
