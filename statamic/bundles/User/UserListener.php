<?php

namespace Statamic\Addons\User;

use Statamic\API\Auth;
use Statamic\API\User;
use Statamic\API\Request;
use Statamic\Extend\Listener;

class UserListener extends Listener
{
    public $events = [
        'user.reset' => 'reset',
        'User.reset' => 'reset',
        'User.forgot' => 'forgot',
        'User.login' => 'login',
        'User.logout' => 'logout',
        'User.register' => 'register'
    ];

    private $request;

    /**
     * Handle a password reset request
     *
     * Both GET and POST requests use the same event.
     */
    public function reset()
    {
        $this->request = request();

        return ($this->request->method() === 'POST')
            ? $this->postResetForm()
            : $this->getResetForm();
    }

    /**
     * Show a password reset form
     *
     * @return Illuminate\View\View
     */
    private function getResetForm()
    {
        if (! $user = User::get($this->request->query('user'))) {
            dd('Invalid user'); // @todo Do this nicer.
        }

        $resetter = new PasswordReset;
        $resetter->code($this->request->query('code'));
        $resetter->user($user);

        return view('users.reset', [
            'code'  => $resetter->code(),
            'valid' => $resetter->valid(),
            'title' => $user->status() === 'pending' ? 'Activate Account' : 'Reset Password'
        ]);
    }

    private function postResetForm()
    {
        if (! $user = User::get($this->request->input('user'))) {
            dd('Invalid user'); // @todo Do this nicer.
        }

        $resetter = new PasswordReset;
        $resetter->code($this->request->input('code'));
        $resetter->user($user);

        $validator = app('validator')->make($this->request->all(), [
            'code' => 'required|in:'.$user->getPasswordResetToken(),
            'password' => 'required|confirmed'
        ], [
            'code.in' => 'The code is invalid.'
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator);
        }

        $message = ($user->status() === 'pending')
            ? 'Your account has been activated.'
            : 'Your password has been reset.';

        $resetter->updatePassword($this->request->input('password'));

        // Redirect if one has been specified, otherwise just go back.
        $response = ($this->request->has('redirect'))
            ? redirect($this->request->input('redirect'))
            : back();

        return $response->with('success', $message);
    }

    public function forgot()
    {
        $this->request = request();

        $validator = app('validator')->make($this->request->all(), [
            'username' => 'required'
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator);
        }

        $username = $this->request->input('username');

        // If an invalid username has been entered we'll tell a white lie and say
        // that the email has been sent. This is a security measure to prevent
        // spamming of the form until a valid username is discovered.
        if (! $user = User::username($username)) {
            return back()->with(['email_sent' => true]);
        }

        $resetter = new PasswordReset;
        $resetter->user($user);
        $resetter->baseUrl($this->request->input('reset_url'));
        $resetter->send();

        return back()->with(['email_sent' => true]);
    }

    public function login()
    {
        $validator = \Validator::make(Request::all(), [
            'username' => 'required',
            'password' => 'required'
        ], [], [
            'username' => 'username field',
            'password' => 'password field',
        ]);

        if ($validator->fails()) {
            return back()->withInput()->withErrors($validator);
        }

        $logged_in = Auth::login(
            Request::input('username'),
            Request::input('password'),
            Request::has('remember')
        );

        if (! $logged_in) {
            return back()->withInput()->withErrors('Invalid credentials.');
        }

        $redirect = Request::input('redirect', '/');

        return redirect($redirect);
    }

    public function logout()
    {
        \Auth::logout();

        return redirect(Request::get('redirect', '/'));
    }

    public function register()
    {
        $registrar = new UserRegistrar(request());

        $validator = $registrar->validator();

        if ($validator->fails()) {
            return back()->withInput()->withErrors($validator);
        }

        $user = $registrar->create();

        event('user.registered', $user);

        Auth::login($user);

        $redirect = Request::input('redirect', '/');

        return redirect($redirect);
    }
}
