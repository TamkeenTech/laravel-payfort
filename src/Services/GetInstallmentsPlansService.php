<?php

namespace TamkeenTech\Payfort\Services;

use TamkeenTech\Payfort\Exceptions\RequestFailed;
use TamkeenTech\Payfort\Repositories\Payfort;

class GetInstallmentsPlansService extends Payfort
{
    public function handle()
    {
        $request = [
            'query_command' => 'GET_INSTALLMENTS_PLANS',
            'access_code' => $this->merchant['access_code'],
            'merchant_identifier' => $this->merchant['merchant_identifier'],
            'language' => $this->language,
        ];
        // calculating signature
        $request['signature'] = $this->calculateSignature($request);
        $this->response = $this->callApi($request, $this->getOperationUrl(), false);
        throw_unless(
            $this->isSuccessful($this->response['response_code']),
            RequestFailed::class,
            "{$this->response['response_code']} - {$this->response['response_message']}"
        );

        return $this->getInstallmentDetails();
    }

    private function isSuccessful($response_code): bool
    {
        return substr($response_code, 0, 2) === '62' &&
            substr($response_code, 2) === '000';
    }

    private function getInstallmentDetails()
    {
        return $this->response['installment_detail'];
    }
}
