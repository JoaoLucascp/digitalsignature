<?php
/**
 * Logger class for Digital Signature Plugin
 * Provides comprehensive logging for debugging and monitoring
 */

namespace GlpiPlugin\Digitalsignature;

class Logger {
    
    private static $log_file = null;
    private static $log_levels = [
        'DEBUG' => 0,
        'INFO' => 1,
        'WARNING' => 2,
        'ERROR' => 3,
        'FATAL' => 4
    ];
    
    /**
     * Initialize logger with proper file path
     */
    public static function init() {
        global $CFG_GLPI;
        
        // Create logs directory if it doesn't exist
        $log_dir = GLPI_LOG_DIR . '/plugins';
        if (!is_dir($log_dir)) {
            @mkdir($log_dir, 0755, true);
        }
        
        self::$log_file = $log_dir . '/digitalsignature.log';
        
        // Log plugin initialization
        self::info('Digital Signature Plugin Logger initialized');
    }
    
    /**
     * Log debug information
     */
    public static function debug($message, $context = []) {
        self::log('DEBUG', $message, $context);
    }
    
    /**
     * Log info messages
     */
    public static function info($message, $context = []) {
        self::log('INFO', $message, $context);
    }
    
    /**
     * Log warning messages
     */
    public static function warning($message, $context = []) {
        self::log('WARNING', $message, $context);
    }
    
    /**
     * Log error messages
     */
    public static function error($message, $context = []) {
        self::log('ERROR', $message, $context);
    }
    
    /**
     * Log fatal errors
     */
    public static function fatal($message, $context = []) {
        self::log('FATAL', $message, $context);
    }
    
    /**
     * Main logging method
     */
    private static function log($level, $message, $context = []) {
        if (!self::$log_file) {
            self::init();
        }
        
        $timestamp = date('Y-m-d H:i:s');
        $user_id = isset($_SESSION['glpiID']) ? $_SESSION['glpiID'] : 'anonymous';
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $request_uri = $_SERVER['REQUEST_URI'] ?? '';
        
        // Format context data
        $context_str = '';
        if (!empty($context)) {
            $context_str = ' | Context: ' . json_encode($context, JSON_UNESCAPED_UNICODE);
        }
        
        // Create log entry
        $log_entry = sprintf(
            "[%s] [%s] [User:%s] [IP:%s] [URI:%s] %s%s\n",
            $timestamp,
            $level,
            $user_id,
            $ip,
            $request_uri,
            $message,
            $context_str
        );
        
        // Write to file
        @file_put_contents(self::$log_file, $log_entry, FILE_APPEND | LOCK_EX);
        
        // Also log to GLPI system log for errors and fatal
        if (in_array($level, ['ERROR', 'FATAL'])) {
            if (function_exists('Toolbox::logInFile')) {
                \Toolbox::logInFile('digitalsignature', $message);
            }
        }
    }
    
    /**
     * Log HTTP request details
     */
    public static function logRequest($action = '') {
        $data = [
            'method' => $_SERVER['REQUEST_METHOD'] ?? 'unknown',
            'post_data' => $_POST ? array_keys($_POST) : [],
            'get_data' => $_GET ? array_keys($_GET) : [],
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
            'referer' => $_SERVER['HTTP_REFERER'] ?? 'none'
        ];
        
        self::debug("HTTP Request: $action", $data);
    }
    
    /**
     * Log plugin events
     */
    public static function logPluginEvent($event, $details = []) {
        self::info("Plugin Event: $event", $details);
    }
    
    /**
     * Log JavaScript errors (called via AJAX)
     */
    public static function logJSError($error, $file = '', $line = '') {
        $context = [
            'file' => $file,
            'line' => $line,
            'browser' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown'
        ];
        
        self::error("JavaScript Error: $error", $context);
    }
    
    /**
     * Clear old log entries (keep last 30 days)
     */
    public static function cleanup() {
        if (!self::$log_file || !file_exists(self::$log_file)) {
            return;
        }
        
        $lines = file(self::$log_file);
        $cutoff_date = date('Y-m-d', strtotime('-30 days'));
        $kept_lines = [];
        
        foreach ($lines as $line) {
            if (preg_match('/^\[(\d{4}-\d{2}-\d{2})/', $line, $matches)) {
                if ($matches[1] >= $cutoff_date) {
                    $kept_lines[] = $line;
                }
            }
        }
        
        file_put_contents(self::$log_file, implode('', $kept_lines));
        self::info('Log cleanup completed', ['kept_lines' => count($kept_lines)]);
    }
}