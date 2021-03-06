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

    public function test_refund_service_send_the_required_params()
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
                "currency" => "SAR",
                "amount" => 12300.0,
                "order_description" => "REFUND",
                "signature" => "sssss",
            ])) === 0 && $request->url() === 'test_link' && $request->method() === 'POST';
        });
    }
    public function test_refund_service_send_the_required_params_retry()
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
                "currency" => "SAR",
                "amount" => 12300.0,
                "order_description" => "REFUND",
                "signature" => "sssss",
            ])) === 0 && $request->url() === 'test_link' && $request->method() === 'POST';
        });
    }
    public function test_refund_service_return_exception_if_not_success()
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
}
