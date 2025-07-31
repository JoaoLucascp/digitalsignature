/**
 * Digital Signature Plugin - Frontend Logic
 * Uses MutationObserver to reliably initialize signature pads added to the page via AJAX.
 */

(function() {
    'use strict';

    /**
     * Initializes a single signature pad instance.
     * @param {HTMLElement} form The form element containing the signature pad.
     */
    function initializePad(form) {
        // Prevent re-initialization
        if (form.dataset.signaturePadInitialized) {
            return;
        }
        form.dataset.signaturePadInitialized = 'true';

        const canvas = form.querySelector(".signature-canvas");
        const saveButton = form.querySelector('.save-signature-btn');
        const clearButton = form.querySelector('.clear-signature-btn');
        const signatureDataInput = form.querySelector('.signature-data-input');

        if (!canvas || !saveButton || !clearButton || !signatureDataInput) {
            console.error('Digital Signature: Could not find all required elements inside the form.', { form });
            return;
        }

        const signaturePad = new SignaturePad(canvas, {
            backgroundColor: 'rgb(255, 255, 255)',
            penColor: 'rgb(0, 0, 0)'
        });

        function toggleSaveButton() {
            saveButton.disabled = signaturePad.isEmpty();
        }

        signaturePad.addEventListener('endStroke', toggleSaveButton);

        clearButton.addEventListener('click', function() {
            signaturePad.clear();
            toggleSaveButton();
        });

        form.addEventListener('submit', function(event) {
            event.preventDefault();

            if (signaturePad.isEmpty()) {
                alert(__('Por favor, forneça uma assinatura.', 'digitalsignature'));
                return;
            }

            signatureDataInput.value = signaturePad.toDataURL('image/png');
            const formData = new FormData(form);

            saveButton.disabled = true;
            saveButton.innerText = __('Salvando...', 'digitalsignature');

            $.ajax({
                url: form.action,
                type: 'POST',
                data: Object.fromEntries(formData.entries()),
                dataType: 'json',
                success: function(response) {
                    alert(response.message || __('Assinatura salva com sucesso!', 'digitalsignature'));
                    location.reload(); // Reload to show the new document
                },
                error: function(jqXHR) {
                    const errorResponse = jqXHR.responseJSON;
                    alert(__('Erro ao salvar a assinatura:', 'digitalsignature') + ' ' + (errorResponse?.message || jqXHR.statusText));
                    saveButton.disabled = false;
                    saveButton.innerText = __('Salvar Assinatura', 'digitalsignature');
                }
            });
        });

        console.log('Digital Signature: Pad initialized successfully for form:', form.id);
    }

    /**
     * Scans the document for uninitialized signature pads.
     */
    function discoverPads() {
        const forms = document.querySelectorAll('form.digitalsignature-container:not([data-signature-pad-initialized])');
        forms.forEach(initializePad);
    }

    // Use a MutationObserver to detect when new forms are added to the page by GLPI's AJAX.
    const observer = new MutationObserver(discoverPads);
    observer.observe(document.body, {
        childList: true,
        subtree: true
    });

    // Run once on initial page load
    discoverPads();
})();
