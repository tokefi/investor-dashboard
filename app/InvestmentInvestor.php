<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class InvestmentInvestor extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'investment_investor';
    protected $dates = ['share_certificate_issued_at'];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['user_id', 'project_id', 'investment_id','amount', 'accepted','investment_confirmation', 'share_certificate_issued_at', 'share_number', 'share_certificate_path', 'is_cancelled', 'is_repurchased', 'signature_data', 'application_path', 'interested_to_buy','signature_type','signature_data_type', 'hide_investment', 'investing_as', 'admin_investment','master_investment','agent_investment'];

    /**
     * boolean fields
     */
    protected $casts = [
        'hide_investment' => 'boolean',
        'money_received' => 'boolean',
    ];

    public function user()
    {
    	return $this->belongsTo('App\User');
    }

    public function project()
    {
    	return $this->belongsTo('App\Project');
    }

    public function investment()
    {
    	return $this->belongsTo('App\Investment');
    }

    public function investingJoint()
    {
        return $this->hasOne('App\InvestingJoint');
    }
    public function userInvestmentDoc()
    {
        return $this->hasMany('App\UserInvestmentDocument');
    }
    public function transaction()
    {
        return $this->hasMany('App\Transaction');
    }
    public function wholesaleInvestment()
    {
        return $this->hasOne('App\WholesaleInvestment');
    }
    public function childInvestment()
    {
        return $this->hasMany('App\InvestmentInvestor','master_investment');
    }
}
