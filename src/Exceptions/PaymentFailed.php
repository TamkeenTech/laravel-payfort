<?php

namespace TamkeenTech\Payfort\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;

class PaymentFailed extends Exception
{
    public function __construct(string $message = null, int $code = null, private readonly ?string $acquirer = '', private readonly ?string $responseCode = '')
    {
        parent::__construct($message, $code);
    }

    public function render(): JsonResponse
    {
        return response()->json([
            'message' => $this->getMessage(),
            'acquirer' => $this->acquirer,
            'response_code' => $this->responseCode,
        ], $this->getCode() ?: 500);
    }

    public function context(): array
    {
        return [
            'acquirer' => $this->acquirer,
            'response_code' => $this->responseCode,
        ];
    }

    public function getAcquirer(): string|null
    {
        return $this->acquirer;
    }

    public function getResponseCode(): string|null
    {
        return $this->responseCode;
    }
}
