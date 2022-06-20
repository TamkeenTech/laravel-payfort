<?php

namespace TamkeenTech\Payfort;

use TamkeenTech\Payfort\Services\AuthorizePurchaseService;
use TamkeenTech\Payfort\Services\CaptureService;
use TamkeenTech\Payfort\Services\CheckStatusService;
use TamkeenTech\Payfort\Services\GetInstallmentsPlansService;
use TamkeenTech\Payfort\Services\ProcessResponseService;
use TamkeenTech\Payfort\Services\RefundService;
use TamkeenTech\Payfort\Services\TokenizationService;
use TamkeenTech\Payfort\Services\VoidService;

class PayfortIntegration
{
    protected $merchant = [];

    protected $merchant_extras = [];

    public function __construct()
    {
        $this->merchant = config('payfort.merchants.default');
    }

    /**
     * Set merchant extras to be sent to payfort
     *
     * @param  string|int  $extra1
     * @param  string|int  $extra2
     * @param  string|int  $extra3
     * @param  string|int  $extra4
     * @param  string|int  $extra5
     * @return self
     */
    public function setMerchantExtra($extra1, $extra2 = '', $extra3 = '', $extra4 = '', $extra5 = ''): self
    {
        for ($i = 1; $i <= 5; $i++) {
            if (! empty(${'extra'.$i}) && ! is_array(${'extra'.$i})) {
                array_push($this->merchant_extras, ${'extra'.$i});
            }
        }

        return $this;
    }

    /**
     * set payfort merchant to be used
     * will use default if not set
     *
     * @param  merchant  $config
     * @return self
     */
    public function setMerchant(array $merchant): self
    {
        $this->merchant = $merchant;

        return $this;
    }

    public function refund($fort_id, $amount)
    {
        return app(RefundService::class)
            ->setMerchant($this->merchant)
            ->setFortId($fort_id)
            ->setAmount($amount)
            ->handle();
    }

    public function void($fort_id)
    {
        return app(VoidService::class)
            ->setMerchant($this->merchant)
            ->setFortId($fort_id)
            ->handle();
    }

    public function checkStatus($fort_id)
    {
        return app(CheckStatusService::class)
            ->setMerchant($this->merchant)
            ->setFortId($fort_id)
            ->handle();
    }

    public function purchase(
        array $fort_params,
        float $amount,
        string $email,
        string $redirect_url,
        array $installments_params = []
    ) {
        /** @var \TamkeenTech\Payfort\Services\AuthorizePurchaseService */
        return app(AuthorizePurchaseService::class)
            ->setMerchant($this->merchant)
            ->setFortParams($fort_params)
            ->setInstallmentParams($installments_params)
            ->setAmount($amount)
            ->setMerchantExtras($this->merchant_extras)
            ->setEmail($email)
            ->setRedirectUrl($redirect_url)
            ->handle();
    }

    public function authorize(
        array $fort_params,
        float $amount,
        string $email,
        string $redirect_url
    ) {
        /** @var \TamkeenTech\Payfort\Services\AuthorizePurchaseService */
        return app(AuthorizePurchaseService::class)
            ->setAuthorizationCommand()
            ->setMerchant($this->merchant)
            ->setFortParams($fort_params)
            ->setMerchantExtras($this->merchant_extras)
            ->setAmount($amount)
            ->setEmail($email)
            ->setRedirectUrl($redirect_url)
            ->handle();
    }

    /**
     * prepare tokenization params and return array
     * by default it will return a form params.
     *
     * @param  float  $amount
     * @param  string  $email
     * @param  boolean  $form
     * @return array
     */
    public function tokenization(
        float $amount,
        string $redirect_url,
        bool $form_flag = true
    ): array {
        return app(TokenizationService::class)
            ->setMerchant($this->merchant)
            ->setAmount($amount)
            ->setMerchantExtras($this->merchant_extras)
            ->setRedirectUrl($redirect_url)
            ->withForm($form_flag)
            ->handle();
    }

    public function processResponse(array $fort_params)
    {
        return app(ProcessResponseService::class)
            ->setMerchant($this->merchant)
            ->setFortParams($fort_params)
            ->handle();
    }

    public function getInstallmentsPlans()
    {
        return app(GetInstallmentsPlansService::class)
            ->setMerchant($this->merchant)
            ->handle();
    }

    public function capture(string $fort_id, $amount)
    {
        return app(CaptureService::class)
            ->setMerchant($this->merchant)
            ->setFortId($fort_id)
            ->setAmount($amount)
            ->handle();
    }
}
