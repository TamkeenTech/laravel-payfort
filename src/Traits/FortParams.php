<?php

namespace TamkeenTech\Payfort\Traits;

use TamkeenTech\Payfort\Exceptions\PaymentFailed;

/**
 * fort params
 * @property array $fort_params
 */
trait FortParams
{
    public function setFortParams(array $params): self
    {
        $this->fort_params = $params;

        return $this;
    }

    private function validateFortParams(): self
    {
        if (count($this->fort_params) === 0) {
            $msg = "Invalid Response Parameters";
            throw new PaymentFailed($msg);
        }

        return $this;
    }
}
