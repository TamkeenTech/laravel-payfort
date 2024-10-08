<?php

namespace TamkeenTech\Payfort\Test;

use TamkeenTech\Payfort\Traits\ResponseHelpers;
use TamkeenTech\Payfort\Exceptions\PaymentFailed;
use Illuminate\Support\Str;

class ResponseHelpersTest extends TestCase
{
    private $responseHelper;

    protected function setUp(): void
    {
        // Create a class that uses the ResponseHelpers trait
        $this->responseHelper = new class {
            use ResponseHelpers;

            public $fort_params = [];

            // Expose the protected method via a public method for testing
            public function callValidateResponseCode()
            {
                return $this->validateResponseCode();
            }
        };
    }

    /** @test */
    public function can_validate_response_code_successfully()
    {
        $this->responseHelper->fort_params['response_code'] = '02000';
        $result = $this->responseHelper->callValidateResponseCode();
        $this->assertSame($this->responseHelper, $result);
    }

    /** @test */
    public function can_not_validate_response_code_failure_without_acquirer_code()
    {
        $this->expectException(PaymentFailed::class);
        $this->expectExceptionMessage('02001 - Invalid card');

        $this->responseHelper->fort_params = [
            'response_code' => '02001',
            'response_message' => 'Invalid card',
        ];

        $this->responseHelper->callValidateResponseCode();
    }

    /** @test */
    public function can_not_validate_response_code_failure_with_acquirer_code()
    {
        $this->expectException(PaymentFailed::class);
        $this->expectExceptionMessage('1234 - Declined by bank');

        $this->responseHelper->fort_params = [
            'response_code' => '02001',
            'response_message' => 'Declined by bank',
            'acquirer_response_code' => '1234',
        ];

        $this->responseHelper->callValidateResponseCode();
    }

    /** @test */
    public function can_get_response()
    {
        $this->responseHelper->fort_params = ['key' => 'value'];
        $result = $this->responseHelper->getResponse();
        $this->assertEquals(['key' => 'value'], $result);
    }

    /** @test */
    public function can_use_magic_getters()
    {
        $this->responseHelper->fort_params = [
            'fort_id' => '12345',
            'reconciliation_reference' => 'abcde',
            'authorization_code' => 'auth123',
            'merchant_reference' => 'merchant123',
        ];

        $this->assertEquals('12345', $this->responseHelper->getResponseFortId());
        $this->assertEquals('abcde', $this->responseHelper->getResponseReconciliationReference());
        $this->assertEquals('auth123', $this->responseHelper->getResponseAuthorizationCode());
        $this->assertEquals('merchant123', $this->responseHelper->getResponseMerchantReference());
    }

    /** @test */
    public function will_return_null_for_undefined_magic_getter()
    {
        $this->responseHelper->fort_params = [];
        $this->assertNull($this->responseHelper->getResponseUndefinedKey());
    }

    /** @test */
    public function can_get_response_payment_method()
    {
        $this->responseHelper->fort_params['payment_option'] = 'VISA';
        $result = $this->responseHelper->getResponsePaymentMethod();
        $this->assertEquals('VISA', $result);
    }
}
