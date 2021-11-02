<?php
namespace sindibad\zaincash\controller;

use sindibad\zaincash\facades\ZainCash;

class ZainCashCallbackController
{
    public function index(\Illuminate\Http\Request $request)
    {
        //Converts jwt token into currect format for callback response form zaincash gateway
        $payment = ZainCash::callBackResponse($request->token);
        //If view wasn't published provide the one from package
        $viewAccessor = file_exists(resource_path("views/zaincash/callback.blade.php")) ? "." : "::";
        return view("zaincash${viewAccessor}callback" , compact('payment'));
    }
}
