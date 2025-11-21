<?php

namespace App\Actions\Fortify;

use App\Models\User;
use Illuminate\Auth\Events\Failed;
use Illuminate\Contracts\Auth\StatefulGuard;
use Illuminate\Validation\ValidationException;
use Laravel\Fortify\Fortify;
use Laravel\Fortify\LoginRateLimiter;

class AttemptToAuthenticate
{
    /**
     * The guard implementation.
     *
     * @var \Illuminate\Contracts\Auth\StatefulGuard
     */
    protected $guard;

    /**
     * The login rate limiter instance.
     *
     * @var \Laravel\Fortify\LoginRateLimiter
     */
    protected $limiter;

    /**
     * Create a new controller instance.
     */
    public function __construct(StatefulGuard $guard, LoginRateLimiter $limiter)
    {
        $this->guard = $guard;
        $this->limiter = $limiter;
    }

    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  callable  $next
     * @return mixed
     */
    public function handle($request, $next)
    {
        // Check if this is a guest login
        if ($request->has('is_guest') && $request->input('is_guest')) {
            // Find or create the single guest user
            $user = User::firstOrCreate(
                ['email' => 'guest@getaxia.de'],
                [
                    'first_name' => 'Guest',
                    'last_name' => 'User',
                    'is_guest' => true,
                    'password' => \Illuminate\Support\Facades\Hash::make('guest-not-allowed-to-login'),
                    'n8n_webhook_url' => 'https://n8n.getaxia.de/webhook/d2336f92-eb51-4b66-b92d-c9e7d9cf4b7d',
                    'chart_webhook_url' => 'https://n8n.getaxia.de/webhook/c3352634-be98-4448-903a-d04ed64ea90b',
                ]
            );
            
            // Reset guest user data
            $user->companies()->delete();
            
            // Create fresh company
            \App\Models\Company::create([
                'owner_user_id' => $user->id,
            ]);

            $this->guard->login($user);

            return $next($request);
        }

        // Regular email-based login
        if (Fortify::$authenticateUsingCallback) {
            return $this->handleUsingCustomCallback($request, $next);
        }

        if ($this->guard->attempt(
            $request->only(Fortify::username(), 'password'),
            $request->boolean('remember'))
        ) {
            return $next($request);
        }

        $this->throwFailedAuthenticationException($request);
    }

    /**
     * Attempt to authenticate using a custom callback.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  callable  $next
     * @return mixed
     */
    protected function handleUsingCustomCallback($request, $next)
    {
        $user = call_user_func(Fortify::$authenticateUsingCallback, $request);

        if (! $user) {
            $this->fireFailedEvent($request);

            return $this->throwFailedAuthenticationException($request);
        }

        $this->guard->login($user, $request->boolean('remember'));

        return $next($request);
    }

    /**
     * Throw a failed authentication validation exception.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return void
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    protected function throwFailedAuthenticationException($request)
    {
        $this->limiter->increment($request);

        throw ValidationException::withMessages([
            Fortify::username() => [trans('auth.failed')],
        ]);
    }

    /**
     * Fire the failed authentication attempt event.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return void
     */
    protected function fireFailedEvent($request)
    {
        event(new Failed(config('fortify.guard'), null, [
            Fortify::username() => $request->{Fortify::username()},
            'password' => $request->password,
        ]));
    }
}


