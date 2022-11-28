<?php

namespace TamkeenTech\Payfort\Repositories;

use Illuminate\Support\Facades\Http;
use TamkeenTech\Payfort\Events\PayfortMessageLog;
use TamkeenTech\Payfort\Exceptions\PaymentFailed;
use Illuminate\Support\Str;

abstract class Payfort
{
    protected $merchant;


    protected $fort_id;

    protected $language;

    protected $sandbox_mode;

    protected $SHA_type;

    protected $installment_url;

    protected $response;

    protected $merchant_extras = [];

    protected $email = "";

    protected $redirect_url = "";

    /**
     * @var string order currency
     */
    public $currency = 'SAR';

    public $amount;

    public function __construct()
    {
        $this->language = config('payfort.language');
        $this->sandbox_mode = config('payfort.sandbox_mode');
        $this->SHA_type = config('payfort.SHA_type');
    }

    protected function getOperationUrl(): string
    {
        return $this->sandbox_mode ?
            'https://sbpaymentservices.payfort.com/FortAPI/paymentApi' :
            'https://paymentservices.payfort.com/FortAPI/paymentApi';
    }

    protected function generateMerchantReference(): string
    {
        return Str::uuid();
    }

    protected function getGatewayUrl(): string
    {
        $base_uri = $this->sandbox_mode ?
            config('payfort.gateway_sandbox_host') :
            config('payfort.gateway_host');

        return "{$base_uri}FortAPI/paymentPage";
    }

    /**
     * calculate fort signature.
     *
     * @param  array  $data
     * @param  string  $signType  request or response
     * @return string fort signature
     */
    public function calculateSignature(array $data, $signType = 'request')
    {
        unset($data['r']);
        unset($data['signature']);
        unset($data['integration_type']);
        unset($data['token']);
        unset($data['3ds']);

        $shaString = '';
        ksort($data);
        foreach ($data as $k => $v) {
            $shaString .= "$k=$v";
        }

        if ($signType == 'request') {
            $shaString = $this->merchant['SHA_request_phrase'].$shaString.$this->merchant['SHA_request_phrase'];
        } else {
            $shaString = $this->merchant['SHA_response_phrase'].$shaString.$this->merchant['SHA_response_phrase'];
        }

        return hash($this->SHA_type, $shaString);
    }

    /**
     * Send host to host request to the Fort.
     *
     * @param  array  $postData
     * @param  string  $gatewayUrl
     * @param  bool  $shouldBeLogged
     * @return array
     */
    public function callApi($postData, $gatewayUrl, $shouldBeLogged = true): array
    {
        $res = Http::post($gatewayUrl, $postData);

        $res = $res->json();

        // save response log
        if ($shouldBeLogged) {
            PayfortMessageLog::dispatch($postData, $res);
        }

        return $res;
    }

    /**
     * Convert Amount with dicemal points.
     *
     * @param  float  $amount
     * @param  string  $currencyCode
     * @return float
     */
    public function convertFortAmount($amount)
    {
        $decimalPoints = $this->getCurrencyDecimalPoints($this->currency);

        return round($amount * (pow(10, $decimalPoints)), $decimalPoints);
    }

    /**
     * set payfort merchant to be used
     * will use default if not set
     *
     * @param  array  $merchant
     * @return self
     */
    public function setMerchant(array $merchant): self
    {
        $this->merchant = $merchant;

        return $this;
    }

    public function setFortId($fort_id): self
    {
        $this->fort_id = $fort_id;

        return $this;
    }

    public function setAmount($amount): self
    {
        $this->amount = $amount;

        return $this;
    }

    /**
     * Prepare merchant extras
     *
     * @param  array  $extras
     * @return self
     */
    public function setMerchantExtras(array $extras): self
    {
        foreach ($extras as $key => $value) {
            $merchant_key = $key !== 0 ? "merchant_extra{$key}" : "merchant_extra";
            $this->merchant_extras[$merchant_key] = $value;
        }

        return $this;
    }

    public function setEmail($email): self
    {
        $this->email = $email;

        return $this;
    }

    public function setRedirectUrl($url): self
    {
        $this->redirect_url = $url;

        return $this;
    }

    /**
     * Fort id for testing
     * 101010101010 --> throw 07666 transaction declined
     */
    public function isTestingFortId($fort_id)
    {
        $fort_ids = [
            '101010101010' => '07666 - حركة مرفوضة',
        ];

        if (app()->environment('staging') && isset($fort_ids[$fort_id])) {
            throw new PaymentFailed($fort_ids[$fort_id]);
        }
    }

    abstract public function handle();

    /**
     * @param  string  $currency
     * @param  int
     */
    private function getCurrencyDecimalPoints($currency)
    {
        $decimalPoint = 2;

        $arrCurrencies = [
            'JOD' => 3,
            'KWD' => 3,
            'OMR' => 3,
            'TND' => 3,
            'BHD' => 3,
            'LYD' => 3,
            'IQD' => 3,
        ];
        if (isset($arrCurrencies[$currency])) {
            $decimalPoint = $arrCurrencies[$currency];
        }

        return $decimalPoint;
    }
}
