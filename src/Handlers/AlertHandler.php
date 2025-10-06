<?php

namespace AFM\AdvancedLogger\Handlers;

use AFM\AdvancedLogger\Models\LogEntry;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class AlertHandler
{
    protected Application $app;
    protected array $thresholds;
    protected array $channels;

    public function __construct(Application $app)
    {
        $this->app = $app;
        $this->thresholds = config('advanced-logger.alerts.thresholds', []);
        $this->channels = config('advanced-logger.alerts.channels', []);
    }

    /**
     * Check if alerts should be triggered for the given log entry.
     */
    public function checkAlerts(array $logData): void
    {
        if (!config('advanced-logger.alerts.enabled', true)) {
            return;
        }

        $level = $logData['level'];
        
        // Check if this level has alert thresholds
        if (!isset($this->thresholds[$level])) {
            return;
        }

        $threshold = $this->thresholds[$level];
        
        // Check if threshold is exceeded
        if ($this->isThresholdExceeded($level, $threshold, $logData)) {
            $this->sendAlerts($level, $logData);
        }
    }

    /**
     * Check if alert threshold is exceeded.
     */
    protected function isThresholdExceeded(string $level, array $threshold, array $logData): bool
    {
        $count = $threshold['count'] ?? 5;
        $timeWindow = $threshold['time_window'] ?? '1 hour';
        
        // Convert time window to minutes
        $minutes = $this->parseTimeWindow($timeWindow);
        
        // Count recent logs of this level
        $recentCount = LogEntry::where('level', $level)
            ->where('created_at', '>=', now()->subMinutes($minutes))
            ->count();

        return $recentCount >= $count;
    }

    /**
     * Send alerts through configured channels.
     */
    protected function sendAlerts(string $level, array $logData): void
    {
        foreach ($this->channels as $channelName => $channelConfig) {
            if (!($channelConfig['enabled'] ?? false)) {
                continue;
            }

            try {
                match ($channelName) {
                    'email' => $this->sendEmailAlert($channelConfig, $level, $logData),
                    'slack' => $this->sendSlackAlert($channelConfig, $level, $logData),
                    'telegram' => $this->sendTelegramAlert($channelConfig, $level, $logData),
                    default => null,
                };
            } catch (\Exception $e) {
                Log::error("Failed to send {$channelName} alert", [
                    'error' => $e->getMessage(),
                    'log_data' => $logData,
                ]);
            }
        }
    }

    /**
     * Send email alert.
     */
    protected function sendEmailAlert(array $config, string $level, array $logData): void
    {
        $to = $config['to'] ?? null;
        if (!$to) {
            return;
        }

        $subject = ($config['subject_prefix'] ?? '[Log Alert]') . " {$level}: {$logData['message']}";
        
        $data = [
            'level' => $level,
            'message' => $logData['message'],
            'category' => $logData['category'],
            'context' => $logData['context'],
            'timestamp' => now()->format('Y-m-d H:i:s'),
            'url' => $logData['url'],
            'user_id' => $logData['user_id'],
            'ip_address' => $logData['ip_address'],
        ];

        Mail::send('advanced-logger::emails.alert', $data, function ($message) use ($to, $subject) {
            $message->to($to)->subject($subject);
        });
    }

    /**
     * Send Slack alert.
     */
    protected function sendSlackAlert(array $config, string $level, array $logData): void
    {
        $webhook = $config['webhook'] ?? null;
        $channel = $config['channel'] ?? '#alerts';
        
        if (!$webhook) {
            return;
        }

        $color = match ($level) {
            'emergency', 'alert', 'critical' => 'danger',
            'error' => 'warning',
            'warning' => 'warning',
            default => 'good',
        };

        $payload = [
            'channel' => $channel,
            'attachments' => [
                [
                    'color' => $color,
                    'title' => "Log Alert: {$level}",
                    'text' => $logData['message'],
                    'fields' => [
                        [
                            'title' => 'Category',
                            'value' => $logData['category'] ?? 'N/A',
                            'short' => true,
                        ],
                        [
                            'title' => 'Timestamp',
                            'value' => now()->format('Y-m-d H:i:s'),
                            'short' => true,
                        ],
                        [
                            'title' => 'URL',
                            'value' => $logData['url'] ?? 'N/A',
                            'short' => false,
                        ],
                        [
                            'title' => 'User ID',
                            'value' => $logData['user_id'] ?? 'N/A',
                            'short' => true,
                        ],
                        [
                            'title' => 'IP Address',
                            'value' => $logData['ip_address'] ?? 'N/A',
                            'short' => true,
                        ],
                    ],
                    'footer' => 'Advanced Logger',
                    'ts' => now()->timestamp,
                ],
            ],
        ];

        if (!empty($logData['context'])) {
            $payload['attachments'][0]['fields'][] = [
                'title' => 'Context',
                'value' => '```' . json_encode($logData['context'], JSON_PRETTY_PRINT) . '```',
                'short' => false,
            ];
        }

        Http::post($webhook, $payload);
    }

    /**
     * Send Telegram alert.
     */
    protected function sendTelegramAlert(array $config, string $level, array $logData): void
    {
        $botToken = $config['bot_token'] ?? null;
        $chatId = $config['chat_id'] ?? null;
        
        if (!$botToken || !$chatId) {
            return;
        }

        $emoji = match ($level) {
            'emergency', 'alert', 'critical' => '🚨',
            'error' => '❌',
            'warning' => '⚠️',
            default => 'ℹ️',
        };

        $message = "{$emoji} *Log Alert: {$level}*\n\n";
        $message .= "*Message:* {$logData['message']}\n";
        $message .= "*Category:* " . ($logData['category'] ?? 'N/A') . "\n";
        $message .= "*Timestamp:* " . now()->format('Y-m-d H:i:s') . "\n";
        $message .= "*URL:* " . ($logData['url'] ?? 'N/A') . "\n";
        $message .= "*User ID:* " . ($logData['user_id'] ?? 'N/A') . "\n";
        $message .= "*IP:* " . ($logData['ip_address'] ?? 'N/A') . "\n";

        if (!empty($logData['context'])) {
            $message .= "\n*Context:*\n```\n" . json_encode($logData['context'], JSON_PRETTY_PRINT) . "\n```";
        }

        $url = "https://api.telegram.org/bot{$botToken}/sendMessage";
        
        Http::post($url, [
            'chat_id' => $chatId,
            'text' => $message,
            'parse_mode' => 'Markdown',
        ]);
    }

    /**
     * Parse time window string to minutes.
     */
    protected function parseTimeWindow(string $timeWindow): int
    {
        $timeWindow = trim($timeWindow);
        
        if (preg_match('/(\d+)\s*(minute|hour|day|week)s?/i', $timeWindow, $matches)) {
            $value = (int) $matches[1];
            $unit = strtolower($matches[2]);
            
            return match ($unit) {
                'minute' => $value,
                'hour' => $value * 60,
                'day' => $value * 60 * 24,
                'week' => $value * 60 * 24 * 7,
                default => 60,
            };
        }

        return 60; // Default to 1 hour
    }

    /**
     * Get alert statistics.
     */
    public function getAlertStats(): array
    {
        $stats = [];
        
        foreach (array_keys($this->thresholds) as $level) {
            $threshold = $this->thresholds[$level];
            $minutes = $this->parseTimeWindow($threshold['time_window']);
            
            $stats[$level] = [
                'threshold' => $threshold['count'],
                'time_window' => $threshold['time_window'],
                'recent_count' => LogEntry::where('level', $level)
                    ->where('created_at', '>=', now()->subMinutes($minutes))
                    ->count(),
                'threshold_exceeded' => LogEntry::where('level', $level)
                    ->where('created_at', '>=', now()->subMinutes($minutes))
                    ->count() >= $threshold['count'],
            ];
        }

        return $stats;
    }

    /**
     * Test alert channels.
     */
    public function testChannels(): array
    {
        $results = [];
        $testLogData = [
            'level' => 'info',
            'message' => 'Test alert message',
            'category' => 'test',
            'context' => ['test' => true],
            'url' => request()->fullUrl(),
            'user_id' => auth()->id(),
            'ip_address' => request()->ip(),
        ];

        foreach ($this->channels as $channelName => $channelConfig) {
            if (!($channelConfig['enabled'] ?? false)) {
                continue;
            }

            try {
                match ($channelName) {
                    'email' => $this->sendEmailAlert($channelConfig, 'info', $testLogData),
                    'slack' => $this->sendSlackAlert($channelConfig, 'info', $testLogData),
                    'telegram' => $this->sendTelegramAlert($channelConfig, 'info', $testLogData),
                    default => null,
                };
                
                $results[$channelName] = 'success';
            } catch (\Exception $e) {
                $results[$channelName] = 'failed: ' . $e->getMessage();
            }
        }

        return $results;
    }
}
