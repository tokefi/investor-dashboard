<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Price extends Model
{
    protected $table = 'prices';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['project_id', 'price', 'effective_date'];

    public function project()
    {
    	return $this->belongsTo('App\Project');
    }

}
