<?php

namespace TamkeenTech\Payfort\Services;

use Illuminate\Support\Facades\Validator;
use TamkeenTech\Payfort\Events\PayfortMessageLog;
use TamkeenTech\Payfort\Exceptions\PaymentFailed;
use TamkeenTech\Payfort\Repositories\Payfort;
use TamkeenTech\Payfort\Traits\FortParams;
use TamkeenTech\Payfort\Traits\ResponseHelpers;
use TamkeenTech\Payfort\Traits\Signature;

class AuthorizePurchaseService extends Payfort
{
    use FortParams, ResponseHelpers, Signature;

    protected $fort_params = [];

    protected $redirect_3ds = false;

    protected $redirect_3ds_url = "";

    protected $command = "PURCHASE";

    /**
     * Installment Parameters
     */
    protected $has_insallments = false;

    protected $installments_type = 'HOSTED';

    protected $insallments_params = [];

    /**
     * @throws PaymentFailed
     */
    public function handle(): self
    {
        // check form params
        $this->validateFortParams();

        // Log tokenization response
        PayfortMessageLog::dispatch(null, $this->fort_params);

        $this->validateSignature();
        $this->validateResponseCode();

        $request = [
            'command' => $this->command,
            'merchant_reference' => $this->fort_params['merchant_reference'],
            'access_code' => $this->merchant['access_code'],
            'merchant_identifier' => $this->merchant['merchant_identifier'],
            'customer_ip' => request()->ip(),
            'currency' => $this->currency,
            'customer_email' => $this->email,
            'token_name' => $this->fort_params['token_name'],
            'language' => $this->language,
            'return_url' => $this->redirect_url,
            'amount' => $this->convertFortAmount($this->amount),
        ];

        if ($this->has_insallments) {
            $request = array_merge($request, $this->insallments_params);
        }

        if (count($this->apple_pay)) {
            $request = array_merge($request, $this->apple_pay);
        }

        $request = array_merge($request, $this->merchant_extras);

        if (isset($this->fort_params['3ds']) && $this->fort_params['3ds'] == 'no') {
            $request['check_3ds'] = 'NO';
        }

        //calculate request signature
        $signature = $this->calculateSignature($request, 'request');
        $request['signature'] = $signature;

        $this->response = $this->callApi($request, $this->getOperationUrl());

        // validate the response returned
        $this->setFortParams($this->response);
        $this->validateFortParams();
        $this->validateSignature();
        $this->set3DSRedirect();

        if ($this->redirect_3ds && $this->redirect_3ds_url) {
            return $this;
        }

        $this->validateResponseCode();

        return $this;
    }

    public function setAuthorizationCommand(): self
    {
        $this->command = "AUTHORIZATION";

        return $this;
    }

    public function should3DsRedirect(): bool
    {
        return $this->redirect_3ds;
    }

    public function get3DsUri(): string
    {
        return $this->redirect_3ds_url;
    }

    public function setInstallmentParams(array $params = []): self
    {
        if (count($params)) {
            // check installments params
            /** @var \Illuminate\Validation\Validator $validator */
            $validator = Validator::make($params, [
                'issuer_code' => 'required|alpha_num|max:8',
                'plan_code' => 'required|alpha_num|max:8',
            ]);

            if ($validator->passes()) {
                // set installments params
                $this->has_insallments = true;
                $this->insallments_params = $validator->validated();
                $this->insallments_params['installments'] = $this->installments_type;
            }
        }

        return $this;
    }

    private function set3DSRedirect()
    {
        $response_code = $this->fort_params['response_code'];
        if ($this->is3DsResponseCode($response_code) && isset($this->fort_params['3ds_url'])) {
            $this->redirect_3ds = true;
            $this->redirect_3ds_url = $this->fort_params['3ds_url'];
        }

        return $this;
    }

    private function is3DsResponseCode($response_code)
    {
        return substr($response_code, 0, 2) === '20' &&
            substr($response_code, 2) === '064';
    }
}
