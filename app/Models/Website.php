<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Website extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

    /* Mutators */
    public function setNameAttribute($value)
    {
        $this->attributes['name'] = strtolower($value);
    }

    /* Relationships */
    public function listings()
    {
        return $this->hasMany(Listing::class);
    }

    public function user()
    {
        return $this->belongsTo('App\Models\User');
    }

    public function instagram_plugin()
    {
        return $this->belongsTo('App\Models\InstagramPlugin');
    }

    public function stripe_account()
    {
        return $this->belongsTo('App\Models\StripeAccount');
    }

    public function template()
    {
        return $this->belongsTo('App\Models\Template');
    }
}
