<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Listing extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
   		'amenities' => 'array',
   		'photos' => 'array',
		'recent_review' => 'array',
		'user' => 'array',
		'hosts' => 'array',
		'rules' => 'array',
	];

    public function website()
    {
        return $this->belongsTo('App\Models\Website');
    }
}
