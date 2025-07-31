<?php
function plugin_digitalsignature_show_pad(array $params) {
    global $CFG_GLPI;

    // DEBUG: Descomente a linha abaixo para ver o conteúdo dos parâmetros do hook.
    // Isso irá parar a execução da página e mostrar os dados.
    var_dump($params); die('Hook executado. Se você vê esta mensagem, o hook está funcionando!');

    // O hook 'form_answer' passa o objeto Ticket diretamente.
    // Isso é muito mais simples e confiável.
    if (isset($params['item']) && $params['item'] instanceof Ticket) {
        $ticket = $params['item'];
        $ticket_id = $ticket->getID();

        // Garante que o botão só apareça para chamados já existentes (que possuem um ID)
        if (empty($ticket_id)) {
            return true;
        }

        $pad_url = $CFG_GLPI['root_doc'] . "/plugins/digitalsignature/front/pad.php?ticket_id=" . $ticket_id;

        echo '<tr>';
        echo '<th>' . __('Assinatura do Cliente', 'digitalsignature') . '</th>';
        echo '<td>';
        echo '<button type="button" class="vsubmit" onclick="window.open(\'' . $pad_url . '\', \'Assinatura\', \'width=450,height=350\'); return false;">';
        echo '<i class="fas fa-signature"></i> ' . __('Coletar Assinatura', 'digitalsignature');
        echo '</button>';
        echo '</td></tr>';
    }

    return true;
}
