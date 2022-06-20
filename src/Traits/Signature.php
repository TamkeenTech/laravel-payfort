<?php

namespace TamkeenTech\Payfort\Traits;

use TamkeenTech\Payfort\Exceptions\PaymentFailed;

/**
 * Signature trait
 */
trait Signature
{
    protected function validateSignature($request_type = 'response'): self
    {
        $responseSignature = $this->fort_params['signature'];
        $calculatedSignature = $this->calculateSignature($this->fort_params, $request_type);

        if ($responseSignature !== $calculatedSignature) {
            $msg = "Invalid signature.";

            throw (new PaymentFailed($msg));
        }

        return $this;
    }
}
