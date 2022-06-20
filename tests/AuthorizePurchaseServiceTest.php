<?php

namespace TamkeenTech\Payfort\Test;

use TamkeenTech\Payfort\Test\TestCase;
use TamkeenTech\Payfort\Facades\Payfort;
use TamkeenTech\Payfort\Exceptions\PaymentFailed;
use TamkeenTech\Payfort\Services\AuthorizePurchaseService;

class AuthorizePurchaseServiceTest extends TestCase
{
    /** @test */
    public function purchase_service_send_the_required_params()
    {
        $this->partialMock(AuthorizePurchaseService::class, function ($mock) {
            $mock->shouldAllowMockingProtectedMethods();

            $mock->shouldReceive('validateSignature')->andReturnSelf();
            $mock->shouldReceive('validateResponseCode')->andReturnSelf();
            $mock->shouldReceive('calculateSignature')->andReturn("signature");

            $mock->shouldReceive('getOperationUrl')->andReturn('test_link');
            $_SERVER['REMOTE_ADDR'] = "127.0.0.1";

            $request = [
                "command" => "PURCHASE",
                "merchant_reference" => "merchant_reference",
                "access_code" => null,
                "merchant_identifier" => null,
                "customer_ip" => "127.0.0.1",
                "currency" => "SAR",
                "customer_email" => "test@test.com",
                "token_name" => "token_name",
                "language" => null,
                "return_url" => "redirect_uri",
                "amount" => 100000.0,
                "signature" => "signature",
            ];
            $operation_url = 'test_link';
            $response_code = '06000';

            $mock->shouldReceive('callApi')
                ->with($request, $operation_url)
                ->andReturn([
                    'response_code' => $response_code,
                    'response_message' => $response_code
                ]);
        });

        Payfort::purchase([
            "merchant_reference" => "merchant_reference",
            "response_message" => "test",
            "token_name" => "token_name",
            "signature" => "signature"
        ], 1000, "test@test.com", "redirect_uri");
    }

    /** @test */
    public function purchase_service_send_the_required_params_with_installment()
    {
        $install_params = [
            'issuer_code' => 'ab345678',
            'plan_code' => 'de345678'
        ];

        $this->mock(AuthorizePurchaseService::class, function ($mock) {
            $mock->makePartial()
                ->shouldAllowMockingProtectedMethods();

            $mock->shouldReceive('validateSignature')->andReturnSelf();
            $mock->shouldReceive('validateResponseCode')->andReturnSelf();
            $mock->shouldReceive('calculateSignature')->andReturn("signature");
            $mock->shouldReceive('getOperationUrl')->andReturn('test_link');
            $_SERVER['REMOTE_ADDR'] = "127.0.0.1";

            $request = [
                'command' => "PURCHASE",
                'merchant_reference' => "merchant_reference",
                'access_code' => null,
                'merchant_identifier' => null,
                'customer_ip' => "127.0.0.1",
                'amount' => 100000.0,
                'currency' => "SAR",
                'customer_email' => "test@test.com",
                'installments' => 'HOSTED',
                'issuer_code' => 'ab345678',
                'plan_code' => 'de345678',
                'token_name' => "token_name",
                'language' => null,
                'return_url' => "redirect_uri",
                "signature" => "signature"
            ];
            $operation_url = 'test_link';
            $response_code = '06000';

            $mock->shouldReceive('callApi')
                ->with($request, $operation_url)
                ->andReturn([
                    'response_code' => $response_code,
                    'response_message' => $response_code
                ]);
        });

        Payfort::purchase([
            "merchant_reference" => "merchant_reference",
            "response_message" => "test",

            "token_name" => "token_name",
            "signature" => "signature"
        ], 1000, "test@test.com", "redirect_uri", $install_params);
    }

    /** @test */
    public function authorize_service_send_the_required_params()
    {
        $this->mock(AuthorizePurchaseService::class, function ($mock) {
            $mock->makePartial()
                ->shouldAllowMockingProtectedMethods();

            $mock->shouldReceive('validateSignature')->andReturnSelf();
            $mock->shouldReceive('validateResponseCode')->andReturnSelf();
            $mock->shouldReceive('calculateSignature')->andReturn("signature");

            $mock->shouldReceive('getOperationUrl')->andReturn('test_link');
            $_SERVER['REMOTE_ADDR'] = "127.0.0.1";

            $request = [
                'command' => "AUTHORIZATION",
                'merchant_reference' => "merchant_reference",
                'access_code' => null,
                'merchant_identifier' => null,
                'customer_ip' => "127.0.0.1",
                'amount' => 100000.0,
                'currency' => "SAR",
                'customer_email' => "test@test.com",
                'token_name' => "token_name",
                'language' => null,
                'return_url' => "redirect_uri",
                "signature" => "signature"
            ];
            $operation_url = 'test_link';
            $response_code = '06000';

            $mock->shouldReceive('callApi')
                ->with($request, $operation_url)
                ->andReturn([
                    'response_code' => $response_code,
                    'response_message' => $response_code
                ]);
        });

        Payfort::authorize([
            "merchant_reference" => "merchant_reference",
            "response_message" => "test",
            "token_name" => "token_name",
            "signature" => "signature"
        ], 1000, "test@test.com", "redirect_uri");
    }

