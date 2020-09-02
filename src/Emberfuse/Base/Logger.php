<?php

namespace Emberfuse\Base;

use Psr\Log\LoggerInterface;
use InvalidArgumentException;
use Monolog\Handler\StreamHandler;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\ErrorLogHandler;
use Monolog\Logger as MonologLogger;

class Logger implements LoggerInterface
{
    /**
     * Instance of Monolog logger.
     *
     * @var \Monolog\Logger
     */
    protected $monolog;

    /**
     * The Log levels.
     *
     * @var array
     */
    protected $levels = [
        'debug' => MonologLogger::DEBUG,
        'info' => MonologLogger::INFO,
        'notice' => MonologLogger::NOTICE,
        'warning' => MonologLogger::WARNING,
        'error' => MonologLogger::ERROR,
        'critical' => MonologLogger::CRITICAL,
        'alert' => MonologLogger::ALERT,
        'emergency' => MonologLogger::EMERGENCY,
    ];

    /**
     * Create a new log writer instance.
     *
     * @param \Monolog\Logger $monolog
     *
     * @return void
     */
    public function __construct(LoggerInterface $monolog)
    {
        $this->monolog = $monolog;
    }

    /**
     * System is unusable.
     *
     * @param string  $message
     * @param mixed[] $context
     *
     * @return void
     */
    public function emergency($message, array $context = [])
    {
        return $this->writeLog(__FUNCTION__, $message, $context);
    }

    /**
     * Action must be taken immediately.
     *
     * Example: Entire website down, database unavailable, etc. This should
     * trigger the SMS alerts and wake you up.
     *
     * @param string  $message
     * @param mixed[] $context
     *
     * @return void
     */
    public function alert($message, array $context = [])
    {
        return $this->writeLog(__FUNCTION__, $message, $context);
    }

    /**
     * Critical conditions.
     *
     * Example: Application component unavailable, unexpected exception.
     *
     * @param string  $message
     * @param mixed[] $context
     *
     * @return void
     */
    public function critical($message, array $context = [])
    {
        return $this->writeLog(__FUNCTION__, $message, $context);
    }

    /**
     * Runtime errors that do not require immediate action but should typically
     * be logged and monitored.
     *
     * @param string  $message
     * @param mixed[] $context
     *
     * @return void
     */
    public function error($message, array $context = [])
    {
        return $this->writeLog(__FUNCTION__, $message, $context);
    }

    /**
     * Exceptional occurrences that are not errors.
     *
     * Example: Use of deprecated APIs, poor use of an API, undesirable things
     * that are not necessarily wrong.
     *
     * @param string  $message
     * @param mixed[] $context
     *
     * @return void
     */
    public function warning($message, array $context = [])
    {
        return $this->writeLog(__FUNCTION__, $message, $context);
    }

    /**
     * Normal but significant events.
     *
     * @param string  $message
     * @param mixed[] $context
     *
     * @return void
     */
    public function notice($message, array $context = [])
    {
        return $this->writeLog(__FUNCTION__, $message, $context);
    }

    /**
     * Interesting events.
     *
     * Example: User logs in, SQL logs.
     *
     * @param string  $message
     * @param mixed[] $context
     *
     * @return void
     */
    public function info($message, array $context = [])
    {
        return $this->writeLog(__FUNCTION__, $message, $context);
    }

    /**
     * Detailed debug information.
     *
     * @param string  $message
     * @param mixed[] $context
     *
     * @return void
     */
    public function debug($message, array $context = [])
    {
        return $this->writeLog(__FUNCTION__, $message, $context);
    }

    /**
     * Logs with an arbitrary level.
     *
     * @param mixed   $level
     * @param string  $message
     * @param mixed[] $context
     *
     * @return void
     *
     * @throws \Psr\Log\InvalidArgumentException
     */
    public function log($level, $message, array $context = [])
    {
        return $this->writeLog(__FUNCTION__, $message, $context);
    }

    /**
     * Write a message to Monolog.
     *
     * @param string $level
     * @param string $message
     * @param array  $context
     *
     * @return void
     */
    protected function writeLog(string $level, string $message, array $context): void
    {
        $this->getMonolog()->{$level}($this->formatMessage($message), $context);
    }

    /**
     * Format the parameters for the logger.
     *
     * @param mixed $message
     *
     * @return string
     */
    protected function formatMessage(string $message): string
    {
        if (is_array($message)) {
            return var_export($message, true);
        }

        return $message;
    }

    /**
     * Register a file log handler.
     *
     * @param string $path
     * @param string $level
     *
     * @return void
     */
    public function useFiles(string $path, string $level = 'debug'): void
    {
        $this->monolog->pushHandler(
            $handler = new StreamHandler($path, $this->parseLevel($level))
        );

        $handler->setFormatter($this->getDefaultFormatter());
    }

    /**
     * Register an error_log handler.
     *
     * @param string $level
     * @param int    $messageType
     *
     * @return void
     */
    public function useErrorLog(string $level = 'debug', int $messageType = ErrorLogHandler::OPERATING_SYSTEM): void
    {
        $this->getMonolog()->pushHandler(
            $handler = new ErrorLogHandler($messageType, $this->parseLevel($level))
        );

        $handler->setFormatter($this->getDefaultFormatter());
    }

    /**
     * Parse the string level into a Monolog constant.
     *
     * @param string $level
     *
     * @return int
     *
     * @throws \InvalidArgumentException
     */
    protected function parseLevel(string $level): int
    {
        if (isset($this->levels[$level])) {
            return $this->levels[$level];
        }

        throw new InvalidArgumentException('Invalid log level.');
    }

    /**
     * Get a defaut Monolog formatter instance.
     *
     * @return \Monolog\Formatter\LineFormatter
     */
    protected function getDefaultFormatter(): LineFormatter
    {
        return new LineFormatter(null, null, true, true);
    }

    /**
     * Get the Monolog instance.
     *
     * @return \Monolog\Logger
     */
    public function getMonolog()
    {
        return $this->monolog;
    }
}
