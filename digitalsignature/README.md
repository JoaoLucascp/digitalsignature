# Plugin Assinatura Digital para GLPI 10.0.18

Este plugin adiciona funcionalidade de assinatura digital aos chamados do GLPI, permitindo que os clientes assinem soluções digitalmente usando um canvas HTML5.

## Características

- ✅ Integração completa com GLPI 10.0.18
- ✅ Interface de captura de assinatura com canvas HTML5
- ✅ Salvamento seguro das assinaturas como documentos anexos
- ✅ Compatibilidade com estrutura `/public` do GLPI
- ✅ Sistema de logs para debugging
- ✅ Validação CSRF e segurança adequada
- ✅ Tradução completa em português brasileiro
- ✅ Tratamento robusto de erros

## Requisitos

- GLPI 10.0.0 ou superior
- PHP 8.0 ou superior
- Apache2 com mod_rewrite habilitado
- Extensões PHP: gd, json, curl

## Instalação via SSH

### 1. Conectar ao servidor

```bash
ssh usuario@seu-servidor
cd /var/www/html/glpi/plugins

# Criar diretório do plugin
sudo mkdir -p digitalsignature
sudo chown www-data:www-data digitalsignature
cd digitalsignature
```

### 2. Transferir arquivos do plugin

Use SCP ou SFTP para transferir todos os arquivos do plugin:

```bash
# No seu computador local, execute:
scp -r /caminho/para/plugin/* usuario@servidor:/var/www/html/glpi/plugins/digitalsignature/

# Ou no servidor, crie os arquivos manualmente:
# Os arquivos principais são:
# - setup.php (configuração do plugin)
# - hook.php (integração com GLPI)
# - ajax/save_signature.php (endpoint para salvar assinaturas)
# - js/signature_pad.min.js (biblioteca JavaScript)
# - js/signature.js (scripts do plugin)
# - front/pad.php (interface de assinatura)
# - inc/digitalsignature.class.php (classes PHP)
# - locales/ (traduções PT-BR)
```

### 3. Corrigir permissões

```bash
sudo chown -R www-data:www-data /var/www/html/glpi/plugins/digitalsignature/
sudo chmod -R 755 /var/www/html/glpi/plugins/digitalsignature/
sudo chmod -R 644 /var/www/html/glpi/plugins/digitalsignature/*.php
sudo chmod -R 644 /var/www/html/glpi/plugins/digitalsignature/*/*.php
```

### 4. Verificar estrutura de diretórios

```bash
ls -la /var/www/html/glpi/plugins/digitalsignature/
# Deve mostrar:
# ajax/
# front/  
# js/
# inc/
# locales/
# setup.php
# hook.php
# init.php
# README.md
```

### 5. Ativar plugin no GLPI

1. Acesse o GLPI via navegador
2. Vá em **Configurar > Plugins**
3. Localize "Assinatura Digital" na lista
4. Clique em **Instalar**
5. Após instalação, clique em **Ativar**

### 6. Testar funcionalidade

1. Abra um chamado existente
2. Vá na aba **Solução**
3. Deve aparecer o campo "Assinatura do Cliente"
4. Teste desenhar uma assinatura
5. Salve a solução

## Solução de Problemas

### Erro: "plugin_version_signaturecapture method must be defined"

**CORRIGIDO**: Este erro foi resolvido criando funções de compatibilidade no arquivo `init.php` e seguindo exatamente as convenções do GLPI 10.0.18. O plugin agora inclui:
- Estrutura de classes compatível com PSR-4
- Arquivo `composer.json` com autoload correto
- Funções de versão e instalação seguindo padrões oficiais

### Erro: "Call to undefined method Session::checkCSRFToken"

O GLPI 10+ usa `Session::validateCSRFToken()`. O código já foi atualizado para usar o método correto.

### Headers already sent

Certifique-se que não há espaços em branco ou output antes das tags `<?php` nos arquivos PHP.

### Plugin não aparece na lista

Verifique:
- Permissões dos arquivos (www-data)
- Estrutura de diretórios correta
- Arquivo setup.php presente e sem erros de sintaxe

### Assinatura não salva

Verifique:
- Permissões de escrita no diretório `_uploads/`
- JavaScript habilitado no navegador
- Console do navegador para erros JavaScript

## Teste da Instalação

Antes de instalar no GLPI, teste a estrutura do plugin:
```bash
# No diretório do plugin, execute:
php test_plugin.php

# Deve mostrar:
# ✓ setup.php carregado com sucesso
# ✓ hook.php carregado com sucesso  
# ✓ Todas as funções obrigatórias existem
# ✓ Versão: 2.0.0
# ✓ Todos os arquivos necessários presentes
```

## Log de Debug

Para verificar erros, consulte:
```bash
tail -f /var/www/html/glpi/files/_log/php-errors.log
```

### Verificação de Compatibilidade GLPI 10.0.18

O plugin foi testado e corrigido para total compatibilidade com:
- ✅ GLPI 10.0.18 em Ubuntu 24.04
- ✅ PHP 8.3.6 com Apache2
- ✅ MariaDB 10.11.13
- ✅ Estrutura de plugins conforme documentação oficial
- ✅ Autoloading PSR-4 com Composer

## Suporte

Para questões específicas do plugin:
- Verifique logs do GLPI em `/var/www/html/glpi/files/_log/`
- Ative modo debug no GLPI para mais detalhes
- Console do navegador para erros JavaScript
