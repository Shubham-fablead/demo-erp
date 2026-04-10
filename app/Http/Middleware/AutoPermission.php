<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class AutoPermission
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();

        if (!$user) {
            return redirect()->route('login');
        }

        // Keep full access for admin/sub-admin roles.
        if (
            $user->role === 'admin' ||
            $user->role === 'sub-admin' ||
            (int) $user->role_id === 1
        ) {
            return $next($request);
        }

        $route = $request->route();
        $routeName = $route ? $route->getName() : null;
        $normalizedRouteName = $routeName ? strtolower($routeName) : null;

        if (!$normalizedRouteName) {
            return $next($request);
        }

        $parts = explode('.', $normalizedRouteName);

        if (count($parts) < 2) {
            return $next($request);
        }

        $module = $parts[0];
        $action = end($parts);

        // Always allow staff dashboard/profile.
        if ($module === 'auth' && in_array($action, ['dashboard', 'profile'], true)) {
            return $next($request);
        }

        // Notifications list/detail should be available to authenticated staff users.
        if ($module === 'notifications') {
            return $next($request);
        }

        // Global header search should be available for authenticated users.
        if ($normalizedRouteName === 'users.ajaxsearch') {
            return $next($request);
        }

        // Explicit route-name mapping for cases where module prefix is not enough.
        $routeNameModuleMap = [
            'auth.taxrates' => 15,
            'auth.currency' => 15,
        ];

        $moduleIdMap = [
            'product' => 1,
            'category' => 6,
            'brand' => 6,
            'unit' => 6,
            'labour_item' => 6,
            'sale' => 2,
            'sales' => 2,
            'salesreturn' => 2,
            'quotation' => 2,
            'purchase' => 3,
            'purchasereturn' => 3,
            'invoice' => 4,
            'custom_invoice' => 4,
            'custom-invoice' => 4,
            'expense' => 5,
            'expensetype' => 5,
            'staff' => 8,
            'salary' => 8,
            'subbranch' => 8,
            'customer' => 9,
            'vendor' => 10,
            'setting' => 14,
            'account_ledger' => 16,
            'accounting' => 16,
            'income-statement' => 16,
            'banks' => 16,
            'inventory' => 17,
            'appointments' => 17,
            'gst' => 20,
            'exports' => 20,
            'advance_pay' => 23,
            'attendance' => 26,
            'transaction' => 27,
            'credit-notes' => 27,
            'credit-notes-items' => 27,
            'debit-notes-items' => 27,
        ];

        // BOM and Production use the 'inventory' prefix but need their own module IDs
        $routeNameModuleMap['inventory.bom.list']  = 28;
        $routeNameModuleMap['inventory.bom.add']   = 28;
        $routeNameModuleMap['inventory.bom.create'] = 28;
        $routeNameModuleMap['inventory.bom.edit']  = 28;
        $routeNameModuleMap['inventory.bom.view']  = 28;
        $routeNameModuleMap['inventory.production.list'] = 29;
        $routeNameModuleMap['inventory.production.add']  = 29;
        $routeNameModuleMap['inventory.production.edit'] = 29;
        $routeNameModuleMap['inventory.production.view'] = 29;

        $moduleId = $routeNameModuleMap[$normalizedRouteName] ?? ($moduleIdMap[$module] ?? null);

        if (!$moduleId) {
            return redirect()->route('auth.profile')
                ->with('error', 'You do not have permission to access this page');
        }

        // Returns are restricted more tightly: require both add + edit on parent module.
        if (in_array($module, ['salesreturn', 'purchasereturn'], true)) {
            $returnsPermission = DB::table('user_permissions')
                ->where('user_id', $user->id)
                ->where('module_id', $moduleId)
                ->first();

            if (
                !$returnsPermission ||
                (int) ($returnsPermission->add ?? 0) !== 1 ||
                (int) ($returnsPermission->edit ?? 0) !== 1
            ) {
                return redirect()->route('auth.profile')
                    ->with('error', 'You do not have permission to access this page');
            }

            return $next($request);
        }

        $actionMap = [
            'list' => 'view',
            'index' => 'view',
            'show' => 'view',
            'view' => 'view',

            'add' => 'add',
            'create' => 'add',
            'store' => 'add',

            'edit' => 'edit',
            'update' => 'edit',

            'delete' => 'delete',
            'destroy' => 'delete',
        ];

        // Non-CRUD actions (report/pdf/export/etc.) should still require view permission.
        $action = $actionMap[$action] ?? 'view';

        $permission = DB::table('user_permissions')
            ->where('user_id', $user->id)
            ->where('module_id', $moduleId)
            ->first();

        if (!$permission || (int) ($permission->$action ?? 0) !== 1) {
            return redirect()->route('auth.profile')
                ->with('error', 'You do not have permission to access this page');
        }

        return $next($request);
    }
}
