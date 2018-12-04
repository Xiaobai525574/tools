<?php

namespace App\Providers;

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
        //注册sqlExcel类
        $this->app->bind(sqlExcelService::class, function () {
            return new sqlExcelService();
        });

    }
}
