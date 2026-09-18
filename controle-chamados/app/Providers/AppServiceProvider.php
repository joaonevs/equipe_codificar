<?php

namespace App\Providers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Falha cedo em desenvolvimento se alguma relação for acessada sem eager loading.
        Model::preventLazyLoading($this->app->isLocal());
    }
}
