<?php

namespace TamkeenTech\Payfort\Services;

use TamkeenTech\Payfort\Exceptions\PaymentFailed;
use TamkeenTech\Payfort\Repositories\Payfort;

class VoidService extends Payfort
{
    public function handle(): self
    {
        $request = [
            'command' => 'VOID_AUTHORIZATION',
            'access_code' => $this->merchant['access_code'],
            'merchant_identifier' => $this->merchant['merchant_identifier'],
            'language' => $this->language,
            'fort_id' => $this->fort_id,
        ];

        $request = array_merge($request, $this->merchant_extras);

        $request['signature'] = $this->calculateSignature($request);

        $this->response = $response = $this->callApi($request, $this->getOperationUrl());

        throw_unless(
            $this->isSuccessful($response['response_code']),
            PaymentFailed::class,
            "{$response['response_code']} - {$response['response_message']}"
        );

        return $this;
    }

    private function isSuccessful($response_code): bool
    {
        return substr($response_code, 0, 2) === '08' &&
            substr($response_code, 2) === '000';
    }
}
