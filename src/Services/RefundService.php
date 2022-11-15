<?php

namespace TamkeenTech\Payfort\Services;

use TamkeenTech\Payfort\Exceptions\PaymentFailed;
use TamkeenTech\Payfort\Repositories\Payfort;

class RefundService extends Payfort
{
    public function handle(): self
    {
        $this->isTestingFortId($this->fort_id);

        $request = [
            'command' => 'REFUND',
            'access_code' => $this->merchant['access_code'],
            'merchant_identifier' => $this->merchant['merchant_identifier'],
            'language' => $this->language,
            'fort_id' => $this->fort_id,
            'currency' => $this->currency,
            'amount' => $this->convertFortAmount($this->amount),
            'order_description' => "REFUND",
        ];

        $request = array_merge($request, $this->merchant_extras);
        
        // calculating signature
        $request['signature'] = $this->calculateSignature($request);

        $this->response = $response = $this->callApi($request, $this->getOperationUrl());

        throw_unless(
            $this->isSuccessful($response['response_code']),
            new PaymentFailed($response['response_code'].' - '.$response['response_message'])
        );

        return $this;
    }

    private function isSuccessful($response_code): bool
    {
        return (substr($response_code, 0, 2) === '06' &&
                substr($response_code, 2) === '000') || (substr($response_code, 0, 2) === '00' &&
                substr($response_code, 2) === '773');
    }
}
