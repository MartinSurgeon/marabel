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
use Zenoph\Notify\Request\MessageDeliveryRequest;
use Zenoph\Notify\Response\CreditBalanceResponse;

class SMS {

    /**
     * Gateway destination status codes (Zenoph DestinationStatus) that matter
     * to the school, in plain words. Anything else falls back by range.
     */
    private const STATUS_LABELS = [
        2101 => 'Status not known yet',
        2104 => 'Scheduled',
        2105 => 'On its way to the network',
        2106 => 'Being processed by the gateway',
        2107 => 'Queued at the gateway',
        2108 => 'Sent to network, waiting for delivery report',
        2109 => 'Submitted to the network',
        2110 => 'Delivered to the phone',
        2111 => 'Expired: phone was off or out of coverage too long',
        2112 => 'Network could not deliver it',
        2119 => 'Phone number is not valid',
        2121 => 'Not enough SMS credit',
        2122 => 'Mobile network not supported',
        2126 => 'Sender ID was rejected',
        2127 => 'Numeric sender ID not allowed',
        2128 => 'Sender ID is not registered',
        2129 => 'Rejected by the mobile network',
        2130 => 'Blocked by the mobile network as spam',
        2134 => 'Gateway could not reach the network',
        2138 => 'Gateway was busy and dropped it',
        2139 => 'Waiting for the network to accept it',
        2140 => 'SMS credit is on hold',
    ];

    /**
     * Send an SMS message.
     * 'success' means the gateway accepted it (status 'pending' or 'delivered');
     * delivery to the phone is confirmed later with checkDelivery().
     */
    public static function send(string $phone, string $message, string $type = 'broadcast'): array {
        $apiKey = Config::get('sms_api_key', defined('SMS_API_KEY') ? SMS_API_KEY : '');
        $host   = Config::get('sms_host', defined('SMS_HOST') ? SMS_HOST : 'api.smsonlinegh.com');
        $sender = Config::get('sms_sender', defined('SCHOOL_SMS_SENDER') ? SCHOOL_SMS_SENDER : 'Marabel');

        if (empty($apiKey)) {
            return ['success' => false, 'status' => 'failed', 'message' => 'SMS API Key not configured.'];
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
            $report   = self::readReport($response);
            $dest     = $report['destinations'][0] ?? ['message_id' => null, 'status_code' => null];

            // No status in the reply: the gateway took it but said nothing more.
            $status = $dest['status_code'] ? self::statusBucket($dest['status_code']) : 'pending';
            $label  = $dest['status_code'] ? self::statusLabel($dest['status_code']) : 'Accepted by the gateway';

            self::log($phone, $message, $type, $status, json_encode([
                'batch_id'    => $report['batch_id'],
                'message_id'  => $dest['message_id'],
                'status_code' => $dest['status_code'],
                'status'      => $label,
            ]), $report['batch_id'], $dest['message_id'], $dest['status_code']);

            $accepted = in_array($status, ['pending', 'delivered'], true);
            return ['success' => $accepted, 'status' => $status, 'message' => $label, 'batch_id' => $report['batch_id']];

        } catch (Exception $e) {
            self::log($phone, $message, $type, 'failed', $e->getMessage());
            return ['success' => false, 'status' => 'failed', 'message' => $e->getMessage()];
        }
    }

    /**
     * Ask the gateway what happened to a sent batch.
     * Returns destinations as [['message_id' => ?string, 'status_code' => ?int], ...].
     */
    public static function checkDelivery(string $batchId): array {
        $apiKey = Config::get('sms_api_key', defined('SMS_API_KEY') ? SMS_API_KEY : '');
        $host   = Config::get('sms_host', defined('SMS_HOST') ? SMS_HOST : 'api.smsonlinegh.com');

        if (empty($apiKey)) return ['success' => false, 'message' => 'SMS API Key not configured.'];

        try {
            $request = new MessageDeliveryRequest();
            $request->setHost($host);
            $request->setAuthModel(AuthModel::API_KEY);
            $request->setAuthApiKey($apiKey);
            $request->setBatchId($batchId);

            $report = self::readReport($request->submit());
            return ['success' => true, 'destinations' => $report['destinations']];
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Group a gateway status code into the status stored in sms_logs.
     */
    public static function statusBucket(int $code): string {
        return match (true) {
            $code === 2110                  => 'delivered',
            $code === 2111                  => 'expired',
            $code === 2112                  => 'undelivered',
            $code === 2102, $code === 2103  => 'failed',      // schedule cancelled / deleted
            $code >= 2113 && $code <= 2138,
            $code === 2140                  => 'rejected',
            default                         => 'pending',     // queued, submitted, awaiting report, unknown
        };
    }

    /**
     * Plain-words reason for a gateway status code.
     */
    public static function statusLabel(int $code): string {
        if (isset(self::STATUS_LABELS[$code])) return self::STATUS_LABELS[$code];
        return self::statusBucket($code) === 'rejected'
            ? "Rejected by the SMS gateway (code {$code})"
            : "Gateway status code {$code}";
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
     * Pull batch ID and per-destination message ID/status out of a gateway reply.
     * The SDK leaves typed properties unset when a field is missing, and reading
     * them throws, so each read is guarded.
     */
    private static function readReport($response): array {
        $out = ['batch_id' => null, 'destinations' => []];

        try {
            $report = $response->getReport();
        } catch (\Throwable $e) {
            return $out;
        }
        if (!$report) return $out;

        $out['batch_id'] = $report->getBatchId() ?: null;

        foreach ($report->getDestinations()->getItems() as $dest) {
            $messageId = $statusCode = null;
            try { $messageId  = $dest->getMessageId(); } catch (\Throwable $e) {}
            try { $statusCode = $dest->getStatusId(); }  catch (\Throwable $e) {}
            $out['destinations'][] = ['message_id' => $messageId ?: null, 'status_code' => $statusCode ?: null];
        }
        return $out;
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
    private static function log(string $phone, string $message, string $type, string $status, string $data,
                                ?string $batchId = null, ?string $messageId = null, ?int $statusCode = null): void {
        try {
            DB::execute(
                "INSERT INTO sms_logs (recipient_phone, message, sms_type, status, response_data, batch_id, message_id, status_code) VALUES (?, ?, ?, ?, ?, ?, ?, ?)",
                [$phone, $message, $type, $status, $data, $batchId, $messageId, $statusCode]
            );
        } catch (\PDOException $e) {
            // sms_delivery_tracking.sql not run on this database yet: keep the basic log
            DB::execute(
                "INSERT INTO sms_logs (recipient_phone, message, sms_type, status, response_data) VALUES (?, ?, ?, ?, ?)",
                [$phone, $message, $type, $status, $data]
            );
        }
    }
}