    /** @test */
    public function throw_error_if_no_params_from_tokenization()
    {
        $this->expectException(PaymentFailed::class);
        $this->expectExceptionMessage("Invalid Response Parameters");

        Payfort::purchase([], 1000, "test@test.com", "redirect_uri");
    }

    /** @test */
    public function throw_error_if_signature_is_not_valid_from_tokenization()
    {
        $this->expectException(PaymentFailed::class);
        $this->expectExceptionMessage("Invalid signature");

        Payfort::purchase([
            "merchant_reference" => "merchant_reference",
            "response_message" => "test",
            "token_name" => "token_name",
            "signature" => "signature"
        ], 1000, "test@test.com", "redirect_uri");
    }

    /** @test */
    public function throw_error_if_no_success_response_code_from_tokenization()
    {
        $this->expectException(PaymentFailed::class);
        $this->expectExceptionMessage("error_message");

        $this->mock(AuthorizePurchaseService::class, function ($mock) {
            $mock->makePartial()
                ->shouldAllowMockingProtectedMethods();

            $mock->shouldReceive('validateSignature')->andReturnSelf();
        });

        Payfort::purchase([
            "merchant_reference" => "merchant_reference",
            "response_message" => "test",
            "token_name" => "token_name",
            "signature" => "signature",
            "response_code" => "111111",
            "response_message" => "error_message"
        ], 1000, "test@test.com", "redirect_uri");
    }

    /** @test */
    public function return_redirect_3ds_if_exist()
    {
        $this->mock(AuthorizePurchaseService::class, function ($mock) {
            $_SERVER['REMOTE_ADDR'] = "127.0.0.1";

            $mock->makePartial()
                ->shouldAllowMockingProtectedMethods();

            $mock->shouldReceive('validateSignature')->andReturnSelf();
            $mock->shouldReceive('calculateSignature')->andReturn("signature");

            $mock->shouldReceive('callApi')->andReturn([
                'response_code' => '20064', // response code for 3ds
                "3ds_url" => "test_3ds_url"
            ]);
        });

        $payfort = Payfort::purchase([
            "merchant_reference" => "merchant_reference",
            "response_message" => "test",
            "token_name" => "token_name",
            "signature" => "signature",
            "response_code" => 20000,
        ], 1000, "test@test.com", "redirect_uri");

        $this->assertEquals(true, $payfort->should3DsRedirect());
        $this->assertEquals("test_3ds_url", $payfort->get3DsUri());
    }

    /** @test */
    public function throw_error_if_no_params_from_purchase()
    {
        $this->expectException(PaymentFailed::class);
        $this->expectExceptionMessage("Invalid Response Parameters");

        $this->mock(AuthorizePurchaseService::class, function ($mock) {
            $_SERVER['REMOTE_ADDR'] = "127.0.0.1";
            $mock->makePartial()
                ->shouldAllowMockingProtectedMethods();

            $mock->shouldReceive('validateSignature')->andReturnSelf();
            $mock->shouldReceive('calculateSignature')->andReturn("signature");

            $mock->shouldReceive('callApi')->andReturn([]);
        });

        Payfort::purchase([
            "merchant_reference" => "merchant_reference",
            "token_name" => "token_name",
            "response_message" => "test",
            "signature" => "signature",
            "response_code" => 20000,
        ], 1000, "test@test.com", "redirect_uri");
    }

    /** @test */
    public function throw_error_if_signature_is_not_valid_from_purchase()
    {
        $this->expectException(PaymentFailed::class);
        $this->expectExceptionMessage("Invalid signature");

        $this->mock(AuthorizePurchaseService::class, function ($mock) {
            $_SERVER['REMOTE_ADDR'] = "127.0.0.1";

            $mock->makePartial()
                ->shouldAllowMockingProtectedMethods();

            $mock->shouldReceive('calculateSignature')->andReturn("signature");

            $mock->shouldReceive('callApi')->andReturn([
                'response_code' => '20064', // response code for 3ds
                "3ds_url" => "test_3ds_url",
                "signature" => "signatures",
            ]);
        });

        Payfort::purchase([
            "merchant_reference" => "merchant_reference",
            "response_message" => "test",

            "token_name" => "token_name",
            "signature" => "signature",
            "response_code" => 20000,
        ], 1000, "test@test.com", "redirect_uri");
    }

    /** @test */
    public function throw_error_if_no_success_response_code_from_purchase()
    {
        $this->expectException(PaymentFailed::class);
        $this->expectExceptionMessage("error_message_from_response");

        $this->mock(AuthorizePurchaseService::class, function ($mock) {
            $_SERVER['REMOTE_ADDR'] = "127.0.0.1";

            $mock->makePartial()
                ->shouldAllowMockingProtectedMethods();

            $mock->shouldReceive('validateSignature')->andReturnSelf();
            $mock->shouldReceive('calculateSignature')->andReturn("signature");

            $mock->shouldReceive('callApi')->andReturn([
                'response_code' => '10064',
                "signature" => "signatures",
                "response_message" => "error_message_from_response"
            ]);
        });

        Payfort::purchase([
            "merchant_reference" => "merchant_reference",
            "response_message" => "test",
            "token_name" => "token_name",
            "signature" => "signature",
            "response_code" => 20000,
        ], 1000, "test@test.com", "redirect_uri");
    }

