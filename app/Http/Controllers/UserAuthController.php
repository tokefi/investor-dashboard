<?php

namespace App\Http\Controllers;

use Session;
use Validator;
use App\User;
use App\Color;
use Carbon\Carbon;
use App\UserRegistration;
use App\Project;
use App\ProjectEOI;
use App\Mailers\AppMailer;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\UserAuthRequest;
use App\Http\Requests;
use App\InvestingJoint;
use App\ProjectSpvDetail;
use App\InvestmentInvestor;
use App\UserInvestmentDocument;
use App\WholesaleInvestment;
use App\InvestmentRequest;
use App\Jobs\SendReminderEmail;
use Intervention\Image\Facades\Image;
use App\Jobs\SendInvestorNotificationEmail;
use App\Jobs\SendDeveloperNotificationEmail;
use Barryvdh\DomPDF\Facade as PDF;
use Illuminate\Foundation\Auth\AuthenticatesAndRegistersUsers;
use App\Credit;
use App\Helpers\SiteConfigurationHelper;
use Illuminate\Support\Facades\View;


class UserAuthController extends Controller
{
    /**
     * constructor for UsersController
     */
    public function __construct()
    {
        $this->middleware('guest', ['only' => ['login','authenticate','authenticateCheck']]);
        $this->allProjects = Project::where('project_site', url())->get();
        View::share('allProjects', $this->allProjects);
    }

    /**
     * redirection path after signup
     * @var string
     */
    protected $redirectTo = '/users';

    /**
     * Get the post register / login redirect path.
     *
     * @return string
     */
    public function redirectPath()
    {
        if (Auth::user()->roles->contains('role', 'admin') || Auth::user()->roles->contains('role', 'master')) {
            return 'dashboard.index';
        }
        return 'users.index';
    }

    /**
     * renders login page
     * @return view login page
     */
    public function login(Request $request)
    {
        if($request->next)
        {
            $request->source ? $request->attributes->add(['next'=>$request->next."&source=".$request->source]) : $request->next;
        }
        $color = Color::where('project_site',url())->first();
        $redirectNotification = $request->has('redirectNotification')?$request->redirectNotification:0;
        return view('users.login',compact('color', 'redirectNotification'));
    }

    /**
     * logout user
     * @return view login
     */
    public function logout()
    {
        Auth::logout();
        Session::flush();
        return redirect()->route('users.login')->withMessage('<p class="alert alert-success text-center"> Successfully Logged out!</p>');
    }


    public function authenticateCheck(Request $request)
    {
        $user = User::where('email',$request->email)->first();
        if($user){
            return $request->email;
        }else{
            return 'fail';
        }
    }
    public function successEoi(Request $request)
    {
        if(Auth::check())
        {
            $color = Color::where('project_site',url())->first();
            return view('users.successEoi',compact('color'));
        }
        return redirect()->route('users.login')->withMessage('<p class="alert alert-danger text-center">Please Login</p>');
    }
    public function authenticateEoi(UserAuthRequest $request,AppMailer $mailer)
    {
        $auth = false;
        if (Auth::attempt(['email' => $request->email, 'password' => $request->password, 'active'=>1], $request->remember)) {
            $loginBonus = 0;
            $auth = true;
            if(isset(\App\Helpers\SiteConfigurationHelper::getConfigurationAttr()->daily_login_bonus_konkrete) && \App\Helpers\SiteConfigurationHelper::getConfigurationAttr()->daily_login_bonus_konkrete != '') {
                $daily_bonus_konkrete = \App\Helpers\SiteConfigurationHelper::getConfigurationAttr()->daily_login_bonus_konkrete;
            }
            else {
                $daily_bonus_konkrete = \App\Helpers\SiteConfigurationHelper::getEbConfigurationAttr()->daily_login_bonus_konkrete;
            };
            if(Auth::user()->last_login){
                if(!Auth::user()->last_login->gt(\Carbon\Carbon::now()->subDays(1)) && $daily_bonus_konkrete != 0){
                    $loginBonus = rand(1, $daily_bonus_konkrete);
                    Credit::create([
                        'user_id' => Auth::user()->id,
                        'amount' => $loginBonus,
                        'type' => 'Daily login bonus',
                        'project_site' => url(),
                        'currency' => 'konkrete'
                    ]);
                }
            }
            Auth::user()->update(['last_login'=> Carbon::now()]);
            Session::flash('loginaction', 'success.');
            $color = Color::where('project_site',url())->first();
            $project = Project::findOrFail($request->project_id);
            $user = Auth::user();
            $user_info = Auth::user();
            $min_amount_invest = $project->investment->minimum_accepted_amount;
            if((int)$request->investment_amount < (int)$min_amount_invest)
            {
                return redirect()->back()->withCookie(cookie('login_bonus', $loginBonus, 1))->withErrors(['The amount to invest must be at least $'.$min_amount_invest]);
            }
            $this->validate($request, [
                'first_name' => 'required',
                'last_name' =>'required',
                'email' => 'required',
                'phone_number' => 'required|numeric',
                'investment_amount' => 'required|numeric',
                'investment_period' => 'required',
            ]);
            if($project){
                if($project->eoi_button){
                    $request->merge(['country' => array_search($request->country_code, \App\Http\Utilities\Country::all())]);
                    $eoi_data = ProjectEOI::create([
                        'project_id' => $request->project_id,
                        'user_id' => $user->id,
                        'user_name' => $request->first_name.' '.$request->last_name,
                        'user_email' => $request->email,
                        'phone_number' => $request->phone_number,
                        'investment_amount' => $request->investment_amount,
                        'invesment_period' => $request->investment_period,
                        'is_accredited_investor' => $request->is_accredited_investor,
                        'country_code' => $request->country_code,
                        'country'=>$request->country,
                        'project_site' => url(),
                    ]);
                    $mailer->sendProjectEoiEmailToAdmins($project, $eoi_data);
                    $mailer->sendProjectEoiEmailToUser($project, $user_info);
                }
                else{

                }
            }
            return redirect()->route('users.success.eoi')->withCookie(cookie('login_bonus', $loginBonus, 1));
        }
        return redirect()->back()->withInput()->withMessage('<p class="alert alert-danger text-center">Login Failed, please check your password and email combination</p>');
    }

