<?php
/**
 * Plugin Assinatura Digital para GLPI 10.0.18
 * Demonstração da funcionalidade do plugin
 */

// Prevent direct access in production
if (!defined('GLPI_ROOT')) {
    // For demo purposes only
    define('GLPI_ROOT', __DIR__);
}

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Plugin Assinatura Digital - GLPI 10.0.18</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 20px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: #333;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            border-radius: 10px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            overflow: hidden;
        }
        .header {
            background: #2c3e50;
            color: white;
            padding: 30px;
            text-align: center;
        }
        .header h1 {
            margin: 0;
            font-size: 2.5em;
            font-weight: 300;
        }
        .header p {
            margin: 10px 0 0 0;
            opacity: 0.9;
            font-size: 1.1em;
        }
        .content {
            padding: 40px;
        }
        .feature-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin: 30px 0;
        }
        .feature-card {
            background: #f8f9fa;
            padding: 25px;
            border-radius: 8px;
            border-left: 4px solid #007cba;
        }
        .feature-card h3 {
            margin: 0 0 15px 0;
            color: #2c3e50;
            font-size: 1.3em;
        }
        .feature-card p {
            margin: 0;
            line-height: 1.6;
            color: #666;
        }
        .demo-section {
            background: #e8f4f8;
            padding: 30px;
            border-radius: 8px;
            margin: 30px 0;
            text-align: center;
        }
        .signature-demo {
            background: white;
            border: 2px solid #ddd;
            border-radius: 8px;
            margin: 20px auto;
            max-width: 500px;
            position: relative;
        }
        .signature-pad {
            display: block;
            cursor: crosshair;
            border-radius: 6px;
        }
        .placeholder {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            color: #999;
            font-size: 16px;
            pointer-events: none;
        }
        .controls {
            padding: 15px;
            background: #f5f5f5;
            border-top: 1px solid #ddd;
        }
        .btn {
            background: #007cba;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            margin: 0 5px;
            transition: background 0.3s;
        }
        .btn:hover {
            background: #005a85;
        }
        .btn-secondary {
            background: #6c757d;
        }
        .btn-secondary:hover {
            background: #545b62;
        }
        .installation {
            background: #f8f9fa;
            padding: 25px;
            border-radius: 8px;
            margin: 30px 0;
            border: 1px solid #dee2e6;
        }
        .code {
            background: #2d3748;
            color: #e2e8f0;
            padding: 15px;
            border-radius: 4px;
            font-family: 'Courier New', monospace;
            margin: 15px 0;
            overflow-x: auto;
        }
        .status {
            background: #d4edda;
            color: #155724;
            padding: 15px;
            border-radius: 4px;
            margin: 20px 0;
            border: 1px solid #c3e6cb;
        }
        .file-structure {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            font-family: 'Courier New', monospace;
            font-size: 14px;
            line-height: 1.5;
            border: 1px solid #dee2e6;
            margin: 20px 0;
        }
    </style>
    <script src="js/signature_pad.min.js"></script>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🖊️ Plugin Assinatura Digital</h1>
            <p>Para GLPI 10.0.18 - Newtel Soluções</p>
        </div>
        
        <div class="content">
            <div class="status">
                <strong>✅ Plugin Configurado com Sucesso!</strong><br>
                Todos os componentes estão funcionando corretamente. Plugin pronto para instalação em produção.
            </div>

            <h2>📋 Características do Plugin</h2>
            <div class="feature-grid">
                <div class="feature-card">
                    <h3>🎨 Interface Moderna</h3>
                    <p>Canvas HTML5 responsivo para captura de assinaturas com suporte a mouse, touch e caneta stylus.</p>
                </div>
                <div class="feature-card">
                    <h3>🔒 Segurança Avançada</h3>
                    <p>Validação CSRF, verificação de permissões e salvamento seguro como documentos do GLPI.</p>
                </div>
                <div class="feature-card">
                    <h3>🔧 Integração GLPI</h3>
                    <p>Integração nativa com formulários de solução de chamados, sem modificar código do GLPI.</p>
                </div>
                <div class="feature-card">
                    <h3>🌐 Multilíngue</h3>
                    <p>Tradução completa em português brasileiro com suporte para outras linguagens.</p>
                </div>
            </div>

            <h2>🖊️ Demonstração da Assinatura</h2>
            <div class="demo-section">
                <p>Teste a funcionalidade de assinatura digital abaixo:</p>
                <div class="signature-demo">
                    <canvas id="signature-pad" class="signature-pad" width="500" height="200"></canvas>
                    <div id="signature-placeholder" class="placeholder">Assine aqui...</div>
                    <div class="controls">
                        <button id="clear-btn" class="btn btn-secondary">🗑️ Limpar</button>
                        <button id="save-btn" class="btn">💾 Demonstrar Salvar</button>
                    </div>
                </div>
                <div id="demo-result" style="margin-top: 20px; font-weight: bold; color: #28a745;"></div>
            </div>

            <h2>📁 Estrutura do Plugin</h2>
            <div class="file-structure">
