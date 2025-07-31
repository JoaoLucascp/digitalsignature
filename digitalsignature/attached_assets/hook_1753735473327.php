
<?php
function plugin_digitalsignature_show_pad($params) {
    // Verificação mais limpa e uso de instanceof, padrão no GLPI 10+
    if (is_array($params) && isset($params['item']) && $params['item'] instanceof ITILSolution) {
        // Obtenção das variáveis necessárias
        global $CFG_GLPI;
        // Usa a variável de configuração do GLPI para construir a URL de forma robusta.
        $plugin_web_dir = $CFG_GLPI['root_doc'] . '/plugins/digitalsignature';
        $ticket_id      = $params['options']['id'];
        $csrf_token     = Session::getNewCSRFToken();

        // Uso de funções de tradução para internacionalização e legibilidade
        $signature_label    = __('Customer Signature', 'digitalsignature');
        $clear_button_label = __('Clear');
        $saving_label       = __('Saving signature...', 'digitalsignature');
        $error_label        = __('Error saving signature:', 'digitalsignature');
        $comm_error_label   = __('Communication error while saving signature. Check browser console for details.', 'digitalsignature');

        // Usar a sintaxe HEREDOC melhora drasticamente a legibilidade do HTML e do JavaScript.
        echo <<<HTML
<tr>
    <th>{$signature_label}</th>
    <td>
        <div id="signature-pad-container" style="border: 1px solid #ccc; width: 450px; height: 220px; position: relative;">
            <canvas id="signature-pad-canvas" width="450" height="220"></canvas>
        </div>
        <!-- Alterada a classe do botão para 'btn btn-secondary' para evitar conflitos com a classe 'vsubmit' do GLPI -->
        <button type="button" id="signature-clear-button" class="btn btn-secondary mt-2">{$clear_button_label}</button>
    </td>
</tr>
<script type="text/javascript" src="{$plugin_web_dir}/js/signature_pad.min.js"></script>
<script type="text/javascript">
(function() {
    console.log('DigitalSignature: Script loaded. Waiting for form elements...');
    let attempts = 0;
    const maxAttempts = 50; // Tenta por 5 segundos (50 * 100ms)

    const initSignaturePad = () => {
        attempts++;
        const canvas = document.getElementById('signature-pad-canvas');
        const clearButton = document.getElementById('signature-clear-button');
        // The form might not have an ID, so we find it relative to the canvas.
        const form = canvas ? canvas.closest('form') : null;
        // Seletor mais robusto para encontrar o botão de envio, seja <input> ou <button>
        const submitButton = form ? form.querySelector('input[name=add], button[name=add]') : null;

        // Adiciona log granular para ver exatamente o que foi encontrado
        if (attempts % 10 === 0) { // Log a cada segundo para não poluir o console
            console.log(`DigitalSignature Verificando (Tentativa \${attempts}): Canvas=\${!!canvas}, BotaoLimpar=\${!!clearButton}, Form=\${!!form}, BotaoEnviar=\${!!submitButton}`);
        }

        // If any element is missing, stop and wait for the next check.
        if (!canvas || !clearButton || !form || !submitButton || attempts > maxAttempts) {
            if (attempts > maxAttempts) {
                console.error('DigitalSignature: Tempo esgotado esperando pelos elementos do formulário. Abortando.');
                clearInterval(checkInterval);
            }
            return false;
        }

        // All elements found, stop polling and initialize.
        clearInterval(checkInterval);
        console.log('DigitalSignature: All elements found. Initializing...');

        const signaturePad = new SignaturePad(canvas, { backgroundColor: 'rgb(255, 255, 255)' });
        const originalButtonValue = submitButton.value;

        clearButton.addEventListener('click', () => {
            console.log('DigitalSignature: Clear button clicked.');
            signaturePad.clear();
        });

        form.addEventListener('submit', (event) => {
            console.log('DigitalSignature: Form submit event triggered.');

            if (signaturePad.isEmpty() || form.dataset.signatureSaved === 'true') {
                console.log('DigitalSignature: Signature is empty or already saved. Allowing normal form submission.');
                return;
            }

            console.log('DigitalSignature: Signature found. Preventing default submission to save via AJAX.');
            event.preventDefault();

            submitButton.disabled = true;
            submitButton.value = '{$saving_label}';

            const dataURL = signaturePad.toDataURL('image/png');
            // Aponta para o novo ponto de entrada público que criamos.
            const ajax_url = `{$CFG_GLPI['root_doc']}/plugins/digitalsignature/save.php`;

            const formData = new FormData();
            formData.append('ticket_id', '{$ticket_id}');
            formData.append('signature_data', dataURL);

            // A forma mais robusta de obter o token CSRF é lendo-o diretamente do formulário.
            const csrfTokenInput = form.querySelector('input[name="_glpi_csrf_token"]');
            if (!csrfTokenInput) {
                console.error('DigitalSignature: CSRF token input field not found in the form.');
                alert('{$error_label} Security token not found.');
                submitButton.disabled = false;
                submitButton.value = originalButtonValue;
                return;
            }
            formData.append('_glpi_csrf_token', csrfTokenInput.value);
            console.log('DigitalSignature: Sending AJAX request to save signature...');
            fetch(ajax_url, { method: 'POST', body: formData })
                .then(response => response.json())
                .then(data => {
                    console.log('DigitalSignature: AJAX response received:', data);
                    if (data.status === 'success') {
                        console.log('DigitalSignature: Save successful. Submitting original form.');
                        form.dataset.signatureSaved = 'true';
                        form.submit();
                    } else {
                        console.error('DigitalSignature: AJAX save failed.', data.message);
                        alert('{$error_label} ' + (data.message || 'Unknown error.'));
                        submitButton.disabled = false;
                        submitButton.value = originalButtonValue;
                    }
                })
                .catch(error => {
                    console.error('DigitalSignature: AJAX communication error.', error);
                    alert('{$comm_error_label}');
                    submitButton.disabled = false;
                    submitButton.value = originalButtonValue;
                });
        });
        return true;
    };

    // Check for the elements every 100ms.
    const checkInterval = setInterval(initSignaturePad, 100);
})();
</script>
HTML;
    }
    return true;
}
