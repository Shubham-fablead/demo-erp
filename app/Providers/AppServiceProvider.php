<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Artisan;
use App\Models\UserPermission;
use Illuminate\Support\Facades\Auth;
class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if (!File::exists(public_path('storage'))) {
            try {
                Artisan::call('storage:link');
            } catch (\Exception $e) {
                // Optional: Log error or ignore silently
            }
        }
        app()->singleton('hasPermission', function () {
            return function ($moduleId, $action) {
                $user = Auth::user(); // Use default auth guard for web

                if (!$user) return false;

                // Grant all permissions to admin users
                if ($user->role == 'admin' || $user->role_id == 1 || $user->role == 'sub-admin') {
                    return true;
                }

                // Check permission from DB
                return UserPermission::where('user_id', $user->id)
                    ->where('module_id', $moduleId)
                    ->where("{$action}", true)
                    ->exists();
            };
        });
    }
}
