<?php

namespace TamkeenTech\Payfort\Test;

use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use TamkeenTech\Payfort\Test\TestCase;
use TamkeenTech\Payfort\Facades\Payfort;
use TamkeenTech\Payfort\Services\CaptureService;

class CaptureServiceTest extends TestCase
{
    public function setUp(): void
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
    public function capture_service_send_the_required_params()
    {
        $fort_id = 123123;

        $this->partialMock(CaptureService::class, function ($mock) {
            $mock->shouldAllowMockingProtectedMethods();

            $mock->shouldReceive('validateSignature')->andReturnSelf();
            $mock->shouldReceive('validateResponseCode')->andReturnSelf();
            $mock->shouldReceive('calculateSignature')->andReturn("signature");

            $mock->shouldReceive('getOperationUrl')->andReturn('test_link');
        });

        Payfort::capture($fort_id, 1000);

        Http::assertSent(function (Request $request) use ($fort_id) {
            return count(array_diff($request->data(), [
                'command' => 'CAPTURE',
                'access_code' => null,
                'merchant_identifier' => null,
                'amount' => 100000.0,
                'currency' => 'SAR',
                'language' => 'ar',
                'fort_id' => $fort_id,
                "signature" => "signature",
            ])) === 0 && $request->url() === 'test_link' && $request->method() === 'POST';
        });
    }

    /** @test */
    public function capture_service_add_merchant_extras()
    {
        $fort_id = 123123;

        $this->partialMock(CaptureService::class, function ($mock) {
            $mock->shouldAllowMockingProtectedMethods();

            $mock->shouldReceive('validateSignature')->andReturnSelf();
            $mock->shouldReceive('validateResponseCode')->andReturnSelf();
            $mock->shouldReceive('calculateSignature')->andReturn("signature");

            $mock->shouldReceive('getOperationUrl')->andReturn('test_link');
        });

        Payfort::setMerchantExtra(500)->capture($fort_id, 1000);

        Http::assertSent(function (Request $request) use ($fort_id) {
            return count(array_diff($request->data(), [
                'command' => 'CAPTURE',
                'access_code' => null,
                'merchant_identifier' => null,
                'amount' => 100000.0,
                'currency' => 'SAR',
                'language' => 'ar',
                'merchant_extra' => 500,
                'fort_id' => $fort_id,
                "signature" => "signature",
            ])) === 0 && $request->url() === 'test_link' && $request->method() === 'POST';
        });
    }
}
