<?php
/**
 * Digital Signature class for GLPI plugin
 * 
 * @package   DigitalSignature
 * @author    Joao Lucas
 * @copyright Copyright (c) 2025 Newtel Soluções
 * @license   GPLv2+
 */

namespace GlpiPlugin\Digitalsignature;

use CommonDBTM;
use Html;

/**
 * DigitalSignature class
 */
class DigitalSignature extends CommonDBTM
{
    // Right management
    static $rightname = 'computer';

    /**
     * Get name of this type by language of the user
     *
     * @param integer $nb number of elements
     * @return string name of this type
     */
    static function getTypeName($nb = 0) {
        return _n('Assinatura Digital', 'Assinaturas Digitais', $nb, 'digitalsignature');
    }

    /**
     * Get search function for the class
     *
     * @return array of search option
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
            'field'    => 'name',
            'name'     => __('Nome'),
            'datatype' => 'itemlink',
            'massiveaction' => false
        ];

        return $tab;
    }

    /**
     * Show signature form
     *
     * @param integer $ticket_id Ticket ID
     * @return void
     */
    static function showSignatureForm($ticket_id) {
        global $CFG_GLPI;
        
        echo "<div class='center'>";
        echo "<h3>" . __('Assinatura do Cliente', 'digitalsignature') . "</h3>";
        
        // Canvas for signature
        echo "<canvas id='signature-canvas' width='400' height='200' style='border: 1px solid #000; cursor: crosshair;'></canvas><br>";
        echo "<button type='button' id='clear-signature' class='btn btn-secondary'>" . __('Limpar', 'digitalsignature') . "</button>";
        echo "<input type='hidden' id='signature-data' name='signature_data' value='' />";
        echo "<input type='hidden' name='ticket_id' value='" . $ticket_id . "' />";
        
        // JavaScript
        $js_file = $CFG_GLPI['root_doc'] . '/plugins/digitalsignature/js/signature.js';
        echo Html::script($js_file);
        
        echo "</div>";
    }
}