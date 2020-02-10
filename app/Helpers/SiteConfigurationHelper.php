<?php

namespace App\Helpers;
use App\SiteConfiguration;
use App\User;
use App\Color;
use Auth;
use Mail;
use Illuminate\Mail\TransportManager;
use Swift_MailTransport as MailTransport;
use Illuminate\Contracts\Mail\Mailer;


class SiteConfigurationHelper
{
    public static function getConfigurationAttr()
    {
    	$siteConfiguration = SiteConfiguration::where('project_site', url())->first();
        return $siteConfiguration;
    }

    public static function getEbConfigurationAttr()
    {
        $ebConfiguration = SiteConfiguration::where('project_site', 'https://estatebaron.com')->first();
        return $ebConfiguration;
    }

    public static function isSiteAdmin()
    {
        if(Auth::user()->roles->contains('role','admin') && Auth::user()->registration_site==url()){
            return 1;
        }
    	if(Auth::user()->roles->contains('role','master')){
            return 1;
        }
    	return 0;
    }

    public static function getSiteThemeColors()
    {
        $color = Color::where('project_site', url())->first();
        return $color;
    }

    public static function overrideMailerConfig()
    {
        $from = 'info@konkrete.io';
        $siteconfig = SiteConfigurationHelper::getConfigurationAttr();
        $config = $siteconfig->mailSetting()->first();
        if($config) {
            \Config::set('mail.host',$config->host);
            \Config::set('mail.port',$config->port);
            \Config::set('mail.username',$config->username);
            \Config::set('mail.password',$config->password);
            \Config::set('mail.sendmail',$config->from);
            $from = $config->from;
            $app = \App::getInstance();
            $app['swift.transport'] = $app->share(function ($app) {
               return new TransportManager($app);
            });
            $mailer = new \Swift_Mailer($app['swift.transport']->driver());
            Mail::setSwiftMailer($mailer);         
        }

        return $from;
    }
}

?>