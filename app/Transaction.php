<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
	/**
	 * The database table used by the model.
	 * 
	 * @var string 
	 */
    protected $table = 'transactions';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['user_id','project_id','transaction_type','transaction_date','amount','rate','number_of_shares','transaction_description'];

    const BUY = 'BUY';
    const REPURCHASE = 'REPURCHASE';
    const CANCELLED = 'CANCELLED';
    const DIVIDEND = 'DIVIDEND';
    const FIXED_DIVIDEND = 'FIXED DIVIDEND';
    const ANNUALIZED_DIVIDEND = 'ANNUALIZED DIVIDEND';

    public function user()
    {
    	return $this->belongsTo('App\User');
    }

    public function project()
    {
    	return $this->belongsTo('App\Project');
    }

}
