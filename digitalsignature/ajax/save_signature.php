<?php

/**
 * AJAX endpoint to save digital signatures with comprehensive logging
 * Processes signature data and attaches to tickets
 */

// Set proper paths for GLPI structure
if (strpos($_SERVER['SCRIPT_NAME'], '/public/') !== false) {
    define('GLPI_ROOT', dirname(__DIR__, 4));
} else {
    define('GLPI_ROOT', dirname(__DIR__, 3));
}

include_once(GLPI_ROOT . '/inc/includes.php');

// Initialize logging
require_once __DIR__ . '/../inc/logger.class.php';
GlpiPlugin\digitalsignature\Logger::init();

// Set JSON response header
header('Content-Type: application/json');

// Log the incoming request
GlpiPlugin\digitalsignature\Logger::logRequest('save_signature_ajax');
GlpiPlugin\digitalsignature\Logger::info('Signature save request started', [
    'user_id' => Session::getLoginUserID(false),
    'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
    'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
    'post_keys' => array_keys($_POST),
    'signature_data_present' => !empty($_POST['signature_data']),
    'signature_data_size' => isset($_POST['signature_data']) ? strlen($_POST['signature_data']) : 0,
    'ticket_id' => $_POST['ticket_id'] ?? 'not_provided'
]);

