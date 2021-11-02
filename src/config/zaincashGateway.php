<?php
return [
    /**
     *
     */
    "eloquent_storage" => true, // Once enabled, package will create transaction_zaincash table(through zaincash:migrate command) and fill it's values when new transaction is submitted
    "callback_url" => "default", // default, <your website domain>/[your custom segment]
    "merchant_id" => "",
    "token" => "",
    "msisdn" => "",
    "lang" => "en", //ar,en
    "production" => false,


//    Leave untouched if given url's are the one's Zaincash presents
    /**
     *
     */
    "urls" => [
        "test" => [
            "transaction_init_url" => "https://test.zaincash.iq/transaction/init",
            "transaction_payment_url" => "https://test.zaincash.iq/transaction/pay?id=%s"
        ],
        "production" => [
            "transaction_init_url" => "https://api.zaincash.iq/transaction/init",
            "transaction_payment_url" => "https://api.zaincash.iq/transaction/pay?id=%s"
        ]
    ]
];
