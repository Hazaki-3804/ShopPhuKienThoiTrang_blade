<?php

namespace App\Services;

class MoMoService
{
    protected $partnerCode;
    protected $accessKey;
    protected $secretKey;
    protected $endpoint;
    protected $returnUrl;
    protected $notifyUrl;

    public function __construct()
    {
        $this->partnerCode = config('momo.partner_code');
        $this->accessKey = config('momo.access_key');
        $this->secretKey = config('momo.secret_key');
        $this->endpoint = config('momo.endpoint');
        $this->returnUrl = config('momo.return_url');
        $this->notifyUrl = config('momo.notify_url');
    }

    public function createPayment($orderId, $amount, $orderInfo = 'Thanh toán đơn hàng')
    {
        $requestId = time() . "";
        $requestType = "captureWallet";
        $extraData = "";

        $rawHash = "accessKey=" . $this->accessKey .
            "&amount=" . $amount .
            "&extraData=" . $extraData .
            "&ipnUrl=" . $this->notifyUrl .
            "&orderId=" . $orderId .
            "&orderInfo=" . $orderInfo .
            "&partnerCode=" . $this->partnerCode .
            "&redirectUrl=" . $this->returnUrl .
            "&requestId=" . $requestId .
            "&requestType=" . $requestType;

        $signature = hash_hmac("sha256", $rawHash, $this->secretKey);

        $data = [
            'partnerCode' => $this->partnerCode,
            'partnerName' => "Shop Phụ Kiện Thời Trang",
            'storeId' => "ShopNangTho",
            'requestId' => $requestId,
            'amount' => $amount,
            'orderId' => $orderId,
            'orderInfo' => $orderInfo,
            'redirectUrl' => $this->returnUrl,
            'ipnUrl' => $this->notifyUrl,
            'lang' => 'vi',
            'extraData' => $extraData,
            'requestType' => $requestType,
            'signature' => $signature
        ];

        $result = $this->execPostRequest($this->endpoint, json_encode($data));
        $jsonResult = json_decode($result, true);

        return $jsonResult;
    }

    private function execPostRequest($url, $data)
    {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Content-Length: ' . strlen($data)
        ]);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        $result = curl_exec($ch);
        curl_close($ch);
        return $result;
    }

    public function verifySignature($data)
    {
        $rawHash = "accessKey=" . $this->accessKey .
            "&amount=" . $data['amount'] .
            "&extraData=" . $data['extraData'] .
            "&message=" . $data['message'] .
            "&orderId=" . $data['orderId'] .
            "&orderInfo=" . $data['orderInfo'] .
            "&orderType=" . $data['orderType'] .
            "&partnerCode=" . $this->partnerCode .
            "&payType=" . $data['payType'] .
            "&requestId=" . $data['requestId'] .
            "&responseTime=" . $data['responseTime'] .
            "&resultCode=" . $data['resultCode'] .
            "&transId=" . $data['transId'];

        $signature = hash_hmac("sha256", $rawHash, $this->secretKey);

        return $signature === $data['signature'];
    }
}
