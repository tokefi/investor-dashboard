<?php

namespace App\Http\Controllers;

use App\UserKyc;
use Session;
use App\Credit;
use App\Color;
use App\Http\Controllers\Controller;
use App\Http\Requests\UserAuthRequest;
use App\Http\Requests\UserRequest;
use App\InvestmentInvestor;
use App\Invite;
use App\Mailers\AppMailer;
use App\Role;
use App\User;
use App\Project;
use Carbon\Carbon;
use App\IdDocument;
use App\Investment;
use App\ReferralLink;
use App\InvestingJoint;
use Illuminate\Http\Request;
use App\ReferralRelationship;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Intervention\Image\Facades\Image;
use App\SiteConfiguration;
use Barryvdh\DomPDF\Facade as PDF;
use App\Transaction;
use App\Helpers\ModelHelper;
use App\RedemptionRequest;
use App\RedemptionStatus;
use Illuminate\Support\Facades\View;

class UsersController extends Controller
{
    /**
     * constructor for UsersController
     */
    public function __construct()
    {
        $this->middleware('auth', ['except' => ['create', 'login', 'store', 'authenticate']]);
        $this->middleware('guest', ['only' => ['create', 'login']]);
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
        $url = url();
        $user = Auth::user();
        $roles = $user->roles;
        if ($roles->contains('role', 'admin') || $roles->contains('role', 'master')) {
            $users = User::paginate(100)->where('registration_site',$url);
            return view('users.index', compact('users'));
        }

        if(Session::has('loginaction')){
            Session::flash('loginaction', 'success.');
        }

        return redirect()->route('users.show', $user);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        $color = Color::where('project_site',url())->first();
        $siteConfiguration = SiteConfiguration::all();
        $siteConfiguration = $siteConfiguration->where('project_site',url())->first();
        if(request()->ref){
            $ref = request()->ref;
            return view('users.create',compact('ref','color', 'siteConfiguration'));
        }
        return view('users.create',compact('color', 'siteConfiguration'));
    }


