<?php
ini_set ( "soap.wsdl_cache_enabled", "0" );
class PecRequestClass
{

    // شناسه پذیرنده
    public $pin = "7T56v6cnokikwnrxT3Kn";


    // آدرس درگاه پرداخت
    public $url = "https://pec.shaparak.ir/NewIPGServices/Sale/SaleService.asmx?WSDL";

    // شماره سفارش تولید شده توسط سیستم پذیرنده که باید الزاما یکتا باشد
    public $orderId = '';


    //  شماره یکتایی که بانک به ازای هر درخواست تراکنش خرید به پذیرنده ارسال مینماید. 
    public $token ='';


    // آدرسی که بعد از پایان هر عملیات درسمت بانک نتیجه تراکنش باید به آن برگشت داده شود
    public $callbackUrl = 'http://pecpayment.in/confirm.php';


    // شماره یکتایی که بانک بعد از اتمام موفق تراکنش، همراه با شماره درخواست به پذیرنده میدهد. این شماره جهت پیگیریهای مالی استفاده میگردد. 
    public $rrn = '';


    public $errorMsg = '';

    // تابع ایجاد شناسه یکتا برای هر سفارش
    public function generateOrderId() {
        $orderId = bin2hex(random_bytes(20));
        return $orderId;
    }


    // تابع ارسال درخواست به سرویس خرید
    public function sendSalerequest($orderId,$amount,$additionalData="",$orginator=""){
        $this->url = "https://pec.shaparak.ir/NewIPGServices/Sale/SaleService.asmx?WSDL";
        $this->orderId = $orderId;
        $params = array (
			"LoginAccount" => $this->pin,
			"Amount" => $amount,
			"OrderId" => $orderId,
			"CallBackUrl" => $this->callbackUrl,
            "AdditionalData" => $additionalData,
            "Originator" => $orginator 
	    );

        // در این مرحله میتوانید اطلاعات قبل از ارسال را ذخیره سازی کنید.


        $client = new SoapClient ( $this->url );
        try {
            $result = $client->SalePaymentRequest ( array (
                    "requestData" => $params 
            ) );
            if ($result->SalePaymentRequestResult->Token && $result->SalePaymentRequestResult->Status === 0) {
                // توکن دریافت شده را میتوانید در این مرحله به تراکنش مورد نظر مرتبط نموده و ذخیره سازی کنید.
                // $token = $result->SalePaymentRequestResult->Token;
                header ( "Location: https://pec.shaparak.ir/NewIPG/?Token=" . $result->SalePaymentRequestResult->Token ); /* Redirect browser */
                exit ();
            }
            elseif ( $result->SalePaymentRequestResult->Status  != '0') {
                $err_msg = "(<strong> کد خطا : " . $result->SalePaymentRequestResult->Status . "</strong>) " .
                $result->SalePaymentRequestResult->Message ;
                $this->errorMsg = $err_msg;
                return false;
            } 
        } catch ( Exception $ex ) {
            $err_msg =  $ex->getMessage();
        }
            
    }


    // سرویس خرید با امکان تسهیم آنلاین

