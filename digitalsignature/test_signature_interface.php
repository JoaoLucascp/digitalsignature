<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teste - Interface de Assinatura Digital</title>
    <link rel="stylesheet" href="css/digitalsignature.css">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 20px;
            background: #f5f5f5;
        }
        .test-container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .test-info {
            background: #e7f3ff;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            border-left: 4px solid #007cba;
        }
        table.tab_cadre_fixe {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        table.tab_cadre_fixe th {
            background: #f8f9fa;
            padding: 12px;
            text-align: left;
            border: 1px solid #dee2e6;
            width: 15%;
            vertical-align: top;
        }
        table.tab_cadre_fixe td {
            padding: 15px;
            border: 1px solid #dee2e6;
            vertical-align: top;
        }
        .test-buttons {
            margin: 20px 0;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 5px;
        }
        .test-buttons button {
            margin: 5px;
            padding: 8px 16px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        .btn-primary { background: #007cba; color: white; }
        .btn-success { background: #28a745; color: white; }
        .btn-warning { background: #ffc107; color: black; }
        .btn-danger { background: #dc3545; color: white; }
        .log-output {
            background: #2d3748;
            color: #e2e8f0;
            padding: 15px;
            border-radius: 5px;
            font-family: 'Consolas', 'Monaco', monospace;
            font-size: 12px;
            max-height: 300px;
            overflow-y: auto;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="test-container">
        <h1>🖊️ Teste - Plugin de Assinatura Digital</h1>
        
        <div class="test-info">
            <h3>ℹ️ Informações do Teste</h3>
            <p>Esta página simula a interface de assinatura digital como aparece no GLPI.</p>
            <p><strong>Instruções:</strong></p>
            <ul>
                <li>Desenhe sua assinatura no quadro abaixo</li>
                <li>Use o botão "Limpar" para recomeçar</li>
                <li>Observe o status da assinatura</li>
                <li>Verifique os logs no console do navegador (F12)</li>
            </ul>
        </div>

        <table class="tab_cadre_fixe">
            <tr class="tab_bg_1">
                <th width="15%">Assinatura Digital do Cliente</th>
                <td>
                    <div class="signature-help" style="margin-bottom: 10px; color: #666; font-size: 13px;">
                        <i class="fas fa-info-circle"></i> Para finalizar esta solução, o cliente deve fornecer sua assinatura digital.
                    </div>
                    
                    <div id="signature-container" style="margin-bottom: 15px;">
                        <div id="signature-pad-wrapper" style="border: 2px solid #007cba; border-radius: 8px; background: #fff; position: relative; width: 100%; max-width: 600px; height: 200px;">
                            <canvas id="signature-canvas" 
                                    width="600" 
                                    height="200" 
                                    style="display: block; cursor: crosshair; width: 100%; height: 100%; touch-action: none;"
                                    data-ticket-id="123">
                            </canvas>
                            <div id="signature-placeholder" 
                                 style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); color: #ccc; pointer-events: none; font-size: 16px; text-align: center;">
                                Clique e desenhe sua assinatura aqui...
                            </div>
                        </div>
                        
                        <div style="margin-top: 10px;">
                            <button type="button" id="clear-signature" class="btn btn-outline-secondary">
                                <i class="fas fa-eraser"></i> Limpar
                            </button>
                            <span id="signature-status" style="margin-left: 15px; color: #28a745; display: none;">
                                <i class="fas fa-check-circle"></i> Assinatura capturada
                            </span>
                        </div>
                    </div>
                    
                    <input type="hidden" id="signature-data" name="signature_data" value="">
                    <input type="hidden" name="_signature_csrf_token" value="test_token">
                </td>
            </tr>
        </table>

        <div class="test-buttons">
            <h3>🧪 Funções de Teste</h3>
            <button class="btn-primary" onclick="testSignaturePad()">Testar SignaturePad</button>
            <button class="btn-warning" onclick="testFallback()">Testar Fallback</button>
            <button class="btn-success" onclick="testSignatureData()">Ver Dados da Assinatura</button>
            <button class="btn-danger" onclick="clearLogs()">Limpar Logs</button>
            <button class="btn-primary" onclick="simulateSave()">Simular Salvamento</button>
        </div>

        <div id="log-container">
            <h3>📋 Logs de Teste</h3>
            <div id="log-output" class="log-output">
                Plugin de Assinatura Digital - Logs de Teste<br>
                =====================================<br>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="js/signature_pad.min.js"></script>
    <script>
        // Test logging system
        function addLog(message, type = 'info') {
            const logOutput = document.getElementById('log-output');
            const timestamp = new Date().toLocaleTimeString();
            const colorMap = {
                'info': '#4CAF50',
                'warning': '#FF9800', 
                'error': '#F44336',
                'debug': '#2196F3'
            };
            
            logOutput.innerHTML += `<span style="color: ${colorMap[type] || '#ffffff'}">[${timestamp}] [${type.toUpperCase()}] ${message}</span><br>`;
            logOutput.scrollTop = logOutput.scrollHeight;
        }

        // Initialize signature pad with logging
        document.addEventListener('DOMContentLoaded', function() {
            addLog('Digital Signature: Página carregada, inicializando...', 'info');
            
            // Check if SignaturePad library is loaded
            if (typeof SignaturePad === 'undefined') {
                addLog('SignaturePad library não encontrada, usando fallback', 'warning');
                initializeFallbackSignature();
            } else {
                addLog('SignaturePad library carregada, inicializando...', 'info');
                initializeSignaturePad();
            }
        });

        function initializeSignaturePad() {
            const canvas = document.getElementById('signature-canvas');
            const clearBtn = document.getElementById('clear-signature');
            const placeholder = document.getElementById('signature-placeholder');
            const signatureDataInput = document.getElementById('signature-data');
            const statusIndicator = document.getElementById('signature-status');
            
            if (!canvas) {
                addLog('Canvas não encontrado!', 'error');
                return;
            }
            
            addLog('Inicializando SignaturePad...', 'debug');
            
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
                addLog(`Canvas redimensionado: ${rect.width}x${rect.height}`, 'debug');
            }
            
            // Initialize size and handle window resize
            setTimeout(resizeCanvas, 100);
            window.addEventListener('resize', resizeCanvas);
            
            // Handle signature events with proper callbacks
            signaturePad.onBegin = function() {
                addLog('Início do desenho da assinatura', 'info');
                if (placeholder) placeholder.style.display = 'none';
            };
            
            signaturePad.onEnd = function() {
                addLog('Fim do desenho da assinatura', 'info');
                if (!signaturePad.isEmpty()) {
                    const signatureData = signaturePad.toDataURL('image/png', 0.8);
                    signatureDataInput.value = signatureData;
                    if (statusIndicator) statusIndicator.style.display = 'inline';
                    addLog(`Assinatura capturada (${signatureData.length} caracteres)`, 'info');
                }
            };
            
            // Clear button functionality
            if (clearBtn) {
                clearBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    addLog('Limpando assinatura', 'info');
                    signaturePad.clear();
                    signatureDataInput.value = '';
                    if (placeholder) placeholder.style.display = 'block';
                    if (statusIndicator) statusIndicator.style.display = 'none';
                });
            }
            
            // Store reference for testing
            window.digitalSignaturePad = signaturePad;
            addLog('SignaturePad inicializado com sucesso!', 'info');
        }

        function initializeFallbackSignature() {
            addLog('Inicializando sistema fallback de assinatura', 'warning');
            
            const canvas = document.getElementById('signature-canvas');
            const clearBtn = document.getElementById('clear-signature');
            const placeholder = document.getElementById('signature-placeholder');
            const signatureDataInput = document.getElementById('signature-data');
            const statusIndicator = document.getElementById('signature-status');
            
            if (!canvas) {
                addLog('Canvas não encontrado para fallback!', 'error');
                return;
            }
            
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
                addLog(`Canvas fallback redimensionado: ${rect.width}x${rect.height}`, 'debug');
            }
            
            setTimeout(resizeCanvas, 100);
            window.addEventListener('resize', resizeCanvas);
            
            function startDrawing(e) {
                isDrawing = true;
                const rect = canvas.getBoundingClientRect();
                lastX = (e.clientX || e.touches[0].clientX) - rect.left;
                lastY = (e.clientY || e.touches[0].clientY) - rect.top;
                
                if (placeholder) placeholder.style.display = 'none';
                addLog('Fallback - Início do desenho', 'debug');
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
                    addLog(`Fallback - Assinatura capturada (${signatureData.length} caracteres)`, 'info');
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
                    addLog('Fallback - Assinatura limpada', 'info');
                });
            }
            
            addLog('Sistema fallback inicializado com sucesso!', 'info');
        }

        // Test functions
        function testSignaturePad() {
            if (typeof SignaturePad !== 'undefined') {
                addLog('✅ SignaturePad library está disponível', 'info');
                if (window.digitalSignaturePad) {
                    addLog('✅ Instância do SignaturePad encontrada', 'info');
                } else {
                    addLog('❌ Instância do SignaturePad não encontrada', 'error');
                }
            } else {
                addLog('❌ SignaturePad library não está carregada', 'error');
            }
        }

        function testFallback() {
            addLog('Testando sistema fallback...', 'warning');
            // Force fallback initialization
            initializeFallbackSignature();
        }

        function testSignatureData() {
            const signatureData = document.getElementById('signature-data').value;
            if (signatureData) {
                addLog(`✅ Dados da assinatura encontrados (${signatureData.length} caracteres)`, 'info');
                addLog(`Formato: ${signatureData.substring(0, 50)}...`, 'debug');
            } else {
                addLog('❌ Nenhum dado de assinatura encontrado', 'warning');
            }
        }

        function clearLogs() {
            document.getElementById('log-output').innerHTML = 'Plugin de Assinatura Digital - Logs de Teste<br>=====================================<br>';
            addLog('Logs limpos', 'info');
        }

        function simulateSave() {
            const signatureData = document.getElementById('signature-data').value;
            if (!signatureData) {
                addLog('❌ Nenhuma assinatura para salvar', 'error');
                return;
            }
            
            addLog('Simulando salvamento da assinatura...', 'info');
            addLog('POST /plugins/digitalsignature/ajax/save_signature.php', 'debug');
            addLog('Dados: ticket_id=123, signature_data=[DATA], _signature_csrf_token=test_token', 'debug');
            
            setTimeout(() => {
                addLog('✅ Assinatura salva com sucesso (simulação)', 'info');
            }, 1000);
        }
    </script>
</body>
</html>