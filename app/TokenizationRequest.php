<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TokenizationRequest extends Model
{
    protected $table = 'tokenization_requests';

    protected $guarded = [];

    public function user()
    {
    	return $this->belongsTo('App\User');
    }

    public function project()
    {
    	return $this->belongsTo('App\Project');
    }

    public function status()
    {
        return $this->belongsTo('App\TokenizationStatus');
    }
}
