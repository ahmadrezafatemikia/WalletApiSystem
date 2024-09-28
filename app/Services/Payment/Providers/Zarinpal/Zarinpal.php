<?php

namespace App\Services\Payment\Providers\Zarinpal;

use App\Exceptions\Payment\ZarinpalSandboxTrueAndValueIsEmpty;
use App\Services\Payment\Contracts\PaymentInterface;
use Illuminate\Support\Facades\Http;

class Zarinpal implements PaymentInterface
{
    private $merchantId;
    private $sandbox;

    public function __construct()
    {
        $this->sandbox = config('app.payment_providers.zarinpal.sandbox');
        if (!$this->sandbox && empty($this->sandbox)) {
            throw new ZarinpalSandboxTrueAndValueIsEmpty("Zarinpal Sandbox key is required");
        }
        $this->merchantId = $this->sandbox ? \Str::random(36) : ('app.payment_providers.zarinpal.merchant_id');
    }

    /**
     * @param array $data
     * @return string
     */
    public function request(int $amount, string $callback_url, string $description = ''): string
    {
        $paymentDetails = [
            'merchant_id' => $this->merchantId,
            'amount' => $amount,
            'description' => $description,
            'currency' => 'IRT',
            'callback_url' => $callback_url,
        ];
        $payment = $this->http('https://sandbox.zarinpal.com/pg/v4/payment/request.json', $paymentDetails);
        $startPayLink = 'https://sandbox.zarinpal.com/pg/StartPay/' . $payment->data->authority;
        return $startPayLink;
    }

    public function http(string $url, array $data)
    {
        $payment = Http::asJson()->post($url, $data);
        $payment = $payment->object();
        return $payment;
    }

    /**
     * @param array $data
     * @return string
     */
    public function verify(string $amount, string $authority): \stdClass
    {
        $paymentDetails = [
            'merchant_id' => $this->merchantId,
            'amount' => $amount,
            'authority' => $authority,
        ];
        $payment = $this->http('https://sandbox.zarinpal.com/pg/v4/payment/verify.json', $paymentDetails);
        return $payment;
    }
}
