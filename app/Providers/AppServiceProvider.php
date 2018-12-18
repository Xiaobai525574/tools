<?php

namespace App\Providers;

use App\Http\Services\CodeService;
use App\Http\Services\sqlExcelService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        /*注册Excel处理类*/
        $this->app->bind(sqlExcelService::class, function () {
            return new sqlExcelService();
        });
        /*注册代码处理类*/
        $this->app->bind(CodeService::class, function () {
            return new CodeService();
        });

    }
}
