<?php

namespace App\Http\Controllers;

use View;
use Session;
use App\Note;
use App\User;
use App\Color;
use App\Price;
use App\Credit;
use App\Invite;
use App\Project;
use App\Position;
use Carbon\Carbon;
use App\IdDocument;
use App\Investment;
use App\ProjectEOI;
use App\CustomField;
use App\MasterChild;
use App\ProjectProg;
use App\Transaction;
use Mailgun\Mailgun;
use App\ReferralLink;
use App\Http\Requests;
use App\InvestingJoint;
use App\ProjectInterest;
use App\CustomFieldValue;
use App\ProjectSpvDetail;
use App\RedemptionStatus;
use App\UserRegistration;
use App\InvestmentRequest;
use App\Mailers\AppMailer;
use App\RedemptionRequest;
use App\SiteConfiguration;
use App\InvestmentInvestor;
use App\ProspectusDownload;
use App\Helpers\ModelHelper;
use Illuminate\Http\Request;
use App\ReferralRelationship;
use App\Helpers\BulkEmailHelper;
use Chumper\Datatable\Datatable;
use Barryvdh\DomPDF\Facade as PDF;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\File;
use Illuminate\Mail\TransportManager;
use SendGrid\Mail\Mail as SendgridMail;
use App\Helpers\SiteConfigurationHelper;
use Illuminate\Support\Facades\Validator;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\ApplicationSections;


class DashboardController extends Controller
{

    protected $siteConfiguration;

    /**
     * constructor for DashboardController
     */
    public function __construct()
    {
        $this->middleware('auth');
        if(SiteConfigurationHelper::isSiteAgent()){
            $this->middleware('admin',['except' => ['index','users','projects','edit','showUser','projectInvestors']]); 
        }else{
            $this->middleware('admin'); 
        }
        
        $this->siteConfiguration = SiteConfiguration::where('project_site', url())->first();

        $this->allProjects = Project::where('project_site', url())->get();
        View::share('allProjects', $this->allProjects);
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        $color = Color::where('project_site',url())->first();
        $users = User::where('registration_site',url());
        $projects = Project::all();
        $projects = $projects->where('project_site',url());
        $notes = Note::all();
        // $total_goal = Investment::all()->where('project_site',url())->sum('goal_amount');
        // $pledged_investments = InvestmentInvestor::all()->where('project_site',url())->where('hide_investment', 0);
        $activeP = $projects->where('project_site',url())->where('active', true);
        $goal_amount = [];
        $amount = [];
        $funds_received = [];

        foreach ($activeP as $proj) {
            $goal_amount[] = $proj->investment->goal_amount;
            $investors = $proj->investors;

            $pledged_investments = InvestmentInvestor::all()->where('project_site',url())->where('project_id', $proj->id)->where('hide_investment', false);
            foreach($pledged_investments as $pledged_investment){
                $amount[] = $pledged_investment->amount;;
            }

            $funds_received_investments = InvestmentInvestor::all()->where('project_site',url())->where('project_id', $proj->id)->where('hide_investment', false)->where('money_received', true);
            foreach($funds_received_investments as $funds_received_investment){
                $funds_received[] = $funds_received_investment->amount;;
            }
            // foreach($investors as $investor){
            //     $amount[] = $investor->getOriginal('pivot_amount');
            // }
        }

        if(SiteConfigurationHelper::isSiteAgent()){
            $users = User::where('registration_site',url())->where('agent_id',\Illuminate\Support\Facades\Auth::User()->id);
        }else{
            $users = User::where('registration_site',url());
            $total_goal = array_sum($goal_amount);
            $pledged_investments = array_sum($amount);
            $total_funds_received = array_sum($funds_received);
        }
        return view('dashboard.index', compact('users', 'projects', 'pledged_investments', 'total_goal', 'notes','color', 'total_funds_received'));
    }

    public function users(Request $request)
    {
        $color = Color::where('project_site', url())->first();
        $search = trim($request->get('search'));
        $field = $request->get('field') != '' ? $request->get('field') : 'id';
        $sort = $request->get('sort') != '' ? $request->get('sort') : 'asc';
        $users = new User();
        if(SiteConfigurationHelper::isSiteAgent()){
            $users = User::where('registration_site',url())->where('agent_id',\Illuminate\Support\Facades\Auth::User()->id);
        }else{
            $users = $users->where('registration_site', url());
        }
        if ($search != '') {
            $users = $users->where(function ($query) use ($search) {
                $query->whereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", ["%" . $search . "%"]);
                $query->orWhereRaw("email LIKE ?", ["%" . $search . "%"]);
                $query->orWhereRaw("phone_number LIKE ?", ["%" . $search . "%"]);
            });
        }
        $users = $users->orderBy($field, $sort)->get();
        $projects = Project::where('project_site', url())->where('active', true)->where('eoi_button', false)->where('is_coming_soon', false)->get();

        return view('dashboard.users.index', compact('users', 'color', 'projects'))->withPath('?search=' . urlencode($search) . '&field=' . $field . '&sort=' . $sort);
    }

    public function projects()
    {
        $color = Color::where('project_site',url())->first();
        $projects = Project::all();
        $projects = $projects->where('project_site',url());
        $pledged_investments = InvestmentInvestor::where('hide_investment', '0')->get();

        return view('dashboard.projects.index', compact('projects', 'pledged_investments','color'));
    }

    public function test()
    {
        $color = Color::where('project_site',url())->first();
        return view('dashboard.users.test',compact('color'));
    }

    public function getDashboardUsers()
    {
        $datatable = new Datatable();
        return $datatable->collection(User::all())
        ->showColumns('id')
        ->addColumn('Details',function($model){
            return $model;
        })
        ->showColumns('phone_number','email')
        ->searchColumns('first_name')
        ->orderColumns('id','first_name')
        ->make();
    }

    public function getDashboardProjects()
    {
        $datatable = new Datatable();
        return $datatable->collection(Project::all())
        ->showColumns('id', 'title', 'active', 'description')
        ->searchColumns('title', 'description')
        ->orderColumns('id','title', 'active')
        ->make();
    }

    public function showUser($user_id)
    {
        $color = Color::where('project_site',url())->first();
        $user = User::findOrFail($user_id);
        return view('dashboard.users.show', compact('user','color'));
    }

    public function usersInvestments($user_id)
    {
        $color = Color::where('project_site',url())->first();
        $user = User::findOrFail($user_id);

        $projects = Project::where('project_site',url())
        ->where('active', 1)
        ->where('eoi_button', false)
        ->where('is_coming_soon', false)
        ->get();

        $investments = InvestmentInvestor::where('user_id', $user->id)
        ->where('project_site', url())
        ->get();

        return view('dashboard.users.investments', compact('user','color', 'investments', 'projects'));
    }

//Disabled in routes as well due to no usage
/*    public function showProject($project_id)
    {
        $color = Color::where('project_site',url())->first();
        $project = Project::findOrFail($project_id);
        $investments = InvestmentInvestor::where('project_id', $project_id)->get();
        return view('dashboard.projects.show', compact('project', 'investments','color'));
    }*/

    public function projectInvestors($project_id)
    {
        $color = Color::where('project_site',url())->first();
        $projects = Project::all();
        $project = Project::findOrFail($project_id);
        if(SiteConfigurationHelper::isSiteAgent()){
            $investments = InvestmentInvestor::where('project_id', $project_id)->where('agent_id',\Illuminate\Support\Facades\Auth::User()->id)->get();
            $acceptedRegistries = InvestmentInvestor::where('project_id', $project_id)->where('accepted', 1)->where('agent_id',\Illuminate\Support\Facades\Auth::User()->id);
        }else{
            $investments = InvestmentInvestor::where('project_id', $project_id)->get();
            $acceptedRegistries = InvestmentInvestor::where('project_id', $project_id)->where('accepted', 1);
        }
        $transactions = Transaction::where('project_id', $project_id)->get();
        $positions = Position::where('project_id', $project_id)->orderBy('effective_date', 'DESC')->get()->groupby('user_id');
        $projectsInterests = ProjectInterest::where('project_id', $project_id)->orderBy('created_at', 'DESC')->get();
        $projectsEois = ProjectEOI::where('project_id', $project_id)->orderBy('created_at', 'DESC')->get();
        $shareInvestments = $acceptedRegistries->orderBy('share_certificate_issued_at','ASC')->get();
        $newRegistries = ModelHelper::getTotalInvestmentByProject($project_id);
        // dd($positions);
        // dd($shareInvestments->last()->investingJoint);
        return view('dashboard.projects.investors', compact('project', 'investments','color', 'shareInvestments', 'transactions', 'positions', 'projectsInterests', 'projectsEois', 'newRegistries', 'projects'));
    }

    public function editProject($project_id)
    {
        $color = Color::where('project_site',url())->first();
        $projects = Project::all();
        $project = Project::findOrFail($project_id);
        $masterChild = Project::where('master_child',0)->where('id','!=',$project->id)->where('project_site',url())->get();
        if($project->project_site != url()){
            return redirect()->route('dashboard.projects')->withMessage('<p class="alert alert-warning text-center">Access Denied</p>');
        }
        $investments = InvestmentInvestor::where('project_id', $project_id)->get();
        if($project->is_coming_soon || $project->eoi_button == '1' || $project->is_funding_closed == '1'){
            $project->active = 1;
            $project->save();
        }
        if($project->eoi_button == '0' && $project->is_coming_soon == '0' && !$project->projectspvdetail) {
            $project->active = 0;
            $project->save();
        }

        return view('dashboard.projects.edit', compact('project', 'investments','color','masterChild', 'projects'));
    }

    public function activateUser($user_id)
    {
        $user = User::findOrFail($user_id);
        $status = $user->update(['active'=> 1, 'activated_on'=>Carbon::now()]);
        return redirect()->back();
    }

    public function deactivateUser($user_id)
    {
        $user = User::findOrFail($user_id);
        $status = $user->update(['active'=> 0, 'activated_on'=>Carbon::now()]);
        return redirect()->back();
    }
    public function idDocVerification($user_id)
    {
        $color = Color::where('project_site',url())->first();
        $user = User::findOrFail($user_id);
        return view('dashboard.users.idDocVerification',compact('user','color'));
    }
    public function idDocVerify(Request $request,AppMailer $mailer, $user_id)
    {
        $user = User::findOrFail($user_id);
        $user->idDoc->update(['verified'=>$request->status]);
        $user->idDoc()->get()->last()->update(['verified'=>$request->status, 'id_comment'=>$request->fixing_message, 'joint_id_comment'=>$request->fixing_message_for_id]);
        $idimages = $user->idDoc()->get()->last();
        if(\App\Helpers\SiteConfigurationHelper::getConfigurationAttr()->user_sign_up_konkrete && \App\Helpers\SiteConfigurationHelper::getConfigurationAttr()->show_powered_by_estatebaron) {
            $kyc_approval_konkrete = \App\Helpers\SiteConfigurationHelper::getConfigurationAttr()->kyc_approval_konkrete;
        }
        else {
            $kyc_approval_konkrete = 0;
        };

        if($request->status == '1') {
            $invitee = Invite::whereEmail($user->email)->first();
            if($invitee) {
                Credit::create(['user_id'=>$invitee->user_id, 'invite_id'=>$invitee->id, 'amount'=>$kyc_approval_konkrete, 'type'=>'KYC Confirmed by Admin', 'currency'=>'konkrete', 'project_site' => url()]);
            }

            $refRel = ReferralRelationship::where('user_id',$user->id)->get()->first();
            if($refRel){
                $refLink = ReferralLink::find($refRel->referral_link_id);
                $refUser = User::find($refLink->user_id);
                $credit = Credit::create(['user_id'=>$refUser->id, 'amount'=>$kyc_approval_konkrete, 'type'=>'KYC Verfied of '.$user->first_name.' '.$user->last_name, 'currency'=>'konkrete', 'project_site' => url()]);
            }
            $credit = Credit::create(['user_id'=>$user->id, 'amount'=>$kyc_approval_konkrete, 'type'=>'KYC Verification successful', 'currency'=>'konkrete', 'project_site' => url()]);
            $message = '<p class="alert alert-success text-center">User has been verified successfully and a notification has been sent.</p>';
        } else {
            $message = '<p class="alert alert-warning text-center">User has to try again.</p>';
        }
        $mailer->sendVerificationNotificationToUser($user, $request->status, $idimages);
        return redirect()->back()->withMessage($message);
    }
    public function verification($user_id)
    {
        $color = Color::where('project_site',url())->first();
        $user = User::findOrFail($user_id);
        return view('dashboard.users.verification', compact('user','color'));
    }

    public function verifyId(Request $request, AppMailer $mailer, $user_id)
    {
        $user = User::findOrFail($user_id);
        $user->update(['verify_id'=>$request->status]);
        $user->idImage()->get()->last()->update(['verify_id'=>$request->status, 'fixing_message'=>$request->fixing_message, 'fixing_message_for_id'=>$request->fixing_message_for_id]);
        $idimages = $user->idImage()->get()->last();
        if($request->status == '2') {
            $invitee = Invite::whereEmail($user->email)->first();
            if(\App\Helpers\SiteConfigurationHelper::getConfigurationAttr()->show_powered_by_estatebaron){
                $refBonus = 50;
            }else{
                $refBonus = 0;
            }
            if($invitee) {
                Credit::create(['user_id'=>$invitee->user_id, 'invite_id'=>$invitee->id, 'amount'=>$refBonus, 'type'=>'User Confirmed by Admin', 'project_site' => url()]);
            }
            $message = '<p class="alert alert-success text-center">User has been verified successfully and a notification has been sent.</p>';
        } else {
            $message = '<p class="alert alert-warning text-center">User has to try again.</p>';
        }
        $mailer->sendVerificationNotificationToUser($user, $request->status, $idimages);
        return redirect()->back()->withMessage($message);
    }

    public function privateProject($project_id)
    {
        $project = Project::findOrFail($project_id);
        $status = $project->update(['active'=> 2, 'activated_on'=>Carbon::now()]);
        return redirect()->back();
    }

    public function toggleStatus(Request $request, $project_id)
    {
        $project = Project::findOrFail($project_id);
        $status = $project->update(['active'=> $request->active, 'activated_on'=>Carbon::now()]);
        return redirect()->back();
    }

    public function updateSharePrice(Request $request, $project_id)
    {
        $validator = $this->validate($request, array(
            'update_share_price' => 'required',
        ));
        $project = Project::find($project_id);
        
        if($project->master_child){
            return redirect()->back()->withMessage('<p class="alert alert-danger text-center">Master Fund. Cannot allowed to change the share price .</p>');;
        }

        $project->update([
            'share_per_unit_price'=>$request->update_share_price,
        ]);

        $sharePrice = Price::where('project_id', $project_id)->whereDate('created_at', '=', Carbon::today()->format('Y-m-d'))->first();
        if(!$sharePrice)
        {
            $sharePrice = new Price;
            $sharePrice->project_id = $project_id;
            $sharePrice->price = $request->update_share_price;
            $sharePrice->effective_date = Carbon::now()->toDateTimeString();
            $sharePrice->save();
        }
        else 
        {
            $sharePrice->update(['price'=>$request->update_share_price]);
        }

        if($project->isChild){
            $master = Project::find($project->isChild->master);
            $masterSharePriceInit = 0;
            if($master->master_child){
                foreach($master->children as $child){

                    $masterSharePriceInit = $masterSharePriceInit + Project::find($child->child)->share_per_unit_price * $child->allocation/100;
                }
                $master->update([
                    'share_per_unit_price'=>$masterSharePriceInit,
                ]);
            }
            $mastersharePrice = Price::where('project_id', $master->id)->whereDate('created_at', '=', Carbon::today()->format('Y-m-d'))->first();
            if(!$mastersharePrice)
            {
                $mastersharePrice = new Price;
                $mastersharePrice->project_id = $master->id;
                $mastersharePrice->price = $master->share_per_unit_price;
                $mastersharePrice->effective_date = Carbon::now()->toDateTimeString();
                $mastersharePrice->save();
            }
            else 
            {
                $mastersharePrice->update(['price'=>$master->share_per_unit_price]);
            }
        }

        return redirect()->back()->withMessage('<p class="alert alert-success text-center">Share price updated successfully.</p>');
    }

    public function updateInvestment(Request $request, $investment_id)
    {
        $this->validate($request, [
            'investor' => 'required',
            'amount' => 'required|numeric',
        ]);

        $investment = InvestmentInvestor::findOrFail($investment_id);
        // $user = $investment->user;
        // $project = Project::findOrFail($investment->project_id);
        $investment->amount = $request->amount;
        // $pdfBasePath = '/app/application/application-'.$investment->id.'-'.time().'.pdf';
        // $pdfPath = storage_path().$pdfBasePath;
        // $pdf = PDF::loadView('pdf.application', ['project' => $project, 'investment' => $investment, 'user' => $user]);
        // $pdf->save($pdfPath);
        // $investment->application_path = $pdfBasePath;
        $investment->save();

        return redirect()->back()->withMessage('<p class="alert alert-success text-center">Successfully updated.</p>');

    }

