<?php

namespace App\Providers;

use App\Models\Permission;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        'App\Model' => 'App\Policies\ModelPolicy',
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @param Gate $gate
     */
    public function boot()
    {
        $this->registerPolicies();

        if (isset($_SERVER['REQUEST_URI']) && starts_with($_SERVER['REQUEST_URI'], '/admin')) {
            $permissions = cache_remember('permission-collection', 1, function () {
                return Permission::with('roles')->get();
            });

            foreach ($permissions as $permission) {
                Gate::define($permission->name, function ($user) use ($permission) {
                    return $user->hasPermission($permission);
                });
            }
        }
    }
}
