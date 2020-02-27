<?php

namespace App\Helpers;

use App\SiteConfiguration;
use SendGrid\Mail\Mail as SendgridMail;

class BulkEmailHelper
{
    /**
     * Send bulk email using sendgrid API key
     * Chunk the requests in with set of 1000 users
     *
     * @param $subject
     * @param $sendgridPersonalization
     * @param string $content
     * @return array
     * @throws Exception
     */
    public static function sendBulkEmail($subject, $sendgridPersonalization, $content = '')
    {
        $chunkedUsers = array_chunk($sendgridPersonalization, 1000, true);
        $siteConfiguration = SiteConfiguration::where('project_site', url())->first();
        $sendgridApiKey = \Config::get('services.sendgrid_key');
        $sendgridApiKey = $siteConfiguration->sendgrid_api_key ? $siteConfiguration->sendgrid_api_key : $sendgridApiKey;
        $mailSettings = $siteConfiguration->mailSetting()->first();
        $setupEmail = isset($mailSettings->from) ? $mailSettings->from : (\Config::get('mail.from.address'));
        $setupEmailName = $siteConfiguration->website_name ? $siteConfiguration->website_name : (\Config::get('mail.from.name'));

        $sendgrid = new \SendGrid($sendgridApiKey);

        foreach ($chunkedUsers as $singleChunk) {
            $email = new SendgridMail();
            $email->setFrom($setupEmail, $setupEmailName);
            $email->setSubject($subject);
            $email->addTo('diggy@konkrete.io');
            $email->addContent("text/html", $content);

            foreach ($singleChunk as $personalization) {
                $email->addPersonalization($personalization);
            }

            try {
                $response = $sendgrid->send($email);
            } catch (Exception $e) {
                return array(
                    'status' => false,
                    'message' => 'Failed to send bulk email. Error message: ' . $e->getMessage()
                );
            }
        }

        return array('status' => true);
    }
}