    public function acceptInvestment(Request $request, AppMailer $mailer, $investment_id)
    {
        $this->validate($request, [
            'investor' => 'required',
        ]);
        $transactions = collect();
        $investment = InvestmentInvestor::findOrFail($investment_id);
        if($investment){
            //Update current investment and with the share certificate details
            $investment->accepted = 1;
            $investment->money_received = 1;
            $investment->share_certificate_issued_at = Carbon::now();
            if($investment->project->share_vs_unit){
                $investment->share_certificate_path = "/app/invoices/Share-Certificate-".$investment->id.".pdf";
            }else{
                $investment->share_certificate_path = "/app/invoices/Share-Certificate-".$investment->id.".pdf";
            }
            $investment->save();
             // Save details to transaction table
            $masterTransaction = Transaction::create([
                'user_id' => $investment->user_id,
                'project_id' => $investment->project_id,
                'transaction_type' => Transaction::BUY,
                'transaction_date' => Carbon::now(),
                'amount' => round($investment->amount * $investment->buy_rate,2),
                'rate' => round($investment->buy_rate,4),
                'number_of_shares' => $investment->amount,
            ]);

            if($investment->project->master_child){
                $masterTransaction->transaction_description = 'Master Application';
                $masterTransaction->save();
                $transactions->push($masterTransaction);
                $childInvestments = InvestmentInvestor::where('master_investment',$investment->id)->get();
                foreach($childInvestments as $child){
                    //Update current Investment and with the share certificate details
                    $child->accepted = 1;
                    $child->money_received = 1;
                    $child->share_certificate_issued_at = Carbon::now();
                    if($child->project->share_vs_unit){
                        $child->share_certificate_path = "/app/invoices/Share-Certificate-".$child->id.".pdf";
                    }else{
                        $child->share_certificate_path = "/app/invoices/Share-Certificate-".$child->id.".pdf";
                    }
                    $child->save();
                    $childTransaction = Transaction::create([
                        'user_id' => $child->user_id,
                        'project_id' => $child->project_id,
                        'transaction_type' => Transaction::BUY,
                        'transaction_date' => Carbon::now(),
                        'amount' => round($child->amount * $child->buy_rate,2),
                        'rate' => round($child->buy_rate,4),
                        'number_of_shares' => $child->amount,
                    ]);
                    $childTransaction->transaction_description = 'Child Application';
                    $childTransaction->save();
                    $transactions->push($childTransaction);
                }
            }
            // dd($investment);

            $investing = InvestingJoint::where('investment_investor_id', $investment->id)->get()->last();
            if($investment->accepted) {
                // $pdf = PDF::loadView('pdf.invoice', ['investment' => $investment, 'shareInit' => $shareInit, 'investing' => $investing, 'shareStart' => $shareStart, 'shareEnd' => $shareEnd]);
                // $pdf->setPaper('a4', 'landscape');
                if($investment->project->share_vs_unit) {
                    // $pdf->save(storage_path().'/app/invoices/Share-Certificate-'.$investment->id.'.pdf');
                    $formLink = url().'/user/view/'.base64_encode($investment->id).'/share';
                }else {
                    // $pdf->save(storage_path().'/app/invoices/Unit-Certificate-'.$investment->id.'.pdf');
                    $formLink = url().'/user/view/'.base64_encode($investment->id).'/unit';
                }

                $mailer->sendInvoiceToUser($investment,$formLink,$transactions);
                $mailer->sendInvoiceToAdmin($investment,$formLink,$transactions);
            }
            return redirect()->back()->withMessage('<p class="alert alert-success text-center">Successfully updated.</p>');
        }
    }

    public function activateProject($project_id)
    {
        $project = Project::findOrFail($project_id);
        $status = $project->update(['active'=> 1, 'activated_on'=>Carbon::now()]);
        return redirect()->back();
    }

