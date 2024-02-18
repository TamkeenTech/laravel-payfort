<?php

namespace TamkeenTech\Payfort\Test;

use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use TamkeenTech\Payfort\Test\TestCase;
use TamkeenTech\Payfort\Facades\Payfort;
use TamkeenTech\Payfort\Services\RefundService;
use TamkeenTech\Payfort\Exceptions\PaymentFailed;

class RefundServiceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Http::fake([
            '*' => Http::response([
                'response_code' => '06000',
                'response_message' => '06000'
            ])
        ]);
    }

    /** @test */
    public function refund_service_send_the_required_params()
    {
        $this->mock(RefundService::class, function ($mock) {
            $mock->makePartial()
                ->shouldAllowMockingProtectedMethods();

            $mock->shouldReceive('calculateSignature')->andReturn('sssss');
            $mock->shouldReceive('getOperationUrl')->andReturn('test_link');
        });

        Payfort::refund(123, 123, 100);

        Http::assertSent(function (Request $request) {
            return count(array_diff($request->data(), [
                "command" => "REFUND",
                "access_code" => null,
                "merchant_identifier" => null,
                "language" => null,
                "fort_id" => 123,
                "currency" => config('payfort.currency'),
                "amount" => 12300.0,
                "order_description" => "REFUND",
                "signature" => "sssss",
            ])) === 0 && $request->url() === 'test_link' && $request->method() === 'POST';
        });
    }
    /** @test */
    public function refund_service_send_the_required_params_retry()
    {
        $this->mock(RefundService::class, function ($mock) {
            $mock->makePartial()
                ->shouldAllowMockingProtectedMethods();

            $mock->shouldReceive('calculateSignature')->andReturn('sssss');
            $mock->shouldReceive('getOperationUrl')->andReturn('test_link');
        });

        Payfort::refund(123, 123, 100);

        Http::assertSent(function (Request $request) {
            return count(array_diff($request->data(), [
                "command" => "REFUND",
                "access_code" => null,
                "merchant_identifier" => null,
                "language" => null,
                "fort_id" => 123,
                "currency" => config('payfort.currency'),
                "amount" => 12300.0,
                "order_description" => "REFUND",
                "signature" => "sssss",
            ])) === 0 && $request->url() === 'test_link' && $request->method() === 'POST';
        });
    }

    /** @test */
    public function refund_service_return_exception_if_not_success()
    {
        $this->expectException(PaymentFailed::class);

        $this->mock(RefundService::class, function ($mock) {
            $mock->makePartial()
                ->shouldAllowMockingProtectedMethods();

            $mock->shouldReceive('calculateSignature')->andReturn('sssss');
            $response_code = '07000'; // not success code

            $mock->shouldReceive('callApi')
                ->andReturn([
                    'response_code' => $response_code,
                    'response_message' => $response_code
                ]);
        });

        Payfort::refund(123, 123, 100);
    }

    /** @test */
    public function refund_service_add_merchant_extras()
    {
        $fort_id = 123123;

        $this->partialMock(RefundService::class, function ($mock) {
            $mock->shouldAllowMockingProtectedMethods();

            $mock->shouldReceive('validateSignature')->andReturnSelf();
            $mock->shouldReceive('validateResponseCode')->andReturnSelf();
            $mock->shouldReceive('calculateSignature')->andReturn("signature");

            $mock->shouldReceive('getOperationUrl')->andReturn('test_link');
        });

        Payfort::setMerchantExtra(500)->refund($fort_id, 1000);

        Http::assertSent(function (Request $request) use ($fort_id) {
            return count(array_diff($request->data(), [
                "command" => "REFUND",
                "access_code" => null,
                "merchant_identifier" => null,
                "language" => 'en',
                "fort_id" => $fort_id,
                "currency" => config('payfort.currency'),
                "amount" => 100000.0,
                "merchant_extra" => 500,
                "order_description" => "REFUND",
                "signature" => "signature",
            ])) === 0 && $request->url() === 'test_link' && $request->method() === 'POST';
        });
    }
}
