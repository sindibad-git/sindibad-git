<?php

namespace sindibad\zaincash\facades;

use sindibad\zaincash\config\Configs;
use sindibad\zaincash\Invoice;
use sindibad\zaincash\Payment;
use Illuminate\Support\Facades\Facade;

/**
 * @method static Invoice Invoice
 * @method static Payment callBackResponse($token=null)
 */
class ZainCash extends Facade
{
    protected static function getFacadeAccessor()
   {
       return "ZainCash";
   }
}