    public function deactivateProject($project_id)
    {
        $project = Project::findOrFail($project_id);
        $status = $project->update(['active'=> 0, 'activated_on'=>Carbon::now()]);
        return redirect()->back();
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  Request  $request
     * @return Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function edit($user_id)
    {
        $color = Color::where('project_site',url())->first();
        $user = User::findOrFail($user_id);
        return view('dashboard.users.edit', compact('user','color'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  Request  $request
     * @param  int  $id
     * @return Response
     */
    public function update(Request $request, $id)
    {
        dd($request);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function destroy($id)
    {
        //
    }

    public function siteConfigurations()
    {
        $color = Color::where('project_site',url())->first();
        $siteconfiguration = SiteConfiguration::where('project_site',url())->first();
        $mail_setting = $siteconfiguration->mailSetting;
        $siteConfigurationHelper = SiteConfigurationHelper::getConfigurationAttr();
        $customFields = CustomField::where('site_url', url())->get();
        $sections = ApplicationSections::where('site_url', url())->orderBy('rank', 'asc')->get();
        
        return view('dashboard.configuration.siteConfiguration',compact('color','siteconfiguration','mail_setting', 'siteConfigurationHelper', 'customFields','sections'));
    }

    public function investmentMoneyReceived(Request $request, AppMailer $mailer, $investment_id)
    {
        $investment = InvestmentInvestor::findOrFail($investment_id);
        if($investment->project->master_child){
            $childInvestments = InvestmentInvestor::where('master_investment',$investment->id)->get();
            foreach($childInvestments as $child){
                $child->money_received = 1;
                $child->save();
            }
        }
        $investment->money_received = 1;
        $investment->save();

        if($investment->money_received) {
            $mailer->sendMoneyReceivedConfirmationToUser($investment);
        }

        return redirect()->back()->withMessage('<p class="alert alert-success text-center">Successfully updated.</p>');
    }

    public function hideInvestment(Request $request)
    {
        if ($request->ajax()) {
            $investment = InvestmentInvestor::findOrFail($request->investment_id);
            $investment->hide_investment = 1;
            $investment->save();
            return 1;
        }
    }

    public function hideProject(Request $request)
    {
        if ($request->ajax()) {
            $project = Project::findOrFail($request->project_id);
            $project->hide_project = 1;
            $project->active = 0;
            $project->is_coming_soon = 0;
            $project->is_funding_closed = 0;
            $project->eoi_button = 0;
            $project->save();
            return 1;
        }
    }

    public function hideApplicationFillupRequest(Request $request)
    {
        if ($request->ajax()) {
            $application_request = InvestmentRequest::findOrFail($request->application_request_id);
            $application_request->delete();
            return 1;
        }
    }

    public function investmentReminder(AppMailer $mailer, $investment_id){
        $investment = InvestmentInvestor::findOrFail($investment_id);
        $mailer->sendInvestmentReminderToUser($investment);
        Session::flash('action', $investment->id);
        return redirect()->back()->withMessage('<p class="alert alert-success text-center">Reminder sent</p>');
    }

    public function investmentConfirmation(Request $request, AppMailer $mailer, $investment_id){
        $investment = InvestmentInvestor::findOrFail($investment_id);
        $investment->investment_confirmation = $request->investment_confirmation;
        $investment->save();
        $mailer->sendInvestmentConfirmationToUser($investment);
        return redirect()->back()->withMessage('<p class="alert alert-success text-center">Investment Successfully Confirmed</p>');
    }

    public function createBroadcastMailForm(){
        $color = Color::where('project_site',url())->first();
        $siteUsers = User::where('registration_site', url())->get();
        $siteconfiguration = SiteConfiguration::where('project_site',url())->first();
        return view('dashboard.broadcast.broadcastmail',compact('color','siteconfiguration', 'siteUsers'));
    }

    public function sendBroadcastMail(Request $request)
    {
        $this->validate($request, [
            'mail_subject' => 'required',
        ]);

        $subject = $request->mail_subject;
        $content = $request->mail_content;
        // $emailStr = $request->email_string;
        $users = User::where('registration_site', url())->get();
        $sendgridApiKey = \Config::get('services.sendgrid_key');
        $siteConfiguration = SiteConfiguration::where('project_site', url())->first();
        $mailSettings = $siteConfiguration->mailSetting()->first();
        if($siteConfiguration)
            $sendgridApiKey = $siteConfiguration->sendgrid_api_key ? $siteConfiguration->sendgrid_api_key : $sendgridApiKey;
        $setupEmail = isset($mailSettings->from) ? $mailSettings->from : (\Config::get('mail.from.address'));
        $setupEmailName = $siteConfiguration->title_text ? $siteConfiguration->title_text : (\Config::get('mail.from.name'));

        if($setupEmail == "info@konkrete.io") {
            Session::flash('message', '<div class="alert alert-danger text-center">Please setup your email configurations first. <br>You can do that from <b><a href="'.route('dashboard.configurations').'">Configurations tab</a> > Mailer Email</b>.</div>');
            return redirect()->back();
        }

        $email = new SendgridMail();
        $email->setFrom($setupEmail, $setupEmailName);
        $email->setSubject($subject);
        $email->addTo($setupEmail);
        foreach ($users as $userEmailId) {
            $email->addPersonalization(['to' => [['email' => $userEmailId->email]]]);
        }

        $email->addContent(
            "text/html", $content
        );

        $sendgrid = new \SendGrid($sendgridApiKey);
        try {
            $response = $sendgrid->send($email);
        } catch (Exception $e) {
            throw new Exception("Error Processing Request", 1);
        }

        Session::flash('message', '<div class="row text-center" style="padding: 12px;border-radius: 8px;background-color: #EEFBF3;">Emails Queued Successfully</div>');

        return redirect()->back();
    }

    public function investmentCancel($investment_id, AppMailer $mailer)
    {
        $investment = InvestmentInvestor::findOrFail($investment_id);
        $investment->is_cancelled = 1;
        $investment->save();

        $shareInit = 0;
        if($investment->share_number){
            $shareNumber = explode('-', $investment->share_number);
            $shareInit = $shareNumber[0]-1;
        }
        $shareStart = $shareInit+1;
        $shareEnd = $shareInit+(int)$investment->amount;
        $shareCount = (string)($shareStart)."-".(string)($shareEnd);

        // Save details to transaction table
        $noOfShares = $shareEnd-$shareInit;
        if($noOfShares == 0){
            $transactionRate = $investment->amount/1;
        }
        else{
            $transactionRate = $investment->amount/$noOfShares;
        }
        // dd($transactionRate);
        Transaction::create([
            'user_id' => $investment->user_id,
            'project_id' => $investment->project_id,
            'transaction_type' => Transaction::CANCELLED,
            'transaction_date' => Carbon::now(),
            'amount' => round($investment->amount * $investment->buy_rate, 2),
            'rate' => round($investment->buy_rate, 4),
            'number_of_shares' => $investment->amount,
        ]);

        $investing = InvestingJoint::where('investment_investor_id', $investment->id)->get()->last();

        $mailer->sendInvestmentCancellationConfirmationToUser($investment, $shareInit, $investing, $shareStart, $shareEnd);

        return redirect()->back()->withMessage('<p class="alert alert-success text-center">Investment Successfully Cancelled</p>');
    }

    public function declareDividend(Request $request, AppMailer $mailer, $projectId)
    {
        if(!$request->start_date || !$request->end_date){
            return redirect()->back()->withMessage('<p class="alert alert-danger text-center">Provide valid start date and end date</p>');
        }
        $investorList = $request->investors_list;
        $dividendPercent = $request->dividend_percent;
        $strStartDate = (string)$request->start_date;
        $startDate = date_create_from_format('d/m/Y', (string)$request->start_date);
        $strEndDate = (string)$request->end_date;
        $endDate = date_create_from_format('d/m/Y', (string)$request->end_date);
        $dateDiff = date_diff($startDate, $endDate);
        $dateDiff = (int)$dateDiff->format("%R%a") + 1;
        $project = Project::findOrFail($projectId);
        $startDateFormatted = strtr($request->start_date, '/', '-');
        $endDateFormatted = strtr($request->end_date, '/', '-');

        if($investorList != ''){
            if($dateDiff >=0){
                $investors = explode(',', $investorList);
                // $investments = InvestmentInvestor::findMany($investors);
                $investments = ModelHelper::getTotalInvestmentByUsersAndProject($investors, $projectId);
                // Add the records to project progress table
                // ProjectProg::create([
                //     'project_id' => $projectId,
                //     'updated_date' => Carbon::now(),
                //     'progress_description' => 'Dividend Declaration',
                //     'progress_details' => 'A Dividend of '.$dividendPercent.'% annualized for the duration between '.date('m-d-Y', strtotime($startDateFormatted)).' and '.date('m-d-Y', strtotime($endDateFormatted)).' has been declared.'
                // ]);

                // send dividend email to admins
                $csvPath = $this->exportDividendCSV($investments, $dividendPercent, $dateDiff, $project);
                $mailer->sendDividendDistributionNotificationToAdmin($investments, $dividendPercent, $dateDiff, $csvPath, $project);

                // send dividend emails to investors
                $sendgridPersonalization = [];
                $subject = 'Dividend declared for '.$project->title;
                foreach ($investments as $investment) {
                    // Save details to transaction table
                    $dividendAmount = round($investment->amount * ((int)$dividendPercent/(365*100)) * $dateDiff, 2);
                    $shareNumber = explode('-', $investment->share_number);
                    $noOfShares = $investment->shares;
                    Transaction::create([
                        'user_id' => $investment->user_id,
                        'project_id' => $investment->project_id,
                        'transaction_type' => Transaction::ANNUALIZED_DIVIDEND,
                        'transaction_description' => $dividendPercent.'% ANNUALIZED DIVIDEND BETWEEN '.$request->start_date.' and '.$request->end_date,
                        'transaction_date' => Carbon::now(),
                        'amount' => $dividendAmount,
                        'rate' => $dividendPercent,
                        'number_of_shares' => $noOfShares,
                    ]);

                    $prospectusText = 'Prospectus';
                    if($project->project_prospectus_text != '') {
                        $prospectusText = $project->project_prospectus_text;
                    } else if (SiteConfigurationHelper::getConfigurationAttr()->prospectus_text) {
                        $prospectusText = SiteConfigurationHelper::getConfigurationAttr()->prospectus_text;
                    } else {}

                    array_push(
                        $sendgridPersonalization,
                        [
                            'to' => [[ 'email' => $investment->user->email ]],
                            'substitutions' => [
                                '%first_name%' => $investment->user->first_name,
                                '%dividend_percent%' =>$dividendPercent,
                                '%start_date%' => $strStartDate,
                                '%end_date%' => $strEndDate,
                                '%project_title%' => $project->title,
                                '%share_type%' => $project->share_vs_unit ? 'shareholder' : 'unitholder',
                                '%account_name%' => $investment->investingJoint ? $investment->investingJoint->account_name : $investment->user->account_name,
                                '%bank_name%' => $investment->investingJoint ? $investment->investingJoint->bank_name : $investment->user->bank_name,
                                '%user_bsb%' => $investment->investingJoint ? $investment->investingJoint->bsb : $investment->user->bsb,
                                '%account_number%' => $investment->investingJoint ? $investment->investingJoint->account_number : $investment->user->account_number,
                                '%spv_email%' => $project->projectspvdetail ? $project->projectspvdetail->spv_email : 'info@konkrete.iotatebaron.com',
                                '%spv_md_name%' => $project->projectspvdetail ? $project->projectspvdetail->spv_md_name : '',
                                '%spv_name%' => $project->projectspvdetail ? $project->projectspvdetail->spv_name : 'Estate Baron Team',
                                '%project_prospectus_text%' => $prospectusText
                            ]
                        ]
                    );

                }

                // Send bulk emails through sendgrid API
                $resultBulkEmail = BulkEmailHelper::sendBulkEmail(
                    $subject,
                    $sendgridPersonalization,
                    view('emails.sendgrid-api-specific.userDividendDistributioNotify')->render()
                );

                if(!$resultBulkEmail['status']) {
                    return redirect()->back()->withMessage('<p class="alert alert-danger text-center">' . $resultBulkEmail['message'] . '</p>');
                }

                return redirect('/dashboard/projects/'.$project->id.'/investors#new_registry')->withMessage('<p class="alert alert-success text-center">Dividend distribution have been mailed to Investors and admins</p>');
            }
            else {
                return redirect()->back()->withMessage('<p class="alert alert-danger text-center">End date must be greater than start date.</p>');
            }
        }
    }

    public function declareFixedDividend(Request $request, AppMailer $mailer, $projectId)
    {
        $investorList = $request->investors_list;
        $dividendPercent = $request->fixed_dividend_percent;
        $project = Project::findOrFail($projectId);

        if($investorList != ''){
            $investors = explode(',', $investorList);
            
            $investments = ModelHelper::getTotalInvestmentByUsersAndProject($investors, $projectId);

            // Add the records to project progress table
            // ProjectProg::create([
            //     'project_id' => $projectId,
            //     'updated_date' => Carbon::now(),
            //     'progress_description' => 'Fixed Dividend Declaration',
            //     'progress_details' => 'A Fixed Dividend of '.$dividendPercent.'% has been declared.'
            // ]);

            // send dividend email to admins
            $csvPath = $this->exportFixedDividendCSV($investments, $dividendPercent, $project);
            $mailer->sendFixedDividendDistributionNotificationToAdmin($investments, $dividendPercent, $csvPath, $project);

            // send dividend emails to investors
            $sendgridPersonalization = [];
            $subject = 'Dividend declared for '.$project->title;
            foreach ($investments as $investment) {
                // Save details to transaction table
                $dividendAmount = round(($investment->shares * (float)$dividendPercent)/100);
                $shareNumber = explode('-', $investment->share_number);
                $noOfShares = $investment->shares;
                Transaction::create([
                    'user_id' => $investment->user_id,
                    'project_id' => $investment->project_id,
                    'transaction_type' => Transaction::DIVIDEND,
                    'transaction_description'=> $dividendPercent . ' CENTS PER SHARE DIVIDEND',
                    'transaction_date' => Carbon::now(),
                    'amount' => $dividendAmount,
                    'rate' => $dividendPercent,
                    'number_of_shares' => $noOfShares,
                ]);

                $prospectusText = 'Prospectus';
                if($project->project_prospectus_text != '') {
                    $prospectusText = $project->project_prospectus_text;
                } else if (SiteConfigurationHelper::getConfigurationAttr()->prospectus_text) {
                    $prospectusText = SiteConfigurationHelper::getConfigurationAttr()->prospectus_text;
                } else {}

                array_push(
                    $sendgridPersonalization,
                    [
                        'to' => [[ 'email' => $investment->user->email ]],
                        'substitutions' => [
                            '%first_name%' => $investment->user->first_name,
                            '%dividend_percent%' =>$dividendPercent,
                            '%project_title%' => $project->title,
                            '%share_type%' => $project->share_vs_unit ? 'shareholder' : 'unitholder',
                            '%account_name%' => $investment->investingJoint ? $investment->investingJoint->account_name : $investment->user->account_name,
                            '%bank_name%' => $investment->investingJoint ? $investment->investingJoint->bank_name : $investment->user->bank_name,
                            '%user_bsb%' => $investment->investingJoint ? $investment->investingJoint->bsb : $investment->user->bsb,
                            '%account_number%' => $investment->investingJoint ? $investment->investingJoint->account_number : $investment->user->account_number,
                            '%spv_email%' => $project->projectspvdetail ? $project->projectspvdetail->spv_email : 'info@konkrete.iotatebaron.com',
                            '%spv_md_name%' => $project->projectspvdetail ? $project->projectspvdetail->spv_md_name : '',
                            '%spv_name%' => $project->projectspvdetail ? $project->projectspvdetail->spv_name : 'Estate Baron Team',
                            '%project_prospectus_text%' => $prospectusText,
                            '%project_share_or_unit%' => $project->share_vs_unit ? 'share' : 'unit'
                        ]
                    ]
                );
            }

            // Send bulk emails through sendgrid API
            $resultBulkEmail = BulkEmailHelper::sendBulkEmail(
                $subject,
                $sendgridPersonalization,
                view('emails.sendgrid-api-specific.userFixedDividendDistributioNotify')->render()
            );

            if(!$resultBulkEmail['status']) {
                return redirect()->back()->withMessage('<p class="alert alert-danger text-center">' . $resultBulkEmail['message'] . '</p>');
            }

            return redirect('/dashboard/projects/'.$project->id.'/investors#new_registry')->withMessage('<p class="alert alert-success text-center">Cents Per Share Dividend distribution have been mailed to Investors and admins</p>');

            //     $content = \View::make('emails.userFixedDividendDistributioNotify', array('investment' => $investment, 'dividendPercent' => $dividendPercent, 'project' => $project));
            //     $result = $this->queueEmailsUsingMailgun($investment->user->email, $subject, $content->render());
            //     if($result->http_response_code != 200){
            //         array_push($failedEmails, $investment->user->email);
            //     }
            // }
            // if(empty($failedEmails)){
            //     return redirect()->back()->withMessage('<p class="alert alert-success text-center">Fixed Dividend distribution have been mailed to Investors and admins</p>');
            // }
            // else{
            //     $emails = '';
            //     foreach ($failedEmails as $email) {
            //         $emails = $emails.", $email";
            //     }
            //     return redirect()->back()->withMessage('<p class="alert alert-danger text-center">Fixed Dividend distribution email sending failed for investors - '.$emails.'.</p>');
            // }
        }
    }

    public function declareCentsPerShareDividend(Request $request, AppMailer $mailer, $projectId)
    {
        $investorList = $request->investors_list;
        $dividendPercent = $request->cents_per_share_dividend;
        $project = Project::findOrFail($projectId);

        if($investorList != ''){
            $investors = explode(',', $investorList);
            
            $investments = ModelHelper::getTotalInvestmentByUsersAndProject($investors, $projectId);

            // Add the records to project progress table
            // ProjectProg::create([
            //     'project_id' => $projectId,
            //     'updated_date' => Carbon::now(),
            //     'progress_description' => 'Fixed Dividend Declaration',
            //     'progress_details' => 'A Fixed Dividend of '.$dividendPercent.'% has been declared.'
            // ]);

            // send dividend email to admins
            $csvPath = $this->exportFixedDividendCSV($investments, $dividendPercent, $project);
            $mailer->sendCentsPerShareDividendDistributionNotificationToAdmin($investments, $dividendPercent, $csvPath, $project);

            // send dividend emails to investors
            $sendgridPersonalization = [];
            $subject = 'Dividend declared for '.$project->title;
            foreach ($investments as $investment) {
                // Save details to transaction table
                $dividendAmount = round(($investment->shares * $project->share_per_unit_price * (int)$dividendPercent/100), 2);
                $shareNumber = explode('-', $investment->share_number);
                $noOfShares = $investment->shares;
                Transaction::create([
                    'user_id' => $investment->user_id,
                    'project_id' => $investment->project_id,
                    'transaction_type' => Transaction::FIXED_DIVIDEND,
                    'transaction_description' => $dividendPercent . '% FIXED DIVIDEND',
                    'transaction_date' => Carbon::now(),
                    'amount' => $dividendAmount,
                    'rate' => $dividendPercent,
                    'number_of_shares' => $noOfShares
                ]);

                $prospectusText = 'Prospectus';
                if($project->project_prospectus_text != '') {
                    $prospectusText = $project->project_prospectus_text;
                } else if (SiteConfigurationHelper::getConfigurationAttr()->prospectus_text) {
                    $prospectusText = SiteConfigurationHelper::getConfigurationAttr()->prospectus_text;
                } else {}

                array_push(
                    $sendgridPersonalization,
                    [
                        'to' => [[ 'email' => $investment->user->email ]],
                        'substitutions' => [
                            '%first_name%' => $investment->user->first_name,
                            '%dividend_percent%' =>$dividendPercent,
                            '%project_title%' => $project->title,
                            '%share_type%' => $project->share_vs_unit ? 'shareholder' : 'unitholder',
                            '%account_name%' => $investment->investingJoint ? $investment->investingJoint->account_name : $investment->user->account_name,
                            '%bank_name%' => $investment->investingJoint ? $investment->investingJoint->bank_name : $investment->user->bank_name,
                            '%user_bsb%' => $investment->investingJoint ? $investment->investingJoint->bsb : $investment->user->bsb,
                            '%account_number%' => $investment->investingJoint ? $investment->investingJoint->account_number : $investment->user->account_number,
                            '%spv_email%' => $project->projectspvdetail ? $project->projectspvdetail->spv_email : 'info@estatebaron.com',
                            '%spv_md_name%' => $project->projectspvdetail ? $project->projectspvdetail->spv_md_name : '',
                            '%spv_name%' => $project->projectspvdetail ? $project->projectspvdetail->spv_name : 'Estate Baron Team',
                            '%project_prospectus_text%' => $prospectusText,
                            '%project_share_or_unit%' => $project->share_vs_unit ? 'share' : 'unit'
                        ]
                    ]
                );
            }

            // Send bulk emails through sendgrid API
            $resultBulkEmail = BulkEmailHelper::sendBulkEmail(
                $subject,
                $sendgridPersonalization,
                view('emails.sendgrid-api-specific.userCentsPerShareDividendDistributioNotify')->render()
            );

            if(!$resultBulkEmail['status']) {
                return redirect()->back()->withMessage('<p class="alert alert-danger text-center">' . $resultBulkEmail['message'] . '</p>');
            }

            return redirect('/dashboard/projects/'.$project->id.'/investors#new_registry')->withMessage('<p class="alert alert-success text-center">Fixed Dividend distribution have been mailed to Investors and admins</p>');

            //     $content = \View::make('emails.userFixedDividendDistributioNotify', array('investment' => $investment, 'dividendPercent' => $dividendPercent, 'project' => $project));
            //     $result = $this->queueEmailsUsingMailgun($investment->user->email, $subject, $content->render());
            //     if($result->http_response_code != 200){
            //         array_push($failedEmails, $investment->user->email);
            //     }
            // }
            // if(empty($failedEmails)){
            //     return redirect()->back()->withMessage('<p class="alert alert-success text-center">Fixed Dividend distribution have been mailed to Investors and admins</p>');
            // }
            // else{
            //     $emails = '';
            //     foreach ($failedEmails as $email) {
            //         $emails = $emails.", $email";
            //     }
            //     return redirect()->back()->withMessage('<p class="alert alert-danger text-center">Fixed Dividend distribution email sending failed for investors - '.$emails.'.</p>');
            // }
        }
    }

    public function declareRepurchase(Request $request, AppMailer $mailer, $projectId){
        $investorList = $request->investors_list;
        $repurchaseRate = $request->repurchase_rate;
        $project = Project::findOrFail($projectId);

        if($investorList != ''){
            $investors = explode(',', $investorList);
            $investments = InvestmentInvestor::findMany($investors);

            // Add the records to project progress table
            if($project->share_vs_unit) {
                // ProjectProg::create([
                //     'project_id' => $projectId,
                //     'updated_date' => Carbon::now(),
                //     'progress_description' => 'Repurchase Declaration',
                //     'progress_details' => 'Shares Repurchased by company at $'.$repurchaseRate.' per share.'
                // ]);
            }else {
                // ProjectProg::create([
                //     'project_id' => $projectId,
                //     'updated_date' => Carbon::now(),
                //     'progress_description' => 'Repurchase Declaration',
                //     'progress_details' => 'Units Repurchased by company at $'.$repurchaseRate.' per unit.'
                // ]);
            }

            // send dividend email to admins
            $csvPath = $this->exportRepurchaseCSV($investments, $repurchaseRate, $project);
            $mailer->sendRepurchaseNotificationToAdmin($investments, $repurchaseRate, $csvPath, $project);

            // send dividend emails to investors
            $failedEmails = [];
            if($project->share_vs_unit) {
                $subject = 'Shares for '.$project->title;
            }else {
                $subject = 'Units for '.$project->title;
            }
            foreach ($investments as $investment) {
                InvestmentInvestor::where('id', $investment->id)->update([
                    'is_cancelled' => 1,
                    'is_repurchased' => 1
                ]);

                // Save details to transaction table
                $repurchaseAmount = round(($investment->amount * $repurchaseRate), 2);
                $shareNumber = explode('-', $investment->share_number);
                $noOfShares = $shareNumber[1]-$shareNumber[0]+1;
                Transaction::create([
                    'user_id' => $investment->user_id,
                    'project_id' => $investment->project_id,
                    'transaction_type' => 'REPURCHASE',
                    'transaction_date' => Carbon::now(),
                    'amount' => $repurchaseAmount,
                    'rate' => $repurchaseRate,
                    'number_of_shares' => $noOfShares,
                ]);

                $shareNumber = explode('-', $investment->share_number);
                $content = \View::make('emails.userRepurchaseNotify', array('investment' => $investment, 'repurchaseRate' => $repurchaseRate, 'project' => $project, 'shareNumber' => $shareNumber));
                $result = $this->queueEmailsUsingMailgun($investment->user->email, $subject, $content->render());
                if($result->http_response_code != 200){
                    array_push($failedEmails, $investment->user->email);
                }
            }
            if(empty($failedEmails)){
                return redirect()->back()->withMessage('<p class="alert alert-success text-center">Repurchase distribution have been mailed to Investors and admins</p>');
            }
            else{
                $emails = '';
                foreach ($failedEmails as $email) {
                    $emails = $emails.", $email";
                }
                return redirect()->back()->withMessage('<p class="alert alert-danger text-center">Repurchase distribution email sending failed for investors - '.$emails.'.</p>');
            }
        }
    }

    public function exportDividendCSV($investments, $dividendPercent, $dateDiff, $project)
    {
        $csvPath = storage_path().'/app/dividend/dividend_distribution_'.time().'.csv';

        // create a file pointer connected to the output stream
        $file = fopen($csvPath, 'w');

        // Add column names to csv
        if($project->share_vs_unit) {
            fputcsv($file, array("Investor Name", "Investor Bank account name", "Investor bank", "Investor BSB", "Investor Account", "Share amount", "Number of days", "Rate", "Investor Dividend amount"));
        }else {
            fputcsv($file, array("Investor Name", "Investor Bank account name", "Investor bank", "Investor BSB", "Investor Account", "Unit amount", "Number of days", "Rate", "Investor Dividend amount"));
        }

        // data to add to the csv file
        foreach ($investments as $investment) {
            fputcsv($file, array(
                $investment->user->first_name.' '.$investment->user->last_name,
                $investment->investingJoint ? $investment->investingJoint->account_name : $investment->user->account_name,
                $investment->investingJoint ? $investment->investingJoint->bank_name : $investment->user->bank_name,
                $investment->investingJoint ? $investment->investingJoint->bsb : $investment->user->bsb,
                $investment->investingJoint ? $investment->investingJoint->account_number : $investment->user->account_number,
                $investment->amount,
                $dateDiff,
                $dividendPercent,
                round($investment->amount * ((int)$dividendPercent/(365*100)) * $dateDiff, 2)
            ));
        }

        // Close the file
        fclose($file);

        return $csvPath;
    }

    public function exportFixedDividendCSV($investments, $dividendPercent, $project)
    {
        $csvPath = storage_path().'/app/dividend/fixed_dividend_distribution_'.time().'.csv';

        // create a file pointer connected to the output stream
        $file = fopen($csvPath, 'w');

        // Add column names to csv
        if($project->share_vs_unit) {
            fputcsv($file, array("Investor Name", "Investor Bank account name", "Investor bank", "Investor BSB", "Investor Account", "Share amount", "Rate", "Investor Dividend amount"));
        }else {
            fputcsv($file, array("Investor Name", "Investor Bank account name", "Investor bank", "Investor BSB", "Investor Account", "Unit amount", "Rate", "Investor Dividend amount"));
        }

        // data to add to the csv file
        foreach ($investments as $investment) {
            fputcsv($file, array(
                $investment->user->first_name.' '.$investment->user->last_name,
                $investment->investingJoint ? $investment->investingJoint->account_name : $investment->user->account_name,
                $investment->investingJoint ? $investment->investingJoint->bank_name : $investment->user->bank_name,
                $investment->investingJoint ? $investment->investingJoint->bsb : $investment->user->bsb,
                $investment->investingJoint ? $investment->investingJoint->account_number : $investment->user->account_number,
                $investment->shares,
                $dividendPercent,
                round($investment->shares * (int)$dividendPercent/100)
            ));
        }

        // Close the file
        fclose($file);

        return $csvPath;
    }

    public function exportRepurchaseCSV($investments, $repurchaseRate, $project){
        $csvPath = storage_path().'/app/repurchase/repurchase_distribution_'.time().'.csv';

        // create a file pointer connected to the output stream
        $file = fopen($csvPath, 'w');

        // Add column names to csv
        if($project->share_vs_unit) {
            fputcsv($file, array("Investor Name", "Investor Bank account name", "Investor bank", "Investor BSB", "Investor Account", "Share amount", "Repurchase Rate", "Investor Repurchase amount"));
        }else {
            fputcsv($file, array("Investor Name", "Investor Bank account name", "Investor bank", "Investor BSB", "Investor Account", "Unit amount", "Repurchase Rate", "Investor Repurchase amount"));
        }

        // data to add to the csv file
        foreach ($investments as $investment) {
            fputcsv($file, array(
                $investment->user->first_name.' '.$investment->user->last_name,
                $investment->investingJoint ? $investment->investingJoint->account_name : $investment->user->account_name,
                $investment->investingJoint ? $investment->investingJoint->bank_name : $investment->user->bank_name,
                $investment->investingJoint ? $investment->investingJoint->bsb : $investment->user->bsb,
                $investment->investingJoint ? $investment->investingJoint->account_number : $investment->user->account_number,
                $investment->amount,
                $repurchaseRate,
                round($investment->amount * $repurchaseRate, 2)
            ));
        }

        // Close the file
        fclose($file);

        return $csvPath;
    }

    public function queueEmailsUsingMailgun($emailStr, $subject, $content, $attachments = [])
    {
        $this->overrideMailerConfig();
        if(filter_var(\Config::get('mail.sendmail'), FILTER_VALIDATE_EMAIL)){
            $fromMail = \Config::get('mail.sendmail');
        } else{
            $fromMail = 'info@konkrete.io';
        }

        //Disable SSL Check
        $client = new \GuzzleHttp\Client([
            'verify' => false,
        ]);
        $adapter = new \Http\Adapter\Guzzle6\Client($client);

        # Instantiate the client.
        $mgClient = new Mailgun(env('MAILGUN_API_KEY'), $adapter);
        $domain = env('MAILGUN_DOMAIN');

        # Make the call to the client.
        $result = $mgClient->sendMessage($domain,
            array(
                'from'    => $fromMail,
                'to'      => $emailStr,
                // 'bcc'     => 'info@konkrete.iotatebaron.com',
                'subject' => $subject,
                'html'    => $content
            ),
            array(
                'attachment' => $attachments
            )
        );

        return $result;
    }

    public function investmentStatement($projectId)
    {
        $investmentRecords = InvestmentInvestor::where('project_id', $projectId)
        ->where(function ($query) { $query->where('is_cancelled', 0)->where('is_repurchased', 0); })
        ->get()->groupby('user_id');
        foreach ($investmentRecords as $userId => $investments) {
            $UserShares = 0;
            foreach ($investments as $key => $investment) {
                if($investment->accepted && $investment->share_number){
                    $shareNumber = explode('-', $investment->share_number);
                    $noOfShares = $shareNumber[1]-$shareNumber[0]+1;
                    $UserShares += $noOfShares;
                }
            }
            // dd($UserShares);
            Position::create([
                'user_id' => $userId,
                'project_id' => $projectId,
                'effective_date' => Carbon::now(),
                'number_of_shares' => $UserShares,
                'current_value' => $UserShares * 1
            ]);
        }
        return redirect()->back()->withMessage('<p class="alert alert-success text-center">Latest Investor Statement is successfully generated.<br>You can view it in Position records tab.</p>');
    }

    public function sendInvestmentStatement($projectId)
    {
        $positions = Position::where('project_id', $projectId)->orderBy('effective_date', 'DESC')->get()->groupby('user_id');
        $failedEmails = [];
        foreach ($positions as $userId => $value) {
            $position = $value->first();
            $transactions = Transaction::where('project_id', $projectId)->where('user_id', $userId)->orderBy('transaction_date', 'ASC')->get();
            $project = Project::where('id', $projectId)->first();

            // Create PDF of Investor Statement
            $pdfPath = storage_path().'/app/investorStatement/investor-statement-'.$userId.'-'.time().'.pdf';
            $pdf = PDF::loadView('pdf.investorStatement', ['project' => $project, 'position' => $position, 'transactions' => $transactions]);
            $pdf->setPaper('a4', 'portrait');
            $pdf->save($pdfPath);

            // Send Investor Statement mail to investors
            $projectName = $project->projectspvdetail ? $project->projectspvdetail->spv_name : $project->title;
            $subject = 'Investor statement for '.$position->user->first_name.' '.$position->user->last_name.' for '.$projectName;
            $content = \View::make('emails.investorStatement', array('project' => $project, 'position' => $position));
            $attachments = array($pdfPath);
            $result = $this->queueEmailsUsingMailgun($position->user->email, $subject, $content->render(), $attachments);
            if($result->http_response_code != 200){
                array_push($failedEmails, $position->user->email);
            }
        }
        if(empty($failedEmails)){
            return redirect()->back()->withMessage('<p class="alert alert-success text-center">Investor Statement have been successfully mailed to Investors</p>');
        }
        else{
            $emails = '';
            foreach ($failedEmails as $email) {
                $emails = $emails.", $email";
            }
            return redirect()->back()->withMessage('<p class="alert alert-danger text-center">Investor Statement email sending failed for investors - '.$emails.'.</p>');
        }
    }

    public function overrideMailerConfig()
    {
        $siteconfig = SiteConfigurationHelper::getConfigurationAttr();
        $config = $siteconfig->mailSetting()->first();
        if($config){
            // Config::set('mail.driver',$configs['driver']);
            \Config::set('mail.host',$config->host);
            \Config::set('mail.port',$config->port);
            \Config::set('mail.username',$config->username);
            \Config::set('mail.password',$config->password);
            \Config::set('mail.sendmail',$config->from);
            $app = \App::getInstance();
            $app['swift.transport'] = $app->share(function ($app) {
             return new TransportManager($app);
         });

            $mailer = new \Swift_Mailer($app['swift.transport']->driver());
            \Mail::setSwiftMailer($mailer);
        }
    }

    public function applicationForm($investment_id)
    {
        $investment = InvestmentInvestor::find($investment_id);
        // dd($investment);
        if($investment->application_path){
            $filename = $investment->application_path;
            $path = storage_path($filename);

            return \Response::make(file_get_contents($path), 200, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="'.$filename.'"'
            ]);
        }
        else {
            $project = Project::find($investment->project_id);
            $user = User::findOrFail($investment->user_id);
            // dd($user);

            // Create PDF of Application form
            $pdfBasePath = '/app/application/application-'.$investment->id.'-'.time().'.pdf';
            $pdfPath = storage_path().$pdfBasePath;
            $pdf = PDF::loadView('pdf.application', ['project' => $project, 'investment' => $investment, 'user' => $user]);
            $pdf->setPaper('a4', 'portrait');
            $pdf->setWarnings(false);
            $saveResult = $pdf->save($pdfPath);
            $investment->application_path = $pdfBasePath;
            $investment->save();

            if($saveResult){
                return \Response::make(file_get_contents($pdfPath), 200, [
                    'Content-Type' => 'application/pdf',
                    'Content-Disposition' => 'inline; filename="'.$pdfBasePath.'"'
                ]);
            }
        }
    }

    /**
     * Show list of all Investment form filling requests which are pending
     */
    public function investmentRequests()
    {
        $investmentRequests = InvestmentRequest::where('is_link_expired', 0)
        ->whereRaw('investment_requests.project_id IN (select id from projects where project_site="'.url().'")')
        ->orderBy('created_at','DESC')
        ->get();

        $color = Color::where('project_site',url())->first();
        return view('dashboard.requests.requests', compact('investmentRequests', 'color'));
    }

    /**
     * Returns the list of all the users downloaded prospectus
     */
    public function prospectusDownloads()
    {
        $prospectusDownloads = ProspectusDownload::where('project_site', url())
        ->orderBy('created_at','DESC')
        ->get();
        $color = Color::where('project_site',url())->first();
        return view('dashboard.prospectusDownloads', compact('prospectusDownloads', 'color'));
    }

    public function sendEoiLink(Request $request, AppMailer $mailer)
    {
        if ($request->ajax()) {
            $project = Project::find($request->project_id);
            $eoi = ProjectEOI::find($request->eoi_id);
            $mailer->sendEoiApplicationLinkToUser($project, $eoi);
            $eoi->update([
                'is_link_sent' => 1
            ]);
            return 1;
        }
    }

    public function uploadOfferDoc(Request $request)
    {
        $this->validate($request, [
            'offer_doc' => 'required|mimes:pdf',
            'eoi_id' => 'required'
        ]);
        $projectEoi = ProjectEOI::find($request->eoi_id);

        if (!file_exists(public_path().'/assets/documents/eoi/'.$projectEoi->id)) {
            File::makeDirectory(public_path().'/assets/documents/eoi/'.$projectEoi->id, 0775, true);
        }
        if($projectEoi->offer_doc_path){
            File::delete(public_path().$projectEoi->offer_doc_path);
        }
        $destinationPath = '/assets/documents/eoi/'.$projectEoi->id;
        $uniqueFileName = uniqid() . '-' . $request->file('offer_doc')->getClientOriginalName();
        $request->file('offer_doc')->move(public_path().$destinationPath, $uniqueFileName);

        $projectEoi->update([
            'offer_doc_path' => $destinationPath.'/'.$uniqueFileName,
            'offer_doc_name' => $uniqueFileName
        ]);
        if($projectEoi->offer_doc_path) {
            return response()->json([
                'status' => '1',
                'message' => 'File Uploaded Successfully. You can now send application link to the user',
                'eoi_id' => $projectEoi->id,
                'offer_doc_path' => $projectEoi->offer_doc_path,
                'offer_doc_name' => $projectEoi->offer_doc_name,
            ]);
        }
        else
        {
            return response()->json([
                'status' => '0',
                'message' => 'Something went wrong.',
            ]);
        }
    }

    public function kycRequests()
    {
        $color = Color::where('project_site',url())->first();
        $kycRequests = IdDocument::groupBy('user_id')->where('registration_site',url())->orderBy('created_at','DESC')->get();
        return view('dashboard.requests.kycRequest',compact('kycRequests','color'));
    }

    public function documents(Request $request,$id)
    {
        $color = Color::where('project_site',url())->first();
        $user = User::find($id);
        return view('dashboard.users.idDoc',compact('color','user'));
    }

    //Upload KYC documents for users by admin
    public function uploadDocuments(Request $request,AppMailer $mailer,$id)
    {
        $validation_rules = array(
            'joint_investor_id_doc'   => 'mimes:jpeg,jpg,png,pdf',
            'trust_or_company_docs'   => 'mimes:jpeg,jpg,png,pdf',
            'user_id_doc'   => 'mimes:jpeg,jpg,png,pdf',
            'user_id_doc_2'   => 'mimes:jpeg,jpg,png,pdf'
        );
        $validator = Validator::make($request->all(), $validation_rules);
        if ($validator->fails()) {
            return redirect()
            ->back()
            ->withErrors($validator)
            ->withInput();
        }
        $user = User::find($id);
        $check = IdDocument::where('user_id',$user->id)->first();
        // $user->idDoc()->get()->last()->update(['verified'=>$request->status, 'id_comment'=>$request->fixing_message, 'joint_id_comment'=>$request->fixing_message_for_id]);
        // $idimages = $user->idDoc()->get()->last();
        if($request->hasFile('joint_investor_id_doc'))
        {
            $destinationPath = 'assets/users/kyc/'.$user->id.'/joint/'.$request->joint_investor_first.'_'.$request->joint_investor_last.'/';
            $filename = $request->file('joint_investor_id_doc')->getClientOriginalName();
            $fileExtension = $request->file('joint_investor_id_doc')->getClientOriginalExtension();
            // $request->file('joint_investor_id_doc')->move($destinationPath, $filename);
            $storagePath = \Storage::disk('s3')->put($destinationPath.$filename, file_get_contents($request->file('joint_investor_id_doc')),'public');
            if($check){
                $user_doc = $user->idDoc()->update(['joint_id_filename'=>$filename, 'joint_id_path'=>$destinationPath.$filename,'joint_id_extension'=>$fileExtension,'investing_as'=>$request->investing_as, 'media_url'=>'https://s3-' .  config('filesystems.disks.s3.region') . '.amazonaws.com/' . config('filesystems.disks.s3.bucket'), 'joint_first_name'=>$request->joint_investor_first,'joint_last_name'=>$request->joint_investor_last,'registration_site'=>url(), 'verified'=>1]);
            }else{
                $user_doc = IdDocument::create(['type'=>'Document', 'joint_id_filename'=>$filename, 'joint_id_path'=>$destinationPath.$filename,'joint_id_extension'=>$fileExtension,'user_id'=>$user->id,'investing_as'=>$request->investing_as, 'media_url'=>'https://s3-' .  config('filesystems.disks.s3.region') . '.amazonaws.com/' . config('filesystems.disks.s3.bucket'), 'joint_first_name'=>$request->joint_investor_first,'joint_last_name'=>$request->joint_investor_last,'registration_site'=>url(), 'verified'=>1]);
                // $user->idDoc()->save($user_doc);
            }
        }
        $check = IdDocument::where('user_id',$user->id)->first();
        if($request->hasFile('trust_or_company_docs'))
        {
            $destinationPath = 'assets/users/kyc/'.$user->id.'/trust/'.$request->investing_company_name.'/';
            $filename = $request->file('trust_or_company_docs')->getClientOriginalName();
            $fileExtension = $request->file('trust_or_company_docs')->getClientOriginalExtension();
            // $request->file('trust_or_company_docs')->move($destinationPath, $filename);
            $storagePath = \Storage::disk('s3')->put($destinationPath.$filename, file_get_contents($request->file('trust_or_company_docs')),'public');
            if($check){
                $user_doc = $user->idDoc()->update(['filename'=>$filename, 'path'=>$destinationPath.$filename,'extension'=>$fileExtension,'investing_as'=>$request->investing_as, 'media_url'=>'https://s3-' .  config('filesystems.disks.s3.region') . '.amazonaws.com/' . config('filesystems.disks.s3.bucket'), 'trust_or_company'=>$request->investing_company_name,'registration_site'=>url(), 'verified'=>1]);
            }else{
                $user_doc = new IdDocument(['type'=>'Document', 'filename'=>$filename, 'path'=>$destinationPath.$filename,'extension'=>$fileExtension,'user_id'=>$user->id,'extension'=>$fileExtension,'investing_as'=>$request->investing_as, 'media_url'=>'https://s3-' .  config('filesystems.disks.s3.region') . '.amazonaws.com/' . config('filesystems.disks.s3.bucket'), 'trust_or_company'=>$request->investing_company_name,'registration_site'=>url(), 'verified'=>1]);
                $user->idDoc()->save($user_doc);
            }

        }
        $check = IdDocument::where('user_id',$user->id)->first();
        if($request->hasFile('user_id_doc'))
        {
            $destinationPath = 'assets/users/kyc/'.$user->id.'/doc/';
            $filename = $request->file('user_id_doc')->getClientOriginalName();
            $fileExtension = $request->file('user_id_doc')->getClientOriginalExtension();
            $storagePath = \Storage::disk('s3')->put($destinationPath.$filename, file_get_contents($request->file('user_id_doc')),'public');
            // $request->file('user_id_doc')->move($destinationPath, $filename);
            if($check){
                $user_doc = $user->idDoc()->update(['filename'=>$filename, 'path'=>$destinationPath.$filename,'user_id'=>$user->id,'extension'=>$fileExtension,'investing_as'=>$request->investing_as, 'media_url'=>'https://s3-' .  config('filesystems.disks.s3.region') . '.amazonaws.com/' . config('filesystems.disks.s3.bucket'), 'registration_site'=>url(), 'verified'=>1]);
            }else{
                $user_doc = new IdDocument(['type'=>'Document', 'filename'=>$filename, 'path'=>$destinationPath.$filename,'user_id'=>$user->id,'extension'=>$fileExtension,'investing_as'=>$request->investing_as, 'media_url'=>'https://s3-' .  config('filesystems.disks.s3.region') . '.amazonaws.com/' . config('filesystems.disks.s3.bucket'), 'registration_site'=>url(), 'verified'=>1]);
                $user->idDoc()->save($user_doc);
            }
        }

        $check2 = IdDocument::where('user_id', $user->id)->where('type', 'Document_2')->first();
        if($request->hasFile('user_id_doc_2'))
        {
            $destinationPath = 'assets/users/kyc/'.$user->id.'/doc/';
            $filename = $request->file('user_id_doc_2')->getClientOriginalName();
            $fileExtension = $request->file('user_id_doc_2')->getClientOriginalExtension();
            $storagePath = \Storage::disk('s3')->put($destinationPath.$filename, file_get_contents($request->file('user_id_doc_2')),'public');
            if($check2){
                $user_doc = $user->idDocs()->where('type', 'Document_2')->update(['filename'=>$filename, 'path'=>$destinationPath.$filename,'user_id'=>$user->id,'extension'=>$fileExtension,'investing_as'=>$request->investing_as, 'media_url'=>'https://s3-' .  config('filesystems.disks.s3.region') . '.amazonaws.com/' . config('filesystems.disks.s3.bucket'), 'registration_site'=>url(), 'verified'=>1]);
            }else{
                $user_doc = new IdDocument(['type'=>'Document_2', 'filename'=>$filename, 'path'=>$destinationPath.$filename,'user_id'=>$user->id,'extension'=>$fileExtension,'investing_as'=>$request->investing_as, 'media_url'=>'https://s3-' .  config('filesystems.disks.s3.region') . '.amazonaws.com/' . config('filesystems.disks.s3.bucket'), 'registration_site'=>url(), 'verified'=>1]);
                $user->idDoc()->save($user_doc);
            }
        }

        if(\App\Helpers\SiteConfigurationHelper::getConfigurationAttr()->kyc_approval_konkrete && \App\Helpers\SiteConfigurationHelper::getConfigurationAttr()->show_powered_by_estatebaron) {
            $kyc_approval_konkrete = \App\Helpers\SiteConfigurationHelper::getConfigurationAttr()->kyc_approval_konkrete;
        }
        else {
            $kyc_approval_konkrete = 0;
        };

        // $user_konkrete = Credit::where('user_id',$user->id)->first();
        if(!$check) {
            $credit = Credit::create(['user_id'=>$user->id, 'amount'=>$kyc_approval_konkrete, 'type'=>'KYC Verification successful','currency'=>'konkrete', 'project_site' => url()]);
        }
        $idimages = IdDocument::where('user_id',$user->id)->first();
        $mailer->sendVerificationNotificationToUser($user, '1', $idimages);

        return redirect()->back()->withMessage('<p class="alert alert-success">User documents uploaded successfully.</p>');
    }

    public function viewApplication(Request $request, $id)
    {
        $color = Color::where('project_site',url())->first();
        $investment = InvestmentInvestor::find($id);
        $projects_spv = ProjectSpvDetail::where('project_id', $investment->project_id)->first();
        $customFields = CustomField::where('page', 'application_form')->where('site_url', url())->get()->groupBy('section');
        $customFieldValues = CustomFieldValue::where(['investment_investor_id' => $investment->id])->get()->groupBy('custom_field_id');
        
        return view('dashboard.application.edit',compact('color','investment', 'projects_spv', 'customFields', 'customFieldValues'));
    }


    public function updateApplication(Request $request, $investment_id)
    {
        // dd($request);

        $investment = InvestmentInvestor::findOrFail($investment_id);
        $user = $investment->user;

        $project = Project::findOrFail($investment->project_id);
        $min_amount_invest = $project->investment->minimum_accepted_amount;
        if((int)$request->amount_to_invest < (int)$min_amount_invest)
        {
            return redirect()->back()->withErrors(['The amount to invest must be at least '.$min_amount_invest]);
        }
        $validation_rules = array(
            'amount_to_invest'   => 'required|numeric',
            'line_1' => 'required',
            'state' => 'required',
            'postal_code' => 'required'
        );
        $validator = Validator::make($request->all(), $validation_rules);

        // Return back to form with validation errors & session data as input
        if($validator->fails()) {
            return  redirect()->back()->withErrors($validator);
        }

        $investment_investor_id = $investment_id;

        //Save wholesale project input fields
        if(!$project->retail_vs_wholesale && $investment->wholesaleInvestment){
            $wholesale_investing = InvestmentInvestor::findOrFail($investment_investor_id);
            if($request->wholesale_investing_as === 'Wholesale Investor (Net Asset $2,500,000 plus)'){
                $investment->wholesaleInvestment->update([
                    'wholesale_investing_as' => $request->wholesale_investing_as,
                    'accountant_name_and_firm' => $request->accountant_name_firm_txt,
                    'accountant_professional_body_designation'=> $request->accountant_designation_txt,
                    'accountant_email'=> $request->accountant_email_txt,
                    'accountant_phone'=> $request->accountant_phone_txt,
                ]);
            }
            elseif($request->wholesale_investing_as === 'Sophisticated Investor'){
                $investment->wholesaleInvestment->update([
                    'wholesale_investing_as' => $request->wholesale_investing_as,
                    'experience_period' => $request->experience_period_txt,
                    'equity_investment_experience_text'=> $request->equity_investment_experience_txt,
                    'unlisted_investment_experience_text'=> $request->unlisted_investment_experience_txt,
                    'understand_risk_text'=> $request->understand_risk_txt,
                ]);
            }
            else{
                $investment->wholesaleInvestment->update([
                    'wholesale_investing_as' => $request->wholesale_investing_as
                ]);
            }
            $wholesale_investing->save();
        }

        $result = $investment->update([
            'amount' => $request->amount_to_invest,
            'investing_as'=> $request->investing_as,
            'interested_to_buy'=> $request->interested_to_buy,
        ]);

        // Update custom fields
        if (isset($request->custom)) {
            foreach ($request->custom as $key => $value) {
              $customFieldValue = CustomFieldValue::where(['custom_field_id' => $key, 'investment_investor_id' => $investment->id])->first();
              if (!$customFieldValue) {
                $customFieldValue = new CustomFieldValue;
                $customFieldValue->custom_field_id = $key;
                $customFieldValue->investment_investor_id = $investment->id;
              }
              $customFieldValue->value = $value;
              $customFieldValue->save();
            }
          }

        if($request->investing_as !== 'Individual Investor'){
            if($investment->investingJoint){
                $investing_joint = $investment->investingJoint;
                $result = $investing_joint->update([
                    'joint_investor_first_name' => $request->joint_investor_first,
                    'joint_investor_last_name' => $request->joint_investor_last,
                    'investing_company' => $request->investing_company_name,
                    'account_name' => $request->account_name,
                    'bsb' => $request->bsb,
                    'account_number' => $request->account_number,
                    'line_1' => $request->line_1,
                    'line_2' => $request->line_2,
                    'city' => $request->city,
                    'state' => $request->state,
                    'postal_code' => $request->postal_code,
                    'country' => $request->country,
                    'country_code' => $request->country_code,
                    'tfn' => $request->tfn,
                ]);
            }
            else{
                $investing_joint = new InvestingJoint;
                $investing_joint->project_id = $project->id;
                $investing_joint->investment_investor_id = $investment->id;
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
        }

        $updateUserDetails = $user->update([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'phone_number' => $request->phone,
            'tfn' => $request->tfn,
            'account_name' => $request->account_name,
            'bsb' => $request->bsb,
            'account_number' => $request->account_number,
            'line_1' => $request->line_1,
            'line_2' => $request->line_2,
            'city' => $request->city,
            'state' => $request->state,
            'postal_code' => $request->postal_code,
            'country' => $request->country,
            'country_code' => $request->country_code,
        ]);

        // $pdfBasePath = '/app/application/application-'.$investment->id.'-'.time().'.pdf';
        // $pdfPath = storage_path().$pdfBasePath;
        // $pdf = PDF::loadView('pdf.application', ['project' => $project, 'investment' => $investment, 'user' => $user]);
        // $pdf->save($pdfPath);
        // $investment->application_path = $pdfBasePath;
        // $investment->save();

        return redirect()->back()->withMessage('<p class="alert alert-success text-center">Application form updated successfully.</p>');

    }

    /**
     * \brief Show import CSV form
     * \details This shows a form to site admin to upload the CSV file containing users list.
     * \return View
     */
    public function showImportContacts()
    {
        $color = Color::where('project_site',url())->first();
        $siteconfiguration = SiteConfiguration::where('project_site',url())->first();
        return view('dashboard.importcontacts.importcontacts',compact('color','siteconfiguration'));
    }

    /**
     * \brief Import user contact CSV
     * \details - Allows site admin to upload the list of users in CSV file,
     *          - Save new users from CSV file to system
     *          - Send them registration bulk email using sendgrid
     *          - Allow users to register themselves using the email link
     * \return View
     */
    public function saveContactsFromCSV(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'contacts_csv_file' => 'required|mimes:csv,txt'
        ]);
        if($validator->fails()) {
            return redirect()->back()->withMessage('<p class="alert alert-danger text-center">'.$validator->errors()->first().'</p>');
        }

        // START: Don't allow using this functionality unless admin sets his own mailer settings.
        $mailSettings = $this->siteConfiguration->mailSetting()->first();
        $setupEmail = isset($mailSettings->from) ? $mailSettings->from : (\Config::get('mail.from.address'));
        if($setupEmail == "info@konkrete.io") {
            Session::flash('message', '<div class="alert alert-danger text-center">Please setup your email configurations first. <br>You can do that from <b><a href="'.route('dashboard.configurations').'">Configurations tab</a> > Mailer Email</b>.</div>');
            return redirect()->back();
        }
        // END.

        try {
            $csvTmpPath = $request->file('contacts_csv_file')->getRealPath();
            $alldata = array_map('str_getcsv', file($csvTmpPath));
            $csv_data = array_slice($alldata, 1);

            if(!empty($csv_data)) {
                $sendgridPersonalization = [];
                foreach ($csv_data as $key => $userRecord) {
                    if(
                        ($userRecord[0] != '') &&
                        ($userRecord[2] != '')
                    ) {
                        $toRegEmail = trim($userRecord[2]);
                        $user = User::where('email', $toRegEmail)->first();
                        $userReg = UserRegistration::where('email', $toRegEmail)->first();

                        if(!$user && !$userReg) {
                            $result = UserRegistration::create([
                                'email' => $toRegEmail,
                                'role' => \Config::get('constants.roles.INVESTOR'),
                                'registration_site' => url(),
                                'first_name' => trim($userRecord[0]),
                                'last_name' => trim($userRecord[1])
                            ]);
                            array_push(
                                $sendgridPersonalization,
                                [
                                    'to' => [[ 'email' => $result->email ]],
                                    'substitutions' => [
                                        '%first_name%' => $result->first_name,
                                        '%user_token%' => $result->token,
                                    ]
                                ]
                            );
                        }
                    }
                }

                // START: Sending bulk email using sendgrid
                $sitename = $this->siteConfiguration->website_name ? $this->siteConfiguration->website_name : 'Estate Baron';
                $resultBulkEmail = BulkEmailHelper::sendBulkEmail(
                    $sitename . ' invitation',
                    $sendgridPersonalization,
                    view('emails.sendgrid-api-specific.welcomeEmailForCSVImportedUser')->render()
                );

                if(!$resultBulkEmail['status']) {
                    return redirect()->back()->withMessage('<p class="alert alert-danger text-center">' . $resultBulkEmail->message . '</p>');
                }
                // END: Sending bulk email using sendgrid

            } else {
                return redirect()->back()->withMessage('<p class="alert alert-danger text-center">CSV file is empty</p>');
            }

        } catch(\Exception $e) {
            return redirect()->back()->withMessage('<p class="alert alert-danger text-center">' . $e->getMessage() . '</p>');
        }
        return redirect()->back()->withMessage('<p class="alert alert-success text-center">CSV file import done successfully.</p>');
    }

    /**
     * \brief Sendgrid bulk API
     * \details - Common function to send bulk email to multiple users.
     *          - Every email is personalized with respective user details.
     * \return Array
     */
    public function sendBulkEmail($subject, $sendgridPersonalization, $content = '')
    {
        $sendgridApiKey = \Config::get('services.sendgrid_key');
        $sendgridApiKey = $this->siteConfiguration->sendgrid_api_key ? $this->siteConfiguration->sendgrid_api_key : $sendgridApiKey;
        $mailSettings = $this->siteConfiguration->mailSetting()->first();
        $setupEmail = isset($mailSettings->from) ? $mailSettings->from : (\Config::get('mail.from.address'));
        $setupEmailName = $this->siteConfiguration->website_name ? $this->siteConfiguration->website_name : (\Config::get('mail.from.name'));

        $email = new SendgridMail();
        $email->setFrom($setupEmail, $setupEmailName);
        $email->setSubject($subject);
        $email->addTo($setupEmail);
        $email->addContent("text/html", $content);
        foreach ($sendgridPersonalization as $personalization) {
            $email->addPersonalization($personalization);
        }

        $sendgrid = new \SendGrid($sendgridApiKey);
        try {
            $response = $sendgrid->send($email);
        } catch (Exception $e) {
            return array(
                'status' => false,
                'message' => 'Failed to send bulk email. Error message: ' . $e->getMessage()
            );
        }

        return array('status' => true);
    }

    /**
     * \brief Get dividend preview data content.
     * \details [long description]
     * \param $request Request
     * \param $projectId Integer
     * \return Response JSON
     */
    public function getDividendPreviewData(Request $request, $projectId)
    {
        if(!$request->start_date || !$request->end_date){
            return response()->json([
                'status' => false,
                'message' => 'Provide valid start date and end date'
            ]);
        }

        $investorList = $request->investors_list;
        $dividendPercent = $request->dividend_percent;
        $strStartDate = (string)$request->start_date;
        $startDate = date_create_from_format('d/m/Y', (string)$request->start_date);
        $strEndDate = (string)$request->end_date;
        $endDate = date_create_from_format('d/m/Y', (string)$request->end_date);
        $dateDiff = date_diff($startDate, $endDate);
        $dateDiff = (int)$dateDiff->format("%R%a") + 1;
        $project = Project::findOrFail($projectId);

        $tableContent = '';

        if($investorList != '') {
            if($dateDiff >=0) {
                $investors = explode(',', $investorList);
                // $investments = InvestmentInvestor::findMany($investors);
                $investments = InvestmentInvestor::whereIn('user_id', $investors)
                ->where('project_id', $projectId)
                ->where('accepted', 1)
                ->where('is_cancelled', false)
                ->select(['*', 'user_id', \DB::raw("SUM(amount) as shares")])
                ->groupBy('user_id')
                ->get();
                $shareType = ($project->share_vs_unit) ? 'Share amount' : 'Unit amount';

                $tableContent .= '<table class="table-striped dividend-confirm-table" border="0" cellpadding="10">';
                $tableContent .= '<thead><tr style="background: #dcdcdc;"><td>Investor Name</td><td>Investor Bank account name</td><td>Investor bank</td><td>Investor BSB</td><td>Investor Account</td><td>' . $shareType . '</td><td>Investor Dividend amount</td></tr></thead>';
                $tableContent .= '<tbody>';

                foreach ($investments as $key => $investment) {
                    $investorAc = ($investment->investingJoint) ? $investment->investingJoint->account_name : $investment->user->account_name;
                    $bank = ($investment->investingJoint) ? $investment->investingJoint->bank_name : $investment->user->bank_name;
                    $bsb = ($investment->investingJoint) ? $investment->investingJoint->bsb : $investment->user->bsb;
                    $acNum = ($investment->investingJoint) ? $investment->investingJoint->account_number : $investment->user->account_number;

                    $tableContent .= '<tr><td>' . $investment->user->first_name . ' ' . $investment->user->last_name . '</td><td>' . $investorAc . '</td><td>' . $bank . '</td><td>' . $bsb . '</td><td>' . $acNum . '</td><td>' . $investment->amount . '<br></td><td>' . round($investment->amount * ((int)$dividendPercent/(365*100)) * $dateDiff, 2) . '<br></td></tr>';
                }

                $tableContent .= '</tbody></table>';

            }
        }

        return response()->json([
            'status' => true,
            'message' => 'Successful.',
            'data' => $tableContent
        ]);
    }

    public function getFixedDividendPreviewData(Request $request, $projectId)
    {
        $investorList = $request->investors_list;
        $dividendPercent = $request->dividend_percent;
        $project = Project::findOrFail($projectId);

        $tableContent = '';

        if($investorList != '') {
            $investors = explode(',', $investorList);
            $investments = InvestmentInvestor::whereIn('user_id', $investors)
            ->where('project_id', $projectId)
            ->where('accepted', 1)
            ->where('is_cancelled', false)
            ->select(['*', 'user_id', \DB::raw("SUM(amount) as shares")])
            ->groupBy('user_id')
            ->get();
            $shareType = ($project->share_vs_unit) ? 'No. of shares' : 'No. of units';

            $tableContent .= '<table class="table-striped dividend-confirm-table" border="0" cellpadding="10">';
            $tableContent .= '<thead><tr style="background: #dcdcdc;"><td>Investor Name</td><td>Investor Bank account name</td><td>Investor bank</td><td>Investor BSB</td><td>Investor Account</td><td>' . $shareType . '</td><td>Market Value</td><td>Investor Dividend amount</td></tr></thead>';
            $tableContent .= '<tbody>';

            foreach ($investments as $key => $investment) {
                $investorAc = ($investment->investingJoint) ? $investment->investingJoint->account_name : $investment->user->account_name;
                $bank = ($investment->investingJoint) ? $investment->investingJoint->bank_name : $investment->user->bank_name;
                $bsb = ($investment->investingJoint) ? $investment->investingJoint->bsb : $investment->user->bsb;
                $acNum = ($investment->investingJoint) ? $investment->investingJoint->account_number : $investment->user->account_number;
                $dividendAmount = round((($investment->shares * (float)$dividendPercent)/100), 2);
                $marketValue = round(($investment->shares)*($project->share_per_unit_price), 2);

                $tableContent .= '<tr><td>' . $investment->user->first_name . ' ' . $investment->user->last_name . '</td><td>' . $investorAc . '</td><td>' . $bank . '</td><td>' . $bsb . '</td><td>' . $acNum . '</td><td>' . round($investment->shares) . '<br></td><td>$' . $marketValue . '</td><td>' . '$' . $dividendAmount . '<br></td></tr>';
            }

            $tableContent .= '</tbody></table>';
        }

        return response()->json([
            'status' => true,
            'message' => 'Successful.',
            'data' => $tableContent
        ]);
    }

    public function getCentsPerSharePreviewData(Request $request, $projectId)
    {
        $investorList = $request->investors_list;
        $dividendPercent = $request->dividend_percent;
        $project = Project::findOrFail($projectId);

        $tableContent = '';

        if($investorList != '') {
            $investors = explode(',', $investorList);
            $investments = InvestmentInvestor::whereIn('user_id', $investors)
            ->where('project_id', $projectId)
            ->where('accepted', 1)
            ->where('is_cancelled', false)
            ->select(['*', 'user_id', \DB::raw("SUM(amount) as shares")])
            ->groupBy('user_id')
            ->get();
            $shareType = ($project->share_vs_unit) ? 'No. of shares' : 'No. of units';

            $tableContent .= '<table class="table-striped dividend-confirm-table" border="0" cellpadding="10">';
            $tableContent .= '<thead><tr style="background: #dcdcdc;"><td>Investor Name</td><td>Investor Bank account name</td><td>Investor bank</td><td>Investor BSB</td><td>Investor Account</td><td>' . $shareType . '</td><td>Market Value</td><td>Investor Dividend amount</td></tr></thead>';
            $tableContent .= '<tbody>';

            foreach ($investments as $key => $investment) {
                $investorAc = ($investment->investingJoint) ? $investment->investingJoint->account_name : $investment->user->account_name;
                $bank = ($investment->investingJoint) ? $investment->investingJoint->bank_name : $investment->user->bank_name;
                $bsb = ($investment->investingJoint) ? $investment->investingJoint->bsb : $investment->user->bsb;
                $acNum = ($investment->investingJoint) ? $investment->investingJoint->account_number : $investment->user->account_number;
                $dividendAmount = round(((($investment->shares) *($project->share_per_unit_price) * (int)$dividendPercent)/100), 2);
                $marketValue = round(($investment->shares)*($project->share_per_unit_price), 2);

                $tableContent .= '<tr><td>' . $investment->user->first_name . ' ' . $investment->user->last_name . '</td><td>' . $investorAc . '</td><td>' . $bank . '</td><td>' . $bsb . '</td><td>' . $acNum . '</td><td>' . round($investment->shares) . '<br></td><td>$' . $marketValue . '</td><td>' . '$' . $dividendAmount . '<br></td></tr>';
            }

            $tableContent .= '</tbody></table>';
        }

        return response()->json([
            'status' => true,
            'message' => 'Successful.',
            'data' => $tableContent
        ]);
    }
    public function getRepurchasePreviewData(Request $request, $projectId) {
        $investorList = $request->investors_list;
        $repurchaseRate = $request->repurchase_rate;
        $project = Project::findOrFail($projectId);

        $tableContent = '';

        if($investorList != '') {
            $investors = explode(',', $investorList);
            $investments = InvestmentInvestor::findMany($investors);
            $shareType = ($project->share_vs_unit) ? 'Share amount' : 'Unit amount';

            $tableContent .= '<table class="table-striped dividend-confirm-table" border="0" cellpadding="10">';
            $tableContent .= '<thead><tr style="background: #dcdcdc;"><td>Investor Name</td><td>Investor Bank account name</td><td>Investor bank</td><td>Investor BSB</td><td>Investor Account</td><td>' . $shareType . '</td><td>Investor Repurchase amount</td></tr></thead>';
            $tableContent .= '<tbody>';

            foreach ($investments as $key => $investment) {
                $investorAc = ($investment->investingJoint) ? $investment->investingJoint->account_name : $investment->user->account_name;
                $bank = ($investment->investingJoint) ? $investment->investingJoint->bank_name : $investment->user->bank_name;
                $bsb = ($investment->investingJoint) ? $investment->investingJoint->bsb : $investment->user->bsb;
                $acNum = ($investment->investingJoint) ? $investment->investingJoint->account_number : $investment->user->account_number;

                $tableContent .= '<tr><td>' . $investment->user->first_name . ' ' . $investment->user->last_name . '</td><td>' . $investorAc . '</td><td>' . $bank . '</td><td>' . $bsb . '</td><td>' . $acNum . '</td><td>' . $investment->amount . '<br></td><td>' . round($investment->amount * $repurchaseRate, 2) . '<br></td></tr>';
            }

            $tableContent .= '</tbody></table>';
        }

        return response()->json([
            'status' => true,
            'message' => 'Successful.',
            'data' => $tableContent
        ]);
    }

    public function getAllRedemptionRequests()
    {
        $color = Color::where('project_site',url())->first();
        $redemptionRequests = RedemptionRequest::whereHas('project', function ($q) {
            $q->where('project_site', url());
        })->orderBy('status_id', 'asc')->orderBy('created_at', 'asc')
        ->get();

        return view('dashboard.redemptions.requests', compact('redemptionRequests', 'color'));
    }

    public function acceptRedemptionRequest(Request $request, AppMailer $mailer, $redemptionId)
    {
        $validator = Validator::make($request->all(), [
            'num_shares'    => 'numeric|required'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->errors()->first()
            ]);
        }

        $masterRedemption = RedemptionRequest::findOrFail($redemptionId);

        $investment = \App\Helpers\ModelHelper::getTotalInvestmentByUsersAndProject(array($masterRedemption->user_id), $masterRedemption->project_id)->first();

        if ($request->num_shares < 1 || $request->num_shares > $masterRedemption->request_amount || $request->num_shares > $investment->shares) {
            return response()->json([
                'status' => false,
                'message' => 'Invalid redemption amount.'
            ]);
        }

        if ($request->num_shares != $masterRedemption->request_amount) {
            // Partial redemption
            $masterRedemption->accepted_amount = $request->num_shares;
            $masterRedemption->price = $masterRedemption->project->share_per_unit_price;
            $masterRedemption->status_id = RedemptionStatus::STATUS_PARTIAL_ACCEPTANCE;
            $masterRedemption->comments = $request->comments;
            $masterRedemption->save();

            // Create new redemption request with remaining amount
            RedemptionRequest::create([
                'user_id' => $masterRedemption->user_id,
                'project_id' => $masterRedemption->project_id,
                'request_amount' => ($masterRedemption->request_amount - $request->num_shares),
                'status_id' => RedemptionStatus::STATUS_PENDING
            ]);

        } else {
            // Whole amount redumption
            $masterRedemption->accepted_amount = $masterRedemption->request_amount;
            $masterRedemption->price = $masterRedemption->project->share_per_unit_price;
            $masterRedemption->status_id = RedemptionStatus::STATUS_APPROVED;
            $masterRedemption->comments = $request->comments;
            $masterRedemption->save();
        }

        Transaction::create([
            'user_id' => $masterRedemption->user_id,
            'project_id' => $masterRedemption->project_id,
            'transaction_type' => Transaction::REPURCHASE,
            'transaction_date' => Carbon::now(),
            'amount' => $masterRedemption->accepted_amount * $masterRedemption->price,
            'rate' => $masterRedemption->price,
            'number_of_shares' => $masterRedemption->request_amount,
        ]);
        //master child redemption accept
        if($masterRedemption->project->master_child){
            $master = RedemptionRequest::get()->last();
            $childRedemptions = RedemptionRequest::where('master_redemption',$redemptionId)->get();
            foreach($childRedemptions as $redemption){
                $shareRedemptionNumber = ($request->num_shares*$masterRedemption->project->share_per_unit_price*$redemption->project->isChild->allocation/100)/$redemption->project->share_per_unit_price;
                $investment = \App\Helpers\ModelHelper::getTotalInvestmentByUsersAndProject(array($redemption->user_id), $redemption->project_id)->first();
                if ($shareRedemptionNumber < 1 || $shareRedemptionNumber > $redemption->request_amount ) {
                    return response()->json([
                        'status' => false,
                        'message' => 'Invalid redemption amount.'
                    ]);
                }
                
                if ($shareRedemptionNumber != $redemption->request_amount) {
            // Partial redemption
                    $redemption->accepted_amount = $shareRedemptionNumber;
                    $redemption->price = $redemption->project->share_per_unit_price;
                    $redemption->status_id = RedemptionStatus::STATUS_PARTIAL_ACCEPTANCE;
                    $redemption->comments = $request->comments;
                    $redemption->save();

            // Create new redemption request with remaining amount
                    RedemptionRequest::create([
                        'user_id' => $redemption->user_id,
                        'project_id' => $redemption->project_id,
                        'request_amount' => ($redemption->request_amount - $shareRedemptionNumber),
                        'status_id' => RedemptionStatus::STATUS_PENDING,
                        'master_redemption' => $master->id,
                    ]);

                } else {
            // Whole amount redumption
                    $redemption->accepted_amount = $redemption->request_amount;
                    $redemption->price = $redemption->project->share_per_unit_price;
                    $redemption->status_id = RedemptionStatus::STATUS_APPROVED;
                    $redemption->comments = $request->comments;
                    $redemption->save();
                }

                Transaction::create([
                    'user_id' => $redemption->user_id,
                    'project_id' => $redemption->project_id,
                    'transaction_type' => Transaction::REPURCHASE,
                    'transaction_date' => Carbon::now(),
                    'amount' => $redemption->accepted_amount * $redemption->price,
                    'rate' => $redemption->price,
                    'number_of_shares' => $redemption->accepted_amount,
                ]);


            }
        }
        
        $mailer->sendRedemptionRequestAcceptedToUser($masterRedemption);
        
        return response()->json([
            'status' => true,
            'data' => [
                'shares' => $request->num_shares
            ]
        ]);
    }

    public function rejectRedemptionRequest(Request $request, AppMailer $mailer, $redemptionId)
    {
        $validator = Validator::make($request->all(), [
            'comments'      => 'required|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->errors()->first()
            ]);
        }

        $redemption = RedemptionRequest::findOrFail($redemptionId);
        $redemption->price = $redemption->project->share_per_unit_price;
        $redemption->status_id = RedemptionStatus::STATUS_REJECTED;
        $redemption->comments = $request->comments;
        $redemption->save();
        //master child redemption reject
        if($redemption->project->master_child){
            $childRedemptions = RedemptionRequest::where('master_redemption',$redemptionId)->get();

            foreach($childRedemptions as $childRedemption){
                $childRedemption->price = $childRedemption->project->share_per_unit_price;
                $childRedemption->status_id = RedemptionStatus::STATUS_REJECTED;
                $childRedemption->comments = $request->comments;
                $childRedemption->save();
            }
        }

        $mailer->sendRedemptionRequestRejectedToUser($redemption);

        return response()->json([
            'status' => true,
            'data' => [
                'shares' => $request->num_shares
            ]
        ]);
    }

    public function moneySentForRedemptionRequest(Request $request, AppMailer $mailer, $redemptionId)
    {
        $redemption = RedemptionRequest::findOrFail($redemptionId);

        if ($redemption->status_id != RedemptionStatus::STATUS_PARTIAL_ACCEPTANCE && $redemption->status_id != RedemptionStatus::STATUS_APPROVED) {
            return response()->json([
                'status' => false,
                'message' => 'Accept the redemption first!'
            ]);
        }

        $redemption->is_money_sent = 1;
        $redemption->save();

        if($redemption->project->master_child){
            $childRedemptions = RedemptionRequest::where('master_redemption',$redemptionId)->get();

            foreach($childRedemptions as $childRedemption){
                $childRedemption->is_money_sent = 1;
                $childRedemption->save();
            }
        }

        $mailer->sendRedemptionMoneySentToUser($redemption);

        return response()->json([
            'status' => true
        ]);
    }

    public function investorStatement(Request $request, $projectId, $investorId)
    {
        $validator = Validator::make($request->all(), [
            'start_date'    => 'required',
            'end_date'      => 'required|after:start_date'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->errors()->first()
            ]);
        }

        $startDate = Carbon::parse($request->start_date)->toDateString();
        $endDate = Carbon::parse($request->end_date)->toDateString();

        // Get Position records of user for project based on Dates.
        $openingBalance = ModelHelper::getTotalInvestmentByUserAndProject($investorId, $projectId, $startDate);
        $closingBalance = ModelHelper::getTotalInvestmentByUserAndProject($investorId, $projectId, $endDate);

        // Get Transaction records of user for project based on dates 
        $transactions = Transaction::where('user_id', $investorId)
        ->where('project_id', $projectId)
        ->whereRaw('DATE(created_at) BETWEEN ? AND ?', [$startDate, $endDate])
        ->get();

        $transactionTable = '<table class="table-striped investor-statement-confirm-table" border="0" cellpadding="10" width="100%">';
        $transactionTable .= '<thead><tr style="background: #dcdcdc;"><td>Transaction date</td><td>Transaction type</td><td>Number of shares</td><td>Share price</td><td>Cash amount</td></tr></thead>';
        $transactionTable .= '<tbody>';
        
        foreach ($transactions as $key => $transaction) {
            $numberOfShares = (strpos($transaction->transaction_type, 'DIVIDEND') === false) ? $transaction->number_of_shares : '-';
            $rate = (strpos($transaction->transaction_type, 'DIVIDEND') === false) ? '$ ' . number_format($transaction->rate, 4) : '-';
            $transactionType = $transaction->transaction_description ?? $transaction->transaction_type;
            
            $transactionTable .= '<tr>
            <td>' . Carbon::parse($transaction->created_at)->format('d-m-Y') . '</td>
            <td class="text-left">' . $transactionType . '</td>
            <td>' . $numberOfShares . '</td>
            <td>' . $rate . '</td>
            <td class="text-right">$ ' . number_format($transaction->amount, 2) . '</td>
            </tr>';
        }
        $transactionTable .= '</tbody></table>';

        if (!$transactions->count()) {
            $transactionTable .= '<br><div class="alert alert-warning text-center">No records available!</div>';
        }

        return response()->json([
            'status' => true,
            'data' => [
                'openingBalance' => $openingBalance,
                'closingBalance' => $closingBalance,
                'transactionTable' => $transactionTable,
                'transactions' => $transactions 
            ]
        ]);
    }

