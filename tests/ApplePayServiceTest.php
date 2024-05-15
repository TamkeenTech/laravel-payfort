<?php

namespace TamkeenTech\Payfort\Test;

use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use TamkeenTech\Payfort\Test\TestCase;
use TamkeenTech\Payfort\Facades\Payfort;
use TamkeenTech\Payfort\Exceptions\PaymentFailed;
use TamkeenTech\Payfort\Services\ApplePayService;

class ApplePayServiceTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        Http::fake([
            '*' => Http::response([
                'response_code' => '06000',
                'response_message' => '06000',
                'reconciliation_reference' => '123412341234',
                'merchant_reference' => 'test_merchant_reference',
                'authorization_code' => '123456',
                'fort_id' => '1234567890'
            ])
        ]);
    }

    /** @test */
    public function purchase_service_send_the_required_params_with_apple()
    {
        $this->mock(ApplePayService::class, function ($mock) {
            $mock->makePartial()
                ->shouldAllowMockingProtectedMethods();

            $mock->shouldReceive('validateSignature')->andReturnSelf();
            $mock->shouldReceive('validateResponseCode')->andReturnSelf();
            $mock->shouldReceive('calculateSignature')->andReturn("signature");

            $mock->shouldReceive('getOperationUrl')->andReturn('test_link');
        });

        Payfort::applePay(
            [
                "paymentData" => [
                    "data" => "apple_data",
                    "header" => [
                        'ephemeralPublicKey' => 'apple_ephemeralPublicKey',
                        'publicKeyHash' => 'apple_publicKeyHash',
                        'transactionId' => 'apple_transactionId',
                    ],
                    "signature" => "signature",
                ],
                "paymentMethod" => [
                    'displayName' => 'apple_displayName',
                    'network' => 'apple_network',
                    'type' => 'apple_type',
                ],
            ],
            1000,
            "test@test.com",
            "redirect_uri"
        );

        Http::assertSent(function (Request $request) {
            [
                "command" => "PURCHASE",
                "merchant_reference" => "merchant_reference",
                "access_code" => null,
                "merchant_identifier" => null,
                "customer_ip" => "127.0.0.1",
                "currency" => config('payfort.currency'),
                "customer_email" => "test@test.com",
                "token_name" => "token_name",
                "language" => null,
                "return_url" => "redirect_uri",
                "amount" => 100000.0,
                "apple_data" => "apple_data",
                "apple_header" => [
                    'apple_ephemeralPublicKey' => 'apple_ephemeralPublicKey',
                    'apple_publicKeyHash' => 'apple_publicKeyHash',
                    'apple_transactionId' => 'apple_transactionId',
                ],
                "apple_paymentMethod" => [
                    'apple_displayName' => 'apple_displayName',
                    'apple_network' => 'apple_network',
                    'apple_type' => 'apple_type',
                ],
                "signature" => "signature",
            ];

            return $request->url() === 'test_link' && $request->method() === 'POST';
        });
    }

    /** @test */
    public function authorize_service_send_the_required_params_with_apple()
    {
        $this->mock(ApplePayService::class, function ($mock) {
            $mock->makePartial()
                ->shouldAllowMockingProtectedMethods();

            $mock->shouldReceive('validateSignature')->andReturnSelf();
            $mock->shouldReceive('validateResponseCode')->andReturnSelf();
            $mock->shouldReceive('calculateSignature')->andReturn("signature");
            $mock->shouldReceive('getOperationUrl')->andReturn('test_link');
        });

        Payfort::applePay([
            "paymentData" => [
                "data" => "apple_data",
                "header" => [
                    'ephemeralPublicKey' => 'apple_ephemeralPublicKey',
                    'publicKeyHash' => 'apple_publicKeyHash',
                    'transactionId' => 'apple_transactionId',
                ],
                "signature" => "signature",
            ],
            "paymentMethod" => [
                'displayName' => 'apple_displayName',
                'network' => 'apple_network',
                'type' => 'apple_type',
            ],
        ], 1000, "test@test.com");


        Http::assertSent(function (Request $request) {
            [
                "command" => "PURCHASE",
                "merchant_reference" => "merchant_reference",
                "access_code" => null,
                "merchant_identifier" => null,
                "customer_ip" => "127.0.0.1",
                "currency" => config('payfort.currency'),
                "customer_email" => "test@test.com",
                "token_name" => "token_name",
                "language" => null,
                "return_url" => "redirect_uri",
                "amount" => 100000.0,
                "apple_data" => "apple_data",
                "apple_header" => [
                    'apple_ephemeralPublicKey' => 'apple_ephemeralPublicKey',
                    'apple_publicKeyHash' => 'apple_publicKeyHash',
                    'apple_transactionId' => 'apple_transactionId',
                ],
                "apple_paymentMethod" => [
                    'apple_displayName' => 'apple_displayName',
                    'apple_network' => 'apple_network',
                    'apple_type' => 'apple_type',
                ],
                "signature" => "signature",
            ];

            return $request->url() === 'test_link' && $request->method() === 'POST';
        });
    }

    /** @test */
    public function can_access_response_fields()
    {
        $this->partialMock(ApplePayService::class, function ($mock) {
            $mock->shouldAllowMockingProtectedMethods();

            $mock->shouldReceive('validateSignature')->andReturnSelf();
            $mock->shouldReceive('validateResponseCode')->andReturnSelf();
            $mock->shouldReceive('calculateSignature')->andReturn("signature");

            $mock->shouldReceive('getOperationUrl')->andReturn('test_link');
        });

        $payfort = Payfort::applePay([
            "paymentData" => [
                "data" => "apple_data",
                "header" => [
                    'ephemeralPublicKey' => 'apple_ephemeralPublicKey',
                    'publicKeyHash' => 'apple_publicKeyHash',
                    'transactionId' => 'apple_transactionId',
                ],
                "signature" => "signature",
            ],
            "paymentMethod" => [
                'displayName' => 'apple_displayName',
                'network' => 'apple_network',
                'type' => 'apple_type',
            ],
        ], 1000, "test@test.com");

        $this->assertEquals("1234567890", $payfort->getResponseFortId());
        $this->assertEquals("test_merchant_reference", $payfort->getResponseMerchantReference());
        $this->assertEquals("123456", $payfort->getResponseAuthorizationCode());
        $this->assertEquals("123412341234", $payfort->getResponseReconciliationReference());
    }
}
