<?php

use Illuminate\Support\Facades\Route;
use sindibad\zaincash\controller\ZainCashCallbackController;

Route::group(["prefix" => "zaincash" , "as" => "zaincash.package."] , function (){
    Route::get("/callback", [ZainCashCallbackController::class , "index"])->name("callback");
});

