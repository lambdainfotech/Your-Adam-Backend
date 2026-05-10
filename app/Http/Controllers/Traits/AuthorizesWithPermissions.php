<?php

namespace App\Http\Controllers\Traits;

use Illuminate\Support\Str;

trait AuthorizesWithPermissions
{
    /**
     * Override this in controllers that don't follow the naming convention.
     * e.g. StockInController → protected string $permissionModule = 'stock-in';
     */
    protected ?string $permissionModule = null;

    /**
     * Map method names to permission actions.
     */
    protected array $permissionActionMap = [
        'index'   => 'view',
        'show'    => 'view',
        'create'  => 'create',
        'store'   => 'create',
        'edit'    => 'edit',
        'update'  => 'edit',
        'destroy' => 'delete',
    ];

    /**
     * Override this for methods that don't follow the default mapping.
     * e.g. ['toggleStatus' => 'edit', 'quickUpdateStock' => 'edit']
     */
    protected array $customActionMap = [];

    /**
     * Authorize the current action based on controller + method name.
     * Call this at the start of any controller method that needs protection.
     * Returns redirect with flash message instead of 403 error page.
     */
    /**
     * Get the authenticated user from various sources.
     * JWT middleware may set user on request resolver rather than auth guard.
     */
    protected function getAuthUser(): ?\Illuminate\Contracts\Auth\Authenticatable
    {
        return auth()->user()
            ?? request()->user()
            ?? auth('jwt')->user()
            ?? null;
    }

    protected function authorizeAction(?string $explicitAction = null): mixed
    {
        $user = $this->getAuthUser();

        if (!$user) {
            return redirect()->route('admin.login')->with('error', 'Your session has expired. Please login again.');
        }

        $module = $this->getPermissionModule();
        $action = $explicitAction ?? $this->getPermissionAction();
        $permission = "{$module}.{$action}";

        if (!$user->hasPermission($permission)) {
            $actionLabel = match ($action) {
                'view'   => 'view',
                'create' => 'create',
                'edit'   => 'edit',
                'delete' => 'delete',
                default  => 'manage',
            };

            $moduleLabel = ucwords(str_replace('-', ' ', $module));
            $message = "You do not have permission to {$actionLabel} {$moduleLabel}. Please contact your administrator.";

            // Prevent redirect loop: if previous URL is same as current, show error page
            $previous = url()->previous();
            $current = url()->current();

            if ($previous === $current) {
                abort(403, $message);
            }

            return redirect()->back()->with('error', $message);
        }

        return null;
    }

    /**
     * Authorize multiple permissions (any one of them).
     */
    protected function authorizeAny(array $permissions): void
    {
        $user = $this->getAuthUser();
        abort_unless($user, 403, 'You do not have permission to perform this action.');
        $hasAny = collect($permissions)->some(fn ($p) => $user->hasPermission($p));
        abort_unless($hasAny, 403, 'You do not have permission to perform this action.');
    }

    /**
     * Get the permission module from controller name.
     */
    protected function getPermissionModule(): string
    {
        if ($this->permissionModule !== null) {
            return $this->permissionModule;
        }

        $class = class_basename(static::class);
        $name = str_replace('Controller', '', $class);

        return Str::kebab(Str::plural(Str::snake($name)));
    }

    /**
     * Get the permission action from the calling method name.
     */
    protected function getPermissionAction(): string
    {
        $method = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 3)[2]['function'] ?? 'view';

        $map = array_merge($this->permissionActionMap, $this->customActionMap);

        return $map[$method] ?? 'manage';
    }
}
