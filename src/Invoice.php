<?php

namespace sindibad\zaincash;

use sindibad\zaincash\config\Configs;
use sindibad\zaincash\models\Transaction;
use Carbon\Carbon;
use Firebase\JWT\JWT;
use GuzzleHttp\Client as HTTPClient;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Request;

class Invoice extends Model
{

    protected $fillable = [
        'id',
        'transactionId'
    ];

    protected $config = null;
    //Configs embedded in Invoice
    protected $embeddedConfig = [];


    private $transactionId = null;
    private $referenceNumber = null;
    protected $amount = 0;
    protected $description = '';
    protected $orderId = null;
    protected $backBtnUrl = "/";
    protected $backBtnText = "Back";
    protected $extras   = [];
    protected $content  = [];
    protected $serviceType='--';


    protected $errorMessages  = [];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->config = Configs::loadConfigs();
    }

    /**
     * @return int
     */
    public function getAmount(): int
    {
        return $this->amount;
    }

    /**
     * @param int $amount
     * @return Invoice
     */
    public function setAmount(int $amount)
    {
        $this->amount = $amount;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getOrderId()
    {
        return $this->orderId;
    }

    /**
     * @param null $orderId
     * @return Invoice
     */
    public function setOrderId($orderId)
    {
        $this->orderId = $orderId;
        return $this;
    }


    /**
     * @return mixed
     */
    public function getServiceType()
    {
        return $this->serviceType;
    }

    /**
     * @param mixed $serviceType
     * @return Invoice
     */
    public function setServiceType($serviceType)
    {
        $this->serviceType = $serviceType;
        return $this;
    }



    /**
     * @return string
     */
    public function getBackBtnUrl(): string
    {
        return $this->backBtnUrl;
    }

    /**
     * @param string $backBtnUrl
     * @return Invoice
     */
    public function setBackBtnUrl(string $backBtnUrl)
    {
        $this->backBtnUrl = $backBtnUrl;
        return $this;
    }

    /**
     * @return string
     */
    public function getBackBtnText(): string
    {
        return $this->backBtnText;
    }

    /**
     * @return string
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * @param string $description
     */
    public function setDescription(string $description): void
    {
        $this->description = $description;
    }

    /**
     * @param string $backBtnText
     */
    public function setBackBtnText(string $backBtnText)
    {
        $this->backBtnText = $backBtnText;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getTransactionId()
    {
        return $this->transactionId;
    }


    /**
     * @param $url
     * @return Invoice
     */
    public function setCallbackUrl($url)
    {
        $this->config[Configs::CALLBACK_URL] = $url;
        $this->embeddedConfig[Configs::CALLBACK_URL] = $url;
        return $this;
    }

    public function setEnableElequentStorage($enable)
    {
        $this->config[Configs::ELOQUENT_STORAGE_ENABLE] = $enable;
        $this->embeddedConfig[Configs::ELOQUENT_STORAGE_ENABLE] = $enable;
        return $this;
    }

    /**
     * @return string
     */
    public function getCallbackUrl()
    {
        return $this->config[Configs::CALLBACK_URL];
    }

    /**
     * @param $lang
     * @return Invoice
     */
    public function setLang($lang)
    {
        $this->config[Configs::LANG] = $lang;
        $this->embeddedConfig[Configs::LANG] = $lang;
        return $this;
    }

    public function getLang($lang){
        return $this->config[Configs::LANG];
    }

    public function appendExtra($key, $value)
    {
        $this->extras[$key] = $value;
        return $this;
    }


    public function getErrors()
    {
        return $this->errorMessages;
    }

    /**
     * @return null|string
     */
    public function getReferenceNumber()
    {
        return $this->referenceNumber;
    }

    public function pay()
    {
        //init payload
        $payload = [
            "amount" => $this->getAmount(),
            "id"    => $this->getOrderId(),
            "b"     => $this->getBackBtnText(), // Back Button Text
            "bu"    => $this->getBackBtnUrl(), // Back Button Url
            "conf"  => $this->embeddedConfig,
        ];

        if (!$this->config[Configs::ELOQUENT_STORAGE_ENABLE]){
            $payload["extra"] = $this->extras;
            $payload['serviceType'] = $this->getServiceType();
        }

        //MAIN PART
        $jwt = $this->encode($payload);


        $context=$this->prepareBody($jwt);
        $payInvoiceInfo = $this->initInvoice($context);

        if (! $payInvoiceInfo){
            return false;
        }

        $payInvoiceInfo['orderId'] = $this->getOrderId();
        $this->transactionId = $payInvoiceInfo['id'];

        if ($this->config[Configs::ELOQUENT_STORAGE_ENABLE]) {
            $transaction=new Transaction();
            $transaction->amount=$this->getAmount();
            $transaction->transactionId=$this->transactionId;
            $transaction->status=$payInvoiceInfo['status'];
            $transaction->serviceType=$this->getServiceType();
            $transaction->paid_at=null;
            $transaction->orderId=$this->getOrderId();
            $transaction->applicant_ip=Request::ip();
            $transaction->extras=$this->extras;
            $transaction->save();
        }

        $payInvoiceInfo['url'] = sprintf($this->config[Configs::TRANSACTION_PAYMENT_URL] , $payInvoiceInfo['id']);


        return $payInvoiceInfo;
    }

    private function encode($payload)
    {
        $body = [
            'serviceType'=>$this->getServiceType(),
            'amount' => $this->getAmount(),
            'orderId' => urlencode(Crypt::encrypt(json_encode($payload , JSON_UNESCAPED_UNICODE))),
            'msisdn' => $this->config[Configs::MSISDN],
            'redirectUrl' => $this->config[Configs::CALLBACK_URL],
        ];

        $token = JWT::encode(
            $body,      //Data to be encoded in the JWT
            $this->config[Configs::TOKEN],
            'HS256'
        );

        return $token;
    }

    public function prepareBody($token)
    {
        $requestBody = [
            'form_params' => [
                'token' => urlencode($token),
                'merchantId' => $this->config[Configs::MERCHANT_ID],
                'lang' => $this->config[Configs::LANG],
            ]
        ];

        return $requestBody;
    }

    private function initInvoice(array $requestBody)
    {
        $client = new HTTPClient();

        $response = $client->request('POST', $this->config[Configs::TRANSACTION_INIT_URL], $requestBody);

        if ($response === false || $response === null){
            $this->errorMessages[] = "No response from cURL";
            return false;
        }

        $content = json_decode($response->getBody(), true);

        if (array_key_exists("err" , $content)){
            $this->errorMessages = $content['err'];
            return false;
        }

        return $content;
    }





}