    public function onlineMultiplexedSalePaymentService($orderId,$amount,$accounts,$orginator){
        $this->url = "https://pec.shaparak.ir/NewIPGServices/MultiplexedSale/OnlineMultiplexedSalePaymentService.asmx?wsdl";
        // اطلاعات اضافی باید خالی باشد.
        $additionalData = "";
        $this->orderId = $orderId;
        $params = array (
			"LoginAccount" => $this->pin,
			"Amount" => $amount,
			"OrderId" => $orderId,
			"CallBackUrl" => $this->callbackUrl,
            "AdditionalData" => $additionalData,
            "Originator" => $orginator,
	    );
        $params["MultiplexedAccounts"] = $accounts;

        // در این مرحله میتوانید اطلاعات را قبل از ارسال ذخیره سازی کنید.

        $requestData['requestData'] = $params;
        $client = new SoapClient ( $this->url );
        try {
            $result = $client->MultiplexedSaleWithIBANPaymentRequest($requestData);
            if ($result->MultiplexedSaleWithIBANPaymentRequestResult->Token && $result->MultiplexedSaleWithIBANPaymentRequestResult->Status === 0) {
                // توکن دریافت شده را  در این مرحله به تراکنش مورد نظر مرتبط نموده و ذخیره سازی کنید.
                // $token = $result->MultiplexedSaleWithIBANPaymentRequestResult->Token;
                header ( "Location: https://pec.shaparak.ir/NewIPG/?Token=" . $result->MultiplexedSaleWithIBANPaymentRequestResult->Token ); /* Redirect browser */
                exit ();
            }
            elseif ( $result->MultiplexedSaleWithIBANPaymentRequestResult->Status  != '0') {
                $err_msg = "(<strong> کد خطا : " . $result->MultiplexedSaleWithIBANPaymentRequestResult->Status . "</strong>) " .
                $result->MultiplexedSaleWithIBANPaymentRequestResult->Message ;
                $this->errorMsg = $err_msg;
                return false;
            } 
        } catch ( Exception $ex ) {
            $err_msg =  $ex->getMessage();
        }
    }

    public function confirmServices($token){
        $confirmUrl = 'https://pec.shaparak.ir/NewIPGServices/Confirm/ConfirmService.asmx?WSDL';
        $this->url = $confirmUrl;
        $params = array (
            "LoginAccount" => $this->pin,
            "Token" => $token 
        );
        
        $client = new SoapClient ( $this->url );
        
        try {
            $result = $client->ConfirmPayment ( array (
                    "requestData" => $params 
            ) );
            if ($result->ConfirmPaymentResult->Status != '0') {
                // نمایش نتیجه ی پرداخت
                $err_msg = "(<strong> کد خطا : " . $result->ConfirmPaymentResult->Status . "</strong>) ";
                $this->errorMsg = $err_msg;
                return false;
            }
            // پرداخت با موفقییت انجام شده است 
            return true;
        } catch ( Exception $ex ) {
            $err_msg =  $ex->getMessage()  ;
        }
    }


    // تابع انجام تراکنش برگشت
    public function reversalRequest($token){
        $reversalUrl = "https://pec.shaparak.ir/NewIPGServices/Reverse/ReversalService.asmx?wsdl";
        $this->url = $reversalUrl;
        $params = array (
            "LoginAccount" => $this->pin,
            "Token" => $token 
        );
        
        $client = new SoapClient ( $this->url );
        
        try {
            $result = $client->ReversalRequest ( array (
                    "requestData" => $params 
            ) );
            if ($result->ReversalRequestResult->Status != '0') {
                // درخواست برگشت تراکنش با خطا مواجه شده است
                $this->errorMsg = "(<strong> کد خطا : " . $result->ReversalRequestResult->Status . "</strong>) " .
                $result->ReversalRequestResult->Message;
                return false;
            }
            // درخواست برگشت تراکنش با موفققیت انجام شد
            return true;
        } catch ( Exception $ex ) {
            $err_msg =  $ex->getMessage()  ;
        }


        
    }

    


    // سرویس خرید با شناسه حساب دولتی
    public function GovermentIdSaleService($orderId,$amount,$GovId,$orginator=""){
        $this->url = "https://pec.shaparak.ir/NewIPGServices/Sale/GovermentIdSaleServiceSW2.asmx?wsdl";
        $additionalData = "GOVId=".$GovId;
        $this->orderId = $orderId;
        $params = array (
			"LoginAccount" => $this->pin,
			"Amount" => $amount,
			"OrderId" => $orderId,
			"CallBackUrl" => $this->callbackUrl,
            "AdditionalData" => $additionalData,
            "Originator" => $orginator 
	    );

        // در این مرحله میتوانید اطلاعات را قبل از ارسال ذخیره سازی کنید.


        $client = new SoapClient ( $this->url );
        try {
            $result = $client->SalePaymentRequest ( array (
                    "requestData" => $params 
            ) );
            if ($result->SalePaymentRequestResult->Token && $result->SalePaymentRequestResult->Status === 0) {
                // توکن دریافت شده را  در این مرحله به تراکنش مورد نظر مرتبط نموده و ذخیره سازی کنید.
                // $token = $result->SalePaymentRequestResult->Token;
                header ( "Location: https://pec.shaparak.ir/NewIPG/?Token=" . $result->SalePaymentRequestResult->Token ); /* Redirect browser */
                exit ();
            }
            elseif ( $result->SalePaymentRequestResult->Status  != '0') {
                $err_msg = "(<strong> کد خطا : " . $result->SalePaymentRequestResult->Status . "</strong>) " .
                $result->SalePaymentRequestResult->Message ;
                $this->errorMsg = $err_msg;
                return false;
            } 
        } catch ( Exception $ex ) {
            $err_msg =  $ex->getMessage();
        }
            
    }

