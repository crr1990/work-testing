<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Boot the authentication services for the application.
     *
     * @return void
     */
    public function boot()
    {
        // 当使用auth中间件的api门卫的时候验证请求体
        $this->app['auth']->viaRequest('api', function ($request)
        {
            return app('auth')->setRequest($request)->user();
        });
    }
}