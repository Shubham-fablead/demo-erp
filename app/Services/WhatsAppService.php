<?php

namespace App\Services;

use App\Models\FacebookAppConfiguration;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsAppService
{
    /**
     * Send WhatsApp message using template
     *
     * @param int $branchId Branch ID to get configuration
     * @param string $toPhoneNumber Recipient phone number (with country code, e.g., 919876543210)
     * @param string $templateName Template name (e.g., 'hello_world')
     * @param array $templateParams Template parameters (optional)
     * @param string $languageCode Language code (default: 'en')
     * @return array
     */
    public static function sendTemplateMessage($branchId, $toPhoneNumber, $templateName, $templateParams = [], $languageCode = 'en')
    {
        try {
            // Get Facebook App Configuration for the branch
            $config = FacebookAppConfiguration::where('branch_id', $branchId)
                ->where('isDeleted', 0)
                ->first();

            if (!$config) {
                Log::warning("WhatsApp configuration not found for branch ID: {$branchId}");
                return [
                    'success' => false,
                    'message' => 'WhatsApp configuration not found for this branch'
                ];
            }

            // Validate required fields
            if (empty($config->phone_number_id) || empty($config->access_token)) {
                Log::error("WhatsApp configuration incomplete for branch ID: {$branchId}");
                return [
                    'success' => false,
                    'message' => 'WhatsApp configuration incomplete'
                ];
            }

            // Format phone number (remove all non-numeric characters)
            // Store as: 9687877132 (digits only, no spaces, dashes, or special characters)
            $phoneNumber = preg_replace('/[^0-9]/', '', $toPhoneNumber);

            // Ensure phone number is not empty
            if (empty($phoneNumber)) {
                Log::error("Invalid phone number format", [
                    'original' => $toPhoneNumber,
                    'branch_id' => $branchId
                ]);
                return [
                    'success' => false,
                    'message' => 'Invalid phone number format'
                ];
            }

            // Store original for logging
            $originalPhone = $phoneNumber;

            // Format phone number for WhatsApp API
            // WhatsApp requires international format without + or 00
            if (strlen($phoneNumber) == 10) {
                // 10-digit number - assume India and add country code
                $phoneNumber = '91' . $phoneNumber;
                Log::info("Phone number formatted (10 digits)", [
                    'original' => $originalPhone,
                    'formatted' => $phoneNumber,
                    'branch_id' => $branchId
                ]);
            } elseif (strlen($phoneNumber) == 11 && substr($phoneNumber, 0, 1) == '0') {
                // 11-digit starting with 0 - remove 0 and add country code
                $phoneNumber = '91' . substr($phoneNumber, 1);
                Log::info("Phone number formatted (11 digits with 0)", [
                    'original' => $originalPhone,
                    'formatted' => $phoneNumber,
                    'branch_id' => $branchId
                ]);
            } elseif (strlen($phoneNumber) >= 12 && strlen($phoneNumber) <= 15) {
                // Already has country code - use as is
                Log::info("Phone number already has country code", [
                    'original' => $originalPhone,
                    'formatted' => $phoneNumber,
                    'branch_id' => $branchId
                ]);
            } elseif (strlen($phoneNumber) < 10 || strlen($phoneNumber) > 15) {
                Log::error("Invalid phone number length", [
                    'phone' => $phoneNumber,
                    'original' => $originalPhone,
                    'length' => strlen($phoneNumber),
                    'branch_id' => $branchId
                ]);
                return [
                    'success' => false,
                    'message' => 'Invalid phone number length. Phone number must be 10-15 digits.'
                ];
            }

            // Meta WhatsApp API endpoint
            $url = "https://graph.facebook.com/v18.0/{$config->phone_number_id}/messages";

            // Prepare message payload
            // Use provided language code or default to 'en'
            $language = !empty($languageCode) ? $languageCode : 'en';

            $payload = [
                'messaging_product' => 'whatsapp',
                'to' => $phoneNumber,
                'type' => 'template',
                'template' => [
                    'name' => $templateName,
                    'language' => [
                        'code' => $language
                    ]
                ]
            ];

            // Add template parameters if provided
            if (!empty($templateParams)) {
                $payload['template']['components'] = [
                    [
                        'type' => 'body',
                        'parameters' => array_map(function($param) {
                            return [
                                'type' => 'text',
                                'text' => $param
                            ];
                        }, $templateParams)
                    ]
                ];
            }

            // Log the payload being sent (for debugging)
            Log::info("WhatsApp API request payload", [
                'branch_id' => $branchId,
                'url' => $url,
                'to' => $phoneNumber,
                'template_name' => $templateName,
                'language' => $language,
                'params_count' => count($templateParams),
                'payload' => $payload
            ]);

            // Send request to Meta WhatsApp API
            // Bearer token = $config->access_token (from Facebook App Configuration)
            // Set timeout to 15 seconds and retry on failure
            try {
                $response = Http::timeout(15)
                    ->retry(2, 100) // Retry 2 times with 100ms delay
                    ->withHeaders([
                        'Authorization' => 'Bearer ' . $config->access_token,
                        'Content-Type' => 'application/json',
                    ])->post($url, $payload);
            } catch (\Illuminate\Http\Client\ConnectionException $e) {
                // Handle timeout/connection errors
                Log::error("WhatsApp API connection timeout", [
                    'branch_id' => $branchId,
                    'to' => $phoneNumber,
                    'template' => $templateName,
                    'error' => $e->getMessage()
                ]);

                return [
                    'success' => false,
                    'message' => 'Connection timeout. Please check your internet connection and try again.',
                    'error' => 'Connection timeout'
                ];
            }

            $responseData = $response->json();

            // Log the full response
            Log::info("WhatsApp API response", [
                'branch_id' => $branchId,
                'to' => $phoneNumber,
                'status_code' => $response->status(),
                'response' => $responseData
            ]);

            if ($response->successful() && isset($responseData['messages'][0]['id'])) {
                $messageStatus = $responseData['messages'][0]['message_status'] ?? 'unknown';

                Log::info("WhatsApp message sent successfully", [
                    'branch_id' => $branchId,
                    'to' => $phoneNumber,
                    'template' => $templateName,
                    'message_id' => $responseData['messages'][0]['id'],
                    'message_status' => $messageStatus,
                    'note' => $messageStatus === 'accepted'
                        ? 'Message accepted by WhatsApp API. If not received: 1) Check template category (MARKETING templates have delivery restrictions), 2) Phone number in allowed list, 3) User opt-in status'
                        : 'Message status: ' . $messageStatus
                ]);

                return [
                    'success' => true,
                    'message' => 'WhatsApp message sent successfully',
                    'message_id' => $responseData['messages'][0]['id'],
                    'message_status' => $messageStatus,
                    'note' => $messageStatus === 'accepted'
                        ? 'Message accepted. If not received, check: Template category (MARKETING has restrictions), Phone in allowed list, User opt-in'
                        : 'Message status: ' . $messageStatus
                ];
            } else {
                Log::error("WhatsApp API error", [
                    'branch_id' => $branchId,
                    'to' => $phoneNumber,
                    'response' => $responseData,
                    'status' => $response->status()
                ]);

                // Handle specific error codes
                $errorCode = $responseData['error']['code'] ?? null;
                $errorSubCode = $responseData['error']['error_subcode'] ?? null;
                $errorMessage = $responseData['error']['message'] ?? 'Failed to send WhatsApp message';

                // Provide user-friendly error messages
                if ($errorCode == 190) {
                    // Access token expired or invalid
                    if ($errorSubCode == 463) {
                        $errorMessage = 'Access token has expired. Please update your Facebook App Configuration access token in the settings.';
                    } else {
                        $errorMessage = 'Access token is invalid or expired. Please update your Facebook App Configuration access token in the settings.';
                    }
                } elseif ($errorCode == 131030) {
                    $errorMessage = 'Phone number not in allowed list. Please add this number (' . $phoneNumber . ') to your Meta WhatsApp Business allowed recipients list for testing. Go to Meta Business Manager > WhatsApp > API Setup > Manage phone number list.';
                    Log::error("WhatsApp recipient not in allowed list", [
                        'branch_id' => $branchId,
                        'phone_number' => $phoneNumber,
                        'message' => 'Add this number to allowed recipients in Meta Business Manager'
                    ]);
                } elseif ($errorCode == 131026) {
                    $errorMessage = 'Invalid phone number format. Please ensure the phone number is correct.';
                } elseif ($errorCode == 131047) {
                    $errorMessage = 'Template not found or not approved. Please check your template name and approval status.';
                } elseif ($errorCode == 132001) {
                    // Template name does not exist in the translation
                    $templateNameFromError = $responseData['error']['error_data']['details'] ?? 'unknown';
                    $errorMessage = "Template name does not exist in WhatsApp. The template name '{$templateName}' is not found in your WhatsApp Business account. Please verify the template name in your WhatsApp Message Templates settings and ensure it matches the exact name in Meta Business Manager.";
                    Log::error("WhatsApp template name mismatch", [
                        'branch_id' => $branchId,
                        'template_name_used' => $templateName,
                        'error_details' => $templateNameFromError
                    ]);
                }

                return [
                    'success' => false,
                    'message' => $errorMessage,
                    'error' => $responseData['error'] ?? null,
                    'error_code' => $errorCode
                ];
            }

        } catch (\Exception $e) {
            Log::error("WhatsApp service exception", [
                'branch_id' => $branchId,
                'to' => $toPhoneNumber ?? 'N/A',
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return [
                'success' => false,
                'message' => 'Exception: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Send hello_world template message
     *
     * @param int $branchId Branch ID
     * @param string $toPhoneNumber Recipient phone number
     * @return array
     */
    public static function sendHelloWorld($branchId, $toPhoneNumber)
    {
        return self::sendTemplateMessage($branchId, $toPhoneNumber, 'hello_world');
    }

    /**
     * Send order confirmation message with dynamic order details
     *
     * @param int $branchId Branch ID
     * @param string $toPhoneNumber Recipient phone number
     * @param string $customerName Customer name
     * @param string $orderNumber Order number
     * @param float $totalAmount Total order amount
     * @param string $templateName Template name (default: 'order_confirmation' or use 'hello_world' if custom template)
     * @return array
     */
    public static function sendOrderConfirmation($branchId, $toPhoneNumber, $customerName, $orderNumber, $totalAmount, $templateName = 'hello_world')
    {
        // Format amount with 2 decimal places
        $formattedAmount = number_format($totalAmount, 2);

        // Dynamic template parameters
        // Adjust these parameters based on your WhatsApp template structure
        // Example: If your template has placeholders like {{1}} for customer name, {{2}} for order number, {{3}} for amount
        $templateParams = [
            $customerName,      // Parameter 1: Customer name
            $orderNumber,       // Parameter 2: Order number
            $formattedAmount    // Parameter 3: Total amount
        ];

        return self::sendTemplateMessage($branchId, $toPhoneNumber, $templateName, $templateParams);
    }
}

