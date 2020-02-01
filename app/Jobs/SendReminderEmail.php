<?php

namespace App\Jobs;

use App\Jobs\Job;
use Mail;
use App\Role;
use App\User;
use App\Project;
use App\InvestingJoint;
use App\UserRegistration;
use App\InvestmentInvestor;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Mail\Mailer;
use Illuminate\Mail\TransportManager;
use App\Helpers\SiteConfigurationHelper;
use Swift_MailTransport as MailTransport;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Bus\SelfHandling;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendReminderEmail extends Job implements SelfHandling, ShouldQueue
{
    use InteractsWithQueue, SerializesModels;
    protected $mailer;
    protected $from = 'info@estatebaron.com';
    protected $to;
    protected $bcc;
    protected $view;
    protected $subject;
    protected $data = [];
    protected $investor;
    protected $project;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(User $user, Project $project, InvestmentInvestor $investor)
    {
        //
        $this->investor = $investor;
        $this->project = $project;
        $this->user = $user;

    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(Mailer $mailer)
    {
        $investor = $this->investor;
        $project = $this->project;
        $user = $investor->user;
        $role = Role::findOrFail(1);
        $recipients = ['info@estatebaron.com'];
        foreach ($role->users as $recipient) {
            if($recipient->registration_site == url()){
                array_push($recipients, $recipient->email);
            }
        }
        $amount = $investor->amount;
        $investing_as = $investor->investing_as;
        $investment_investor_id = $investor->id;
        $investing = InvestingJoint::where('investment_investor_id', $investment_investor_id)->get()->last();
        $this->from = SiteConfigurationHelper::overrideMailerConfig();
        $this->bcc = 'abhi.mahavarkar@gmail.com';
        $this->to = $recipients;
        $this->view = 'emails.admin';
        $this->subject = $user->first_name.' '.$user->last_name.' has invested '.$amount.' in '.$project->title;
        $this->data = compact('project', 'investor' , 'investing_as','investing','user');
        $mailer->send($this->view, $this->data, function ($message) {
            $message->from($this->from, ($titleName=SiteConfigurationHelper::getConfigurationAttr()->title_text) ? $titleName : 'Estate Baron')->to($this->to)->subject($this->subject);
        });
    }
}