    // سرویس پرداخت وجوه دولتی با امکان تسهیم چند حسابی
    public function MultixGovermentIdSaleService($orderId,$amount,$GovId,$accounts,$orginator=""){
        $this->url = "https://pec.shaparak.ir/NewIPGServices/Sale/GovermentIdSaleServiceSW2.asmx?wsdl";
        $additionalData = "GOVId=".$GovId;
        $this->orderId = $orderId;
        $params = array (
			"LoginAccount" => $this->pin,
			"Amount" => $amount,
			"OrderId" => $orderId,
			"CallBackUrl" => $this->callbackUrl,
            "AdditionalData" => $additionalData,
            "Originator" => $orginator,
	    );
        $params["MultiplexedAccounts"] = $accounts;

        // در این مرحله میتوانید اطلاعات را قبل از ارسال ذخیره سازی کنید.

        $requestData['requestData'] = $params;
        $client = new SoapClient ( $this->url );
        try {
            $result = $client->GovSaleWithMultiIbanPaymentRequestSW2($requestData);
            if ($result->GovSaleWithMultiIbanPaymentRequestSW2Result->Token && $result->GovSaleWithMultiIbanPaymentRequestSW2Result->Status === 0) {
                // توکن دریافت شده را  در این مرحله به تراکنش مورد نظر مرتبط نموده و ذخیره سازی کنید.
                // $token = $result->GovSaleWithMultiIbanPaymentRequestSW2Result->Token;
                header ( "Location: https://pec.shaparak.ir/NewIPG/?Token=" . $result->GovSaleWithMultiIbanPaymentRequestSW2Result->Token ); /* Redirect browser */
                exit ();
            }
            elseif ( $result->GovSaleWithMultiIbanPaymentRequestSW2Result->Status  != '0') {
                $err_msg = "(<strong> کد خطا : " . $result->GovSaleWithMultiIbanPaymentRequestSW2Result->Status . "</strong>) " .
                $result->GovSaleWithMultiIbanPaymentRequestSW2Result->Message ;
                $this->errorMsg = $err_msg;
                return false;
            } 
        } catch ( Exception $ex ) {
            $err_msg =  $ex->getMessage();
        }
    }

    // سرویس پرداخت قبض
    public function payBillRequest($orderId,$billId,$payId,$additionalData="",$orginator=""){
        $this->url = "https://pec.shaparak.ir/NewIPGServices/Bill/BillService.asmx?wsdl";
        $this->orderId = $orderId;
        $params = array (
			"LoginAccount" => $this->pin,
			"BillId" => $billId,
            "PayId" => $payId,
			"OrderId" => $orderId,
            "Amount" => '',
			"CallBackUrl" => $this->callbackUrl,
            "AdditionalData" => $additionalData,
            "Originator" => $orginator, 
	    );

        // در این مرحله میتوانید اطلاعات قبل از ارسال را ذخیره سازی کنید.

        $client = new SoapClient ( $this->url );
        try {
            $result = $client->BillPaymentRequest ( array (
                    "requestData" => $params 
            ));
            if ($result->BillPaymentRequestResult->Token && $result->BillPaymentRequestResult->Status === 0) {
                // توکن دریافت شده را میتوانید در این مرحله به تراکنش مورد نظر مرتبط نموده و ذخیره سازی کنید.
                // $token = $result->BillPaymentRequestResult->Token;
                header ( "Location: https://pec.shaparak.ir/NewIPG/?Token=" . $result->BillPaymentRequestResult->Token ); /* Redirect browser */
                exit ();
            }
            elseif ( $result->BillPaymentRequestResult->Status  != '0') {
                $err_msg = "(<strong> کد خطا : " . $result->BillPaymentRequestResult->Status . "</strong>) " .
                $result->BillPaymentRequestResult->Message ;
                $this->errorMsg = $err_msg;
                return false;
            }
        } catch ( Exception $ex ) {
            $this->errorMsg =  $ex->getMessage();
        }
            
    }


