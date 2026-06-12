<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\CalculadoraDeContrato;
use App\Rules\Contrato\SemDescontoRule;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(CalculadoraDeContrato::class, function ($app) {

            $classes = config('contrato.regras', []);

            if (empty($classes)) {
                $classes = [SemDescontoRule::class];
            }

            $regras = array_map(function ($classe) use ($app) {
                return $app->make($classe);
            }, $classes);

            return new CalculadoraDeContrato($regras);
        });
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
