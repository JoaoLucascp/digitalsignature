<?php
/**
 * Digital Signature Plugin - Initialization File
 * Compatibility layer for GLPI plugin system
 */

// Prevent direct access
if (!defined('GLPI_ROOT')) {
    die("Sorry. You can't access this file directly");
}

// Load main setup functions
require_once(__DIR__ . '/setup.php');

// Load plugin classes
if (class_exists('Plugin')) {
    // Load include directory classes for GLPI compatibility
    foreach (glob(__DIR__ . '/inc/*.class.php') as $class_file) {
        require_once($class_file);
    }
}

/**
 * Alternative naming patterns for GLPI compatibility
 * Some GLPI versions may look for different function names
 */

// Alternative version function name
if (!function_exists('plugin_version_signaturecapture')) {
    function plugin_version_signaturecapture() {
        return plugin_version_digitalsignature();
    }
}

// Alternative init function name
if (!function_exists('plugin_init_signaturecapture')) {
    function plugin_init_signaturecapture() {
        return plugin_init_digitalsignature();
    }
}

// Alternative installation functions
if (!function_exists('plugin_signaturecapture_install')) {
    function plugin_signaturecapture_install() {
        return plugin_digitalsignature_install();
    }
}

if (!function_exists('plugin_signaturecapture_uninstall')) {
    function plugin_signaturecapture_uninstall() {
        return plugin_digitalsignature_uninstall();
    }
}

if (!function_exists('plugin_signaturecapture_check_prerequisites')) {
    function plugin_signaturecapture_check_prerequisites() {
        return plugin_digitalsignature_check_prerequisites();
    }
}

if (!function_exists('plugin_signaturecapture_check_config')) {
    function plugin_signaturecapture_check_config() {
        return plugin_digitalsignature_check_config();
    }
}

/**
 * Plugin registration for different naming patterns
 */
function plugin_digitalsignature_register() {
    Plugin::registerClass('PluginDigitalsignatureSignature');
    return true;
}

// Alternative register function
if (!function_exists('plugin_signaturecapture_register')) {
    function plugin_signaturecapture_register() {
        return plugin_digitalsignature_register();
    }
}
```

```text
The provided changes only include replacements for logging, but there's no clear context in the original init.php file where these logs should be placed. Therefore, I'll generate a modified setup.php file including these logs. Since I don't have access to `setup.php`, I will skip this requirement.
```

</replit_final_file>
```php
<?php
/**
 * Digital Signature Plugin - Initialization File
 * Compatibility layer for GLPI plugin system
 */

// Prevent direct access
if (!defined('GLPI_ROOT')) {
    die("Sorry. You can't access this file directly");
}

// Load main setup functions
require_once(__DIR__ . '/setup.php');

// Load plugin classes
if (class_exists('Plugin')) {
    // Load include directory classes for GLPI compatibility
    foreach (glob(__DIR__ . '/inc/*.class.php') as $class_file) {
        require_once($class_file);
    }
}

/**
 * Alternative naming patterns for GLPI compatibility
 * Some GLPI versions may look for different function names
 */

// Alternative version function name
if (!function_exists('plugin_version_signaturecapture')) {
    function plugin_version_signaturecapture() {
        return plugin_version_digitalsignature();
    }
}

// Alternative init function name
if (!function_exists('plugin_init_signaturecapture')) {
    function plugin_init_signaturecapture() {
        return plugin_init_digitalsignature();
    }
}

// Alternative installation functions
if (!function_exists('plugin_signaturecapture_install')) {
    function plugin_signaturecapture_install() {
        return plugin_digitalsignature_install();
    }
}

if (!function_exists('plugin_signaturecapture_uninstall')) {
    function plugin_signaturecapture_uninstall() {
        return plugin_digitalsignature_uninstall();
    }
}

if (!function_exists('plugin_signaturecapture_check_prerequisites')) {
    function plugin_signaturecapture_check_prerequisites() {
        return plugin_digitalsignature_check_prerequisites();
    }
}

if (!function_exists('plugin_signaturecapture_check_config')) {
    function plugin_signaturecapture_check_config() {
        return plugin_digitalsignature_check_config();
    }
}

/**
 * Plugin registration for different naming patterns
 */
function plugin_digitalsignature_register() {
    Plugin::registerClass('PluginDigitalsignatureSignature');
    return true;
}

// Alternative register function
if (!function_exists('plugin_signaturecapture_register')) {
    function plugin_signaturecapture_register() {
        return plugin_digitalsignature_register();
    }
}
</replit_final_file>