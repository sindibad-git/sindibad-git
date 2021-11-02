<?php
namespace sindibad\zaincash\provider;
use sindibad\zaincash\command\ZainCashMigrateCommand;
use sindibad\zaincash\config\Configs;
use sindibad\zaincash\ZainCash;
use Illuminate\Support\ServiceProvider;

class ZaincashServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->publishes([
            Configs::CONFIG_FILE_DEFAULT_PATH => config_path(Configs::CONFIG_FILE_NAME . ".php")
        ] , 'config');


        $this->loadRoutesFrom(__DIR__ . "/../routes/web.php");

        $this->loadViewsFrom(__DIR__ . "/../views", "zaincash");

        $this->publishes([
            __DIR__ . "/../views" => resource_path("views/zaincash")
        ] , 'views');

        $this->commands([
            ZainCashMigrateCommand::class
        ]);

//        $this->publishes([
//            __DIR__ . "../views" => config_path("zaincashGatway.php")
//        ] , 'config');
    }

    //used for binding in service container
    public function register()
    {
        //registering class in service container for dependency injection, binding the class means adding it to service container
        //if we want to use this directly in app we will need to  use resolve method like: resolve('ZainCash')
        //in order to avoid that we will use facade accessor to make it kinda abstract
        $this->app->bind("ZainCash" , ZainCash::class);
        //if zaincash class has input parameters this is not how we define the binding

    }
}
