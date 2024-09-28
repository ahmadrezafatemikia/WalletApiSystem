<?php

namespace App\Services\Payment\Contracts;

use stdClass;

interface PaymentInterface
{
    public function request(int $amount, string $callback_url, string $description = ''): string;

    public function verify(string $amount, string $authority): stdClass;
}
