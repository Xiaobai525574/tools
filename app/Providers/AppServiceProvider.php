<?php

namespace App\Providers;

use App\Http\Services\SqlCodeService\SqlCodeSelect;
use App\Http\Services\SqlExcelService\SqlExcel;
use App\Http\Services\SqlService\SqlSelect;
use Illuminate\Support\Facades\Schema;
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
        Schema::defaultStringLength(191);
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        /*注册Excel处理类*/
        $this->app->bind(SqlExcel::class, function () {
            return new SqlExcel();
        });
        /*注册select代码处理类*/
        $this->app->bind(SqlCodeSelect::class, function () {
            return new SqlCodeSelect();
        });
        /*注册select sql处理类*/
        $this->app->bind(SqlSelect::class, function () {
            return new SqlSelect();
        });

    }
}
