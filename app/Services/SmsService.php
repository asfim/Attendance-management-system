<?php

namespace App\Services;

use App\Models\SmsSetting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class SmsService
{
    /**
     * Send Attendance SMS
     * 
     * @param \App\Models\StudentProfile $student
     * @param string $state ('Check-In' or 'Check-Out')
     * @param \Carbon\Carbon $punchTime
     */
    public function sendAttendanceSms($student, $state, $punchTime)
    {
        try {
            $setting = SmsSetting::first();
            if (!$setting || !$setting->is_active) {
                return false;
            }

            // Get Guardian Number (assuming ParentProfile has phone)
            $phone = null;
            if ($student->parent && $student->parent->phone) {
                $phone = $student->parent->phone;
            }

            if (!$phone) {
                return false; // No number to send to
            }

            // Determine template
            $template = ($state === 'Check-In') ? $setting->entry_message_template : $setting->exit_message_template;
            
            if (empty($template)) {
                return false;
            }

            // Replace variables in message
            $name = $student->user ? $student->user->name : 'Student';
            $time = $punchTime->format('h:i A');
            $date = $punchTime->format('d M Y');

            $message = str_replace(
                ['[name]', '[time]', '[date]'],
                [$name, $time, $date],
                $template
            );

            // Construct API URL
            $url = '';
            $encodedPhone = urlencode($phone);
            $encodedMessage = urlencode($message);
            $apiKey = $setting->api_key;
            $senderId = $setting->sender_id;

            if ($setting->gateway_provider === 'bulksmsbd') {
                $url = "http://api.bulksmsbd.com/api/smsapi?api_key={$apiKey}&type=text&number={$encodedPhone}&senderid={$senderId}&message={$encodedMessage}";
            } elseif ($setting->gateway_provider === 'greenweb') {
                $url = "http://api.greenweb.com.bd/api.php?token={$apiKey}&to={$encodedPhone}&message={$encodedMessage}";
            } elseif ($setting->gateway_provider === 'bdbulksms') {
                $url = "http://api.bdbulksms.net/api.php?token={$apiKey}&to={$encodedPhone}&message={$encodedMessage}";
            } elseif ($setting->gateway_provider === 'custom') {
                $url = $setting->custom_api_url;
                $url = str_replace('[number]', $encodedPhone, $url);
                $url = str_replace('[message]', $encodedMessage, $url);
            }

            if (empty($url)) {
                return false;
            }

            // Send HTTP GET Request to API Gateway
            $response = Http::get($url);

            if ($response->successful()) {
                Log::info("SMS Sent to {$phone} for {$name} - State: {$state}");
                return true;
            } else {
                Log::error("SMS Gateway Error: " . $response->body());
                return false;
            }
        } catch (\Exception $e) {
            Log::error("SMS Service Exception: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Send Custom SMS
     * 
     * @param string $phone
     * @param string $message
     */
    public function sendSms($phone, $message)
    {
        try {
            $setting = SmsSetting::first();
            if (!$setting || !$setting->is_active) {
                return false;
            }

            if (!$phone || empty($message)) {
                return false;
            }

            // Construct API URL
            $url = '';
            $encodedPhone = urlencode($phone);
            $encodedMessage = urlencode($message);
            $apiKey = $setting->api_key;
            $senderId = $setting->sender_id;

            if ($setting->gateway_provider === 'bulksmsbd') {
                $url = "http://api.bulksmsbd.com/api/smsapi?api_key={$apiKey}&type=text&number={$encodedPhone}&senderid={$senderId}&message={$encodedMessage}";
            } elseif ($setting->gateway_provider === 'greenweb') {
                $url = "http://api.greenweb.com.bd/api.php?token={$apiKey}&to={$encodedPhone}&message={$encodedMessage}";
            } elseif ($setting->gateway_provider === 'bdbulksms') {
                $url = "http://api.bdbulksms.net/api.php?token={$apiKey}&to={$encodedPhone}&message={$encodedMessage}";
            } elseif ($setting->gateway_provider === 'custom') {
                $url = $setting->custom_api_url;
                $url = str_replace('[number]', $encodedPhone, $url);
                $url = str_replace('[message]', $encodedMessage, $url);
            }

            if (empty($url)) {
                return false;
            }

            // Send HTTP GET Request to API Gateway
            $response = Http::get($url);

            if ($response->successful()) {
                Log::info("Custom SMS Sent to {$phone}");
                return true;
            } else {
                Log::error("SMS Gateway Error: " . $response->body());
                return false;
            }
        } catch (\Exception $e) {
            Log::error("Custom SMS Service Exception: " . $e->getMessage());
            return false;
        }
    }
}
