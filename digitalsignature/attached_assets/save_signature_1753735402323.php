<?php
// ajax/save_signature.php

define('GLPI_ROOT', __DIR__ . '/../../../'); // Caminho corrigido
include_once(GLPI_ROOT . '/inc/includes.php');

header('Content-Type: application/json');

// Verificação de segurança CSRF
if (!Session::checkCSRFToken()) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Ação não permitida. Token de segurança inválido.']);
    exit;
}

if (!Session::getLoginUserID()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Acesso não autorizado.']);
    exit;
}

$ticket_id = filter_input(INPUT_POST, 'ticket_id', FILTER_VALIDATE_INT);
$signature_data = $_POST['signature_data'] ?? '';

if (!$ticket_id || !$signature_data) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Dados inválidos']);
    exit;
}

// Decodifica a imagem
$data = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $signature_data));
if ($data === false) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Formato da imagem de assinatura inválido.']);
    exit;
}

$tmp_file = GLPI_TMP_DIR . '/sig_' . uniqid() . '.png';
if (file_put_contents($tmp_file, $data) === false) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Falha ao salvar arquivo temporário.']);
    exit;
}

$doc = new Document();
$doc_input = [
    'name'        => "Assinatura do Chamado #$ticket_id",
    'entities_id' => $_SESSION['glpiactive_entity'],
    'filename'    => "assinatura_ticket_{$ticket_id}.png",
    '_filename'   => "assinatura_ticket_{$ticket_id}.png", // Nome do arquivo para o usuário
    '_tmp_name'   => $tmp_file, // Caminho completo para o arquivo temporário
];

if (!$doc->add($doc_input)) {
    unlink($tmp_file);
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Falha ao criar o documento no GLPI.']);
    exit;
}

// Associa o documento ao chamado usando a classe correta: Document_Item
$doc_item = new Document_Item();
$di_options = ['_add_log' => false]; // Suprime o log genérico "Adicionar o item"
if (!$doc_item->add(['documents_id' => $doc->getID(), 'itemtype' => 'Ticket', 'items_id' => $ticket_id], $di_options)) {
    unlink($tmp_file); // Limpa o arquivo se a associação falhar
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Falha ao associar o documento ao chamado.']);
    exit;
}

// Adiciona um acompanhamento privado para registrar a ação de forma clara no histórico
$followup = new TicketFollowup();
$fup_input = [
    'tickets_id' => $ticket_id,
    'content'    => __('Assinatura do cliente adicionada ao chamado.', 'digitalsignature'),
    'is_private' => 1, // 1 para privado, 0 para público
    'users_id'   => Session::getLoginUserID(),
];
if (!$followup->add($fup_input)) {
    // Não é um erro crítico, a assinatura foi salva. Apenas registra no log do PHP.
    Toolbox::logWarning("Falha ao adicionar o acompanhamento da assinatura para o chamado $ticket_id");
}

echo json_encode(['success' => true, 'message' => 'Assinatura salva com sucesso']);