    public function authenticateOffer(UserAuthRequest $request)
    {
        $auth = false;
        if (Auth::attempt(['email' => $request->email, 'password' => $request->password, 'active'=>1], $request->remember)) {
            $request->merge([
                'password'=>bcrypt($request->password)
            ]);
            $auth = true;
            Auth::user()->update(['last_login'=> Carbon::now()]);
            Session::flash('loginaction', 'success.');
            $color = Color::where('project_site',url())->first();
            $project = Project::findOrFail($request->project_id);
            $user = Auth::user();
            $user_info = Auth::user();
            $project = Project::findOrFail($request->project_id);
            $min_amount_invest = $project->investment->minimum_accepted_amount;
            if((int)$request->amount_to_invest < (int)$min_amount_invest)
            {
                return redirect()->back()->withErrors(['The amount to invest must be at least '.$min_amount_invest]);
            }
            $validation_rules = array(
                'joint_investor_id_doc'   => 'mimes:jpeg,jpg,png,pdf',
                'trust_or_company_docs'   => 'mimes:jpeg,jpg,png,pdf',
                'user_id_doc'   => 'mimes:jpeg,jpg,png,pdf',
                'amount_to_invest'   => 'required|numeric',
                'line_1' => 'required',
                'state' => 'required',
                'postal_code' => 'required'
            );
            $validator = Validator::make($request->all(), $validation_rules);

        // Return back to form w/ validation errors & session data as input
            if($validator->fails()) {
                return  redirect()->back()->withErrors($validator);
            }
            $user = Auth::user();
            $agent_investment = 0;
            if($request->agent_investment == 'agent_investment'){
                $agent_investment = 1;
            }
        // If application store request received from request form
            if($request->investment_request_id)
            {
                $investmentRequest = InvestmentRequest::find($request->investment_request_id);
                if($investmentRequest) {
                    if(\App\Helpers\SiteConfigurationHelper::isSiteAdmin()){
                        if($investmentRequest->project->project_site == url()) {
                            $user = User::find($investmentRequest->user_id);
                            $project = Project::find($investmentRequest->project_id);
                        }
                        else{
                            return redirect()->back()->withErrors('Not Project Admin');
                        }
                    }
                    else{
                        return redirect()->back()->withErrors('Not Site Admin');
                    }
                }
                else {
                    return redirect()->back()->withErrors('Something went wrong');
                }
            }
            $amount = floatval(str_replace(',', '', str_replace('A$ ', '', $request->amount_to_invest)));
        $amount_5 = $amount*0.05; //5 percent of investment
        //update agent for investor
        if($request->agent_investment == 'agent_investment'){
            if(!$user->agent_id){
                User::find($user->id)->update([
                  'agent_id' => $request->agent_id
              ]);
            }elseif($user->agent_id != $request->agent_id){
                return redirect()->back()->withMessage('Agent changed. Application not submitted');
            }
        }
        $user->investments()->attach($project, ['investment_id'=>$project->investment->id,'amount'=>$amount, 'buy_rate' => $project->share_per_unit_price, 'project_site'=>url(),'investing_as'=>$request->investing_as, 'signature_data'=>$request->signature_data,'signature_data_type'=>$request->signature_data_type,'signature_type'=>$request->signature_type,'interested_to_buy'=>$request->interested_to_buy,'agent_investment'=>$agent_investment, 'agent_id'=>$request->agent_id]);
        $investor = InvestmentInvestor::get()->last();
        if($project->master_child){
          foreach($project->children as $child){
            $percAmount = round($amount* ($child->allocation)/100 * $project->share_per_unit_price);
            $childProject = Project::find($child->child);
            $user->investments()->attach($childProject, ['investment_id'=>$childProject->investment->id,'amount'=>round($percAmount/$childProject->share_per_unit_price), 'buy_rate' => $childProject->share_per_unit_price, 'project_site'=>url(), 'signature_data'=>$request->signature_data, 'interested_to_buy'=>$request->interested_to_buy,'signature_data_type'=>$request->signature_data_type,'signature_type'=>$request->signature_type, 'agent_investment'=>$agent_investment, 'master_investment'=>$investor->id, 'agent_id'=>$request->agent_id]);
          }
        }
        if($request->investing_as != 'Individual Investor'){
            $investing_joint = new InvestingJoint;
            $investing_joint->project_id = $project->id;
            $investing_joint->investment_investor_id = $investor->id;
            $investing_joint->joint_investor_first_name = $request->joint_investor_first;
            $investing_joint->joint_investor_last_name = $request->joint_investor_last;
            $investing_joint->investing_company = $request->investing_company_name;
            $investing_joint->account_name = $request->account_name;
            $investing_joint->bsb = $request->bsb;
            $investing_joint->account_number = $request->account_number;
            $investing_joint->line_1 = $request->line_1;
            $investing_joint->line_2 = $request->line_2;
            $investing_joint->city = $request->city;
            $investing_joint->state = $request->state;
            $investing_joint->postal_code = $request->postal_code;
            $investing_joint->country = $request->country;
            $investing_joint->country_code = $request->country_code;
            $investing_joint->tfn = $request->tfn;
            $investing_joint->save();
        }
        else{
            $user->update($request->all());
        }
        $investor_joint = InvestingJoint::get()->last();
        if($request->hasFile('joint_investor_id_doc'))
        {
            $destinationPath = 'assets/users/'.$user->id.'/investments/'.$investor->id.'/'.$request->joint_investor_first.'_'.$request->joint_investor_last.'/';
            $filename = $request->file('joint_investor_id_doc')->getClientOriginalName();
            $fileExtension = $request->file('joint_investor_id_doc')->getClientOriginalExtension();
            $request->file('joint_investor_id_doc')->move($destinationPath, $filename);
            $user_investment_doc = new UserInvestmentDocument(['type'=>'joint_investor', 'filename'=>$filename, 'path'=>$destinationPath.$filename,'project_id'=>$project->id,'investing_joint_id'=>$investor_joint->id,'investment_investor_id'=>$investor->id,'extension'=>$fileExtension,'user_id'=>$user->id]);
            $project->investmentDocuments()->save($user_investment_doc);

        }
        if($request->hasFile('trust_or_company_docs'))
        {
            $destinationPath = 'assets/users/'.$user->id.'/investments/'.$investor->id.'/'.$request->investing_company_name.'/';
            $filename = $request->file('trust_or_company_docs')->getClientOriginalName();
            $fileExtension = $request->file('trust_or_company_docs')->getClientOriginalExtension();
            $request->file('trust_or_company_docs')->move($destinationPath, $filename);
            $user_investment_doc = new UserInvestmentDocument(['type'=>'trust_or_company', 'filename'=>$filename, 'path'=>$destinationPath.$filename,'project_id'=>$project->id,'investing_joint_id'=>$investor_joint->id,'investment_investor_id'=>$investor->id,'extension'=>$fileExtension,'user_id'=>$user->id]);
            $project->investmentDocuments()->save($user_investment_doc);

        }
        if($request->hasFile('user_id_doc'))
        {
            $destinationPath = 'assets/users/'.$user->id.'/investments/'.$investor->id.'/normal_name/';
            $filename = $request->file('user_id_doc')->getClientOriginalName();
            $fileExtension = $request->file('user_id_doc')->getClientOriginalExtension();
            $request->file('user_id_doc')->move($destinationPath, $filename);
            $user_investment_doc = new UserInvestmentDocument(['type'=>'normal_name', 'filename'=>$filename, 'path'=>$destinationPath.$filename,'project_id'=>$project->id,'investing_joint_id'=>$investor_joint->id,'investment_investor_id'=>$investor->id,'extension'=>$fileExtension,'user_id'=>$user->id]);
            $project->investmentDocuments()->save($user_investment_doc);

        }
        //Save wholesale project input fields
        if(!$project->retail_vs_wholesale) {
            $wholesale_investor = WholesaleInvestment::get()->last();{
                $wholesale_investing = new WholesaleInvestment;
                $wholesale_investing->project_id = $project->id;
                $wholesale_investing->investment_investor_id = $investor->id;
                $wholesale_investing->wholesale_investing_as = $request->wholesale_investing_as;
                if($request->wholesale_investing_as === 'Wholesale Investor (Net Asset $2,500,000 plus)'){
                    $wholesale_investing->accountant_name_and_firm = $request->accountant_name_firm_txt;
                    $wholesale_investing->accountant_professional_body_designation = $request->accountant_designation_txt;
                    $wholesale_investing->accountant_email = $request->accountant_email_txt;
                    $wholesale_investing->accountant_phone = $request->accountant_phone_txt;
                }
                elseif($request->wholesale_investing_as === 'Sophisticated Investor'){
                    $wholesale_investing->experience_period = $request->experience_period_txt;
                    $wholesale_investing->equity_investment_experience_text = $request->equity_investment_experience_txt;
                    $wholesale_investing->unlisted_investment_experience_text = $request->unlisted_investment_experience_txt;
                    $wholesale_investing->understand_risk_text = $request->understand_risk_txt;
                }
                $wholesale_investing->save();
            }
        }
        // Mark request form link expired
        if($request->investment_request_id){
            InvestmentRequest::find($request->investment_request_id)->update([
                'is_link_expired' => 1
            ]);
        }
        // Create PDF of Application form
        // $pdfBasePath = '/app/application/application-'.$investor->id.'-'.time().'.pdf';
        // $pdfPath = storage_path().$pdfBasePath;
        // $pdf = PDF::loadView('pdf.application', ['project' => $project, 'investment' => $investor, 'user' => $user]);
        // $pdf->save($pdfPath);
        // $investor->application_path = $pdfBasePath;
        $investor->save();
        $this->dispatch(new SendInvestorNotificationEmail($user,$project, $investor));
        $this->dispatch(new SendReminderEmail($user,$project,$investor));
        $amount = $amount * $project->share_per_unit_price;
        $viewHtml = view('projects.gform.thankyou', compact('project', 'user', 'amount_5', 'amount'))->render();
        return response()->json(array('success'=>true,'html'=>$viewHtml,'auth'=>$auth));
    }
    return response()->json(array('success'=>false,'auth'=>$auth));
}