    /**
     * Store a newly created resource in storage.
     *
     * @param  Request  $request
     * @return Response
     */
    public function store(UserRequest $request, AppMailer $mailer)
    {
        if (!$request['username']) {
            $request['username']= str_slug($request->first_name.' '.$request->last_name.' '.rand(1, 9999));
            $request['password']= bcrypt($request->password);
        }
        $role = Role::whereRole($request->role)->firstOrFail();

        $user = User::create($request->all());
        $time_now = Carbon::now();
        $user->roles()->attach($role);

        $mailer->sendEmailConfirmationTo($user);

        if ($request->wantsJson()) {
            return $user;
        } else {
            return redirect()->route('users.login')->withMessage('<p class="alert alert-success text-center">Successfully Registered User, please log in.</p>');
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function show($id)
    {
        $color = Color::where('project_site',url())->first();
        $user = Auth::user();
        $roles = $user->roles;
        if ($roles->contains('role', 'admin') || $roles->contains('role', 'master')) {
            $user = User::findOrFail($id);
            return view('users.show', compact('user','color'));
        } else {
            if($user->id == $id) {
                return view('users.show', compact('user','color'));
            }
        }
        return redirect()->route('users.show', $user)->withMessage('<p class="alert text-center alert-warning">You can not access that profile.</p>');
    }
    public function book($id)
    {
        $user = User::findOrFail($id);
        return view('users.book', compact('user'));
    }
    public function bookUser($id)
    {
        $user = User::whereUsername($username)->firstOrFail();
        return view('users.book', compact('user'));
    }
    public function submit($id)
    {
        $color = Color::where('project_site',url())->first();
        $user = User::findOrFail($id);
        return view('users.submit', compact('user','color'));
    }
    /**
     * Display the specified user
     * @param  string $username
     * @return view
     */
    public function showUser($username)
    {
        $color = Color::where('project_site',url())->first();
        $user = User::whereUsername($username)->firstOrFail();
        return view('users.show', compact('user','color'));
    }
    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function edit($id)
    {
        $color = Color::where('project_site',url())->first();
        $user = Auth::user();
        $roles = $user->roles;
        if ($roles->contains('role', 'admin') || $roles->contains('role', 'master')) {
            $user = User::findOrFail($id);
            return view('users.edit', compact('user','color'));
        } else {
            if($user->id == $id) {
                return view('users.edit', compact('user','color'));
            }
        }
        return redirect()->route('users.edit', $user)->withMessage('<p class="alert text-center alert-warning">You can not access that profile.</p>');
    }
    public function fbedit($id)
    {
        $color = Color::where('project_site',url())->first();
        $user = Auth::user();
        $roles = $user->roles;
        if ($roles->contains('role', 'admin') || $roles->contains('role', 'master')) {
            $user = User::findOrFail($id);
            return view('users.edit', compact('user','color'));
        } else {
            if($user->id == $id) {
                return view('users.edit', compact('user','color'));
            }
        }
        return redirect()->route('users.edit', $user)->withMessage('<p class="alert text-center alert-warning">You can not access that profile.</p>');
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  Request  $request
     * @param  int  $id
     * @return Response
     */
    public function update(UserRequest $request, $id)
    {
        $user = Auth::user();
        $roles = $user->roles;
        $access = 0;
        if ($roles->contains('role', 'admin') || $roles->contains('role', 'master')) {
            $access = 1;
        } else {
            if($user->id == $id) {
                $access =1;
            }
        }

        if($access){
            $user = User::findOrFail($id);
            $status = $user->update($request->all());
            if ($status) {
                return redirect()->route('users.show', [$user])->withMessage('<p class="alert alert-success text-center">Updated Successfully</p>');
            }
        }
        return redirect()->route('users.edit', [$user])->withMessage('<p class="alert alert-danger text-center">Not updated Successfully</p>');
    }
    public function fbupdate(Request $request, $id)
    {
        $this->validate($request, ['first_name'=>'required','last_name'=>'required','phone_number'=>'required']);
        $user = Auth::user();
        $roles = $user->roles;
        $access = 0;
        if ($roles->contains('role', 'admin') || $roles->contains('role', 'master')) {
            $access = 1;
        } else {
            if($user->id == $id) {
                $access =1;
            }
        }

        if($access){
            $status = $user->update($request->all());
            // dd($status);
            if ($status) {
                // dd($status);
                // return view('users.registrationFinish', compact('user'));
                return redirect()->route('users.registrationFinish')->withMessage('<p class="alert alert-success text-center"> Successfully</p>');
            }
        }
        return redirect()->back()->withMessage('<p class="alert alert-danger text-center">Not updated Successfully</p>');
        // return view('users.registrationFinish', compact('user'));
    }

    public function registrationFinish1(){
        $color = Color::where('project_site',url())->first();
        $user = Auth::user();
        return view('users.registrationFinish', compact('user','color'));
    }

    /**
     * Changes role after Registration
     * @return [type] [description]
     */
    public function changeRole(Request $request){
        $user = Auth::user();
        $user->roles()->detach();
        $role = Role::whereRole($request->role)->firstOrFail();
        $user->roles()->attach($role);
        // $user->roles()->updateExistingPivot($userRole->id,$role);
        return redirect('/#projects')->withCookie(\Cookie::forget('referrer'));
    }
    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function destroy($id)
    {
        #code
    }

    public function verification($id)
    {
        $color = Color::where('project_site',url())->first();
        $user = User::findOrFail($id);
        return view('users.verify', compact('user','color'));
    }

    public function verificationUpload(Request $request, AppMailer $mailer, $id)
    {
        $this->validate($request, ['photo' => 'required','photo_with_id' => 'required']);
        $user = User::findOrFail($id);
        $destinationPath = 'assets/users/';
        $time_now = time();
        $filename = $user->first_name.'_'.$user->id.'_'.$time_now;
        $status1 = Image::make(base64_decode($request->photo))->fit(400, 300)->flip('h')->save($destinationPath.$filename.'.jpg');
        $status2 = Image::make(base64_decode($request->photo_with_id))->fit(400, 300)->save($destinationPath.$filename.'_with_id.jpg');
        if($status1 && $status2) {
            $id_image = new \App\IdImage(['filename'=>$filename.'.jpg', 'path'=>$destinationPath.$filename.'.jpg', 'filename_for_id'=>$filename.'_with_id.jpg', 'path_for_id'=>$destinationPath.$filename.'_with_id.jpg']);
            $user->idImage()->save($id_image);
            $user->profile_picture = $destinationPath.$filename.'.jpg';
            $user->save();
        }
        $credit = Credit::create(['user_id'=>$user->id, 'amount'=>50, 'type'=>'verification docs', 'project_site' => url()]);
        $status = $user->update(['verify_id'=>'1']);
        $mailer->sendVerificationNotificationToUser($user, '0', $id_image);
        $mailer->sendIdVerificationEmailToAdmin($user);
        return redirect()->route('users.verification.status', $user)->withMessage('<p class="alert alert-success">Thank You, we will verify the images.</p>');
    }

    public function verificationStatus($id)
    {
        $color = Color::where('project_site',url())->first();
        $user = User::findOrFail($id);
        return view('users.verificationStatus', compact('user','color'));
    }

    public function showInterests($id)
    {
        $color = Color::where('project_site',url())->first();
        $user = User::findOrFail($id);
        $pledged_investments = InvestmentInvestor::all();
        $interests = $user->investments;
        return view('users.interests', compact('user','interests', 'pledged_investments','color'));
    }

    public function showInvitation($id)
    {
        $color = Color::where('project_site',url())->first();
        $user = User::findOrFail($id);
        return view('users.invitation', compact('user','color'));
    }

    public function sendInvitation(Request $request, $id, AppMailer $mailer)
    {
        $user = User::findOrFail($id);
        $this->validate($request, ['email' => 'required']);
        $str = $request->email;
        $email_array = explode(";",$str);
        $failed_emails = "";
        $sent_emails = "";
        foreach ($email_array as $key => $email) {
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $failed_emails = $failed_emails." ".$email;
            } else {
                $user_email_count = count(User::whereEmail("$email")->get());
                $invite_email_count = count(Invite::whereEmail("$email")->get());
                if ($user_email_count != 0 || $invite_email_count != 0) {
                    $failed_emails = $failed_emails." ".$email;
                } else {
                    $token = str_random(60);
                    $invite = Invite::create(['email'=>$email, 'user_id'=>$id, 'token'=>$token]);
                    $mailer->sendInviteToUser($email, $user, $token);
                    $sent_emails = $sent_emails." ".$email;
                }
            }
        }
        if($failed_emails != "" ) {
            if($sent_emails != "") {
                return redirect()->back()->withMessage('<p class="alert alert-success text-center">Your invitation to '.$sent_emails.' was sent succesfully, we will notify when your invite was accepted.</p><br><p class="alert alert-warning text-center">You can not send Invitation to '.$failed_emails.'</p>');
            } else {
                return redirect()->back()->withMessage('<p class="alert alert-warning text-center">You can not send Invitation to '.$failed_emails.'</p>');
            }
        }
        return redirect()->back()->withMessage('<p class="alert alert-success text-center">Your invitation to '.$sent_emails.' was sent succesfully, we will notify when your invite was accepted.</p>');
    }

    /**
     * add investor role to user
     * @param User $users
     */
    public function addInvestor($users)
    {
        $user = User::findOrFail($users);

        if(!$user->id == Auth::user()->id)
        {
            return back()->withMessage('<p class="alert alert-warning text-center">Unauthorized action.</p>');
        }

        if ($user->roles->contains('role', 'investor')) {
            return back()->withMessage('<p class="alert alert-warning text-center">Already Investor</p>');
        }

        $role = Role::whereRole('investor')->firstOrFail();

        $user->roles()->attach($role);

        return back()->withMessage('<p class="alert alert-success text-center">Successfully Added Investor Role</p>');
    }

    /**
     * add Developer role to user
     * @param User $users
     */
    public function addDeveloper($users)
    {
        $user = User::findOrFail($users);

        if(!$user->id == Auth::user()->id)
        {
            return back()->withMessage('<p class="alert alert-warning text-center">Unauthorized action.</p>');
        }

        if ($user->roles->contains('role', 'developer')) {
            return back()->withMessage('<p class="alert alert-warning text-center">Already Developer</p>');
        }

        $role = Role::whereRole('developer')->firstOrFail();

        $user->roles()->attach($role);

        return back()->withMessage('<p class="alert alert-success text-center">Successfully Added Developer Role</p>');

    }

    /**
     * Delete Investor role from user
     * @param  User $users
     */
    public function destroyInvestor($users)
    {
        $user = User::findOrFail($users);

        if(!$user->id == Auth::user()->id)
        {
            return back()->withMessage('<p class="alert alert-warning text-center">Unauthorized action.</p>');
        }

        if ($user->roles->contains('role', 'investor') && $user->roles->count() > 1) {
           $role = Role::whereRole('investor')->firstOrFail();

           $user->roles()->detach($role);

           return back()->withMessage('<p class="alert alert-success text-center">Successfully Deleted Investor Role</p>');
       }

       return back()->withMessage('<p class="alert alert-warning text-center">Unauthorized action.</p>');

   }

    /**
     * delete Developer role from user
     * @param  User $users
     */
    public function destroyDeveloper($users)
    {
        $user = User::findOrFail($users);

        if(!$user->id == Auth::user()->id)
        {
            return back()->withMessage('<p class="alert alert-warning text-center">Unauthorized action.</p>');
        }

        if ($user->roles->contains('role', 'developer') && $user->roles->count() > 1) {
           $role = Role::whereRole('developer')->firstOrFail();

           $user->roles()->detach($role);

           return back()->withMessage('<p class="alert alert-success text-center">Successfully Deleted Developer Role</p>');
       }

       return back()->withMessage('<p class="alert alert-warning text-center">Unauthorized action.</p>');

   }

    /**
     * get user investments
     * @param  User $user_id
     */
    public function usersInvestments($user_id)
    {
        $color = Color::where('project_site',url())->first();
        $user = Auth::user();
        if($user->id != $user_id){
            return redirect()->route('users.investments', $user)->withMessage('<p class="alert text-center alert-warning">You can not access that profile.</p>');
        }
        $investments = ModelHelper::getTotalInvestmentByUser($user_id);


        //Merge user investments and dividends
        $transactions = Transaction::where('user_id', $user_id)->where('transaction_type', 'DIVIDEND')->orWhere('transaction_type', 'ANNUALIZED DIVIDEND')->get();
        $usersInvestments = InvestmentInvestor::where('user_id', $user_id)->get();
 
        $allTransactions = collect();
        foreach ($usersInvestments as $usersInvestment){
            $allTransactions->push($usersInvestment);
        }
        foreach ($transactions as $transaction){
            $allTransactions->push($transaction);
        }

        return view('users.investments', compact('user','color', 'investments', 'transactions', 'allTransactions'));
    }

    /**
     * render share certificateof the user
     * @param  InvestmentInvestor $investment_id
     */
    public function viewShareCertificate($investment_id)
    {
        $investment_id = base64_decode($investment_id);
        $color = Color::where('project_site',url())->first();
        $investmentDetails = InvestmentInvestor::findOrFail($investment_id);
        $userId = $investmentDetails->user_id;
        $projectId = $investmentDetails->project_id;
        $investmentDetails = ModelHelper::getTotalInvestmentByUsersAndProject(array($userId), $projectId);
        $investment = $investmentDetails->count() ? $investmentDetails->first() : null;
        
        // dd($investment_id);
        // $shareStart = $investment->share_number;
        // $shareStart =  explode('-',$shareStart);
        // $shareEnd = $shareStart[1];
        // $shareStart = $shareStart[0];
        $investing = InvestingJoint::where('investment_investor_id', $investment->id)->get()->last();
        $project = $investment->project;
        $user = $investment->user;
        return view('pdf.invoiceHtml',compact('investment','color','user','project','investing'));
        // $pdf->setPaper('a4', 'landscape');
        // $pdf->setOptions(['Content-Type' => 'application/pdf','images' => true]);
        // return $pdf->stream('invoice.pdf',200,['Content-Type' => 'application/pdf','Content-Disposition' => 'inline']);


        // $filename = 'app/invoices/Share-Certificate-'.base64_decode($investment_id).'.pdf';
        // $path = storage_path($filename);
        // return response()->download($path);
        // return \Response::make(file_get_contents($path), 200, [
        //     'Content-Type' => 'application/pdf',
        //     'Content-Disposition' => 'inline; filename="'.$filename.'"'
        // ]);
        // $filename = 'app/invoices/Share-Certificate-'.base64_decode($investment_id).'.pdf';
        // $path = storage_path($filename);
        // // return response()->download($path);
        // return \Response::make(file_get_contents($path), 200, [
        //     'Content-Type' => 'application/pdf',
        //     'Content-Disposition' => 'inline; filename="'.$filename.'"'
        // ]);
    }

    public function viewUnitCertificate($investment_id)
    {
        $investment_id = base64_decode($investment_id);
        $color = Color::where('project_site',url())->first();
        $investment = InvestmentInvestor::find($investment_id);
        // dd($investment->project);
        $shareStart = $investment->share_number;
        $shareStart =  explode('-',$shareStart);
        $shareEnd = $shareStart[1];
        $shareStart = $shareStart[0];
        $investing = InvestingJoint::where('investment_investor_id', $investment->id)->get()->last();
        $project = $investment->project;
        $user = $investment->user;
        return view('pdf.invoiceHtml',compact('investment','color','user','project','investing','shareEnd','shareStart'));
        // return view('pdf.invoice',compact('investment','color','user','project','investing','shareEnd','shareStart'));
    }

    public function viewApplication($investment_id)
    {
        $color = Color::where('project_site',url())->first();
        $investment_id = base64_decode($investment_id);
        $color = Color::where('project_site',url())->first();
        $investment = InvestmentInvestor::find($investment_id);
        $investing = InvestingJoint::where('investment_investor_id', $investment->id)->get()->last();
        $project = $investment->project;
        $user = $investment->user;
        return view('pdf.applicationHtml',compact('investment','color','user','project','investing'));
    }

    public function usersNotifications($user_id)
    {
        $color = Color::where('project_site',url())->first();
        $user = Auth::user();
        if($user->id != $user_id){
            return redirect()->route('users.notifications', $user)->withMessage('<p class="alert text-center alert-warning">You can not access that profile.</p>');
        }
        $investments = InvestmentInvestor::where('user_id', $user->id)
        ->where('project_site', url())->get()->groupBy('project_id');
        $project_prog = array();
        if($investments->count()){
            foreach ($investments as $projectId => $investment) {
                $project_progs = Project::findOrFail($projectId)->project_progs;
                if($project_progs->count()){
                    foreach ($project_progs as $key => $value) {
                        array_push($project_prog, $value);
                    }
                }
            }
        }
        $project_prog = collect($project_prog);
        return view('users.notification', compact('user','project_prog', 'color'));
    }

    public function documents(Request $request,$id)
    {
        $color = Color::where('project_site',url())->first();
        $user = Auth::user();
        if($user->id != $id){
            return redirect()->route('users.document', $user)->withMessage('<p class="alert text-center alert-warning">You can not access that profile.</p>');
        }
        // dd(\Storage::disk('s3')->files());
        return view('users.idDoc',compact('color','user'));
    }
    public function uploadDocuments(Request $request,AppMailer $mailer,$id)
    {
        $validation_rules = array(
            'joint_investor_id_doc'   => 'mimes:jpeg,jpg,png,pdf',
            'trust_or_company_docs'   => 'mimes:jpeg,jpg,png,pdf',
            'user_id_doc' => 'mimes:jpeg,jpg,png,pdf'
        );
        $validator = Validator::make($request->all(), $validation_rules);
        if ($validator->fails()) {
            return redirect()
            ->back()
            ->withErrors($validator)
            ->withInput();
        }
        $user = Auth::user();
        if($user->id != $id){
            return redirect()->route('users.document', $user)->withMessage('<p class="alert text-center alert-warning">You can not access that profile.</p>');
        }
        $check = IdDocument::where('user_id',$user->id)->first();
        if($request->hasFile('joint_investor_id_doc'))
        {
            $destinationPath = 'assets/users/kyc/'.$user->id.'/joint/'.$request->joint_investor_first.'_'.$request->joint_investor_last.'/';
            $filename = $request->file('joint_investor_id_doc')->getClientOriginalName();
            $fileExtension = $request->file('joint_investor_id_doc')->getClientOriginalExtension();
            // $request->file('joint_investor_id_doc')->move($destinationPath, $filename);
            $storagePath = \Storage::disk('s3')->put($destinationPath.$filename, file_get_contents($request->file('joint_investor_id_doc')),'public');
            if($check){
                $user_doc = $user->idDoc()->update(['joint_id_filename'=>$filename, 'joint_id_path'=>$destinationPath.$filename,'joint_id_extension'=>$fileExtension,'investing_as'=>$request->investing_as,'joint_first_name'=>$request->joint_investor_first,'joint_last_name'=>$request->joint_investor_last,'media_url'=>'https://s3-' .  config('filesystems.disks.s3.region') . '.amazonaws.com/' . config('filesystems.disks.s3.bucket'),'registration_site'=>url()]);
            }else{
                $user_doc = IdDocument::create(['type'=>'JointDocument', 'joint_id_filename'=>$filename, 'joint_id_path'=>$destinationPath.$filename,'joint_id_extension'=>$fileExtension,'user_id'=>$user->id,'investing_as'=>$request->investing_as,'joint_first_name'=>$request->joint_investor_first,'joint_last_name'=>$request->joint_investor_last,'media_url'=>'https://s3-' .  config('filesystems.disks.s3.region') . '.amazonaws.com/' . config('filesystems.disks.s3.bucket')]);
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
                $user_doc = $user->idDoc()->update(['filename'=>$filename, 'path'=>$destinationPath.$filename,'extension'=>$fileExtension,'investing_as'=>$request->investing_as,'trust_or_company'=>$request->investing_company_name,'media_url'=>'https://s3-' .  config('filesystems.disks.s3.region') . '.amazonaws.com/' . config('filesystems.disks.s3.bucket'),'registration_site'=>url()]);
            }else{
                $user_doc = new IdDocument(['type'=>'TrustDoc', 'filename'=>$filename, 'path'=>$destinationPath.$filename,'extension'=>$fileExtension,'user_id'=>$user->id,'extension'=>$fileExtension,'investing_as'=>$request->investing_as,'trust_or_company'=>$request->investing_company_name,'media_url'=>'https://s3-' .  config('filesystems.disks.s3.region') . '.amazonaws.com/' . config('filesystems.disks.s3.bucket'),'registration_site'=>url()]);
                $user->idDoc()->save($user_doc);
            }

        }
        $check = IdDocument::where('user_id',$user->id)->first();
        if($request->hasFile('user_id_doc'))
        {
            $destinationPath = 'assets/users/kyc/'.$user->id.'/doc/';
            $filename = $request->file('user_id_doc')->getClientOriginalName();
            $fileExtension = $request->file('user_id_doc')->getClientOriginalExtension();
            // $request->file('user_id_doc')->move($destinationPath, $filename);
            $storagePath = \Storage::disk('s3')->put($destinationPath.$filename, file_get_contents($request->file('user_id_doc')),'public');
            if($check){
                $user_doc = $user->idDoc()->update(['filename'=>$filename, 'path'=>$destinationPath.$filename,'user_id'=>$user->id,'extension'=>$fileExtension,'investing_as'=>$request->investing_as,'media_url'=>'https://s3-' .  config('filesystems.disks.s3.region') . '.amazonaws.com/' . config('filesystems.disks.s3.bucket'),'registration_site'=>url()]);
            }else{
                $user_doc = new IdDocument(['type'=>'Document', 'filename'=>$filename, 'path'=>$destinationPath.$filename,'user_id'=>$user->id,'extension'=>$fileExtension,'investing_as'=>$request->investing_as,'media_url'=>'https://s3-' .  config('filesystems.disks.s3.region') . '.amazonaws.com/' . config('filesystems.disks.s3.bucket'),'registration_site'=>url()]);
                $user->idDoc()->save($user_doc);
            }
        }

        if(\App\Helpers\SiteConfigurationHelper::getConfigurationAttr()->kyc_upload_konkrete) {
            $kyc_upload_konkrete = \App\Helpers\SiteConfigurationHelper::getConfigurationAttr()->kyc_upload_konkrete;
        }
        else {
            $kyc_upload_konkrete = \App\Helpers\SiteConfigurationHelper::getEbConfigurationAttr()->kyc_upload_konkrete;
        };

        if(!$user->idDoc){
            $credit = Credit::create(['user_id'=>$user->id, 'amount'=>$kyc_upload_konkrete, 'type'=>'KYC Submitted','currency'=>'konkrete', 'project_site' => url()]);
        }
        $mailer->sendIdVerificationNotificationToUser($user, '0');
        $mailer->sendIdVerificationEmailToAdmin($user);
        return redirect()->back()->withMessage('<p class="alert alert-success">Thank You! Successfully Uploaded documents, We will verify the Documents.</p>');
        // return redirect()->back()->withMessage('Successfully Uploaded documents');
    }
    public function referralUser($user_id)
    {
        $color = Color::where('project_site',url())->first();
        $user = Auth::user();
        if($user->id != $user_id){
            return redirect()->route('users.referral', $user)->withMessage('<p class="alert text-center alert-warning">You can not access that profile.</p>');
        }
        $ref = ReferralLink::where('user_id',$user_id)->get()->first();
        if(!$ref){
            return view('users.userReferral',compact('user','color','refUsers'));
        }
        $relationRefs = ReferralRelationship::where('referral_link_id',$ref->id)->get()->all();
        foreach($relationRefs as $relationRef){
            $refUsers[] = User::find($relationRef->user_id);
        }
        return view('users.userReferral',compact('user','color','refUsers'));
    }

    /**
     * @param Request $request
     * @param $userId
     * @return \Illuminate\Http\JsonResponse
     */
    public function kycConfirmByDigitalId(Request $request, $userId)
    {
        $userKyc = UserKyc::where(['user_id' => $userId, 'kyc_type' => 'digital_id'])->first();
        if (!$userKyc) {
            UserKyc::create([
                'user_id' => $userId,
                'kyc_type' => 'digital_id',
                'response_payload' => json_encode($request->all())
            ]);
        }

        return response()->json(['status' => true]);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function requestRedemption(Request $request, AppMailer $mailer)
    {
        $validation_rules = array(
            'num_shares'    => 'numeric|required',
            'project_id'    => 'required'
        );

        $validator = Validator::make($request->all(), $validation_rules);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->errors()->first()
            ]);
        }

        $user = Auth::user();
        $investment = ModelHelper::getTotalInvestmentByUserAndProject($user->id, $request->project_id);
        
        if ($request->num_shares < 1 || $request->num_shares > $investment->shares) {
            return response()->json([
                'status' => false,
                'message' => 'Redemption shares should be between 1 and ' . $investment->shares
            ]);
        }

        $pendingRequest = RedemptionRequest::where('user_id', $user->id)
            ->where('project_id', $request->project_id)
            ->where('status_id', RedemptionStatus::STATUS_PENDING)
            ->get();

        if ($pendingRequest->count()) {
            return response()->json([
                'status' => false,
                'message' => 'You already have a request in pending status!'
            ]);
        }

        $project = Project::findOrFail($request->project_id);

        // Create redemption record in DB
        RedemptionRequest::create([
            'user_id' => $user->id,
            'project_id' => $request->project_id,
            'request_amount' => $request->num_shares,
            'status_id' => RedemptionStatus::STATUS_PENDING
        ]);
        
        // Send email to admin
        $mailer->sendRedemptionRequestEmailToAdmin($user, $project, $request->num_shares);

        // Send email to user
        $mailer->sendRedemptionRequestEmailToUser($user, $project, $request->num_shares);

        return response()->json([
            'status' => true,
            'data' => [
                'shares' => $request->num_shares
            ]
        ]);

    }

    public function redemptions($userId)
    {
        $color = Color::where('project_site',url())->first();
        $user = Auth::user();
        if($user->id != $userId){
            return redirect()->route('users.redemptions', $user)->withMessage('<p class="alert text-center alert-warning">You can not access that profile.</p>');
        }

        $redemptions = RedemptionRequest::whereHas('project', function ($q) {
                $q->where('project_site', url());
            })
            ->where('user_id', $user->id)
            ->orderBy('status_id', 'asc')->orderBy('created_at', 'asc')
            ->get();

        return view('users.redemptionRequests', compact('user','color', 'redemptions'));
    }
}