    public function sendInvestorStatement(Request $request, AppMailer $mailer, $projectId, $investorId)
    {
        $validator = Validator::make($request->all(), [
            'start_date'    => 'required',
            'end_date'      => 'required|after:start_date'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->errors()->first()
            ]);
        }

        $startDate = Carbon::parse($request->start_date)->toDateString();
        $endDate = Carbon::parse($request->end_date)->toDateString();
        $project = Project::findOrFail($projectId);
        $user = User::findOrFail($investorId);

        // Get Position records of user for project based on Dates.
        $openingBalance = ModelHelper::getTotalInvestmentByUserAndProject($investorId, $projectId, $startDate);
        $closingBalance = ModelHelper::getTotalInvestmentByUserAndProject($investorId, $projectId, $endDate);

        // Get Transaction records of user for project based on dates 
        $transactions = Transaction::where('user_id', $investorId)
        ->where('project_id', $projectId)
        ->whereRaw('DATE(created_at) BETWEEN ? AND ?', [$startDate, $endDate])
        ->get();

        // Send email to investor 
        $mailer->sendInvestorStatementRecordsToUser($project, $user, $startDate, $endDate, $openingBalance, $closingBalance, $transactions);

        return response()->json([
            'status' => true,
            'data' => [
                'openingBalance' => $openingBalance,
                'closingBalance' => $closingBalance,
                'transactions' => $transactions 
            ]
        ]);
    }
    public function showImportClients()
    {
        $color = Color::where('project_site',url())->first();
        $siteconfiguration = SiteConfiguration::where('project_site',url())->first();
        return view('dashboard.importClientsApplication.importClientsAppliction',compact('color','siteconfiguration'));
    }
    public function saveClientsApplicationFromCSV(Request $request)
    {
        $validator = Validator::make($request->all(), ['clients_file' => 'required|mimes:csv,txt']);
        if($validator->fails()) {
            return redirect()->back()->withMessage('<p class="alert alert-danger text-center">'.$validator->errors()->first().'</p>');
        }
        try {
            $csvTmpPath = $request->file('clients_file')->getRealPath();
            $alldata = array_map('str_getcsv', file($csvTmpPath));
            $csvFields = $alldata[0];
            $csv_data = array_slice($alldata, 1);
            
            $preCustomFields = CustomField::where('site_url', url())
            ->where('page', 'application_form')
            ->get();
            
            // import client application
            if(!empty($csv_data)) {
                foreach ($csv_data as $key => $clientAppliation) {
                    //Define the user existing or new
                    if($clientAppliation[3]){
                        $requestCustomFields = [];
                        $activatedUser = User::where('email', $clientAppliation[3])->first();
                        
                        if (!$activatedUser){
                            $nonActivatedUser = UserRegistration::where('email', $request->email)->first();
                            // dd($nonActivatedUser,$clientAppliation[3],$activatedUser);
                            if($nonActivatedUser)
                                $nonActivatedUser->delete();

                            $activatedUser = User::create([
                                'username'=>str_slug($clientAppliation[1].' '.$clientAppliation[2].' '.rand(1, 9999)),
                                'email' => $clientAppliation[3],
                                'first_name' => $clientAppliation[1],
                                'last_name' => $clientAppliation[2],
                                'phone_number' => $clientAppliation[4],
                                'password' => bcrypt('password'),
                                'account_name' => $clientAppliation[6],
                                'bsb' => $clientAppliation[7],
                                'registration_site' => url(),
                                'account_number' => $clientAppliation[8],
                                'line_1' => $clientAppliation[9],
                                'line_2' => $clientAppliation[10],
                                'city' => $clientAppliation[11],
                                'state' => $clientAppliation[12],
                                'postal_code' => $clientAppliation[13],
                                'country' => $clientAppliation[15],
                                'tfn' => $clientAppliation[16],
                                'activated_on'=>  Carbon::now(),// user activation date
                                'active' => 1,
                            ]);
                            // dd($activatedUser);
                        }
                        
                        //submit application and issue share certificate
                        if($clientAppliation[0]){
                            $project = Project::where('id',$clientAppliation[0])->where('project_site',url())->first();
                            $activatedUser->investments()->attach($project, [
                                'investment_id'=>$project->investment->id,
                                'amount'=>$clientAppliation[5], 
                                'buy_rate' => $project->share_per_unit_price, 
                                'project_site'=>url(),
                                'investing_as'=>$clientAppliation[20], 
                                'interested_to_buy'=>$clientAppliation[30],
                                'money_received'=>1,
                                'accepted'=>1,
                                'created_at' => $clientAppliation[31],
                                'updated_at' => $clientAppliation[31],
                                'share_certificate_issued_at' => $clientAppliation[32],
                                'custom_field_values' => json_encode($requestCustomFields)
                            ]);

                            // Add custom field values
                            $lastInvestment = InvestmentInvestor::where('user_id', $activatedUser->id)->orderBy('id', 'desc')->first();
                            foreach($preCustomFields as $key=>$value) {
                                $fieldIndex = array_search($value->name, $csvFields);
                                if ($fieldIndex !== false) {
                                    CustomFieldValue::create([
                                        'custom_field_id' => $value->id, 
                                        'investment_investor_id' => $lastInvestment->id,
                                        'value' => $clientAppliation[$fieldIndex]
                                    ]);
                                }
                            }
                            
                            if ($clientAppliation[20] != 'Individual Investor') {
                                $investor = InvestmentInvestor::get()->last();
                                $investing_joint = new InvestingJoint;
                                $investing_joint->project_id = $project->id;
                                $investing_joint->investment_investor_id = $investor->id;
                                $investing_joint->joint_investor_first_name = $clientAppliation[17];
                                $investing_joint->joint_investor_last_name = $clientAppliation[18];
                                $investing_joint->investing_company = $clientAppliation[19];
                                $investing_joint->account_name = $clientAppliation[6];
                                $investing_joint->bsb = $clientAppliation[7];
                                $investing_joint->account_number = $clientAppliation[8];
                                $investing_joint->line_1 = $clientAppliation[9];
                                $investing_joint->line_2 = $clientAppliation[10];
                                $investing_joint->city = $clientAppliation[11];
                                $investing_joint->state = $clientAppliation[12];
                                $investing_joint->postal_code = $clientAppliation[13];
                                $investing_joint->country = $clientAppliation[15];
                                $investing_joint->tfn = $clientAppliation[16];
                                $investing_joint->save();
                            }

                            $masterTransaction = Transaction::create([
                                'user_id' => $activatedUser->id,
                                'project_id' => $project->id,
                                'transaction_type' => Transaction::BUY,
                                'transaction_date' => $clientAppliation[32],// update transaction date
                                'amount' => round($clientAppliation[5],2),
                                'rate' => round($project->share_per_unit_price,4),
                                'number_of_shares' => round($clientAppliation[5]/$project->share_per_unit_price),
                            ]);
                        // $investor = InvestmentInvestor::get()->last();
                            if($project->master_child){
                                foreach($project->children as $child){
                                    $percAmount = round($clientAppliation[5]* ($child->allocation)/100 * $project->share_per_unit_price);
                                    $childProject = Project::find($child->child);
                                    $activatedUser->investments()->attach($childProject, [
                                        'investment_id'=>$childProject->investment->id,
                                        'amount'=>round($percAmount/$childProject->share_per_unit_price), 
                                        'buy_rate' => $childProject->share_per_unit_price, 
                                        'project_site'=>url(),
                                        'investing_as'=>$clientAppliation[20], 
                                        'interested_to_buy'=>$clientAppliation[30],
                                        'money_received'=>1,
                                        'accepted'=>1,
                                        'created_at' => $clientAppliation[31],
                                        'updated_at' => $clientAppliation[31],
                                        'share_certificate_issued_at' => $clientAppliation[32],
                                        'custom_field_values' => json_encode($requestCustomFields)
                                    ]);

                                    // Add custom field values
                                    $lastInvestment = InvestmentInvestor::where('user_id', $activatedUser->id)->orderBy('id', 'desc')->first();
                                    foreach($preCustomFields as $key=>$value) {
                                        $fieldIndex = array_search($value->name, $csvFields);
                                        if ($fieldIndex !== false) {
                                            CustomFieldValue::create([
                                                'custom_field_id' => $value->id, 
                                                'investment_investor_id' => $lastInvestment->id,
                                                'value' => $clientAppliation[$fieldIndex]
                                            ]);
                                        }
                                    }

                                    Transaction::create([
                                        'user_id' => $activatedUser->id,
                                        'project_id' => $childProject->id,
                                        'transaction_type' => Transaction::BUY,
                                        'transaction_date' => $clientAppliation[32],
                                        'amount' => round($percAmount,2),
                                        'rate' => round($childProject->share_per_unit_price,4),
                                        'number_of_shares' => round($percAmount/$childProject->share_per_unit_price),
                                        'transaction_description'=>'Child Application',
                                    ]);
                                }
                                $masterTransaction->transaction_description = 'Master Application';
                                $masterTransaction->save();
                            }
                        }
                    }
                }
            } else {
                return redirect()->back()->withMessage('<p class="alert alert-danger text-center">CSV file is empty</p>');
            }

        } catch(\Exception $e) {
            return redirect()->back()->withMessage('<p class="alert alert-danger text-center">' . $e->getMessage() . '</p>');
        }
        return redirect()->back()->withMessage('<p class="alert alert-success text-center">CSV file import done successfully.</p>');
    }

    public function exportImportApplicationSampleCSV()
    {
        $headers = array(
            "Content-type" => "text/csv",
            "Content-Disposition" => "attachment; filename=file.csv",
            "Pragma" => "no-cache",
            "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
            "Expires" => "0"
        );

        $columns = ['project_id', 'client_first_name', 'client_last_name', 'client_email', 'phone_number', 'investment_amount', 'account_name', 'bsb', 'account_number', 'line_1', 'line_2', 'city', 'state', 'postal_code', 'project_site', 'country', 'tfn', 'joint_investor_first_name', 'joint_investor_last_name', 'investing_company', 'investing_as', 'wholesale_investing_as', 'accountant_name_and_firm', 'accountant_professional_body_designation', 'accountant_email', 'accountant_phone', 'equity_investment_experience_text', 'experience_period', 'unlisted_investment_experience_text', 'understand_risk_text', 'interested_to_buy', 'investment_date', 'accepted_date'];
        
        $sampleValue = ['xxx', 'xxxxx', 'xxxxx', 'xxx@xxx.xx', 'xxxxxxxxxx', 'xxx', 'xxxxxxx', 'xxx', 'xxxxxxxxx', 'xxxxx', 'xxxxx', 'xxxxx', 'xxxxxx', 'xxx', 'https://xxx.xx', 'xxx', 'xxx', 'xxx', 'xxxx', 'xxx', 'xxxxx', 'xxx', 'xxx', 'xxxx', 'xxx@xxx.xx', 'xxx', 'xxx', 'xxx', 'xxxx', 'xxx', 'X(0/1)', 'yyyy-mm-dd hh:ii:ss', 'yyyy-mm-dd hh:ii:ss'];

        $customFields = CustomField::where('site_url', url())
        ->where('page', 'application_form')
        ->get();
        
        foreach ($customFields as $key => $value) {
            array_push($columns, $value->name);
            array_push($sampleValue, ($value->type == 'date') ? 'yyyy-mm-dd' : 'xxxxx');
        }
        
        $callback = function() use ($sampleValue, $columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);
            fputcsv($file, $sampleValue);
            fputcsv($file, $sampleValue);
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function downloadProjectRegistryRecord($project_id)
    {
        $project = Project::findOrFail($project_id);
        $table = ModelHelper::getTotalInvestmentByProject($project_id);
        
        $filename = storage_path().'/app/registry/'.$project->title."-registry-record.csv";
        $handle = fopen($filename, 'w+');
        if($project->retail_vs_wholesale){

            fputcsv($handle, array("Project Name","Investor Name", "Phone", "Email", "Line_1","Line_2","City","State","Country","Postal code", "Number of shares", "Market Value","investing_as","Joint Investor First Name","Joint Investor Last Name","Investing Company", "Account Name", "BSB","Account Number","TFN"));

            foreach($table as $row) {

                if($row->user->idDoc != NULL && $row->user->idDoc->investing_as != 'Individual Investor'){
                // dd($row->user);
                    fputcsv($handle, array($project->title,$row->user->first_name.' '.$row->user->last_name,$row->user->phone_number,$row->user->email,$row->user->line_1,$row->user->line_2,$row->user->city,$row->user->state,$row->user->country,$row->user->postal_code,round($row->shares),number_format(($row->shares * $project->share_per_unit_price), 2),$row->user->idDoc->investing_as,$row->user->idDoc->joint_investor_first_name,$row->user->idDoc->joint_investor_last_name,$row->user->idDoc->trust_or_company, $row->user->account_name,$row->user->bsb, $row->user->account_number,$row->user->tfn));
                }elseif($row->investing_as != 'Individual Investor'){
                // dd($row->investingJoint);
                    fputcsv($handle, array($project->title,$row->user->first_name.' '.$row->user->last_name,$row->user->phone_number,$row->user->email,$row->user->line_1,$row->user->line_2,$row->user->city,$row->user->state,$row->user->country,$row->user->postal_code,round($row->shares),number_format(($row->shares * $project->share_per_unit_price), 2),$row->investing_as,$row->investingJoint->joint_investor_first_name,$row->investingJoint->joint_investor_last_name,$row->investingJoint->investing_company, $row->investingJoint->account_name,$row->investingJoint->bsb, $row->investingJoint->account_number,$row->investingJoint->tfn));
                }else{
                    fputcsv($handle, array($project->title,$row->user->first_name.' '.$row->user->last_name,$row->user->phone_number,$row->user->email,$row->user->line_1,$row->user->line_2,$row->user->city,$row->user->state,$row->user->country,$row->user->postal_code,round($row->shares),number_format(($row->shares * $project->share_per_unit_price), 2),$row->investing_as,'','','', $row->user->account_name,$row->user->bsb, $row->user->account_number,$row->user->tfn));
                }
            }
        }else{
            // dd($table->count());
            fputcsv($handle, array("Project Name","Investor Name", "Phone", "Email", "Line_1","Line_2","City","State","Country","Postal code", "Number of shares", "Market Value","investing_as","Joint Investor First Name","Joint Investor Last Name","Investing Company", "Account Name", "BSB","Account Number","TFN",));

            foreach($table as $row) {
                // dd($row);
                if($row->user->idDoc != NULL && $row->user->idDoc->investing_as != 'Individual Investor'){
                // dd($row->user);
                    fputcsv($handle, array($project->title,$row->user->first_name.' '.$row->user->last_name,$row->user->phone_number,$row->user->email,$row->user->line_1,$row->user->line_2,$row->user->city,$row->user->state,$row->user->country,$row->user->postal_code,round($row->shares),number_format(($row->shares * $project->share_per_unit_price), 2),$row->user->idDoc->investing_as,$row->user->idDoc->joint_investor_first_name,$row->user->idDoc->joint_investor_last_name,$row->user->idDoc->trust_or_company, $row->user->account_name,$row->user->bsb, $row->user->account_number,$row->user->tfn));
                }elseif($row->investing_as != 'Individual Investor'){
                // dd($row->investingJoint);
                    fputcsv($handle, array($project->title,$row->user->first_name.' '.$row->user->last_name,$row->user->phone_number,$row->user->email,$row->user->line_1,$row->user->line_2,$row->user->city,$row->user->state,$row->user->country,$row->user->postal_code,round($row->shares),number_format(($row->shares * $project->share_per_unit_price), 2),$row->investing_as,$row->investingJoint->joint_investor_first_name,$row->investingJoint->joint_investor_last_name,$row->investingJoint->investing_company, $row->investingJoint->account_name,$row->investingJoint->bsb, $row->investingJoint->account_number,$row->investingJoint->tfn));
                }else{
                    fputcsv($handle, array($project->title,$row->user->first_name.' '.$row->user->last_name,$row->user->phone_number,$row->user->email,$row->user->line_1,$row->user->line_2,$row->user->city,$row->user->state,$row->user->country,$row->user->postal_code,round($row->shares),number_format(($row->shares * $project->share_per_unit_price), 2),$row->investing_as,'','','', $row->user->account_name,$row->user->bsb, $row->user->account_number,$row->user->tfn));
                }
            }
        }
        fclose($handle);

        $headers = array(
            'Content-Type' => 'text/csv',
        );

        return \Response::download($filename, $project->title.'-registry-record.csv', $headers);
    }

    public function downloadProjectAcceptedApplicationRecord($project_id)
    {
        $project = Project::findOrFail($project_id);
        $table = InvestmentInvestor::where('project_id',$project_id)->where('accepted',1)->where('money_received',1)->get();
        $customFields = CustomField::where('page', 'application_form')->where('site_url', url())->get()->pluck('label')->toArray();

        // dd($project);
        $filename = storage_path().'/app/acceptedApplication/'.$project->title."-acceptedApplication-record.csv";
        $columns = ["Project Name","Unique Id","Investor Name", "Phone", "Email", "Line_1","Line_2","City","State","Country","Postal code", "Number of shares", "Market Value","investing_as", "Account Name", "BSB","Account Number","TFN"];
        $handle = fopen($filename, 'w+');
        if($project->retail_vs_wholesale){
            array_push($columns,"Joint Investor First Name","Joint Investor Last Name","Investing Company");
            foreach ($customFields as $key => $value) {
                array_push($columns, $value);
            }
            fputcsv($handle, $columns);

            foreach($table as $row) {
                $columnValues = [$project->title,'INV'.$row->id,$row->user->first_name.' '.$row->user->last_name,$row->user->phone_number,$row->user->email,$row->user->line_1,$row->user->line_2,$row->user->city,$row->user->state,$row->user->country,$row->user->postal_code,round($row->amount),number_format(($row->amount * $project->share_per_unit_price), 2),$row->investing_as, isset($row->investingJoint->account_name)?$row->investingJoint->account_name:null,isset($row->investingJoint->bsb)?$row->investingJoint->bsb:null, isset($row->investingJoint->account_number)?$row->investingJoint->account_number:null,isset($row->investingJoint->tfn)?$row->investingJoint->tfn:null];
                if($row->user->idDoc != NULL && $row->user->idDoc->investing_as != 'Individual Investor'){
                // dd($row->user);
                    array_push($columnValues, $row->user->idDoc->joint_investor_first_name,$row->user->idDoc->joint_investor_last_name,$row->user->idDoc->trust_or_company);
                    foreach ($row->customFieldValuesInvestment as $key => $value) {
                        array_push($columnValues,$value->value);
                    }
                    fputcsv($handle, $columnValues);
                }elseif($row->investing_as != 'Individual Investor'){
                // dd($row->investingJoint);
                    array_push($columnValues, isset($row->investingJoint->joint_investor_first_name)?$row->investingJoint->joint_investor_first_name:null,isset($row->investingJoint->joint_investor_last_name)?$row->investingJoint->joint_investor_last_name:null,isset($row->investingJoint->investing_company)?$row->investingJoint->investing_company:null);
                    foreach ($row->customFieldValuesInvestment as $key => $value) {
                        array_push($columnValues,$value->value);
                    }
                    fputcsv($handle, $columnValues);
                }else{
                    array_push($columnValues,'','','');
                    foreach ($row->customFieldValuesInvestment as $key => $value) {
                        array_push($columnValues,$value->value);
                    }
                    fputcsv($handle, $columnValues);
                }
            }
        }else{
            array_push($columns, "Wholesale Investing As","Accountant Name And Firm","Accountant Professional Body Designation","Accountant Email","Accountant Phone","Equity Investment Experience Text","Experience Period","Unlisted Investment Experience Text","Understand Risk Text","Joint Investor First Name","Joint Investor Last Name","Investing Company");

            foreach ($customFields as $key => $value) {
                array_push($columns, $value);
            }
            // dd($columns);
            fputcsv($handle, $columns);

            foreach($table as $row) {
                // dd($table[8]->customFieldValuesInvestment);
                $columnValues = [$project->title,'INV'.$row->id,$row->user->first_name.' '.$row->user->last_name,$row->user->phone_number,$row->user->email,$row->user->line_1,$row->user->line_2,$row->user->city,$row->user->state,$row->user->country,$row->user->postal_code,round($row->amount),number_format(($row->amount * $project->share_per_unit_price), 2),$row->investing_as, isset($row->investingJoint->account_name)?$row->investingJoint->account_name:null,isset($row->investingJoint->bsb)?$row->investingJoint->bsb:null, isset($row->investingJoint->account_number)?$row->investingJoint->account_number:null,isset($row->investingJoint->tfn)?$row->investingJoint->tfn:null,isset($row->wholesaleInvestment->wholesale_investing_as)?$row->wholesaleInvestment->wholesale_investing_as:null,isset($row->wholesaleInvestment->accountant_name_and_firm)?$row->wholesaleInvestment->accountant_name_and_firm:null,isset($row->wholesaleInvestment->accountant_professional_body_designation)?$row->wholesaleInvestment->accountant_name_and_firm:null,isset($row->wholesaleInvestment->accountant_email)?$row->wholesaleInvestment->accountant_email:null,isset($row->wholesaleInvestment->accountant_phone)?$row->wholesaleInvestment->accountant_phone:null,isset($row->wholesaleInvestment->equity_investment_experience_text)?$row->wholesaleInvestment->equity_investment_experience_text:null,isset($row->wholesaleInvestment->experience_period)?$row->wholesaleInvestment->experience_period:null,isset($row->wholesaleInvestment->unlisted_investment_experience_text)?$row->wholesaleInvestment->unlisted_investment_experience_text:null,isset($row->wholesaleInvestment->understand_risk_text)?$row->wholesaleInvestment->understand_risk_text:null];
                // dd($columnValues);
                if($row->user->idDoc != NULL && $row->user->idDoc->investing_as != 'Individual Investor'){
                    array_push($columnValues,$row->user->idDoc->joint_investor_first_name,$row->user->idDoc->joint_investor_last_name,$row->user->idDoc->trust_or_company );
                    foreach ($row->customFieldValuesInvestment as $key => $value) {
                        array_push($columnValues,$value->value);
                    }
                    fputcsv($handle, $columnValues);
                }elseif($row->investing_as != 'Individual Investor'){
                // dd($row->investingJoint);
                    array_push($columnValues, isset($row->investingJoint->joint_investor_first_name)?$row->investingJoint->joint_investor_first_name:null,isset($row->investingJoint->joint_investor_last_name)?$row->investingJoint->joint_investor_last_name:null,isset($row->investingJoint->investing_company)?$row->investingJoint->investing_company:null );
                    foreach ($row->customFieldValuesInvestment as $key => $value) {
                        array_push($columnValues,$value->value);
                    }
                    fputcsv($handle, $columnValues);
                }else{
                    array_push($columnValues, '','','');
                    foreach ($row->customFieldValuesInvestment as $key => $value) {
                        array_push($columnValues,$value->value);
                    }
                    // dd($columnValues);
                    fputcsv($handle, $columnValues);
                }
            }
        }
        fclose($handle);

        $headers = array(
            'Content-Type' => 'text/csv',
        );

        return \Response::download($filename, $project->title.'-acceptedApplication-record.csv', $headers);
    }

    public function downloadProjectApplicationRecord($project_id)
    {
        $project = Project::findOrFail($project_id);
        $table = InvestmentInvestor::where('project_id',$project_id)->where('hide_investment',0)->get();
        $customFields = CustomField::where('page', 'application_form')->where('site_url', url())->get()->pluck('label')->toArray();

        $filename = storage_path().'/app/application/'.$project->title."-application-record.csv";
        $columns = ["Project Name","Unique ID","Investor Name", "Phone", "Email", "Line_1","Line_2","City","State","Country","Postal code", "Number of shares", "Market Value","investing_as", "Account Name", "BSB","Account Number","TFN"];
        $handle = fopen($filename, 'w+');
        if($project->retail_vs_wholesale){
            array_push($columns,"Joint Investor First Name","Joint Investor Last Name","Investing Company");
            foreach ($customFields as $key => $value) {
                array_push($columns, $value);
            }
            fputcsv($handle, $columns);

            foreach($table as $row) {
                $columnValues = [$project->title,'INV'.$row->id,$row->user->first_name.' '.$row->user->last_name,$row->user->phone_number,$row->user->email,$row->user->line_1,$row->user->line_2,$row->user->city,$row->user->state,$row->user->country,$row->user->postal_code,round($row->amount),number_format(($row->amount * $project->share_per_unit_price), 2),$row->investing_as, isset($row->investingJoint->account_name)?$row->investingJoint->account_name:null,isset($row->investingJoint->bsb)?$row->investingJoint->bsb:null, isset($row->investingJoint->account_number)?$row->investingJoint->account_number:null,isset($row->investingJoint->tfn)?$row->investingJoint->tfn:null];
                if($row->user->idDoc != NULL && $row->user->idDoc->investing_as != 'Individual Investor'){
                // dd($row->user);
                    array_push($columnValues, $row->user->idDoc->joint_investor_first_name,$row->user->idDoc->joint_investor_last_name,$row->user->idDoc->trust_or_company);
                    foreach ($row->customFieldValuesInvestment as $key => $value) {
                        array_push($columnValues,$value->value);
                    }
                    fputcsv($handle, $columnValues);
                }elseif($row->investing_as != 'Individual Investor'){
                // dd($row->investingJoint);
                    array_push($columnValues, isset($row->investingJoint->joint_investor_first_name)?$row->investingJoint->joint_investor_first_name:null,isset($row->investingJoint->joint_investor_last_name)?$row->investingJoint->joint_investor_last_name:null,isset($row->investingJoint->investing_company)?$row->investingJoint->investing_company:null);
                    foreach ($row->customFieldValuesInvestment as $key => $value) {
                        array_push($columnValues,$value->value);
                    }
                    fputcsv($handle, $columnValues);
                }else{
                    array_push($columnValues,'','','');
                    foreach ($row->customFieldValuesInvestment as $key => $value) {
                        array_push($columnValues,$value->value);
                    }
                    fputcsv($handle, $columnValues);
                }
            }
        }else{
            array_push($columns, "Wholesale Investing As","Accountant Name And Firm","Accountant Professional Body Designation","Accountant Email","Accountant Phone","Equity Investment Experience Text","Experience Period","Unlisted Investment Experience Text","Understand Risk Text","Joint Investor First Name","Joint Investor Last Name","Investing Company");

            foreach ($customFields as $key => $value) {
                array_push($columns, $value);
            }
            // dd($columns);
            fputcsv($handle, $columns);

            foreach($table as $row) {
                // dd($table[8]->customFieldValuesInvestment);
                $columnValues = [$project->title,'INV'.$row->id,$row->user->first_name.' '.$row->user->last_name,$row->user->phone_number,$row->user->email,$row->user->line_1,$row->user->line_2,$row->user->city,$row->user->state,$row->user->country,$row->user->postal_code,round($row->amount),number_format(($row->amount * $project->share_per_unit_price), 2),$row->investing_as, isset($row->investingJoint->account_name)?$row->investingJoint->account_name:null,isset($row->investingJoint->bsb)?$row->investingJoint->bsb:null, isset($row->investingJoint->account_number)?$row->investingJoint->account_number:null,isset($row->investingJoint->tfn)?$row->investingJoint->tfn:null,isset($row->wholesaleInvestment->wholesale_investing_as)?$row->wholesaleInvestment->wholesale_investing_as:null,isset($row->wholesaleInvestment->accountant_name_and_firm)?$row->wholesaleInvestment->accountant_name_and_firm:null,isset($row->wholesaleInvestment->accountant_professional_body_designation)?$row->wholesaleInvestment->accountant_name_and_firm:null,isset($row->wholesaleInvestment->accountant_email)?$row->wholesaleInvestment->accountant_email:null,isset($row->wholesaleInvestment->accountant_phone)?$row->wholesaleInvestment->accountant_phone:null,isset($row->wholesaleInvestment->equity_investment_experience_text)?$row->wholesaleInvestment->equity_investment_experience_text:null,isset($row->wholesaleInvestment->experience_period)?$row->wholesaleInvestment->experience_period:null,isset($row->wholesaleInvestment->unlisted_investment_experience_text)?$row->wholesaleInvestment->unlisted_investment_experience_text:null,isset($row->wholesaleInvestment->understand_risk_text)?$row->wholesaleInvestment->understand_risk_text:null];
                // dd($columnValues);
                if($row->user->idDoc != NULL && $row->user->idDoc->investing_as != 'Individual Investor'){
                    array_push($columnValues,$row->user->idDoc->joint_investor_first_name,$row->user->idDoc->joint_investor_last_name,$row->user->idDoc->trust_or_company );
                    foreach ($row->customFieldValuesInvestment as $key => $value) {
                        array_push($columnValues,$value->value);
                    }
                    fputcsv($handle, $columnValues);
                }elseif($row->investing_as != 'Individual Investor'){
                // dd($row->investingJoint);
                    array_push($columnValues, isset($row->investingJoint->joint_investor_first_name)?$row->investingJoint->joint_investor_first_name:null,isset($row->investingJoint->joint_investor_last_name)?$row->investingJoint->joint_investor_last_name:null,isset($row->investingJoint->investing_company)?$row->investingJoint->investing_company:null );
                    foreach ($row->customFieldValuesInvestment as $key => $value) {
                        array_push($columnValues,$value->value);
                    }
                    fputcsv($handle, $columnValues);
                }else{
                    array_push($columnValues, '','','');
                    foreach ($row->customFieldValuesInvestment as $key => $value) {
                        array_push($columnValues,$value->value);
                    }
                    // dd($columnValues);
                    fputcsv($handle, $columnValues);
                }
            }
        }

        fclose($handle);

        $headers = array(
            'Content-Type' => 'text/csv',
        );

        return \Response::download($filename, $project->title.'-application-record.csv', $headers);
    }

    public function updateAllocation(Request $request)
    {
        // dd($request->all());
        $master = Project::find($request->master);
        if(array_sum( (array) $request->percAll) === 100){

            for($i=0; $i<count($request->percAll); $i++){
                $per = $request->percAll[$i];
                $childAlloc = MasterChild::where('master',$request->master)->where('child',$request->childName[$i])->first();
                $childAlloc->update(['allocation'=>$per]);
            }
            $masterSharePriceInit = 0;
            if($master->master_child){
                foreach($master->children as $child){

                    $masterSharePriceInit = $masterSharePriceInit + Project::find($child->child)->share_per_unit_price * $child->allocation/100;
                }
                $master->update([
                    'share_per_unit_price'=>$masterSharePriceInit,
                ]);
            }

            
            return 1;
        }

        return 1;
    }

    public function uploadTransactionHardCopy(Request $request)
    {
        $this->validate($request, ['hard_copy' => 'required|mimes:pdf']);
        $investment = InvestmentInvestor::findOrFail($request->investment_id);
        $destinationPath = 'assets/investment/hard/copy/';
        $filename = $investment->id ;
            
        $storagePath = \Storage::disk('s3')->put($destinationPath.$filename, file_get_contents($request->file('hard_copy')),'public');

        $investment->update(['hard_copy_path'=>'https://s3-' .  config('filesystems.disks.s3.region') . '.amazonaws.com/' . config('filesystems.disks.s3.bucket').'/'.$destinationPath.$filename]);
        return redirect()->back()->withMessage('<p class="alert alert-success text-center">Upload Successfully!</p>');
    }

    public function displayApplicationFields($id)
    {
        $investment = InvestmentInvestor::where('id',$id)->first();
        
        $customFields = CustomField::where('page', 'application_form')->where('site_url', url())->get()->groupBy('section');
        $sections = ApplicationSections::where('site_url', url())->orderBy('rank', 'asc')->get();
        $investmentCustomValues = $investment->customFieldValuesInvestment->groupBy('custom_field_id');
        // dd($customFields);
        $tableContent = '';

            $tableContent .= '<table class="table-striped dividend-confirm-table" border="0" cellpadding="10" width="100%">';
            $tableContent .= '<tbody>';

                //investment_details section
                $tableContent .= '<tr><td style="border-right:1px solid; border-color:#000;" > Project Name </td><td>' . $investment->project->title . '</td></tr>';
                $tableContent .= '<tr><td style="border-right:1px solid; border-color:#000;"> Amount </td><td>' . $investment->amount*$investment->buy_rate . '</td></tr>';
                $tableContent .= '<tr><td style="border-right:1px solid; border-color:#000;"> Number of shares </td><td>' . $investment->amount .'</td></tr>';
                
                if (isset($customFields['investment_details'])){
                    foreach ($customFields['investment_details'] as $customField){
                        if(isset($investmentCustomValues[$customField->id])){
                        $value = $investmentCustomValues[$customField->id]->first() ?$investmentCustomValues[$customField->id]->first()->value : '';
                        $tableContent .= '<tr><td style="border-right:1px solid; border-color:#000;">'.$customField->label .' </td><td>' . $value .'</td></tr>';
                    }
                        }
                }


                //section investing_type
                $tableContent .= '<tr><td style="border-right:1px solid; border-color:#000;"> Given Name </td><td>' . $investment->user->first_name .'</td></tr>';
                $tableContent .= '<tr><td style="border-right:1px solid; border-color:#000;"> SurName </td><td>' . $investment->user->last_name .'</td></tr>';
                $tableContent .= '<tr><td style="border-right:1px solid; border-color:#000;"> Investing As </td><td>' . $investment->investing_as .'</td></tr>';
                if($investment->investing_as === 'Joint Investor'){
                    $joint_investor_first_name = isset($investment->investingJoint) ? $investment->investingJoint->joint_investor_first_name : '';
                    $tableContent .= '<tr><td style="border-right:1px solid; border-color:#000;"> Joint Investor First Name </td><td>' . $joint_investor_first_name .'</td></tr>';
                    $joint_investor_last_name = isset($investment->investingJoint) ? $investment->investingJoint->joint_investor_last_name : '';
                    $tableContent .= '<tr><td style="border-right:1px solid; border-color:#000;"> Joint Investor Last Name </td><td>' . $joint_investor_last_name .'</td></tr>';
                }
                if($investment->investing_as === 'Trust or Company'){
                    $investing_company = isset($investment->investingJoint) ? $investment->investingJoint->investing_company : '';
                    $tableContent .= '<tr><td style="border-right:1px solid; border-color:#000;"> Company Name </td><td>' . $investing_company .'</td></tr>';
                }
                // dd( $investmentCustomValues);
                if (isset($customFields['investing_type'])){
                    foreach ($customFields['investing_type'] as $customField){
                        if(isset($investmentCustomValues[$customField->id])){
                        $value = $investmentCustomValues[$customField->id]->first() ? $investmentCustomValues[$customField->id]->first()->value : '';
                        $tableContent .= '<tr><td style="border-right:1px solid; border-color:#000;">'.$customField->label .' </td><td>' . $value .'</td></tr>';
                    }
                }
                }

                //contact_details
                $line_1 = ($investment->investingJoint) ? $investment->investingJoint->line_1 : $investment->user->line_1;
                $line_2 = ($investment->investingJoint) ? $investment->investingJoint->line_2 : $investment->user->line_2;
                $city = ($investment->investingJoint) ? $investment->investingJoint->city : $investment->user->city;
                $state = ($investment->investingJoint) ? $investment->investingJoint->state : $investment->user->state;
                $country = ($investment->investingJoint) ? $investment->investingJoint->country : $investment->user->country;
                $postal_code = ($investment->investingJoint) ? $investment->investingJoint->postal_code : $investment->user->postal_code;
                $tableContent .= '<tr><td style="border-right:1px solid; border-color:#000;"> Address </td><td>' . $line_1.','. $line_2.','. $city.','. $state.','. $country.'-'. $postal_code .'</td></tr>';
                $tfn = ($investment->investingJoint) ? $investment->investingJoint->tfn : $investment->user->tfn;
                $tableContent .= '<tr><td style="border-right:1px solid; border-color:#000;"> TFN </td><td>' . $tfn .'</td></tr>';
                $tableContent .= '<tr><td style="border-right:1px solid; border-color:#000;"> Phone </td><td>' . $investment->user->phone_number .'</td></tr>';
                $tableContent .= '<tr><td style="border-right:1px solid; border-color:#000;"> Email </td><td>' . $investment->user->email .'</td></tr>';
                if (isset($customFields['contact_details'])){
                    foreach ($customFields['contact_details'] as $customField){
                        if(isset($investmentCustomValues[$customField->id])){
                        $value = $investmentCustomValues[$customField->id]->first() ?$investmentCustomValues[$customField->id]->first()->value : '';
                        $tableContent .= '<tr><td style="border-right:1px solid; border-color:#000;">'.$customField->label .' </td><td>' . $value .'</td></tr>';
                    }
                }
                }

                //nominated_bank_account
                $investorAc = ($investment->investingJoint) ? $investment->investingJoint->account_name : $investment->user->account_name;
                $tableContent .= '<tr><td style="border-right:1px solid; border-color:#000;"> Account Name </td><td>' . $investorAc .'</td></tr>';
                $bank = ($investment->investingJoint) ? $investment->investingJoint->bank_name : $investment->user->bank_name;
                $tableContent .= '<tr><td style="border-right:1px solid; border-color:#000;"> Bank Name </td><td>' . $bank.'</td></tr>';
                $bsb = ($investment->investingJoint) ? $investment->investingJoint->bsb : $investment->user->bsb;
                $tableContent .= '<tr><td style="border-right:1px solid; border-color:#000;"> BSB </td><td>' . $bsb .'</td></tr>';
                $acNum = ($investment->investingJoint) ? $investment->investingJoint->account_number : $investment->user->account_number;
                $tableContent .= '<tr><td style="border-right:1px solid; border-color:#000;"> Account Number </td><td>' . $acNum .'</td></tr>';
                if (isset($customFields['nominated_bank_account'])){
                    foreach ($customFields['nominated_bank_account'] as $customField){
                        if(isset($investmentCustomValues[$customField->id])){
                        $value = $investmentCustomValues[$customField->id]->first() ?$investmentCustomValues[$customField->id]->first()->value : '';
                        $tableContent .= '<tr><td style="border-right:1px solid; border-color:#000;">'.$customField->label .' </td><td>' . $value .'</td></tr>';
                    }
                }
                }

                //other section
                
                    foreach ($customFields as $key => $fields){
                        if ($key !== 'investment_details' && $key !== 'investing_type' && $key !== 'contact_details' && $key !== 'nominated_bank_account' && $key !== 'interested_to_buy' && $key !== 'signature'){

                            foreach ($fields as $customField) { 
                                if(isset($investmentCustomValues[$customField->id])){
                                    $value = $investmentCustomValues[$customField->id]->first() ?$investmentCustomValues[$customField->id]->first()->value : '';
                                    $tableContent .= '<tr><td style="border-right:1px solid; border-color:#000;">'.$customField->label .' </td><td>' . $value .'</td></tr>';
                                }
                            }
                        }
                    }
                //interested_to_buy
                $is_interested_investment_offers = $investment->user->is_interested_investment_offers ? 'Yes' : 'No';
                $tableContent .= '<tr><td style="border-right:1px solid; border-color:#000;"> Interested to Buy </td><td>' . $is_interested_investment_offers  .'</td></tr>';
                if (isset($customFields['interested_to_buy'])){
                    foreach ($customFields['interested_to_buy'] as $customField){
                        if(isset($investmentCustomValues[$customField->id])){
                        $value = $investmentCustomValues[$customField->id]->first() ?$investmentCustomValues[$customField->id]->first()->value : '';
                        $tableContent .= '<tr><td style="border-right:1px solid; border-color:#000;">'.$customField->label .' </td><td>' . $value .'</td></tr>';
                    }
                }
                }

                //signature
                if (isset($customFields['signature'])){
                    foreach ($customFields['signature'] as $customField){
                        if(isset($investmentCustomValues[$customField->id])){
                        $value = $investmentCustomValues[$customField->id]->first() ?$investmentCustomValues[$customField->id]->first()->value : '';
                        $tableContent .= '<tr><td style="border-right:1px solid; border-color:#000;">'.$customField->label .' </td><td>' . $value .'</td></tr>';
                    }
                }
                }

            $tableContent .= '</tbody></table>';


        return response()->json([
            'status' => true,
            'message' => 'Successful.',
            'data' => $tableContent
        ]);
    }
}
