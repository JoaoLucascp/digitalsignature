<?php
/**
 * Plugindigitalsignaturesignature class
 * For backward compatibility with GLPI plugin structure
 */

if (!defined('GLPI_ROOT')) {
    die("Sorry. You can't access this file directly");
}

class Plugindigitalsignaturesignature extends CommonDBTM {
    
    static $rightname = 'config';
    
    /**
     * Get name of this type
     *
     * @param integer $nb number of items
     * @return string
     */
    static function getTypeName($nb = 0) {
        return _n('Assinatura Digital', 'Assinaturas Digitais', $nb, 'digitalsignature');
    }
    
    /**
     * Get search options
     *
     * @return array
     */
    function rawSearchOptions() {
        $tab = [];
        
        $tab[] = [
            'id'   => 'common',
            'name' => __('Características')
        ];
        
        $tab[] = [
            'id'       => '1',
            'table'    => $this->getTable(),
            'field'    => 'id',
            'name'     => __('ID'),
            'datatype' => 'number'
        ];
        
        return $tab;
    }
    
    /**
     * Display signature pad
     *
     * @param integer $ticket_id
     * @return void
     */
    static function showsignaturePad($ticket_id) {
        global $CFG_GLPI;
        
        GlpiPlugin\digitalsignature\Logger::info('Displaying signature pad for ticket', [
            'ticket_id' => $ticket_id,
            'user_id' => Session::getLoginUserID()
        ]);

        // Use a form to wrap the signature pad and include the CSRF token
        echo "<form id='signature-form-hook' class='digitalsignature-container' style='margin-top: 20px; border-top: 1px solid #ddd; padding-top: 15px;'>";
        echo "<h4>" . __('Assinatura do Cliente', 'digitalsignature') . "</h4>";
        echo "<p>" . __('Por favor, desenhe sua assinatura no quadro abaixo:', 'digitalsignature') . "</p>";
        echo "<canvas id='signature-canvas' width='500' height='200' style='border: 1px solid #ccc; border-radius: 4px;'></canvas><br>";
        
        // Add buttons and hidden fields required for submission
        echo "<div style='margin-top: 10px;'>";
        echo "<button type='button' id='clear-signature' class='btn btn-secondary'>" . __('Limpar', 'digitalsignature') . "</button>";
        echo "<button type='submit' id='save-signature' class='btn btn-primary' disabled style='margin-left: 10px;'>" . __('Salvar Assinatura', 'digitalsignature') . "</button>";
        echo "</div>";
        
        echo Html::hidden('signature_data', ['id' => 'signature-data', 'value' => '']);
        echo Html::hidden('_glpi_csrf_token', ['value' => Session::getNewCSRFToken()]);
        echo Html::hidden('ticket_id', ['value' => $ticket_id]);
        echo "</form>";
    }
}