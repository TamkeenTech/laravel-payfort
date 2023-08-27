<?php

namespace TamkeenTech\Payfort\Facades;

use Illuminate\Support\Facades\Facade;
use TamkeenTech\Payfort\PayfortIntegration;
use TamkeenTech\Payfort\Services\CaptureService;
use TamkeenTech\Payfort\Services\ProcessResponseService;
use TamkeenTech\Payfort\Services\TokenizationService;
use TamkeenTech\Payfort\Services\VoidService;

/**
 * phpcs:disable
 * @method static PayfortIntegration setMerchant(array $merchant)
 * @method static PayfortIntegration setMerchantExtra(...$extras)
 * @method static \TamkeenTech\Payfort\Services\RefundService refund($fort_id, $amount)
 * @method static TokenizationService tokenization(float $amount, string $redirect_url, bool $form_flag = true)
 * @method static ProcessResponseService processResponse(array $fort_params)
 * @method static CaptureService capture(string $fort_id, $amount)
 * @method static ApplePayService applePay(array $params, float $amount, string $email, string $command = 'PURCHASE')
 * @method static VoidService void($fort_id)
 * phpcs:enable
 *
 * @see \TamkeenTech\Payfort\PayfortIntegration
 */
class Payfort extends Facade
{
    public static function getFacadeAccessor()
    {
        return PayfortIntegration::class;
    }
}
