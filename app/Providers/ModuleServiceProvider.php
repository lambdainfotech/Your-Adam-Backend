<?php

namespace App\Providers;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Symfony\Component\Finder\Finder;

class ModuleServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $modules = config('modules.enabled', []);
        
        foreach ($modules as $module) {
            $this->registerModule($module);
        }
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        $modules = config('modules.enabled', []);
        
        foreach ($modules as $module) {
            $this->bootModule($module);
        }
    }

    /**
     * Register a module.
     */
    protected function registerModule(string $module): void
    {
        $modulePath = config('modules.paths.modules') . '/' . $module;
        
        if (!is_dir($modulePath)) {
            return;
        }

        // Register module service provider
        $providerClass = config('modules.namespace') . "\\{$module}\\Providers\\{$module}ServiceProvider";
        
        if (class_exists($providerClass)) {
            $this->app->register($providerClass);
        }

        // Merge config
        $configPath = "{$modulePath}/Config/" . strtolower($module) . '.php';
        if (file_exists($configPath)) {
            $this->mergeConfigFrom($configPath, strtolower($module));
        }
    }

    /**
     * Boot a module.
     */
    protected function bootModule(string $module): void
    {
        $modulePath = config('modules.paths.modules') . '/' . $module;
        
        if (!is_dir($modulePath)) {
            return;
        }

        // Load migrations
        $migrationsPath = "{$modulePath}/" . config('modules.paths.migrations');
        if (is_dir($migrationsPath)) {
            $this->loadMigrationsFrom($migrationsPath);
        }

        // Load routes
        $routesPath = "{$modulePath}/" . config('modules.paths.routes') . '/api.php';
        if (file_exists($routesPath)) {
            $this->loadRoutesFrom($routesPath);
        }

        // Load translations
        $langPath = "{$modulePath}/Resources/lang";
        if (is_dir($langPath)) {
            $this->loadTranslationsFrom($langPath, strtolower($module));
        }
    }
}
