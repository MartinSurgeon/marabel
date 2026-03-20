<?php
/**
 * SMS Helper
 * Wrapper for Zenoph SMS SDK.
 */

require_once PRIVATE_PATH . '/lib/zenoph/Zenoph/Notify/AutoLoader.php';

use Zenoph\Notify\Enums\AuthModel;
use Zenoph\Notify\Enums\SMSType;
use Zenoph\Notify\Request\SMSRequest;

class SMS {

    /**
     * Send an SMS message.
     * 
     * @param string $phone The recipient's phone number (e.g. 0541234567)
     * @param string $message The message content
     * @param string $type The SMS type (for logging purposes: otp, report_card, broadcast, reminder)
     * @return array ['success' => bool, 'message' => string, 'response' => mixed]
     */
    public static function send(string $phone, string $message, string $type = 'broadcast'): array {
        if (!defined('SMS_API_KEY') || !SMS_API_KEY) {
            return ['success' => false, 'message' => 'SMS API Key not configured.'];
        }

        try {
            // Prepare the request
            $request = new SMSRequest();
            $request->setHost(SMS_HOST);
            $request->setAuthModel(AuthModel::API_KEY);
            $request->setAuthApiKey(SMS_API_KEY);

            // Set message details
            $request->setSender(SCHOOL_SMS_SENDER);
            $request->setMessage($message);
            $request->setSMSType(SMSType::GSM_DEFAULT);
            
            // Format phone number
            $formattedPhone = self::formatPhone($phone);
            $request->addDestination($formattedPhone);

            // Submit for response
            $response = $request->submit();

            // Log the attempt
            self::log($phone, $message, $type, 'sent', json_encode($response));

            return ['success' => true, 'message' => 'Message sent successfully', 'response' => $response];

        } catch (Exception $e) {
            self::log($phone, $message, $type, 'failed', $e->getMessage());
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
