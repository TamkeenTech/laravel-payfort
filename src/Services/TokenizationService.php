<?php

namespace TamkeenTech\Payfort\Services;

use TamkeenTech\Payfort\Exceptions\PaymentFailed;
use TamkeenTech\Payfort\Repositories\Payfort;

class TokenizationService extends Payfort
{
    protected $redirect_url;

    protected $has_3ds;

    protected $payment_method;

    protected $with_form = false;

    public function handle(): array
    {
        $params = [
            'service_command' => 'TOKENIZATION',
            'merchant_identifier' => $this->merchant['merchant_identifier'],
            'access_code' => $this->merchant['access_code'],
            'merchant_reference' => $this->generateMerchantReference(),
            'language' => $this->language,
            'return_url' => $this->redirect_url,
        ];

        if ($this->payment_method === 'installments_merchantpage') {
            $params['currency'] = strtoupper($this->currency);
            $params['installments'] = 'STANDALONE';
            $params['amount'] = $this->convertFortAmount($this->amount);
        }

        $params = array_merge($params, $this->merchant_extras);

        $params['signature'] = $this->calculateSignature($params);

        $result = [
            'url' => $this->getGatewayUrl(),
            'params' => $params,
            'paymentMethod' => 'cc_merchantpage2',
        ];

        if ($this->with_form) {
            $result['form'] = $this->getPaymentForm($this->getGatewayUrl(), $params);
        }

        return $result;
    }

    public function set3DSFlag(bool $flag): self
    {
        $this->has_3ds = $flag;

        return $this;
    }

    public function setPaymentMethod(string $method): self
    {
        if (! in_array($method, ['cc_merchantpage', 'cc_merchantpage2', 'installments_merchantpage'])) {
            throw new PaymentFailed("payment method not supported");
        }

        $this->payment_method = $method;

        return $this;
    }

    public function getPaymentForm($gatewayUrl, $postData)
    {
        $form = '<form style="display:none" name="payfort_payment_form"'
            .' id="payfort_payment_form" method="post" action="'
            .$gatewayUrl.'">';
        foreach ($postData as $k => $v) {
            $form .= '<input type="hidden" name="'.$k.'" value="'.$v.'">';
        }
        $form .= '<input type="submit" id="submit">';

        return $form;
    }

    public function withForm(bool $flag): self
    {
        $this->with_form = $flag;

        return $this;
    }
}