    /**
     * authenticate user
     * @param  UserAuthRequest $request
     * @return view user show page
     */
    public function authenticate(UserAuthRequest $request)
    {
        $loginBonus = 0;
        if(isset(\App\Helpers\SiteConfigurationHelper::getConfigurationAttr()->daily_login_bonus_konkrete) && \App\Helpers\SiteConfigurationHelper::getConfigurationAttr()->daily_login_bonus_konkrete != '' && \App\Helpers\SiteConfigurationHelper::getConfigurationAttr()->show_powered_by_estatebaron) {
            $daily_bonus_konkrete = \App\Helpers\SiteConfigurationHelper::getConfigurationAttr()->daily_login_bonus_konkrete;
        }
        else {
            $daily_bonus_konkrete = $loginBonus;
        };
        if (Auth::attempt(['email' => $request->email, 'password' => $request->password, 'active'=>1], $request->remember)) {
            if(Auth::user()->last_login){
                if(!Auth::user()->last_login->gt(\Carbon\Carbon::now()->subDays(1)) && $daily_bonus_konkrete != 0){
                    $loginBonus = rand(1, $daily_bonus_konkrete);
                    Credit::create([
                        'user_id' => Auth::user()->id,
                        'amount' => $loginBonus,
                        'type' => 'Daily login bonus',
                        'project_site' => url(),
                        'currency' => 'konkrete'
                    ]);
                }
            }
            Auth::user()->update(['last_login'=> Carbon::now()]);
            if (Auth::user()->roles->contains('role', 'admin') || Auth::user()->roles->contains('role', 'master')) {
                $this->redirectTo = "/dashboard";
            }
            if($request->next){
                if( strpos( $request->next, '?id=' ) !== false ){
                    $this->redirectTo = "/".$request->next."&source=eoi";
                }else{
                    $this->redirectTo = "/".$request->next;
                }
            }
            elseif($request->redirectNotification){
                $this->redirectTo = "/users/".Auth::User()->id."/notification";
            }
            else{
                $this->redirectTo = "/#projects";
            }
            if(\App\Helpers\SiteConfigurationHelper::getConfigurationAttr()->show_powered_by_estatebaron){
                Session::flash('loginaction', 'success.');
            }
            // return redirect($this->redirectTo);
            return redirect($this->redirectTo)->withCookie(cookie('login_bonus', $loginBonus, 1));
        }
        if (Auth::viaRemember()) {
            Auth::user()->update(['last_login'=> Carbon::now()]);
            return redirect()->route($this->redirectPath());
        }
        $user = User::whereEmail($request->email)->first();
        if($user) {
            if ($user->active) {
                return redirect()->route('users.login')->withInput($request->only('email', 'remember'))->withMessage('<p class="alert alert-danger text-center">email and password combination is wrong</p>');
            } else {
                return redirect()->route('users.login')->withInput($request->only('email', 'remember'))->withMessage('<p class="alert alert-danger text-center">User is not active, please activate user.</p>');
            }
        }
        $user_incomplete = UserRegistration::whereEmail($request->email)->first();
        if($user_incomplete) {
            if (!$user_incomplete->active) {
                return redirect()->route('users.login')->withInput($request->only('email', 'remember'))->withMessage('<p class="alert alert-danger text-center">This email is registered but you dont seem to have activated yourself.<br> We have sent an activation link to this email, please click on the link to activate yourself and then you will be able to access the site <br><br> or <a href="/registrations/resend?email='.$request->email.'">click here to resend activation link</a></p>');
            } else {
                return redirect()->route('users.login')->withInput($request->only('email', 'remember'))->withMessage('<p class="alert alert-danger text-center">This email is registered but you dont seem to have activated yourself.<br> We have sent an activation link to this email, please click on the link to activate yourself and then you will be able to access the site <br><br> or <a href="/registrations/resend?email='.$request->email.'">click here to resend activation link</a></p>');
            }
        }
        if($request->eoiLogin == 'eoiLogin'){
            return redirect()->back()->withInput($request->only('email', 'remember'))->withMessage('<p class="alert alert-warning text-center">This email is not registered, please sign up.</p>')->with('error_code', 5);
        }
        return redirect()->route('users.login')->withInput($request->only('email', 'remember'))->withMessage('<p class="alert alert-warning text-center">This email is not registered, please sign up.</p>');
    }

