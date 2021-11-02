<?php

namespace sindibad\zaincash;

use sindibad\zaincash\config\Configs;
use sindibad\zaincash\events\PaymentReceivedEvent;
use sindibad\zaincash\models\Transaction;
use Carbon\Carbon;
use Firebase\JWT\BeforeValidException;
use Firebase\JWT\JWT;
use Illuminate\Support\Facades\Crypt;

class Payment
{
    const SUCCESS_STATUS = "success";
    const FAILED_STATUS = "failed";
    const CANCEL_STATUS = "cancel";
    const REPETITIOUS_STATUS = "repetitious";
    const JWT_INVALID_STATUS = "invalid_token";


    private $amount;
    private $status;
    private $orderId;
    private $payment;
    private $operationId;
    private $config;
    private $extras = [];
    private $serviceType = "";
    private $initDate = null;

    private $backButtonText = "";
    private $backButtonLink = "";
    private $errorMessage   = "";


    /** @var Transaction|null */
    private $transaction = null;

    public function __construct($token)
    {
        //if there is no token set in url,status will be cancen
        if (!$token) {
            $this->status = self::CANCEL_STATUS;
            $this->errorMessage = "Canceled by user";
            return;
        }

        $this->config = Configs::loadConfigs();

        //JWT time sync with server loop
        do {
            $try = 0;
            try {
                $this->payment = JWT::decode($token, $this->config[Configs::TOKEN], ['HS256']);
                break;
            } catch (BeforeValidException $e) {
                if (++$try == 2) {
                    $this->status = self::JWT_INVALID_STATUS;
                    $this->errorMessage = "token received is not valid!!!";
                    return;
                }
            }
        }while(true);
        //this is where all data and appended extras are being decoded
        $payload = json_decode(Crypt::decrypt(urldecode($this->payment->orderid)), true);
        $this->backButtonText = $payload['b'];
        $this->backButtonLink = $payload['bu'];
        //payload['conf'] will be priority in array merge
        $this->config   = array_merge($this->config, $payload['conf']);
        $this->orderId  = $payload['id'];
        $this->amount   = $payload['amount'];
        $this->operationId = $this->payment->operationid ?? null;
        $this->initDate = Carbon::createFromTimestamp($this->payment->iat);

        if ($this->config[Configs::ELOQUENT_STORAGE_ENABLE]) {
            $this->transaction = Transaction::query()->where("transactionId", $this->payment->id)->firstOrFail();
            $this->extras = $this->transaction->extras;
            $this->serviceType = $this->transaction->serviceType;

            if ($this->transaction->status === Transaction::PENDING_STATUS) {
                if ($this->transaction->amount == $this->amount && $this->transaction->orderId == $this->orderId){
                    $this->status = $this->payment->status;
                    if ($this->payment->status === Transaction::FAILED_STATUS){
                        $this->errorMessage = $this->payment->msg;
                    }
                }else{
                    $this->status = Transaction::FAILED_STATUS;
                    $this->errorMessage = "Invalid Transaction";
                }
                $this->transaction->status = $this->status === self::SUCCESS_STATUS ? Transaction::PAID_STATUS : Transaction::FAILED_STATUS;
                $this->transaction->operationId = $this->operationId;
                $this->transaction->paid_at = now();
                $this->transaction->save();
            } else {
                $this->status = Transaction::REPETITIOUS_STATUS;
                $this->errorMessage = "Repetitious Transaction";
            }
        }
        else {
            $this->extras = $payload['extra'];
            $this->serviceType = $payload['serviceType'];
            $this->status = $this->payment->status;
        }
        if ($this->status !== self::REPETITIOUS_STATUS){
            //call back response get event
            event(new PaymentReceivedEvent($this));
        }
    }


    public function getExtra($key, $def=null)
    {
        return $this->extras[$key] ?? $def;
    }

    public function getConfig($key, $def=null){
        return $this->config[$key] ?? $def;
    }

    /**
     * @return string
     */
    public function getServiceType()
    {
        return $this->serviceType;
    }

    /**
     * @return array
     */
    public function getExtras()
    {
        return $this->extras;
    }

    /**
     * @return string
     */
    public function getOrderId()
    {
        return $this->orderId;
    }

    /**
     * @return int
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * @return Transaction|null
     */
    public function getTransaction()
    {
        return $this->transaction;
    }

    /**
     * @return string
     */
    public function getOperationId()
    {
        return $this->operationId;
    }

    /**
     * @return string
     */
    public function getBackButtonLink()
    {
        return $this->backButtonLink;
    }

    /**
     * @return string
     */
    public function getBackButtonText()
    {
        return $this->backButtonText;
    }

    /**
     * @return string
     */
    public function getErrorMessage()
    {
        return $this->errorMessage;
    }

    /**
     * @return string
     */
    public function getStatus(): string
    {
        return $this->status;
    }

    /**
     * @return Carbon|null
     */
    public function getInitDate(): ?Carbon
    {
        return $this->initDate;
    }

}
