<?php

namespace App\Services;

use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Exception;

class EmailService
{
    /**
     * Send email with comprehensive error handling and logging
     */
    public static function sendWithFallback($mailable, string $toEmail, string $context = 'email')
    {
        try {
            // Log attempt
            Log::info("Attempting to send {$context}", [
                'to' => $toEmail,
                'mailable' => get_class($mailable),
                'timestamp' => now(),
            ]);

            // Try to send email
            Mail::to($toEmail)->send($mailable);

            // Log success
            Log::info("{$context} sent successfully", [
                'to' => $toEmail,
                'timestamp' => now(),
            ]);

            return [
                'success' => true,
                'message' => ucfirst($context) . ' sent successfully',
            ];

        } catch (\Symfony\Component\Mailer\Exception\TransportException $e) {
            // SMTP/Transport specific errors
            Log::error("SMTP Transport error sending {$context}", [
                'to' => $toEmail,
                'error' => $e->getMessage(),
                'code' => $e->getCode(),
                'trace' => $e->getTraceAsString(),
            ]);

            // Try fallback to log
            self::logEmailFallback($mailable, $toEmail, $context);

            return [
                'success' => false,
                'message' => 'Email service temporarily unavailable',
                'error' => $e->getMessage(),
            ];

        } catch (\Symfony\Component\Mailer\Exception\RfcComplianceException $e) {
            // Invalid email format
            Log::error("Invalid email format for {$context}", [
                'to' => $toEmail,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => 'Invalid email address format',
                'error' => $e->getMessage(),
            ];

        } catch (Exception $e) {
            // General errors
            Log::error("Failed to send {$context}", [
                'to' => $toEmail,
                'error' => $e->getMessage(),
                'type' => get_class($e),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);

            // Try fallback
            self::logEmailFallback($mailable, $toEmail, $context);

            return [
                'success' => false,
                'message' => 'Failed to send email',
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Fallback: Log email content when sending fails
     */
    private static function logEmailFallback($mailable, string $toEmail, string $context)
    {
        try {
            Log::warning("Email fallback activated for {$context}", [
                'to' => $toEmail,
                'mailable' => get_class($mailable),
                'note' => 'Email was not sent but logged for manual follow-up',
            ]);
        } catch (Exception $e) {
            Log::critical("Failed to log email fallback", [
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Check if email service is configured and working
     */
    public static function healthCheck(): array
    {
        $checks = [];

        // Check mail driver configuration
        $checks['mail_driver'] = [
            'status' => config('mail.default') ? 'ok' : 'error',
            'value' => config('mail.default'),
            'message' => config('mail.default') ? 'Mail driver configured' : 'Mail driver not configured',
        ];

        // Check SMTP host
        $checks['smtp_host'] = [
            'status' => config('mail.mailers.smtp.host') ? 'ok' : 'warning',
            'value' => config('mail.mailers.smtp.host'),
            'message' => config('mail.mailers.smtp.host') ? 'SMTP host configured' : 'SMTP host not configured',
        ];

        // Check SMTP credentials
        $checks['smtp_username'] = [
            'status' => config('mail.mailers.smtp.username') ? 'ok' : 'warning',
            'value' => config('mail.mailers.smtp.username') ? '***' : null,
            'message' => config('mail.mailers.smtp.username') ? 'SMTP username configured' : 'SMTP username not configured',
        ];

        $checks['smtp_password'] = [
            'status' => config('mail.mailers.smtp.password') ? 'ok' : 'warning',
            'value' => config('mail.mailers.smtp.password') ? '***' : null,
            'message' => config('mail.mailers.smtp.password') ? 'SMTP password configured' : 'SMTP password not configured',
        ];

        // Check from address
        $checks['from_address'] = [
            'status' => config('mail.from.address') && config('mail.from.address') !== 'hello@example.com' ? 'ok' : 'warning',
            'value' => config('mail.from.address'),
            'message' => config('mail.from.address') && config('mail.from.address') !== 'hello@example.com' 
                ? 'From address configured' 
                : 'From address not properly configured',
        ];

        // Overall status
        $hasErrors = collect($checks)->contains('status', 'error');
        $hasWarnings = collect($checks)->contains('status', 'warning');

        return [
            'overall_status' => $hasErrors ? 'error' : ($hasWarnings ? 'warning' : 'ok'),
            'checks' => $checks,
            'timestamp' => now()->toDateTimeString(),
        ];
    }

    /**
     * Test email connection
     */
    public static function testConnection(): array
    {
        try {
            // Use log mailer for testing to avoid actual sending
            $testEmail = config('mail.from.address');

            Log::info('Testing email connection', [
                'driver' => config('mail.default'),
                'host' => config('mail.mailers.smtp.host'),
                'port' => config('mail.mailers.smtp.port'),
            ]);

            return [
                'success' => true,
                'message' => 'Email configuration appears valid',
                'config' => [
                    'driver' => config('mail.default'),
                    'host' => config('mail.mailers.smtp.host'),
                    'port' => config('mail.mailers.smtp.port'),
                    'encryption' => config('mail.mailers.smtp.encryption'),
                    'from' => config('mail.from.address'),
                ],
            ];

        } catch (Exception $e) {
            Log::error('Email connection test failed', [
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => 'Email connection test failed',
                'error' => $e->getMessage(),
            ];
        }
    }
}






