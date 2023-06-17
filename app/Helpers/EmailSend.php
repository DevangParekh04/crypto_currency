<?php

namespace App\Helpers;

use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Models\EmailFormats;
use App\Helpers\Email;

class EmailSend
{
    /**
     * This function is used to send a reset password email.
     *
     * @param array $emailData
     * @return bool
     */
    public function emailForgotPassword($emailData): bool
    {
        try {
            $emailFormat = EmailFormats::find(1);
            if (empty($emailFormat)) {
                Log::debug('Unable to find email format 1');
                return false;
            }

            $body = $emailFormat->emailformat;
            $body = str_replace("%name%", $emailData['name'], $body);
            $body = str_replace("%otp%", $emailData['otp'], $body);
            Mail::to($emailData['email'])->queue(new Email($emailFormat->subject, $body));
            return true;
        } catch (Exception $exception) {
            Log::debug('Failed to send reset password email: ' . $exception->getMessage());
        }
        return false;
    }
}