    // استعلام جزییات قبض
    public function getBillInformation($billId,$payId){
        $this->url = "https://pec.shaparak.ir/NewIPGServices/Bill/BillService.asmx?wsdl";
        $params = array (
			"billId" => $billId,
			"payId" => $payId,
	    );
        // در این مرحله میتوانید اطلاعات قبل از ارسال را ذخیره سازی کنید.

        $client = new SoapClient ( $this->url );
        try {
            $result = $client->GetBillInfo($params);
            if ($result->GetBillInfoResult->Status === 0) {
                // توکن دریافت شده را میتوانید در این مرحله به تراکنش مورد نظر مرتبط نموده و ذخیره سازی کنید.
                // $token = $result->GetBillInfoResult->Token;
                return $result->GetBillInfoResult;
            }
            elseif ( $result->GetBillInfoResult->Status  != '0') {
                $err_msg = "(<strong> کد خطا : " . $result->GetBillInfoResult->Status . "</strong>) ";
                $this->errorMsg = $err_msg;
                return false;
            }
        } catch ( Exception $ex ) {
            $this->errorMsg =  $ex->getMessage();
        }
    }


    // سرویس خرید شارژ موبایل
    public function chargeMobileService($orderId,$chargeMobileNumber,$requesterMobileNumber,$topupType,$amount,$additionalData,$orginator){
        $this->url = "https://pec.shaparak.ir/NewIPGServices/SimCharge/TopupChargeService.asmx?wsdl";
        $this->orderId = $orderId;
        $params = array (
			"LoginAccount" => $this->pin,
			"OrderId" => $orderId,
            "ChargeMobileNumber" => $chargeMobileNumber,
            "RequesterMobileNumber" => $requesterMobileNumber,
            "TopupType" => $topupType,
            "Amount" => $amount,
			"CallBackUrl" => $this->callbackUrl,
            "AdditionalData" => $additionalData,
            "Originator" => $orginator, 
	    );

        // در این مرحله میتوانید اطلاعات قبل از ارسال را ذخیره سازی کنید.

        $client = new SoapClient ( $this->url );
        try {
            $result = $client->TopupChargeRequest ( array (
                    "requestData" => $params 
            ));
            if ($result->TopupChargeRequestResult->Token && $result->TopupChargeRequestResult->Status === 0) {
                // توکن دریافت شده را میتوانید در این مرحله به تراکنش مورد نظر مرتبط نموده و ذخیره سازی کنید.
                // $token = $result->TopupChargeRequestResult->Token;
                header ( "Location: https://pec.shaparak.ir/NewIPG/?Token=" . $result->TopupChargeRequestResult->Token ); /* Redirect browser */
                exit ();
            }
            elseif ( $result->TopupChargeRequestResult->Status  != '0') {
                $err_msg = "(<strong> کد خطا : " . $result->TopupChargeRequestResult->Status . "</strong>) " .
                $result->TopupChargeRequestResult->Message ;
                $this->errorMsg = $err_msg;
                return false;
            }
        } catch ( Exception $ex ) {
            $this->errorMsg =  $ex->getMessage();
        }
    }

    public function alertMsg(){
        $res = '';
        $res ='<div class="alert alert-danger"><span class="error-msg">'.$this->errorMsg.'</span></div>';
        return $res;
    }


    public function changeDate($date){
        $res = explode('/',$date);
        $re = jalali_to_gregorian($res[0],$res[1],$res[2]);
        return implode('/',$re);
    }

}

?>