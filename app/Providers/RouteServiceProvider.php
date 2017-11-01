<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * This namespace is applied to your controller routes.
     *
     * In addition, it is set as the URL generator's root namespace.
     *
     * @var string
     */
    protected $namespace = 'App\Http\Controllers';

    /**
     * Define your route model bindings, pattern filters, etc.
     *
     * @return void
     */
    public function boot()
    {
        //

        parent::boot();
    }

    /**
     * Define the routes for the application.
     *
     * @return void
     */
    public function map()
    {
        $this->mapApiRoutes();

        $this->mapWebRoutes();

        //
    }

    /**
     * Define the "web" routes for the application.
     *
     * These routes all receive session state, CSRF protection, etc.
     *
     * @return void
     */
    protected function mapWebRoutes()
    {
        Route::group([
            'middleware' => 'web',
            'namespace' => $this->namespace,
        ], function ($router) {
            require base_path('routes/web.php');
            require base_path('routes/admin.php');
        });

        //模块路由
        Route::group([
            'middleware' => 'web',
        ], function ($router) {
            //引用模块路由
            $path = base_path('modules');
            $d = dir($path);
            while ($file = $d->read()) {
                if ($file != '.' && $file != '..') {
                    if (is_dir($path . DIRECTORY_SEPARATOR . $file)) {
                        $web = $path . DIRECTORY_SEPARATOR . $file . '/routes/web.php';
                        $admin = $path . DIRECTORY_SEPARATOR . $file . '/routes/admin.php';
                        if (file_exists($web)) {
                            require $web;
                        }
                        if (file_exists($admin)) {
                            require $admin;
                        }
                    }
                }
            }
        });
    }

    /**
     * Define the "api" routes for the application.
     *
     * These routes are typically stateless.
     *
     * @return void
     */
    protected function mapApiRoutes()
    {
        Route::group([
            'middleware' => 'api',
            'namespace' => $this->namespace,
            'prefix' => 'api',
        ], function ($router) {
            require base_path('routes/api.php');
        });

        //模块路由
        Route::group([
            'middleware' => 'api',
            'prefix' => 'api',
        ], function ($router) {
            $path = base_path('modules');
            $d = dir($path);
            while ($file = $d->read()) {
                if ($file != '.' && $file != '..') {
                    if (is_dir($path . DIRECTORY_SEPARATOR . $file)) {
                        $api = $path . DIRECTORY_SEPARATOR . $file . '/routes/api.php';
                        if (file_exists($api)) {
                            require $api;
                        }
                    }
                }
            }
        });
    }
}
