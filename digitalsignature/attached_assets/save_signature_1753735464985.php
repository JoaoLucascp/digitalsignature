<?php

// Apenas precisamos carregar nosso plugin para usar suas classes.
Plugin::load('digitalsignature');

use GlpiPlugin\Digitalsignature\Log;

// Define o cabeçalho da resposta como JSON
header('Content-Type: application/json');

/**
 * Envia uma resposta de erro em JSON, registra o log e encerra o script.
 *
 * @param string $message     A mensagem de erro para o usuário.
 * @param array  $log_context Contexto adicional para o log.
 *
 * @return void
 */
function send_json_error($message, $log_context = []) {
    Log::error($message, $log_context);
    echo json_encode(['status' => 'error', 'message' => $message]);
    exit;
}

// 0. Verificação do Método da Requisição
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    send_json_error(__('Invalid request method.'));
}

// 1. Verificação de Segurança (CSRF)
if (!Session::checkCSRF('_glpi_csrf_token', $_POST)) {
    send_json_error(__('Security token validation failed.'), ['post_data' => $_POST]);
}

// 2. Validação dos Dados de Entrada
$ticket_id = filter_input(INPUT_POST, 'ticket_id', FILTER_VALIDATE_INT);
if (!$ticket_id) {
    send_json_error(__('Invalid or missing Ticket ID.'));
}

$signature_data = $_POST['signature_data'] ?? null;
if (empty($signature_data) || strpos($signature_data, 'data:image/png;base64,') !== 0) {
    send_json_error(__('Invalid or missing signature data.'));
}

// 3. Lógica de Negócio - Salvar a Assinatura
try {
    // Carrega o ticket para garantir que ele existe
    $ticket = new Ticket();
    if (!$ticket->getFromDB($ticket_id)) {
        send_json_error(__('Ticket not found.'), ['ticket_id' => $ticket_id]);
    }

    // Decodifica a imagem
    $base64_image = str_replace('data:image/png;base64,', '', $signature_data);
    $image_data = base64_decode($base64_image);
    if ($image_data === false) {
        send_json_error(__('Failed to decode signature image.'));
    }

    // Cria um novo Documento no GLPI para armazenar a assinatura
    $doc = new Document();
    $input = [
        'entities_id'   => $ticket->fields['entities_id'],
        'is_recursive'  => $ticket->fields['is_recursive'],
        'name'          => sprintf(__('Signature for Solution of Ticket %d'), $ticket_id),
        'filename'      => 'signature_' . $ticket_id . '_' . time() . '.png',
        'itemtype'      => 'Ticket', // Associa o documento ao tipo de item Ticket
        'items_id'      => $ticket_id,  // Associa o documento ao ID do ticket
        'mime'          => 'image/png',
    ];

    if ($doc->add($input)) {
        // Salva o conteúdo do arquivo
        $doc_path = GLPI_DOC_DIR . '/' . $doc->fields['filepath'];
        if (file_put_contents($doc_path, $image_data)) {
            Log::info('Signature saved successfully for ticket ' . $ticket_id, ['document_id' => $doc->getID()]);
            echo json_encode(['status' => 'success', 'message' => 'Signature saved.']);
        } else {
            // Se falhar ao salvar o arquivo, remove o registro do documento do DB para não deixar órfãos
            $doc->delete(['id' => $doc->getID()]);
            send_json_error(__('Failed to write signature file to disk.'), ['path' => $doc_path]);
        }
    } else {
        send_json_error(__('Failed to create document record in GLPI.'), ['glpi_error' => $doc->getErrorMessage(ERROR_VALIDATE)]);
    }

} catch (Exception $e) {
    send_json_error('An unexpected error occurred: ' . $e->getMessage(), ['exception' => $e]);
}