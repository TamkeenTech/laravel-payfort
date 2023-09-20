<?php

namespace TamkeenTech\Payfort\Traits;

use TamkeenTech\Payfort\Exceptions\PaymentFailed;

/**
 * response code
 */
trait ResponseHelpers
{
    protected function validateResponseCode(): self
    {
        if (substr($this->fort_params['response_code'], 2) != '000') {
            extract($this->fort_params);

            $code = $acquirer_response_code ?? $response_code;
            $message = "{$code} - {$response_message}";

            throw new PaymentFailed(
                message: $message,
                acquirer: $acquirer_response_code ?? '',
                responseCode: $response_code ?? ''
            );
        }

        return $this;
    }

    public function getResponseFortId(): string
    {
        return $this->fort_params['fort_id'];
    }

    public function getResponsePaymentMethod(): string
    {
        return $this->fort_params['payment_option'];
    }
}
