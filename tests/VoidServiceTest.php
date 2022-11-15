<?php

namespace TamkeenTech\Payfort\Test;

use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Event;
use TamkeenTech\Payfort\Test\TestCase;
use TamkeenTech\Payfort\Facades\Payfort;
use TamkeenTech\Payfort\Services\VoidService;
use TamkeenTech\Payfort\Events\PayfortMessageLog;
use TamkeenTech\Payfort\Exceptions\PaymentFailed;

class VoidServiceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Http::fake([
            '*' => Http::response([
                'response_code' => '08000',
                'response_message' => '08000'
            ])
        ]);
    }

    /** @test */
    public function service_trigger_log_event()
    {
        Event::fake();

        $this->partialMock(VoidService::class, function ($mock) {
            $mock->shouldAllowMockingProtectedMethods();

            $mock->shouldReceive('calculateSignature')->andReturn("signature");
            $mock->shouldReceive('getOperationUrl')->andReturn('test_link');
        });

        Payfort::void(123123123);

        Event::assertDispatched(PayfortMessageLog::class);
    }

    /** @test */
    public function void_service_send_required_params()
    {
        $this->mock(VoidService::class, function ($mock) {
            $mock->makePartial()
                ->shouldAllowMockingProtectedMethods();

            $mock->shouldReceive('calculateSignature')->andReturn('sssss');
            $mock->shouldReceive('getOperationUrl')->andReturn('test_link');
        });

        Payfort::void(123);

        Http::assertSent(function (Request $request) {
            return count(array_diff($request->data(), [
                'command' => 'VOID_AUTHORIZATION',
                "access_code" => null,
                "merchant_identifier" => null,
                "language" => null,
                "fort_id" => 123,
                "signature" => "sssss",
            ])) === 0 && $request->url() === 'test_link' && $request->method() === 'POST';
        });
    }

    /** @test */
    public function void_service_return_exception_if_not_success()
    {
        $this->expectException(PaymentFailed::class);

        $this->mock(VoidService::class, function ($mock) {
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

        Payfort::void(123);
    }

    /** @test */
    public function void_service_add_merchant_extras()
    {
        $fort_id = 123123;

        $this->partialMock(VoidService::class, function ($mock) {
            $mock->shouldAllowMockingProtectedMethods();

            $mock->shouldReceive('validateSignature')->andReturnSelf();
            $mock->shouldReceive('validateResponseCode')->andReturnSelf();
            $mock->shouldReceive('calculateSignature')->andReturn("signature");

            $mock->shouldReceive('getOperationUrl')->andReturn('test_link');
        });

        Payfort::setMerchantExtra(500)->void($fort_id, 1000);

        Http::assertSent(function (Request $request) use ($fort_id) {
            return count(array_diff($request->data(), [
                "command" => "VOID_AUTHORIZATION",
                "access_code" => null,
                "merchant_identifier" => null,
                "language" => null,
                "fort_id" => $fort_id,
                "merchant_extra" => 500,
                "signature" => "signature"
            ])) === 0 && $request->url() === 'test_link' && $request->method() === 'POST';
        });
    }
}