try {
    // Security checks
    if (!Session::getLoginUserID()) {
        GlpiPlugin\digitalsignature\Logger::error('Authentication failed - user not logged in');
        http_response_code(401);
        echo json_encode([
            'success' => false,
            'message' => __('Usuário não autenticado', 'digitalsignature')
        ]);
        exit;
    }

    // Get user information using GLPI 10+ compatible method
    $user_id = Session::getLoginUserID();
    $user_name = '';
    if ($user_id) {
        $user = new User();
        if ($user->getFromDB($user_id)) {
            $user_name = $user->fields['name'] ?? '';
        }
    }

    GlpiPlugin\digitalsignature\Logger::debug('User authentication successful', [
        'user_id' => $user_id,
        'user_name' => $user_name
    ]);

    // Verify CSRF token - multiple methods for compatibility
    if (empty($_POST['_glpi_csrf_token']) || !Session::validateCSRFToken($_POST['_glpi_csrf_token'])) {
        GlpiPlugin\digitalsignature\Logger::error('CSRF token validation failed', [
            'csrf_token_provided' => $_POST['_glpi_csrf_token'] ?? 'not provided'
        ]);

        http_response_code(403);
        echo json_encode([
            'success' => false,
            'message' => __('Invalid CSRF token', 'digitalsignature')
        ]);
        exit;
    }

    GlpiPlugin\digitalsignature\Logger::debug('CSRF token validated successfully');

    // Validate input data
    $ticket_id = filter_var($_POST['ticket_id'] ?? 0, FILTER_VALIDATE_INT);
    $signature_data = $_POST['signature_data'] ?? '';

    GlpiPlugin\digitalsignature\Logger::debug('Input validation', [
        'ticket_id_raw' => $_POST['ticket_id'] ?? 'not_provided',
        'ticket_id_filtered' => $ticket_id,
        'signature_data_provided' => !empty($signature_data),
        'signature_data_length' => strlen($signature_data),
        'signature_data_format' => substr($signature_data, 0, 50) . '...'
    ]);

    if (!$ticket_id) {
        GlpiPlugin\digitalsignature\Logger::error('Invalid ticket ID provided', [
            'ticket_id_raw' => $_POST['ticket_id'] ?? 'not_provided'
        ]);

        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => __('Ticket ID é obrigatório', 'digitalsignature')
        ]);
        exit;
    }

    if (empty($signature_data)) {
        GlpiPlugin\digitalsignature\Logger::error('No signature data provided');

        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => __('Dados de assinatura são obrigatórios', 'digitalsignature')
        ]);
        exit;
    }

    // Validate signature data format
    if (!preg_match('/^data:image\/png;base64,/', $signature_data)) {
        GlpiPlugin\digitalsignature\Logger::error('Invalid signature data format', [
            'format_start' => substr($signature_data, 0, 30)
        ]);

        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => __('Formato de assinatura inválido', 'digitalsignature')
        ]);
        exit;
    }

    // Load ticket to verify it exists and user has access
    $ticket = new Ticket();
    if (!$ticket->getFromDB($ticket_id)) {
        GlpiPlugin\digitalsignature\Logger::error('Ticket not found in database', [
            'ticket_id' => $ticket_id
        ]);

        http_response_code(404);
        echo json_encode([
            'success' => false,
            'message' => __('Ticket não encontrado', 'digitalsignature')
        ]);
        exit;
    }

    GlpiPlugin\digitalsignature\Logger::debug('Ticket loaded successfully', [
        'ticket_id' => $ticket_id,
        'ticket_status' => $ticket->fields['status'] ?? 'unknown',
        'ticket_entity' => $ticket->fields['entities_id'] ?? 'unknown'
    ]);

    // Check user rights
    if (!$ticket->canUpdateItem()) {
        GlpiPlugin\digitalsignature\Logger::error('User lacks permission to update ticket', [
            'ticket_id' => $ticket_id,
            'user_id' => Session::getLoginUserID(),
            'user_rights' => Session::haveRight('ticket', UPDATE)
        ]);

        http_response_code(403);
        echo json_encode([
            'success' => false,
            'message' => __('Você não tem permissão para modificar este ticket', 'digitalsignature')
        ]);
        exit;
    }

    // Decode signature data
    $image_data = str_replace('data:image/png;base64,', '', $signature_data);
    $image_data = str_replace(' ', '+', $image_data);
    $decoded_image = base64_decode($image_data);

    if ($decoded_image === false) {
        GlpiPlugin\digitalsignature\Logger::error('Failed to decode signature image data');

        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => __('Falha ao decodificar dados da assinatura', 'digitalsignature')
        ]);
        exit;
    }

    GlpiPlugin\digitalsignature\Logger::debug('Signature image decoded successfully', [
        'decoded_size' => strlen($decoded_image),
        'original_size' => strlen($image_data)
    ]);

    // Create temporary file
    $temp_filename = 'signature_ticket_' . $ticket_id . '_' . date('Y-m-d_H-i-s') . '.png';
    $temp_path = GLPI_TMP_DIR . '/' . $temp_filename;

    if (!file_put_contents($temp_path, $decoded_image)) {
        GlpiPlugin\digitalsignature\Logger::error('Failed to create temporary file', [
            'temp_path' => $temp_path,
            'temp_dir_writable' => is_writable(GLPI_TMP_DIR)
        ]);

        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => __('Falha ao criar arquivo temporário', 'digitalsignature')
        ]);
        exit;
    }

    GlpiPlugin\digitalsignature\Logger::debug('Temporary file created', [
        'temp_path' => $temp_path,
        'file_size' => filesize($temp_path)
    ]);

    // Create document and attach to ticket
    $document = new Document();
    $document_input = [
        'name' => 'Assinatura Digital - Ticket #' . $ticket_id,
        'filename' => $temp_filename,
        'filepath' => $temp_path,
        'mime' => 'image/png',
        'tag' => 'digitalsignature_' . $ticket_id,
        'users_id' => Session::getLoginUserID()
    ];

    GlpiPlugin\digitalsignature\Logger::debug('Creating document', $document_input);

    $document_id = $document->add($document_input);

    if (!$document_id) {
        GlpiPlugin\digitalsignature\Logger::error('Failed to create document', [
            'document_input' => $document_input,
            'last_error' => $document->getLastError()
        ]);

        // Clean up temp file
        @unlink($temp_path);

        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => __('Falha ao criar documento', 'digitalsignature')
        ]);
        exit;
    }

    GlpiPlugin\digitalsignature\Logger::debug('Document created successfully', [
        'document_id' => $document_id
    ]);

    // Link document to ticket
    $document_item = new Document_Item();
    $link_input = [
        'documents_id' => $document_id,
        'items_id' => $ticket_id,
        'itemtype' => 'Ticket'
    ];

    $link_id = $document_item->add($link_input);

    if (!$link_id) {
        GlpiPlugin\digitalsignature\Logger::error('Failed to link document to ticket', [
            'link_input' => $link_input,
            'document_id' => $document_id,
            'ticket_id' => $ticket_id
        ]);

        // Clean up - delete document if linking failed
        $document->delete(['id' => $document_id], true);
        @unlink($temp_path);

        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => __('Falha ao anexar assinatura ao ticket', 'digitalsignature')
        ]);
        exit;
    }

    // Clean up temp file
    @unlink($temp_path);

    GlpiPlugin\digitalsignature\Logger::info('Signature saved successfully', [
        'ticket_id' => $ticket_id,
        'document_id' => $document_id,
        'link_id' => $link_id,
        'user_id' => Session::getLoginUserID(),
        'filename' => $temp_filename
    ]);

    // Success response
    echo json_encode([
        'success' => true,
        'message' => __('Assinatura salva com sucesso', 'digitalsignature'),
        'document_id' => $document_id,
        'ticket_id' => $ticket_id
    ]);

} catch (Exception $e) {
    GlpiPlugin\digitalsignature\Logger::error('Exception occurred during signature save', [
        'exception_message' => $e->getMessage(),
        'exception_file' => $e->getFile(),
        'exception_line' => $e->getLine(),
        'stack_trace' => $e->getTraceAsString()
    ]);

    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => __('Erro interno do servidor', 'digitalsignature') . ': ' . $e->getMessage()
    ]);
}