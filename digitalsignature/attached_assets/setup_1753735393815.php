<?php
function plugin_init_digitalsignature() {
    global $PLUGIN_HOOKS;
    $PLUGIN_HOOKS['csrf_compliant']['digitalsignature'] = true;
    // Hook específico para o formulário de solução do chamado. É mais confiável.
    $PLUGIN_HOOKS['form_answer']['digitalsignature'] = 'plugin_digitalsignature_show_pad';
}

function plugin_version_digitalsignature() {
    return [
        'name'           => __('Digital Signature', 'digitalsignature'),
        'version'        => '1.0.0',
        'author'         => 'João Lucas',
        'license'        => 'GPLv2+',
        'homepage'       => '',
        'minGlpiVersion' => '10.0.0'
    ];
}

function plugin_digitalsignature_check_config() {
    return true;
}

function plugin_digitalsignature_install() {
    return true;
}

function plugin_digitalsignature_uninstall() {
    return true;
}