    /** @test */
    public function float_amount_is_parsed_successfully_authorize()
    {
        $this->mock(AuthorizePurchaseService::class, function ($mock) {
            $mock->makePartial()
                ->shouldAllowMockingProtectedMethods();

            $mock->shouldReceive('validateSignature')->andReturnSelf();
            $mock->shouldReceive('validateResponseCode')->andReturnSelf();
            $mock->shouldReceive('calculateSignature')->andReturn("signature");

            $mock->shouldReceive('getOperationUrl')->andReturn('test_link');
            $_SERVER['REMOTE_ADDR'] = "127.0.0.1";

            $request = [
                'command' => "AUTHORIZATION",
                'merchant_reference' => "merchant_reference",
                'access_code' => null,
                'merchant_identifier' => null,
                'customer_ip' => "127.0.0.1",
                'amount' => 938630,
                'currency' => "SAR",
                'customer_email' => "test@test.com",
                'token_name' => "token_name",
                'language' => null,
                'return_url' => "redirect_uri",
                "signature" => "signature"
            ];
            $operation_url = 'test_link';
            $response_code = '06000';

            $mock->shouldReceive('callApi')
                ->with($request, $operation_url)
                ->andReturn([
                    'response_code' => $response_code,
                    'response_message' => $response_code
                ]);
        });

        Payfort::authorize([
            "merchant_reference" => "merchant_reference",
            "response_message" => "test",
            "token_name" => "token_name",
            "signature" => "signature"
        ], 9386.30, "test@test.com", "redirect_uri");
    }

    /** @test */
    public function float_amount_is_parsed_successfully_purchase()
    {
        $this->mock(AuthorizePurchaseService::class, function ($mock) {
            $mock->makePartial()
                ->shouldAllowMockingProtectedMethods();

            $mock->shouldReceive('validateSignature')->andReturnSelf();
            $mock->shouldReceive('validateResponseCode')->andReturnSelf();
            $mock->shouldReceive('calculateSignature')->andReturn("signature");

            $mock->shouldReceive('getOperationUrl')->andReturn('test_link');
            $_SERVER['REMOTE_ADDR'] = "127.0.0.1";

            $request = [
                'command' => "PURCHASE",
                'merchant_reference' => "merchant_reference",
                'access_code' => null,
                'merchant_identifier' => null,
                'customer_ip' => "127.0.0.1",
                'amount' => 938630,
                'currency' => "SAR",
                'customer_email' => "test@test.com",
                'token_name' => "token_name",
                'language' => null,
                'return_url' => "redirect_uri",
                "signature" => "signature"
            ];
            $operation_url = 'test_link';
            $response_code = '06000';

            $mock->shouldReceive('callApi')
                ->with($request, $operation_url)
                ->andReturn([
                    'response_code' => $response_code,
                    'response_message' => $response_code
                ]);
        });

        Payfort::purchase([
            "merchant_reference" => "merchant_reference",
            "response_message" => "test",
            "token_name" => "token_name",
            "signature" => "signature"
        ], 9386.30, "test@test.com", "redirect_uri");
    }

    /** @test */
    public function can_set_merchant_extras_successfully()
    {
        $this->mock(AuthorizePurchaseService::class, function ($mock) {
            $mock->makePartial()
                ->shouldAllowMockingProtectedMethods();

            $mock->shouldReceive('validateSignature')->andReturnSelf();
            $mock->shouldReceive('validateResponseCode')->andReturnSelf();
            $mock->shouldReceive('calculateSignature')->andReturn("signature");

            $mock->shouldReceive('getOperationUrl')->andReturn('test_link');
            $_SERVER['REMOTE_ADDR'] = "127.0.0.1";

            $request = [
                'command' => "PURCHASE",
                'merchant_reference' => "merchant_reference",
                'access_code' => null,
                'merchant_identifier' => null,
                'customer_ip' => "127.0.0.1",
                'amount' => 938630,
                'currency' => "SAR",
                'customer_email' => "test@test.com",
                'token_name' => "token_name",
                'language' => null,
                'merchant_extra' => 100,
                'return_url' => "redirect_uri",
                "signature" => "signature"
            ];
            $operation_url = 'test_link';
            $response_code = '06000';

            $mock->shouldReceive('callApi')
                ->with($request, $operation_url)
                ->andReturn([
                    'response_code' => $response_code,
                    'response_message' => $response_code
                ]);
        });

        Payfort::setMerchantExtra(100)
            ->purchase([
                "merchant_reference" => "merchant_reference",
                "response_message" => "test",
                "token_name" => "token_name",
                "signature" => "signature"
            ], 9386.30, "test@test.com", "redirect_uri");
    }
}
