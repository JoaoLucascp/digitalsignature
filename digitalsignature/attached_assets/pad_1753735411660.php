<?php
// front/pad.php

define('GLPI_ROOT', __DIR__ . '/../../../'); // Caminho corrigido
include_once(GLPI_ROOT . '/inc/includes.php');

Session::checkLoginUser();

$ticket_id = (int) ($_GET['ticket_id'] ?? 0);
if (!$ticket_id) {
    // Para o usuário final, é melhor mostrar uma mensagem amigável.
    Html::displayErrorAndDie(__('ID do chamado inválido ou não fornecido.'), true);
}

// Para o token CSRF e outras funções JS do GLPI
Session::setCSRFToken();
Html::requireJs('common');

?>

<!DOCTYPE html>
<html lang="<?= str_replace('_', '-', $_SESSION['glpilanguage']); ?>">
<head>
<meta charset="UTF-8" />
<title><?= __('Assinatura Digital', 'digitalsignature') ?></title>
<link rel="stylesheet" type="text/css" href="<?= $CFG_GLPI['root_doc'] ?>/css/styles.css">
<script src="<?= $CFG_GLPI['root_doc'] ?>/plugins/digitalsignature/js/signature_pad.min.js"></script>
<style>
  body { padding: 15px; text-align: center; }
  #signature-pad { border: 1px solid #ccc; width: 400px; height: 200px; cursor: crosshair; }
  .buttons { margin-top: 10px; }
</style>
</head>
<body>

<h3><?= sprintf(__('Assinatura para o Chamado #%s'), $ticket_id) ?></h3>

<canvas id="signature-pad" width="400" height="200"></canvas>
<div class="buttons">
    <button id="clear-btn" class="submit"><?= __('Limpar') ?></button>
    <button id="save-btn" class="submit"><?= __('Salvar assinatura') ?></button>
</div>

<script>
const signaturePad = new SignaturePad(document.getElementById('signature-pad'), {backgroundColor: 'rgb(255, 255, 255)'});
document.getElementById('clear-btn').addEventListener('click', () => signaturePad.clear());
document.getElementById('save-btn').addEventListener('click', () => {
    if (signaturePad.isEmpty()) {
        alert('<?= __('Por favor, faça uma assinatura antes de salvar.', 'digitalsignature') ?>');
        return;
    }
    const dataURL = signaturePad.toDataURL('image/png');

    fetch('../ajax/save_signature.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' },
        body: `ticket_id=<?= $ticket_id ?>&signature_data=${encodeURIComponent(dataURL)}&_glpi_csrf_token=${glpi.csrf_token}`
    }).then(resp => resp.json()).then(data => {
        if(data.success){
            alert('<?= __('Assinatura salva com sucesso!', 'digitalsignature') ?>');
            if (window.opener && !window.opener.closed) { window.opener.location.reload(); }
            window.close();
        } else {
            alert('<?= __('Erro:') ?> ' + (data.message || '<?= __('Ocorreu um erro desconhecido.') ?>'));
        }
    }).catch((error) => alert('<?= __('Erro de comunicação ao salvar assinatura. Verifique o console para detalhes.') ?>'));
});
</script>

</body>
</html>