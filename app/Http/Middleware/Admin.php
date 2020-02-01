<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Contracts\Auth\Guard;

class Admin
{
    /**
     * The Guard implementation.
     *
     * @var Guard
     */
    protected $auth;

    /**
     * Create a new filter instance.
     *
     * @param  Guard  $auth
     * @return void
     */
    public function __construct(Guard $auth)
    {
        $this->auth = $auth;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $user = $this->auth->user();
        if(!$this->auth->guest()) {
            if (!$user->roles->contains('role', 'admin')) {
                if(!$user->roles->contains('role', 'master')){
                    if ($request->ajax()) {
                        return response('Unauthorized.', 401);
                    } else {
                        return redirect()->route('users.show', [$user])->withMessage('<p class="alert alert-warning text-center ">Admin Only</p>');
                    }
                }
            }
            else{
                if(!$user->roles->contains('role', 'master')){
                    if($user->email == "info@estatebaron.com"){

                    }
                    else{
                        if($user->registration_site != url()){
                            return redirect()->route('users.show', [$user])->withMessage('<p class="alert alert-warning text-center ">You are not Admin for this site</p>');
                        }
                    }
                }
            }
        }
        return $next($request);
    }
}
