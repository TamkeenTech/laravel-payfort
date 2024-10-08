<?php

namespace TamkeenTech\Payfort\Traits;

use TamkeenTech\Payfort\Exceptions\PaymentFailed;

/**
 * response code
 *
 * @method string getResponseFortId()
 * @method string getResponseReconciliationReference()
 * @method string getResponseAuthorizationCode()
 * @method string getResponseMerchantReference()
 * @method string getResponsePaymentMethod()
 */
trait ResponseHelpers
{
    /**
     * @throws PaymentFailed
     */
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

    public function __call($name, $args)
    {
        if (str($name)->startsWith('getResponse')) {
            $key = str($name)->after('getResponse')->snake()->value();
            return $this->fort_params[$key] ?? null;
        }
    }

    public function getResponsePaymentMethod(): string
    {
        return $this->fort_params['payment_option'];
    }
}
