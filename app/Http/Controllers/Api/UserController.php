<?php

namespace App\Http\Controllers\Api;

use App\User;
use Validator;
use App\Http\Requests;
use App\UserRegistration;
use App\Mailers\AppMailer;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Helpers\SiteConfigurationHelper;

class UserController extends Controller
{
    /**
     * Endpoint to register new user from other sites
     * 
     * @param Request $request
     * @param AppMailer $mailer
     * @return Json
     */
    public function register(Request $request, AppMailer $mailer)
    {
        if (!SiteConfigurationHelper::getConfigurationAttr()->allow_user_signup) {
            return response()->json([
                'status' => false,
                'message' => 'The signup process is temporarily suspended.'
            ]);
        }

        $validator = Validator::make($request->all(), [
            'email' => 'required|email'
        ]);

        $validator1 = Validator::make($request->all(), [
            'email' => 'required|email|unique:users,email||unique:user_registrations,email'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->errors()->first()
            ]);
        }

        if ($validator1->fails()) {
            $res1 = User::where('email', $request->email)->where('registration_site', url())->first();
            $res2 = UserRegistration::where('email', $request->email)->where('registration_site', url())->first();
            if (!$res1 && !$res2) {
                $originSite = "";
                if ($user = User::where('email', $request->email)->first()) {
                    $originSite = $user->registration_site;
                }
                if ($userReg = UserRegistration::where('email', $request->email)->first()) {
                    $originSite = $userReg->registration_site;
                }
                // $errorMessage = 'This email is already registered on '.$originSite.', you can use the same login id and password on this site.';
                $errorMessage = 'This email is already registered!';

                return response()->json([
                    'status' => false,
                    'message' => $errorMessage
                ]);
            }

            $errorMessage = 'This email is already registered!';
            
            return response()->json([
                'status' => false,
                'message' => $errorMessage
            ]);
        }

        $data = $request->all();
        $data['role'] = 'investor';
        $data['registration_site'] = url();
        
        $ref = false;
        $user = UserRegistration::create($data);
        $mailer->sendRegistrationConfirmationTo($user, $ref);

        return response()->json([
            'status' => true,
            'message' => 'User Registration successful!'
        ]);
    }
}
