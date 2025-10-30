<?php

namespace App\Services;

use PayOS\PayOS;

class PayosService
{
    protected PayOS $payOS;

    public function __construct()
    {
        $this->payOS = new PayOS(
            config('services.payos.client_id'),
            config('services.payos.api_key'),
            config('services.payos.checksum_key')
        );
    }

    public function handleException(\Throwable $th)
    {
        return response()->json([
            "error" => $th->getCode(),
            "message" => $th->getMessage(),
            "data" => null
        ]);
    }
    public function createPaymentLink($data)
    {
        return $this->payOS->createPaymentLink($data);
    }
}