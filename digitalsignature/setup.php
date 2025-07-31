<?php
/**
 * Digital Signature Plugin for GLPI 10.0.18
 * 
 * This plugin adds digital signature functionality to GLPI tickets
 * allowing customers to sign solutions digitally.
 * 
 * @author Joao Lucas
 * @version 2.0.0
 * @license GPLv2+
 */

// Prevent direct access
if (!defined('GLPI_ROOT')) {
    die("Sorry. You can't access this file directly");
}

/**
 * Plugin initialization function
 * Called on every page load when plugin is active
 */
function plugin_init_digitalsignature() {
    global $PLUGIN_HOOKS;

    // Initialize logging system
    require_once __DIR__ . '/inc/logger.class.php';
    GlpiPlugin\Digitalsignature\Logger::init();
    GlpiPlugin\Digitalsignature\Logger::logPluginEvent('Plugin initialization started');

    // Register plugin classes
    Plugin::registerClass('PluginDigitalsignatureSignature');
    
    // Enable CSRF compliance for security
    $PLUGIN_HOOKS['csrf_compliant']['digitalsignature'] = true;
    
    // Hook to add signature pad to ticket forms - multiple hooks for coverage
    $PLUGIN_HOOKS['post_item_form']['digitalsignature'] = 'plugin_digitalsignature_show_pad';
    $PLUGIN_HOOKS['pre_item_form']['digitalsignature'] = 'plugin_digitalsignature_pre_form';
    
    // Add CSS/JS resources
    $PLUGIN_HOOKS['add_css']['digitalsignature'] = 'css/digitalsignature.css';
    $PLUGIN_HOOKS['add_javascript']['digitalsignature'] = 'js/signature.js';
    
    GlpiPlugin\Digitalsignature\Logger::logPluginEvent('Plugin initialization completed');
    return true;
}

// Plugin version constants for GLPI 10 compatibility
define('PLUGIN_DIGITALSIGNATURE_VERSION', '2.0.0');
define("PLUGIN_DIGITALSIGNATURE_MIN_GLPI_VERSION", "10.0.0"); 
define("PLUGIN_DIGITALSIGNATURE_MAX_GLPI_VERSION", "10.0.99");

/**
 * Get plugin version information
 */
function plugin_version_digitalsignature() {
    return [
        'name'           => 'Assinatura Digital',
        'version'        => PLUGIN_DIGITALSIGNATURE_VERSION,
        'author'         => 'Joao Lucas - Newtel Soluções',
        'license'        => 'GPLv2+',
        'homepage'       => '',
        'requirements'   => [
            'glpi' => [
                'min' => PLUGIN_DIGITALSIGNATURE_MIN_GLPI_VERSION,
                'max' => PLUGIN_DIGITALSIGNATURE_MAX_GLPI_VERSION
            ],
            'php' => [
                'min' => '8.0'
            ]
        ]
    ];
}

/**
 * Check plugin prerequisites
 */
function plugin_digitalsignature_check_prerequisites() {
    // Check GLPI version
    if (version_compare(GLPI_VERSION, '10.0.0', '<')) {
        echo __('Este plugin requer GLPI versão 10.0.0 ou superior', 'digitalsignature');
        return false;
    }
    
    // Check PHP version
    if (version_compare(PHP_VERSION, '8.0', '<')) {
        echo __('Este plugin requer PHP versão 8.0 ou superior', 'digitalsignature');
        return false;
    }
    
    return true;
}

/**
 * Check plugin configuration
 * Called on all GLPI pages to verify plugin status
 */
function plugin_digitalsignature_check_config($verbose = false) {
    // Check if required directories exist and are writable
    $upload_dir = GLPI_DOC_DIR . '/';
    if (!is_writable($upload_dir)) {
        if ($verbose) {
            echo __('Upload directory is not writable', 'digitalsignature');
        }
        return false;
    }
    
    // All checks passed
    return true;
}

/**
 * Plugin installation
 */
function plugin_digitalsignature_install() {
    global $DB;
    
    // Create plugin directory in GLPI_DOC_DIR if not exists
    $signature_dir = GLPI_DOC_DIR . '/digitalsignature';
    if (!is_dir($signature_dir)) {
        if (!mkdir($signature_dir, 0755, true)) {
            Toolbox::logWarning('DigitalSignature: Não foi possível criar diretório de assinaturas');
        }
    }
    
    return true;
}

/**
 * Plugin uninstallation
 */
function plugin_digitalsignature_uninstall() {
    // Optional: Remove signature files on uninstall
    // $signature_dir = GLPI_DOC_DIR . '/digitalsignature';
    // if (is_dir($signature_dir)) {
    //     Toolbox::deleteDir($signature_dir);
    // }
    
    return true;
}

/**
 * Alternative plugin version function for compatibility
 * Some GLPI versions look for this naming pattern
 */
function plugin_version_signaturecapture() {
    return plugin_version_digitalsignature();
}
