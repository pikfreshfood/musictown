<?php

namespace App\Providers;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;
use Throwable;

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
        $this->ensureSqliteDatabaseIsReady();
    }

    private function ensureSqliteDatabaseIsReady(): void
    {
        if ($this->app->runningInConsole() || config('database.default') !== 'sqlite') {
            return;
        }

        $database = config('database.connections.sqlite.database');

        if (!$database || $database === ':memory:') {
            return;
        }

        $directory = dirname($database);
        $created = false;

        if (!is_dir($directory) && !@mkdir($directory, 0755, true)) {
            return;
        }

        if (!file_exists($database)) {
            if (!@touch($database)) {
                return;
            }

            $created = true;
        }

        clearstatcache(true, $database);

        if ($created || @filesize($database) === 0 || $this->sqliteNeedsCoreTables()) {
            try {
                Artisan::call('migrate', ['--force' => true]);
            } catch (Throwable) {
                // Let the original database error surface if the host cannot write or migrate.
            }
        }
    }

    private function sqliteNeedsCoreTables(): bool
    {
        try {
            return !Schema::hasTable('users') || !Schema::hasTable('sessions');
        } catch (Throwable) {
            return true;
        }
    }
}
