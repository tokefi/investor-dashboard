<?php

namespace App\Jobs;

use App\Jobs\Job;
use Mail;
use App\Role;
use App\User;
use App\Project;
use App\UserRegistration;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Mail\Mailer;
use Illuminate\Mail\TransportManager;
use App\Helpers\SiteConfigurationHelper;
use Swift_MailTransport as MailTransport;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Bus\SelfHandling;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\InvestmentInvestor;

class SendInvestorNotificationEmail extends Job implements SelfHandling, ShouldQueue
{
    use InteractsWithQueue, SerializesModels;
    protected $mailer;
    protected $from = 'info@konkrete.io';
    protected $to;
    protected $bcc;
    protected $view;
    protected $subject;
    protected $data = [];
    protected $investor;
    protected $project;
    protected $investment;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(User $user, Project $project, InvestmentInvestor $investor)
    {
        //
        // $this->investor = $user;
        // $this->project = $project;
        // $this->investment = $investor;
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
        $user = $investor->user;
        // $amount = $user->investments->last()->pivot->amount;
        $amount = number_format(round($investor->amount * $investor->buy_rate, 2));
        $investment = $user->investments->last()->pivot;
        $project = $this->project;
        $this->from = SiteConfigurationHelper::overrideMailerConfig();
        $this->to = $user->email;
        $this->view = 'emails.interest';
        $this->subject = 'Thank you for investing in '.$project->title;
        $this->data = compact('user', 'project','amount','investment');

        $mailer->send($this->view, $this->data, function ($message) {
            $message->from($this->from, ($titleName=SiteConfigurationHelper::getConfigurationAttr()->title_text) ? $titleName : 'Estate Baron')->to($this->to)->subject($this->subject);
        });

    }
}
