<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class MasterChild extends Model
{
    /**
	 * The database table used by the model.
	 *
	 * @var string
	 */
    protected $table = 'master_children';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['master','child','allocation'];

    public function isMaster()
    {
    	return $this->hasMany('App/Project');
    }
}
