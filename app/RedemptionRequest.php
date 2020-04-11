<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class RedemptionRequest extends Model
{
    protected $table = 'redemption_requests';

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
        return $this->belongsTo('App\RedemptionStatus');
    }
}