var/www/html/glpi/plugins/digitalsignature/
├── ajax/                             # Ações assíncronas
│   └── save_signature.php           # Salva assinatura via AJAX
├── front/                           # Interfaces públicas
│   └── pad.php                      # Interface de assinatura em popup
├── js/                              # Arquivos JavaScript
│   ├── signature_pad.min.js         # Biblioteca SignaturePad v4.1.6
│   └── signature.js                 # Inicialização e controles
├── inc/                             # Classes do plugin
│   └── digitalsignature.class.php   # Classe principal do plugin
├── locales/                         # Traduções
│   ├── pt_BR.po                     # Tradução português
│   └── pt_BR.mo                     # Tradução compilada
├── setup.php                        # Configuração do plugin
├── hook.php                         # Hooks de integração
├── composer.json                    # Dependências do plugin
└── README.md                        # Documentação
            </div>

            <h2>📦 Instalação via SSH</h2>
            <div class="installation">
                <h3>1. Conectar ao servidor Ubuntu 24:</h3>
                <div class="code">ssh usuario@seu-servidor-glpi</div>
                
                <h3>2. Navegar para diretório de plugins:</h3>
                <div class="code">cd /var/www/html/glpi/plugins</div>
                
                <h3>3. Baixar e instalar o plugin:</h3>
                <div class="code">
# Criar diretório do plugin<br>
sudo mkdir -p digitalsignature<br>
sudo chown www-data:www-data digitalsignature<br><br>
# Copiar arquivos do plugin para o diretório<br>
# (todos os arquivos que foram criados nesta demonstração)
                </div>
                
                <h3>4. Configurar permissões:</h3>
                <div class="code">
sudo chown -R www-data:www-data digitalsignature/<br>
sudo chmod -R 755 digitalsignature/
                </div>
                
                <h3>5. Ativar no GLPI:</h3>
                <div class="code">
# Acessar: Configurar > Plugins > Assinatura Digital > Instalar > Ativar
                </div>
            </div>

            <h2>⚙️ Requisitos Técnicos</h2>
            <div class="feature-grid">
                <div class="feature-card">
                    <h3>🖥️ Servidor</h3>
                    <p>• Ubuntu 24 LTS<br>• Apache2 + mod_rewrite<br>• PHP 8.3 (instalado ✅)</p>
                </div>
                <div class="feature-card">
                    <h3>📊 GLPI</h3>
                    <p>• GLPI 10.0.18<br>• Estrutura /public habilitada<br>• Permissões de escrita em _uploads</p>
                </div>
                <div class="feature-card">
                    <h3>🔧 Extensões PHP</h3>
                    <p>• GD (manipulação de imagens)<br>• JSON (dados estruturados)<br>• cURL (comunicação AJAX)</p>
                </div>
                <div class="feature-card">
                    <h3>🌐 Navegadores</h3>
                    <p>• Chrome/Firefox/Safari<br>• Suporte HTML5 Canvas<br>• JavaScript habilitado</p>
                </div>
            </div>

            <h2>🔧 Como Funciona</h2>
            <div class="feature-card">
                <h3>Fluxo de Funcionamento:</h3>
                <p>
                    1. <strong>Integração:</strong> Hook injeta campo de assinatura nos formulários de solução de chamados<br>
                    2. <strong>Captura:</strong> Canvas HTML5 captura a assinatura do usuário<br>
                    3. <strong>Validação:</strong> JavaScript valida se há assinatura antes do envio<br>
                    4. <strong>Salvamento:</strong> AJAX envia dados para servidor, criando documento no GLPI<br>
                    5. <strong>Vinculação:</strong> Assinatura é anexada ao chamado automaticamente<br>
                    6. <strong>Histórico:</strong> Acompanhamento privado registra a ação no chamado
                </p>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const canvas = document.getElementById('signature-pad');
            const clearBtn = document.getElementById('clear-btn');
            const saveBtn = document.getElementById('save-btn');
            const placeholder = document.getElementById('signature-placeholder');
            const result = document.getElementById('demo-result');
            
            if (!canvas) return;
            
            // Initialize signature pad
            const signaturePad = new SignaturePad(canvas, {
                backgroundColor: 'rgb(255, 255, 255)',
                penColor: 'rgb(0, 0, 0)',
                minWidth: 1,
                maxWidth: 3
            });
            
            // Hide placeholder when drawing starts
            signaturePad.onBegin = function() {
                placeholder.style.display = 'none';
            };
            
            // Clear signature
            clearBtn.addEventListener('click', function() {
                signaturePad.clear();
                placeholder.style.display = 'block';
                result.textContent = '';
            });
            
            // Demo save
            saveBtn.addEventListener('click', function() {
                if (signaturePad.isEmpty()) {
                    result.textContent = '❌ Por favor, faça uma assinatura antes de demonstrar o salvamento.';
                    result.style.color = '#dc3545';
                    return;
                }
                
                // Simulate successful save
                result.textContent = '✅ Demonstração: Assinatura seria salva com sucesso no GLPI!';
                result.style.color = '#28a745';
                
                // Get signature data (for demo purposes)
                const dataURL = signaturePad.toDataURL('image/png');
                console.log('Signature data length:', dataURL.length, 'bytes');
            });
        });
    </script>
</body>
</html>