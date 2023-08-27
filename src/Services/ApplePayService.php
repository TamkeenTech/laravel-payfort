<?php

namespace TamkeenTech\Payfort\Services;

use Illuminate\Support\Facades\Validator;
use TamkeenTech\Payfort\Events\PayfortMessageLog;
use TamkeenTech\Payfort\Repositories\Payfort;
use TamkeenTech\Payfort\Traits\FortParams;
use TamkeenTech\Payfort\Traits\ResponseHelpers;
use TamkeenTech\Payfort\Traits\Signature;

class ApplePayService extends Payfort
{
    use FortParams, ResponseHelpers, Signature;

    protected $fort_params = [];

    protected $command = 'PURCHASE';

    public function handle(): self
    {
        $request = $this->prepareRequest();
        $request = array_merge($request, $this->merchant_extras);

        //calculate request signature
        $request['signature'] = $this->calculateSignature($request, 'request');

        // Log tokenization response
        PayfortMessageLog::dispatch(null, $request);

        $this->response = $this->callApi($request, $this->getOperationUrl());

        // validate the response returned
        $this->setFortParams($this->response);
        $this->validateFortParams();
        $this->validateSignature();
        $this->validateResponseCode();

        return $this;
    }

    public function setCommand(string $command): self
    {
        $this->command = $command;

        return $this;
    }

    public function prepareRequest(): array
    {
        return [
            'apple_data' => data_get($this->fort_params, 'paymentData.data'),
            'apple_header' => [
                'apple_ephemeralPublicKey' => data_get($this->fort_params, 'paymentData.header.ephemeralPublicKey'),
                'apple_publicKeyHash' => data_get($this->fort_params, 'paymentData.header.publicKeyHash'),
                'apple_transactionId' => data_get($this->fort_params, 'paymentData.header.transactionId'),
            ],
            'apple_paymentMethod' => [
                'apple_displayName' => data_get($this->fort_params, 'paymentMethod.displayName'),
                'apple_network' => data_get($this->fort_params, 'paymentMethod.network'),
                'apple_type' => data_get($this->fort_params, 'paymentMethod.type'),
            ],
            'apple_signature' => data_get($this->fort_params, 'paymentData.signature'),
            'digital_wallet' => 'APPLE_PAY',
            'command' => $this->command,
            'merchant_reference' => $this->generateMerchantReference(),
            'access_code' => $this->merchant['access_code'],
            'merchant_identifier' => $this->merchant['merchant_identifier'],
            'customer_ip' => request()->ip(),
            'currency' => $this->currency,
            'customer_email' => $this->email,
            'language' => $this->language,
            'amount' => $this->convertFortAmount($this->amount),
        ];
    }
}
