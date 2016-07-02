<?php

namespace Statamic\Http\Controllers\Auth;

use Statamic\API\OAuth;
use Illuminate\Auth\Guard;
use Illuminate\Http\Request;
use Statamic\Http\Controllers\CpController;
use Illuminate\Validation\Factory as Validator;

class AuthController extends CpController
{
    /**
     * @var \Illuminate\Auth\Guard
     */
    private $auth;

    /**
     * @var \Illuminate\Validation\Factory
     */
    private $validator;

    /**
     * @param \Illuminate\Http\Request           $request
     * @param \Illuminate\Auth\Guard             $auth
     * @param \Illuminate\Validation\Factory     $validator
     */
    public function __construct(Request $request, Guard $auth, Validator $validator)
    {
        parent::__construct($request);

        $this->auth = $auth;
        $this->validator = $validator;
    }

    /**
     * Show the login page
     *
     * @return \Illuminate\View\View
     */
    public function getLogin()
    {
        $data = [
            'title' => translate('cp.login'),
            'oauth' => OAuth::enabled() && !empty(OAuth::providers())
        ];

        return view('auth.login', $data);
    }

    /**
     * Handle a login request to the application.
     *
     * @return \Illuminate\Http\Response
     */
    public function postLogin()
    {
        $this->validate($this->request, [
            'username' => 'required',
            'password' => 'required',
        ]);

        $credentials = $this->request->only('username', 'password');

        if ($this->auth->attempt($credentials, $this->request->has('remember'))) {
            return redirect()->intended($this->redirectPath());
        }

        return redirect($this->loginPath())
            ->withInput($this->request->only('username', 'remember'))
            ->withErrors([
                'username' => 'These credentials are incorrect.',
            ]);
    }

    /**
     * Log the user out of the CP.
     *
     * @return \Illuminate\Http\Response
     */
    public function getLogout()
    {
        $this->auth->logout();

        return redirect(route('login'));
    }

    /**
     * Get the path to the login route
     *
     * @return string
     */
    public function loginPath()
    {
        return route('login');
    }

    /**
     * Get the location users will be taken here when they log in or register
     *
     * @return string
     */
    public function redirectPath()
    {
        return route('cp');
    }
}
