<?php

namespace GlpiPlugin\Digitalsignature;

use Monolog\Handler\StreamHandler;
use Monolog\Logger;

// Prevent direct access
if (!defined('GLPI_ROOT')) {
    die("Sorry. You can't access this file directly");
}

/**
 * Custom Logger for the DigitalSignature Plugin.
 */
class Log
{
    private static $logger;
    const LOG_FILE = GLPI_LOG_DIR . '/plugin_digitalsignature.log';

    /**
     * Initializes the logger instance.
     */
    private static function init()
    {
        if (self::$logger) {
            return;
        }

        self::$logger = new Logger('digitalsignature');
        // Use GLPI's log level from config if available, otherwise default to DEBUG
        $log_level = $GLOBALS['CFG_GLPI']['log_level'] ?? Logger::DEBUG;
        self::$logger->pushHandler(new StreamHandler(self::LOG_FILE, $log_level));
    }

    /**
     * Log a message.
     *
     * @param int    $level   The log level (e.g., Logger::WARNING).
     * @param string $message The message to log.
     * @param array  $context Optional context data.
     */
    private static function log($level, $message, array $context = [])
    {
        self::init();
        self::$logger->addRecord($level, $message, $context);
    }

    public static function debug($message, array $context = []) { self::log(Logger::DEBUG, $message, $context); }
    public static function info($message, array $context = []) { self::log(Logger::INFO, $message, $context); }
    public static function warning($message, array $context = []) { self::log(Logger::WARNING, $message, $context); }
    public static function error($message, array $context = []) { self::log(Logger::ERROR, $message, $context); }
}

