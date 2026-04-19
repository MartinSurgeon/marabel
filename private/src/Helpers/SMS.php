<?php
/**
 * SMS Helper
 * Wrapper for Zenoph SMS SDK.
 */

require_once PRIVATE_PATH . '/lib/zenoph/Zenoph/Notify/AutoLoader.php';

use Zenoph\Notify\Enums\AuthModel;
use Zenoph\Notify\Enums\SMSType;
use Zenoph\Notify\Request\SMSRequest;
use Zenoph\Notify\Request\CreditBalanceRequest;
use Zenoph\Notify\Response\CreditBalanceResponse;

class SMS {

    /**
     * Send an SMS message.
     */
    public static function send(string $phone, string $message, string $type = 'broadcast'): array {
        $apiKey = Config::get('sms_api_key', defined('SMS_API_KEY') ? SMS_API_KEY : '');
        $host   = Config::get('sms_host', defined('SMS_HOST') ? SMS_HOST : 'api.smsonlinegh.com');
        $sender = Config::get('sms_sender', defined('SCHOOL_SMS_SENDER') ? SCHOOL_SMS_SENDER : 'Marabel');

        if (empty($apiKey)) {
            return ['success' => false, 'message' => 'SMS API Key not configured.'];
        }

        try {
            $request = new SMSRequest();
            $request->setHost($host);
            $request->setAuthModel(AuthModel::API_KEY);
            $request->setAuthApiKey($apiKey);

            $request->setSender($sender);
            $request->setMessage($message);
            $request->setSMSType(SMSType::GSM_DEFAULT);
            
            $formattedPhone = self::formatPhone($phone);
            $request->addDestination($formattedPhone);

            $response = $request->submit();

            self::log($phone, $message, $type, 'sent', json_encode($response));
            return ['success' => true, 'message' => 'Message sent successfully', 'response' => $response];

        } catch (Exception $e) {
            self::log($phone, $message, $type, 'failed', $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Get current SMS balance.
     */
    public static function getBalance(): array {
        $apiKey = Config::get('sms_api_key', defined('SMS_API_KEY') ? SMS_API_KEY : '');
        $host   = Config::get('sms_host', defined('SMS_HOST') ? SMS_HOST : 'api.smsonlinegh.com');

        if (empty($apiKey)) return ['success' => false, 'message' => 'API Key missing'];

        try {
            $request = new CreditBalanceRequest();
            $request->setHost($host);
            $request->setAuthModel(AuthModel::API_KEY);
            $request->setAuthApiKey($apiKey);

            $response = $request->submit();
            
            if ($response instanceof CreditBalanceResponse) {
                $balance  = (float)$response->getBalance();
                $currency = $response->getCurrencyCode();
                
                if (!$currency || strtolower($currency) === 'null') {
                    $currency = '₵'; 
                }

                return [
                    'success'  => true,
                    'balance'  => $balance,
                    'currency' => $currency,
                    'estimate' => floor($balance / 0.05)
                ];
            }
            return ['success' => false, 'message' => 'Invalid response type'];
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Format phone number for Zenoph (Ghana: 233XXXXXXXXX).
     */
    private static function formatPhone(string $phone): string {
        $phone = preg_replace('/\D/', '', $phone);
        if (str_starts_with($phone, '0')) {
            return '233' . substr($phone, 1);
        }
        if (strlen($phone) === 9) {
            return '233' . $phone;
        }
        return $phone;
    }

    /**
     * Log SMS to database.
     */
    private static function log(string $phone, string $message, string $type, string $status, string $data): void {
        DB::execute(
            "INSERT INTO sms_logs (recipient_phone, message, sms_type, status, response_data) VALUES (?, ?, ?, ?, ?)",
            [$phone, $message, $type, $status, $data]
        );
    }
}
