<?php
namespace sindibad\zaincash;
use sindibad\zaincash\config\Configs;
use Firebase\JWT\JWT;
use Illuminate\Support\Facades\Request;

class ZainCash
{
    /**
     * @return Invoice
     */
    public function Invoice()
    {
        return new Invoice();
    }

    public function callBackResponse($token = null)
    {
        if (! $token){
            $token = Request::input("token", null);
        }
        return new Payment($token);
    }
}
