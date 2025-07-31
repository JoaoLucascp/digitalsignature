<?php

/**
 * Plugin initialization function.
 *
 * This function is called on every page load when the plugin is active.
 * It's used to register hooks, which are the integration points with GLPI.
 *
 * @return boolean
 */
function plugin_init_digitalsignature() {
    global $PLUGIN_HOOKS;

    // This tells GLPI that the plugin's forms are compliant with CSRF protection.
    $PLUGIN_HOOKS['csrf_compliant']['digitalsignature'] = true;

    // This hook injects content before a form item is displayed.
    // We use it to add the signature pad to the ITILSolution form.
    $PLUGIN_HOOKS['pre_item_form']['digitalsignature'] = 'plugin_digitalsignature_show_pad';

    return true;
}

/**
 * Get plugin version information.
 *
 * This function provides metadata about the plugin to GLPI.
 *
 * @return array
 */
function plugin_version_digitalsignature() {
    return [
        'name'           => __('Digital Signature', 'digitalsignature'), // Made the name translatable
        'version'        => '2.0.2', // It's good practice to increment version on each change
        'author'         => 'Joao Lucas',
        'license'        => 'GPLv2+',
        'homepage'       => '',
        'minGlpiVersion' => '10.0'
    ];
}

/**
 * Optional: defines plugin options.
 *
 * This function is called to check if the plugin's configuration is valid.
 * Returning true means everything is okay.
 *
 * @return boolean
 */
function plugin_digitalsignature_check_config() {
    return true;
}

/**
 * Check prerequisites before install.
 *
 * This function can check for PHP extensions or other dependencies before installation.
 * Returning true allows the installation to proceed.
 *
 * @return boolean
 */
function plugin_digitalsignature_check_prerequisites() {
    return true;
}

/**
 * Plugin install function.
 *
 * This function is executed when the plugin is installed.
 * It's the place to create database tables or set default configuration.
 *
 * @return boolean
 */
function plugin_digitalsignature_install() {
    return true;
}

/**
 * Plugin uninstall function.
 *
 * This function is executed when the plugin is uninstalled.
 * It should clean up everything the plugin created (database tables, config, etc.).
 *
 * @return boolean
 */
function plugin_digitalsignature_uninstall() {
    return true;
}

/**
 * Plugin update function.
 *
 * This function is executed when the plugin is updated.
 *
 * @param string $old_version The old version of the plugin.
 *
 * @return boolean
 */
function plugin_digitalsignature_update($old_version) {
    // No specific actions needed for this update, but the function should exist for good practice.
    return true;
}

/**
 * Get public pages for this plugin.
 *
 * This function allows to expose some pages from the plugin folder to be accessible from the web.
 *
 * @return array
 */
function plugin_digitalsignature_get_public_pages() {
    // Expose the ajax endpoint
    return ['ajax/save_signature.php'];
}

/**
 * Hook function to display the signature pad on the solution form.
 *
 * @param array $params Hook parameters.
 *
 * @return boolean
 */
function plugin_digitalsignature_show_pad($params) {
    if (is_array($params) && isset($params['item']) && $params['item'] instanceof ITILSolution) {
        global $CFG_GLPI;
        $plugin_web_dir = $CFG_GLPI['root_doc'] . '/plugins/digitalsignature';
        $ticket_id      = (int)$params['options']['id'];

        $signature_label    = __('Customer Signature', 'digitalsignature');
        $clear_button_label = __('Clear');
        $saving_label       = __('Saving signature...', 'digitalsignature');
        $error_label        = __('Error saving signature:', 'digitalsignature');
        $comm_error_label   = __('Communication error while saving signature. Check browser console for details.', 'digitalsignature');

        $ajax_url = $plugin_web_dir . '/ajax/save_signature.php';

        echo <<<HTML
<tr>
    <th>{$signature_label}</th>
    <td>
        <div id="signature-pad-container" style="border: 1px solid #ccc; width: 450px; height: 220px; position: relative;">
            <canvas id="signature-pad-canvas" width="450" height="220"></canvas>
        </div>
        <button type="button" id="signature-clear-button" class="btn btn-secondary mt-2">{$clear_button_label}</button>
    </td>
</tr>
<script type="text/javascript" src="{$plugin_web_dir}/js/signature_pad.min.js"></script>
<script type="text/javascript">
(function() {
    // Use um intervalo para garantir que os elementos do formulário existam antes de executar o script
    const checkInterval = setInterval(() => {
        const canvas = document.getElementById('signature-pad-canvas');
        if (!canvas) return; // Se o canvas não existe, tente novamente

        clearInterval(checkInterval); // Pare de verificar assim que encontrar o canvas

        const clearButton = document.getElementById('signature-clear-button');
        const form = canvas.closest('form');
        const submitButton = form ? form.querySelector('button[name=add], input[name=add]') : null;

        if (!clearButton || !form || !submitButton) {
            console.error('DigitalSignature: Could not find all required form elements.');
            return;
        }

        const signaturePad = new SignaturePad(canvas, { backgroundColor: 'rgb(255, 255, 255)' });
        const originalButtonText = submitButton.textContent || submitButton.value;

        clearButton.addEventListener('click', () => signaturePad.clear());

        form.addEventListener('submit', (event) => {
            if (signaturePad.isEmpty() || form.dataset.signatureSaved === 'true') {
                return; // Se não houver assinatura ou já foi salva, envie o formulário normalmente
            }

            event.preventDefault(); // Impede o envio do formulário para salvar a assinatura primeiro

            submitButton.disabled = true;
            submitButton.textContent = '{$saving_label}';

            const formData = new FormData(form); // Captura todos os campos do formulário, incluindo o token CSRF
            formData.append('signature_data', signaturePad.toDataURL('image/png'));

            // DEBUG: Log the URL we are about to fetch
            console.log('DigitalSignature: Attempting to fetch URL:', '{$ajax_url}');

            fetch('{$ajax_url}', { method: 'POST', body: formData })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    form.dataset.signatureSaved = 'true'; // Marca como salvo para evitar loop
                    form.submit(); // Agora envia o formulário
                } else {
                    alert('{$error_label} ' + (data.message || 'Unknown error.'));
                    submitButton.disabled = false;
                    submitButton.textContent = originalButtonText;
                }
            }).catch(error => {
                console.error('Signature Save Error:', error);
                alert('{$comm_error_label}');
                submitButton.disabled = false;
                submitButton.textContent = originalButtonText;
            });
        });
    }, 100);
})();
</script>
HTML;
    }
    return true;
}