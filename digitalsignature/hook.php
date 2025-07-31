<?php

/**
 * Digital Signature Plugin Hooks
 * Integrates signature capture directly into GLPI ticket solution forms
 */

if (!defined('GLPI_ROOT')) {
    die("Sorry. You can't access this file directly");
}

/**
 * Add signature functionality to ticket forms - specifically solution forms
 * 
 * @param array $params Hook parameters
 * @return boolean
 */
function plugin_digitalsignature_show_pad($params) {
    global $CFG_GLPI;
    
    GlpiPlugin\Digitalsignature\Logger::debug('Hook plugin_digitalsignature_show_pad called', [
        'params' => array_keys($params),
        'request_uri' => $_SERVER['REQUEST_URI'] ?? '',
        'get_params' => $_GET ?? []
    ]);
    
    // Check if we're on a ticket form page
    if (!isset($params['item']) || !($params['item'] instanceof Ticket)) {
        GlpiPlugin\Digitalsignature\Logger::debug('Not a ticket item, skipping signature pad');
        return true;
    }
    
    $ticket = $params['item'];
    $ticket_id = $ticket->getID();
    
    // Only show for existing tickets
    if (!$ticket_id) {
        GlpiPlugin\Digitalsignature\Logger::debug('No ticket ID found, skipping signature pad');
        return true;
    }
    
    // Check if user can update tickets
    if (!Session::haveRight('ticket', UPDATE)) {
        GlpiPlugin\Digitalsignature\Logger::debug('User does not have UPDATE rights on tickets');
        return true;
    }
    
    // Check if we're in solution context
    $is_solution_form = (
        isset($_GET['id']) && $_GET['id'] == $ticket_id &&
        (strpos($_SERVER['REQUEST_URI'], 'ticket.form.php') !== false ||
         strpos($_SERVER['REQUEST_URI'], 'itilsolution.form.php') !== false)
    );
    
    if (!$is_solution_form) {
        GlpiPlugin\Digitalsignature\Logger::debug('Not in solution form context');
        return true;
    }
    
    // Log successful context
    GlpiPlugin\Digitalsignature\Logger::info('Displaying signature pad for ticket', [
        'ticket_id' => $ticket_id,
        'user_id' => Session::getLoginUserID()
    ]);
    
    // Plugin web directory
    $plugin_web_dir = $CFG_GLPI['root_doc'] . '/plugins/digitalsignature';
    
    // Get CSRF token
    $csrf_token = Session::getNewCSRFToken();
    
    // Localized strings
    $signature_label = __('Assinatura Digital do Cliente', 'digitalsignature');
    $signature_help = __('Para finalizar esta solução, o cliente deve fornecer sua assinatura digital.', 'digitalsignature');
    $clear_label = __('Limpar', 'digitalsignature');
    $sign_placeholder = __('Clique e desenhe sua assinatura aqui...', 'digitalsignature');
    
    // Output signature pad integrated into GLPI form
    echo <<<HTML
<tr class="tab_bg_1">
    <th width="15%">{$signature_label}</th>
    <td>
        <div class="signature-help" style="margin-bottom: 10px; color: #666; font-size: 13px;">
            <i class="fas fa-info-circle"></i> {$signature_help}
        </div>
        
        <div id="signature-container" style="margin-bottom: 15px;">
            <div id="signature-pad-wrapper" style="border: 2px solid #007cba; border-radius: 8px; background: #fff; position: relative; width: 100%; max-width: 600px; height: 200px;">
                <canvas id="signature-canvas" 
                        width="600" 
                        height="200" 
                        style="display: block; cursor: crosshair; width: 100%; height: 100%; touch-action: none;"
                        data-ticket-id="{$ticket_id}">
                </canvas>
                <div id="signature-placeholder" 
                     style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); color: #ccc; pointer-events: none; font-size: 16px; text-align: center;">
                    {$sign_placeholder}
                </div>
            </div>
            
            <div style="margin-top: 10px;">
                <button type="button" id="clear-signature" class="btn btn-outline-secondary">
                    <i class="fas fa-eraser"></i> {$clear_label}
                </button>
                <span id="signature-status" style="margin-left: 15px; color: #28a745; display: none;">
                    <i class="fas fa-check-circle"></i> Assinatura capturada
                </span>
            </div>
        </div>
        
        <input type="hidden" id="signature-data" name="signature_data" value="">
        <input type="hidden" name="_signature_csrf_token" value="{$csrf_token}">
    </td>
</tr>

