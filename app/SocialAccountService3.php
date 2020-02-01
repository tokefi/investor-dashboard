<?php

namespace App;

use Laravel\Socialite\Contracts\User as ProviderUser;
use Intercom\IntercomBasicAuthClient;
use Carbon\Carbon;
use App\Mailers\AppMailer;
class SocialAccountService3
{
    public function createOrGetUser(ProviderUser $providerUser,AppMailer $mailer)
    {
        // $account = SocialAccount::whereProvider('google')

        $account = SocialAccount::whereProvider('google')
        ->whereProviderUserId($providerUser->getId())
        ->first();
        $referrer = '';
        if ($account) {
            // dd($account->user);
            return $account->user;
        } else {

            $account = new SocialAccount([
                'provider_user_id' => $providerUser->getId(),
                'provider' => 'google'
                ]);

            $user = User::whereEmail($providerUser->getEmail())->first();

            if (!$user) {
                // dd($providerUser->email);
                $cookies = \Cookie::get();
                $referrer = isset($cookies['referrer']) ? $cookies['referrer'] : "";
                $fullname=$providerUser->getName();
                $pieces = explode(" ", $fullname);
                $firstname = $pieces[0];
                $lastname = $pieces[1];
                $username = str_slug($firstname.' '.$lastname.' '.rand(1, 9999));
                $user = User::create([
                    'email' => $providerUser->getEmail(),
                    'first_name' => $firstname,
                    'last_name' => $lastname,
                    'username' => $username,
                    // 'gender' => $providerUser->user['gender'],
                    'activated_on' => Carbon::now(),
                    'active' => true,
                    'registration_site' => url(),
                    ]);
                $role = Role::whereRole('investor')->firstOrFail();
                $roleText = 'investor';
                $time_now = Carbon::now();
                $user->roles()->attach($role);
                if(\App\Helpers\SiteConfigurationHelper::getConfigurationAttr()->user_sign_up_konkrete) {
                    $signup_konkrete = \App\Helpers\SiteConfigurationHelper::getConfigurationAttr()->user_sign_up_konkrete;
                }
                else {
                    $signup_konkrete = \App\Helpers\SiteConfigurationHelper::getEbConfigurationAttr()->user_sign_up_konkrete;
                };
                $credit = Credit::create(['user_id'=>$user->id, 'amount'=>$signup_konkrete, 'type'=>'sign up', 'currency' => 'konkrete', 'project_site' => url()]);
                // dd($credit);
                // $password = $userReg->password;
                // $userReg->delete();
                //intercom create user
                // $intercom = IntercomBasicAuthClient::factory(array(
                //     'app_id' => 'refan8ue',
                //     'api_key' => '3efa92a75b60ff52ab74b0cce6a210e33e624e9a',
                //     ));
                // $intercom->createUser(array(
                //     "id" => $user->id,
                //     "user_id" => $user->id,
                //     "email" => $user->email,
                //     "name" => $user->first_name.' '.$user->last_name,
                //     "custom_attributes" => array(
                //         "last_name" => $user->last_name,
                //         "active" => $user->active,
                //         "phone_number" => $user->phone_number,
                //         "activated_on_at" => $user->activated_on->timestamp,
                //         "role" => $roleText
                //         ),
                //     ));
                // $mailer->sendRegistrationNotificationAdmin($user);
                // if (Auth::attempt(['email' => $request->email, 'password' => $password, 'active'=>1], $request->remember)) {
                    // Auth::user()->update(['last_login'=> Carbon::now()]);
                    // return view('users.registrationFinish', compact('user'));
                // }
                    // return $providerUser;
            }

            $account->user()->associate($user);
            $account->save();
            $mailer->sendRegistrationNotificationAdminOther($user,$referrer);
            return $user;

        }

    }
}
