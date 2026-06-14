<?php

declare(strict_types=1);

use Psr\Log\AbstractLogger;
use Psr\Log\LogLevel;

/**
 * PSR-3 logger writing JSON-line records to stdout (info/debug/notice)
 * and stderr (warning and above). Designed for Docker: the orchestrator
 * collects stdout/stderr; the app does not manage log files.
 *
 * Usage:
 *   Logger::get()->info('user.login', ['user_id' => 42]);
 *   Logger::get()->error('db.insert.failed', ['error' => $e->getMessage()]);
 */
class Logger extends AbstractLogger
{
    private static ?Logger $instance = null;

    /**
     * Levels that go to stderr. Everything else goes to stdout.
     */
    private const STDERR_LEVELS = [
        LogLevel::WARNING,
        LogLevel::ERROR,
        LogLevel::CRITICAL,
        LogLevel::ALERT,
        LogLevel::EMERGENCY,
    ];

    public static function get(): Logger
    {
        if (self::$instance === null) {
            self::$instance = new Logger();
        }
        return self::$instance;
    }

    public function log($level, string|\Stringable $message, array $context = []): void
    {
        $record = [
            'ts'    => date('c'),
            'level' => (string) $level,
            'msg'   => $this->interpolate((string) $message, $context),
        ];
        if ($context !== []) {
            $record['context'] = $this->serializeContext($context);
        }

        // STDOUT/STDERR constants exist only in CLI SAPI; php://* streams
        // work in CLI, HTTP and FrankenPHP alike.
        $target = in_array($level, self::STDERR_LEVELS, true) ? 'php://stderr' : 'php://stdout';
        $line = json_encode($record, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . "\n";
        file_put_contents($target, $line, FILE_APPEND);
    }

    /**
     * PSR-3 placeholder interpolation: replace {key} in $message with $context[key].
     */
    private function interpolate(string $message, array $context): string
    {
        if (!str_contains($message, '{')) {
            return $message;
        }
        $replace = [];
        foreach ($context as $key => $val) {
            if (is_scalar($val) || $val instanceof \Stringable) {
                $replace['{' . $key . '}'] = (string) $val;
            }
        }
        return strtr($message, $replace);
    }

    /**
     * Make exceptions JSON-serialisable; pass everything else through.
     */
    private function serializeContext(array $context): array
    {
        if (isset($context['exception']) && $context['exception'] instanceof \Throwable) {
            $e = $context['exception'];
            $context['exception'] = [
                'class'   => get_class($e),
                'message' => $e->getMessage(),
                'file'    => $e->getFile() . ':' . $e->getLine(),
            ];
        }
        return $context;
    }
}