<script type="text/javascript">
document.addEventListener('DOMContentLoaded', function() {
    console.log('Digital Signature: Initializing signature pad...');
    
    // Load signature pad library if not already loaded
    if (typeof SignaturePad === 'undefined') {
        const script = document.createElement('script');
        script.src = '{$plugin_web_dir}/js/signature_pad.min.js';
        script.onload = function() {
            console.log('SignaturePad library loaded');
            initializeSignaturePad();
        };
        script.onerror = function() {
            console.error('Failed to load SignaturePad library, using fallback');
            initializeFallbackSignature();
        };
        document.head.appendChild(script);
    } else {
        initializeSignaturePad();
    }
    
    function initializeSignaturePad() {
        const canvas = document.getElementById('signature-canvas');
        const clearBtn = document.getElementById('clear-signature');
        const placeholder = document.getElementById('signature-placeholder');
        const signatureDataInput = document.getElementById('signature-data');
        const statusIndicator = document.getElementById('signature-status');
        
        if (!canvas) {
            console.error('Digital Signature: Canvas not found');
            return;
        }
        
        // Initialize SignaturePad with proper settings
        const signaturePad = new SignaturePad(canvas, {
            backgroundColor: 'rgb(255, 255, 255)',
            penColor: 'rgb(0, 0, 0)',
            minWidth: 2,
            maxWidth: 4,
            velocityFilterWeight: 0.7,
            minDistance: 5,
            throttle: 16
        });
        
        // Auto-resize canvas
        function resizeCanvas() {
            const ratio = Math.max(window.devicePixelRatio || 1, 1);
            const wrapper = canvas.parentElement;
            const rect = wrapper.getBoundingClientRect();
            
            canvas.width = rect.width * ratio;
            canvas.height = rect.height * ratio;
            canvas.getContext('2d').scale(ratio, ratio);
            canvas.style.width = rect.width + 'px';
            canvas.style.height = rect.height + 'px';
            
            signaturePad.clear();
        }
        
        // Initialize size and handle window resize
        setTimeout(resizeCanvas, 100);
        window.addEventListener('resize', resizeCanvas);
        
        // Handle signature events
        signaturePad.addEventListener('beginStroke', function() {
            console.log('Digital Signature: Begin stroke');
            if (placeholder) placeholder.style.display = 'none';
        });
        
        signaturePad.addEventListener('endStroke', function() {
            console.log('Digital Signature: End stroke');
            if (!signaturePad.isEmpty()) {
                const signatureData = signaturePad.toDataURL('image/png', 0.8);
                signatureDataInput.value = signatureData;
                if (statusIndicator) statusIndicator.style.display = 'inline';
                console.log('Digital Signature: Signature captured and stored');
            }
        });
        
        // Clear button functionality
        if (clearBtn) {
            clearBtn.addEventListener('click', function(e) {
                e.preventDefault();
                console.log('Digital Signature: Clearing signature');
                signaturePad.clear();
                signatureDataInput.value = '';
                if (placeholder) placeholder.style.display = 'block';
                if (statusIndicator) statusIndicator.style.display = 'none';
            });
        }
        
        // Store reference for form validation
        window.digitalSignaturePad = signaturePad;
        
        console.log('Digital Signature: Signature pad initialized successfully');
    }
    
    function initializeFallbackSignature() {
        console.log('Digital Signature: Using fallback drawing implementation');
        
        const canvas = document.getElementById('signature-canvas');
        const clearBtn = document.getElementById('clear-signature');
        const placeholder = document.getElementById('signature-placeholder');
        const signatureDataInput = document.getElementById('signature-data');
        const statusIndicator = document.getElementById('signature-status');
        
        if (!canvas) return;
        
        const ctx = canvas.getContext('2d');
        let isDrawing = false;
        let lastX = 0;
        let lastY = 0;
        
        // Set canvas size
        function resizeCanvas() {
            const wrapper = canvas.parentElement;
            const rect = wrapper.getBoundingClientRect();
            canvas.width = rect.width;
            canvas.height = rect.height;
            canvas.style.width = rect.width + 'px';
            canvas.style.height = rect.height + 'px';
        }
        
        setTimeout(resizeCanvas, 100);
        window.addEventListener('resize', resizeCanvas);
        
        function startDrawing(e) {
            isDrawing = true;
            const rect = canvas.getBoundingClientRect();
            lastX = (e.clientX || e.touches[0].clientX) - rect.left;
            lastY = (e.clientY || e.touches[0].clientY) - rect.top;
            
            if (placeholder) placeholder.style.display = 'none';
            console.log('Digital Signature: Fallback - Begin drawing');
        }
        
        function draw(e) {
            if (!isDrawing) return;
            e.preventDefault();
            
            const rect = canvas.getBoundingClientRect();
            const currentX = (e.clientX || e.touches[0].clientX) - rect.left;
            const currentY = (e.clientY || e.touches[0].clientY) - rect.top;
            
            ctx.beginPath();
            ctx.moveTo(lastX, lastY);
            ctx.lineTo(currentX, currentY);
            ctx.strokeStyle = '#000';
            ctx.lineWidth = 2;
            ctx.lineCap = 'round';
            ctx.stroke();
            
            lastX = currentX;
            lastY = currentY;
        }
        
        function stopDrawing() {
            if (isDrawing) {
                isDrawing = false;
                const signatureData = canvas.toDataURL('image/png', 0.8);
                signatureDataInput.value = signatureData;
                if (statusIndicator) statusIndicator.style.display = 'inline';
                console.log('Digital Signature: Fallback - Signature captured');
            }
        }
        
        // Mouse events
        canvas.addEventListener('mousedown', startDrawing);
        canvas.addEventListener('mousemove', draw);
        canvas.addEventListener('mouseup', stopDrawing);
        canvas.addEventListener('mouseout', stopDrawing);
        
        // Touch events for mobile
        canvas.addEventListener('touchstart', startDrawing);
        canvas.addEventListener('touchmove', draw);
        canvas.addEventListener('touchend', stopDrawing);
        
        // Clear functionality
        if (clearBtn) {
            clearBtn.addEventListener('click', function(e) {
                e.preventDefault();
                ctx.clearRect(0, 0, canvas.width, canvas.height);
                signatureDataInput.value = '';
                if (placeholder) placeholder.style.display = 'block';
                if (statusIndicator) statusIndicator.style.display = 'none';
                console.log('Digital Signature: Fallback - Signature cleared');
            });
        }
        
        console.log('Digital Signature: Fallback signature pad initialized');
    }
});
</script>

HTML;
    
    GlpiPlugin\Digitalsignature\Logger::info('Signature pad HTML rendered successfully', [
        'ticket_id' => $ticket_id
    ]);
    
    return true;
}

/**
 * Pre-form hook to add additional functionality
 */
function plugin_digitalsignature_pre_form($params) {
    GlpiPlugin\Digitalsignature\Logger::debug('Pre-form hook called', [
        'item_type' => get_class($params['item']) ?? 'unknown'
    ]);
    
    return true;
}