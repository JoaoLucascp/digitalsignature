<?php
/**
 * Test script for Digital Signature plugin
 * Run this to verify plugin structure and compatibility
 */

// Define GLPI constants for testing
if (!defined('GLPI_ROOT')) {
    define('GLPI_ROOT', '/var/www/html/glpi');
}

echo "=== TESTE DO PLUGIN DIGITAL SIGNATURE ===\n\n";

// Test 1: Check if setup.php loads correctly
echo "1. Testando setup.php...\n";
try {
    require_once(__DIR__ . '/setup.php');
    echo "   ✓ setup.php carregado com sucesso\n";
} catch (Exception $e) {
    echo "   ✗ Erro ao carregar setup.php: " . $e->getMessage() . "\n";
}

// Test 2: Check if hook.php loads correctly  
echo "\n2. Testando hook.php...\n";
try {
    require_once(__DIR__ . '/hook.php');
    echo "   ✓ hook.php carregado com sucesso\n";
} catch (Exception $e) {
    echo "   ✗ Erro ao carregar hook.php: " . $e->getMessage() . "\n";
}

// Test 3: Check if required functions exist
echo "\n3. Verificando funções obrigatórias...\n";
$required_functions = [
    'plugin_version_digitalsignature',
    'plugin_init_digitalsignature', 
    'plugin_digitalsignature_check_prerequisites',
    'plugin_digitalsignature_check_config',
    'plugin_digitalsignature_install',
    'plugin_digitalsignature_uninstall'
];

foreach ($required_functions as $function) {
    if (function_exists($function)) {
        echo "   ✓ $function existe\n";
    } else {
        echo "   ✗ $function não encontrada\n";
    }
}

// Test 4: Test plugin version function
echo "\n4. Testando função de versão...\n";
if (function_exists('plugin_version_digitalsignature')) {
    $version_info = plugin_version_digitalsignature();
    echo "   ✓ Nome: " . $version_info['name'] . "\n";
    echo "   ✓ Versão: " . $version_info['version'] . "\n";
    echo "   ✓ Autor: " . $version_info['author'] . "\n";
} else {
    echo "   ✗ Função de versão não encontrada\n";
}

// Test 5: Check file structure
echo "\n5. Verificando estrutura de arquivos...\n";
$required_files = [
    'setup.php',
    'hook.php', 
    'inc/digitalsignature.class.php',
    'js/signature.js',
    'js/signature_pad.min.js',
    'css/digitalsignature.css'
];

foreach ($required_files as $file) {
    if (file_exists(__DIR__ . '/' . $file)) {
        echo "   ✓ $file existe\n";
    } else {
        echo "   ✗ $file não encontrado\n";
    }
}

echo "\n=== TESTE CONCLUÍDO ===\n";
echo "Execute: php test_plugin.php no diretório do plugin\n";
?>