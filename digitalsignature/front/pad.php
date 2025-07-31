<?php
/**
 * Digital signature Pad - Front Interface
 * Displays signature capture interface for tickets
 */

include ('../../../inc/includes.php');

use Html;

// Check user authentication
Session::checkLoginUser();

// Get ticket ID from parameters
$ticket_id = $_GET['ticket_id'] ?? 0;
$ticket_id = (int)$ticket_id;

if (!$ticket_id) {
    Html::displayErrorAndDie(__('Ticket ID é obrigatório', 'digitalsignature'));
}

// Verify ticket exists and user has rights
$ticket = new Ticket();
if (!$ticket->getFromDB($ticket_id)) {
    Html::displayErrorAndDie(__('Ticket não encontrado', 'digitalsignature'));
}

// Check if user can update ticket
if (!$ticket->canUpdateItem()) {
    Html::displayErrorAndDie(__('Você não tem permissão para modificar este ticket', 'digitalsignature'));
}

// Start HTML output
Html::header(
    __('Assinatura Digital', 'digitalsignature'),
    $_SERVER['PHP_SELF'],
    "helpdesk",
    "ticket",
    $ticket_id
);

echo "<div class='center'>";
echo "<h2>" . sprintf(__('Assinatura para o Ticket #%d', 'digitalsignature'), $ticket_id) . "</h2>";

// Display signature form with new CSRF token generation
echo "<form id='signature-form' method='post' action='" . $CFG_GLPI['root_doc'] . "/plugins/digitalsignature/ajax/save_signature.php'>";
echo Html::hidden('_glpi_csrf_token', ['value' => Session::getNewCSRFToken()]);
echo Html::hidden('ticket_id', ['value' => $ticket_id]);

echo "<div class='digitalsignature-container'>";
echo "<p>" . __('Por favor, desenhe sua assinatura no quadro abaixo:', 'digitalsignature') . "</p>";
echo "<canvas id='signature-canvas' width='500' height='200'></canvas><br>";
echo "<button type='button' id='clear-signature' class='btn btn-secondary'>" . __('Limpar', 'digitalsignature') . "</button>";
echo "<button type='submit' id='save-signature' class='btn btn-primary' disabled>" . __('Salvar Assinatura', 'digitalsignature') . "</button>";
echo Html::hidden('signature_data', ['value' => '']);
echo "</div>";

echo "</form>";
echo "</div>";

// Load JavaScript
echo Html::script($CFG_GLPI['root_doc'] . '/plugins/digitalsignature/js/signature_pad.min.js');
echo Html::script($CFG_GLPI['root_doc'] . '/plugins/digitalsignature/js/signature.js');

// Initialize signature pad
echo "<script>
jQuery(document).ready(function($) {
    if (typeof initializesignaturePad === 'function') {
        initializesignaturePad();
    }
});
</script>";

Html::footer();
?>