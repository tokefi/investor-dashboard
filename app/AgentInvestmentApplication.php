<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class AgentInvestmentApplication extends Model
{

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'agent_investment_applications';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['project_id', 'user_id', 'client_first_name', 'client_email', 'phone_number', 'investment_amount', 'client_last_name', 'project_site', 'line_1', 'line_2','city', 'state','postal_code','country','tfn','account_name','bsb','account_number','investing_as','investing_company','joint_investor_last_name','joint_investor_first_name','wholesale_investing_as','accountant_name_and_firm','accountant_professional_body_designation','accountant_email','accountant_phone','equity_investment_experience_text','experience_period','unlisted_investment_experience_text','understand_risk_text', 'custom_field_values'];

    /**
     * belongs to relationship with projects
     * @return instance
     */

    public function user()
    {
    	return $this->belongsTo('App\User');
    }

    public function project()
    {
    	return $this->belongsTo('App\Project');
    }

    public function customFieldValues()
    {
        return $this->hasMany('App\CustomFieldValue', 'agent_investment_id');
    }
}
