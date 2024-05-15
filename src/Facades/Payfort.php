<?php

namespace TamkeenTech\Payfort\Facades;

use Illuminate\Support\Facades\Facade;
use TamkeenTech\Payfort\PayfortIntegration;
use TamkeenTech\Payfort\Services\CaptureService;
use TamkeenTech\Payfort\Services\ProcessResponseService;
use TamkeenTech\Payfort\Services\TokenizationService;
use TamkeenTech\Payfort\Services\VoidService;

/**
 * @method static PayfortIntegration setMerchant(array $merchant)
 * @method static PayfortIntegration setMerchantExtra(...$extras)
 * @method static \TamkeenTech\Payfort\Services\RefundService refund($fort_id, $amount)
 * @method static \TamkeenTech\Payfort\Services\TokenizationService tokenization(float $amount, string $redirect_url, bool $form_flag = true)
 * @method static \TamkeenTech\Payfort\Services\ProcessResponseService processResponse(array $fort_params)
 * @method static \TamkeenTech\Payfort\Services\CaptureService capture(string $fort_id, $amount)
 * @method static \TamkeenTech\Payfort\Services\ApplePayService applePay(array $params, float $amount, string $email, string $command = 'PURCHASE')
 * @method static \TamkeenTech\Payfort\Services\VoidService void($fort_id)
 * @method static \TamkeenTech\Payfort\Services\AuthorizePurchaseService authorize(array $fort_params, float $amount, string $email, string $redirect_url)
 * @method static \TamkeenTech\Payfort\Services\AuthorizePurchaseService purchase(array $fort_params, float $amount, string $email, string $redirect_url, array $installments_params = [])
 *
 * @mixin \TamkeenTech\Payfort\PayfortIntegration
 * @see \TamkeenTech\Payfort\PayfortIntegration
 */
class Payfort extends Facade
{
    public static function getFacadeAccessor()
    {
        return PayfortIntegration::class;
    }
}