    public function activate($token)
    {
        $user = User::whereActivationToken($token)->firstOrFail();
        if($user->active) {
            $status = $user->update(['active'=> 0, 'activated_on'=>Carbon::now()]);
            return redirect()->route('users.login')->withMessage('<p class="alert alert-info text-center">User Already Activated</p>');
        }
        $user->active = true;
        $user->activated_on = Carbon::now();
        $user->save();
        return redirect()->route('users.login')->withMessage('<p class="alert alert-success text-center">Activation Successful! Login to see the opportunities, you recieved $25 in your credit.</p>');
    }

    public function requestFormFilling(UserAuthRequest $request,AppMailer $mailer)
    {
        $auth = false;
        if (Auth::attempt(['email' => $request->email, 'password' => $request->password, 'active'=>1], $request->remember)) {
            $auth = true;
            $user = Auth::user();
            if(isset(\App\Helpers\SiteConfigurationHelper::getConfigurationAttr()->daily_login_bonus_konkrete) && \App\Helpers\SiteConfigurationHelper::getConfigurationAttr()->daily_login_bonus_konkrete != '') {
                $daily_bonus_konkrete = \App\Helpers\SiteConfigurationHelper::getConfigurationAttr()->daily_login_bonus_konkrete;
            }
            else {
                $daily_bonus_konkrete = \App\Helpers\SiteConfigurationHelper::getEbConfigurationAttr()->daily_login_bonus_konkrete;
            };
            if(Auth::user()->last_login){
                if(!Auth::user()->last_login->gt(\Carbon\Carbon::now()->subDays(1)) && $daily_bonus_konkrete != 0){
                    $loginBonus = rand(1, $daily_bonus_konkrete);
                    Credit::create([
                        'user_id' => Auth::user()->id,
                        'amount' => $loginBonus,
                        'type' => 'Daily login bonus',
                        'project_site' => url(),
                        'currency' => 'konkrete'
                    ]);
                }
            }
            return response()->json(array('success'=>true,'auth'=>$auth));
        }
        return response()->json(array('success'=>false,'auth'=>$auth));
    }
}
