<?php

namespace App\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class BoundedContextServiceProvider extends ServiceProvider
{
    /**
     * @var array<int, string>
     */
    protected array $contexts = [
        'Auth',
        'Usuario',
        'Paciente',
        'Marco',
        'Luna',
        'Proteccion',
        'Receta',
        'Venta',
    ];

    public function boot(): void
    {
        $this->loadBoundedContextRoutes();
        $this->loadBoundedContextMigrations();
    }

    protected function loadBoundedContextRoutes(): void
    {
        foreach ($this->contexts as $context) {
            $webRoutes = base_path("src/{$context}/web.php");

            if (file_exists($webRoutes)) {
                Route::middleware('web')->group($webRoutes);
            }
        }
    }

    protected function loadBoundedContextMigrations(): void
    {
        foreach ($this->contexts as $context) {
            $migrations = base_path("src/{$context}/Infrastructure/Migrations");

            if (is_dir($migrations)) {
                $this->loadMigrationsFrom($migrations);
            }
        }
    }
}
