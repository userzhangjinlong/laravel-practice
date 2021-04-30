<?php

namespace App\Providers;

use Elasticsearch\ClientBuilder;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //注册一个名为es的实例
//        $this->app->singleton('es', function (){
//            $builder = ClientBuilder::create()->setHosts(config('database.elasticsearch.hosts'));
//            //如果是开发环境
//            if (app()->environment() === 'local'){
//                $builder->setLogger(app('log')->driver());
//            }
//
//            return $builder->build();
//        });
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
