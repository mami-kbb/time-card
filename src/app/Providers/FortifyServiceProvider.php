<?php

namespace App\Providers;

use App\Actions\Fortify\CreateNewUser;
use App\Actions\Fortify\ResetUserPassword;
use App\Actions\Fortify\UpdateUserPassword;
use App\Actions\Fortify\UpdateUserProfileInformation;
use Laravel\Fortify\Http\Requests\LoginRequest as FortifyLoginRequest;
use App\Http\Requests\LoginRequest;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Laravel\Fortify\Fortify;
use Laravel\Fortify\Contracts\LoginResponse;
use App\Http\Responses\LoginResponse as CustomLoginResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Laravel\Fortify\Contracts\LogoutResponse;
use App\Http\Responses\LogoutResponse as CustomLogoutResponse;

class FortifyServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Fortify::createUsersUsing(CreateNewUser::class);

        Fortify::registerView(function () {
            return view('auth.register');
        });

        Fortify::loginView(function () {
            if(request()->is('admin/*')) {
                return view('admin.auth.login');
            }
            return view('auth.login');
        });

        RateLimiter::for('login', function (Request $request) {
            $email = (string)$request->email;
            return Limit::perMinute(10)->by($email . $request->ip());
        });

        $this->app->bind(FortifyLoginRequest::class, LoginRequest::class);

        $this->app->singleton(LoginResponse::class, CustomLoginResponse::class);

        $this->app->singleton(LogoutResponse::class, CustomLogoutResponse::class);

        Fortify::verifyEmailView(function () {
            return view('auth.verify-email');
        });

        Fortify::authenticateUsing(function (Request $request) {
            if ($request->is('admin/*')) {
                config(['fortify.guard' => 'admin']);
            } else {
                config(['fortify.guard' => 'web']);
            }

            $user = User::where('email', $request->email)->first();

            if (!$user || !Hash::check($request->password, $user->password)) {
                return null;
            }

            if ($request->is('admin/*') && $user->role !==1) {
                    return null;
                }

            if (!$request->is('admin/*') && $user->role !== 0) {
                return null;
            }

            return $user;
        });
    }
}
