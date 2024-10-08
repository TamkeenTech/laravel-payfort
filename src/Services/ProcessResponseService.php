<?php

namespace TamkeenTech\Payfort\Services;

use TamkeenTech\Payfort\Events\PayfortMessageLog;
use TamkeenTech\Payfort\Exceptions\PaymentFailed;
use TamkeenTech\Payfort\Repositories\Payfort;
use TamkeenTech\Payfort\Traits\FortParams;
use TamkeenTech\Payfort\Traits\ResponseHelpers;
use TamkeenTech\Payfort\Traits\Signature;

class ProcessResponseService extends Payfort
{
    use FortParams, ResponseHelpers, Signature;

    protected $fort_params = [];

    /**
     * @throws PaymentFailed
     */
    public function handle(): self
    {
        $this->validateFortParams();

        PayfortMessageLog::dispatch(null, $this->fort_params);

        $this->validateResponseCode();
        $this->validateSignature('response');

        $this->response = $this->fort_params;

        return $this;
    }
}
