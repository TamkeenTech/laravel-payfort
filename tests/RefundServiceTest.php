<?php

namespace TamkeenTech\Payfort\Test;

use TamkeenTech\Payfort\Test\TestCase;
use TamkeenTech\Payfort\Facades\Payfort;
use TamkeenTech\Payfort\Services\RefundService;
use TamkeenTech\Payfort\Exceptions\PaymentFailed;

class RefundServiceTest extends TestCase
{
    public function test_refund_service_send_the_required_params()
    {
        $this->mock(RefundService::class, function ($mock) {
            $mock->makePartial()
                ->shouldAllowMockingProtectedMethods();

            $mock->shouldReceive('calculateSignature')->andReturn('sssss');
            $mock->shouldReceive('getOperationUrl')->andReturn('test_link');

            $request = [
                "command" => "REFUND",
                "access_code" => null,
                "merchant_identifier" => null,
                "language" => null,
                "fort_id" => 123,
                "currency" => "SAR",
                "amount" => 12300.0,
                "order_description" => "REFUND",
                "signature" => "sssss",
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

        Payfort::refund(123, 123, 100);
    }
    public function test_refund_service_send_the_required_params_retry()
    {
        $this->mock(RefundService::class, function ($mock) {
            $mock->makePartial()
                ->shouldAllowMockingProtectedMethods();

            $mock->shouldReceive('calculateSignature')->andReturn('sssss');
            $mock->shouldReceive('getOperationUrl')->andReturn('test_link');

            $request = [
                "command" => "REFUND",
                "access_code" => null,
                "merchant_identifier" => null,
                "language" => null,
                "fort_id" => 123,
                "currency" => "SAR",
                "amount" => 12300.0,
                "order_description" => "REFUND",
                "signature" => "sssss",
            ];
            $operation_url = 'test_link';
            $response_code = '00773';

            $mock->shouldReceive('callApi')
                ->with($request, $operation_url)
                ->andReturn([
                    'response_code' => $response_code,
                    'response_message' => $response_code
                ]);
        });

        Payfort::refund(123, 123, 100);
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
