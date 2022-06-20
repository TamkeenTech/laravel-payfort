<?php

namespace TamkeenTech\Payfort\Test;

use TamkeenTech\Payfort\Test\TestCase;
use TamkeenTech\Payfort\Facades\Payfort;
use TamkeenTech\Payfort\Services\TokenizationService;

class TokenizationServiceTest extends TestCase
{
    /** @test */
    public function params_are_set_correctly()
    {
        $returnUrl = 'http://localhost';

        $this->partialMock(TokenizationService::class, function ($mock) {
            $mock->shouldAllowMockingProtectedMethods();

            $mock->shouldReceive('generateMerchantReference')
                ->andReturn('refernece');

            $mock->shouldReceive('calculateSignature')
                ->andReturn('signature');
        });

        $return = Payfort::tokenization(100, $returnUrl);

        $this->assertEquals([
            "service_command" => "TOKENIZATION",
            "merchant_identifier" => null,
            "access_code" => null,
            "language" => null,
            "return_url" => $returnUrl,
            "merchant_reference" => "refernece",
            "signature" => "signature"
        ], $return['params']);
    }

    /** @test */
    public function return_form_params_by_default()
    {
        $returnUrl = 'http://localhost';

        $this->partialMock(TokenizationService::class, function ($mock) {
            $mock->shouldAllowMockingProtectedMethods();

            $mock->shouldReceive('generateMerchantReference')
                ->andReturn('refernece');

            $mock->shouldReceive('calculateSignature')
                ->andReturn('signature');
        });

        $return = Payfort::tokenization(100, $returnUrl);

        $this->assertArrayHasKey('form', $return);
    }

    /** @test */
    public function can_exclude_form_params()
    {
        $returnUrl = 'http://localhost';

        $this->partialMock(TokenizationService::class, function ($mock) {
            $mock->shouldAllowMockingProtectedMethods();

            $mock->shouldReceive('generateMerchantReference')
                ->andReturn('refernece');

            $mock->shouldReceive('calculateSignature')
                ->andReturn('signature');
        });

        $return = Payfort::tokenization(100, $returnUrl, false);

        $this->assertArrayNotHasKey('form', $return);
    }

    /** @test */
    public function can_set_merchant_extras_successfully()
    {
        $returnUrl = 'http://localhost';

        $this->partialMock(TokenizationService::class, function ($mock) {
            $mock->shouldAllowMockingProtectedMethods();

            $mock->shouldReceive('generateMerchantReference')
                ->andReturn('refernece');

            $mock->shouldReceive('calculateSignature')
                ->andReturn('signature');
        });

        $return = Payfort::setMerchantExtra(100, "new")
            ->tokenization(100, $returnUrl, false);

        $this->assertEquals([
            "service_command" => "TOKENIZATION",
            "merchant_identifier" => null,
            "access_code" => null,
            "merchant_reference" => "refernece",
            "language" => null,
            "return_url" => $returnUrl,
            "merchant_extra" => 100,
            "merchant_extra1" => "new",
            "signature" => "signature",
        ], $return['params']);
    }
}
