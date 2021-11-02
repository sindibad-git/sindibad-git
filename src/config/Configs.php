<?php

namespace sindibad\zaincash\config;

use Exception;
use Illuminate\Routing\Route;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Validator;
use phpDocumentor\Reflection\Types\Self_;
use phpDocumentor\Reflection\Types\This;

/*capable of matching all config files with main one in package
you can set these values in Invoice Class instead of inscribing main config file*/

class Configs
{

    const CONFIG_FILE_NAME = "zaincashGateway";
    const CONFIG_FILE_DEFAULT_PATH = __DIR__ . "/" . self::CONFIG_FILE_NAME . ".php";

    const TRANSACTION_INIT_URL = "transaction_init_url";
    const TRANSACTION_PAYMENT_URL = "transaction_payment_url";
    const ELOQUENT_STORAGE_ENABLE = "eloquent_storage";
    const CALLBACK_URL = "callback_url";
    const MERCHANT_ID = "merchant_id";
    const TOKEN = "token";
    const MSISDN = "msisdn";
    const LANG = "lang";
    const PRODUCTION = "production";


    public static function loadConfigs()
    {
        // Using this command for tandom config changes
        Artisan::call("config:cache");
        // get configuration file data
        $configs = config(self::CONFIG_FILE_NAME) ?? [];

        // get default when user don't publish
        if (!$configs) {
            $configs = require(self::CONFIG_FILE_DEFAULT_PATH);
        }
        // Check if provided values are in correct form with nullity check
        self::verifyConfigs($configs);

        // Changing Transaction uurl's based on either production or test mode(true false value)
        if ($configs[self::PRODUCTION]) {
            $configs[self::TRANSACTION_INIT_URL] = $configs['urls']['production'][self::TRANSACTION_INIT_URL];
            $configs[self::TRANSACTION_PAYMENT_URL] = $configs['urls']['production'][self::TRANSACTION_PAYMENT_URL];
        } else {
            $configs[self::TRANSACTION_INIT_URL] = $configs['urls']['test'][self::TRANSACTION_INIT_URL];
            $configs[self::TRANSACTION_PAYMENT_URL] = $configs['urls']['test'][self::TRANSACTION_PAYMENT_URL];
        }

        //In case CALLBACK_URL is set to default use package route
        if ($configs[self::CALLBACK_URL] == "default") {
            $configs[self::CALLBACK_URL] = route("zaincash.package.callback");
        }

        return $configs;
    }

    /**
     * @throws Exception
     */
    public static function verifyConfigs(array $configs)
    {

        if (!isset($configs[self::ELOQUENT_STORAGE_ENABLE]) || !is_bool($configs[self::ELOQUENT_STORAGE_ENABLE])) {
            throw new Exception("ERROR: eloquent_storage is not set or has an invalid type, it should be a boolean.", 1);
        }

        if (!isset($configs[self::MSISDN]) || !is_string($configs[self::MSISDN])) {
            throw new Exception("ERROR: msisdn is not set or has an invalid type, it should be a string.", 1);
        }
        if (!isset($configs[self::TOKEN]) || !is_string($configs[self::TOKEN])) {
            throw new Exception("ERROR: secret token is not set or has an invalid type, it should be a string.", 1);
        }
        if (!isset($configs[self::MERCHANT_ID]) || !is_string($configs[self::MERCHANT_ID])) {
            throw new Exception("ERROR: merchantid is not set or has an invalid type, it should be a string.", 1);
        }
        if (!isset($configs[self::PRODUCTION]) || !is_bool($configs[self::PRODUCTION])) {
            throw new Exception("ERROR: production_cred is not set or has an invalid type, it should be a boolean.", 1);
        }
        if (!isset($configs[self::LANG]) || !is_string($configs[self::LANG]) || !in_array($configs['lang'], ['ar', 'en'])) {
            throw new Exception("ERROR: language is not set or has an invalid type, it should be a string with value of 'ar' or 'en'.", 1);
        }
        if (!isset($configs[self::CALLBACK_URL]) || !is_string($configs[self::CALLBACK_URL])) {
            throw new Exception("ERROR: callback_url is not set or has an invalid type, it should be a string.", 1);
        }
    }
}
