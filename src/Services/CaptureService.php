<?php

namespace TamkeenTech\Payfort\Services;

use TamkeenTech\Payfort\Exceptions\PaymentFailed;
use TamkeenTech\Payfort\Repositories\Payfort;
use TamkeenTech\Payfort\Traits\FortParams;
use TamkeenTech\Payfort\Traits\ResponseHelpers;

class CaptureService extends Payfort
{
    use ResponseHelpers, FortParams;

    /**
     * @throws PaymentFailed
     */
    public function handle(): self
    {
        $request = [
            'command' => 'CAPTURE',
            'access_code' => $this->merchant['access_code'],
            'merchant_identifier' => $this->merchant['merchant_identifier'],
            'amount' => $this->convertFortAmount($this->amount),
            'currency' => $this->currency,
            'language' => $this->language,
            'fort_id' => $this->fort_id,
        ];

        $request = array_merge($request, $this->merchant_extras);

        // calculating signature
        $request['signature'] = $this->calculateSignature($request);

        $this->response = $this->callApi($request, $this->getOperationUrl());

        $this->setFortParams($this->response);
        $this->validateFortParams();
        $this->validateResponseCode();

        return $this;
    }
}
