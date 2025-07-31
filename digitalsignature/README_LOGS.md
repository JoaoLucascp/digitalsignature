# Sistema de Logs - Plugin Digital Signature

## Localização dos Logs

O plugin gera logs detalhados em:
```
/var/log/glpi/plugins/digitalsignature.log
```

Ou alternativamente (dependendo da configuração GLPI):
```
/var/www/html/glpi/files/_log/digitalsignature.log
```

## Tipos de Log

### 1. DEBUG
- Inicialização do plugin
- Hooks chamados
- Validações de contexto
- Estados do canvas e JavaScript

### 2. INFO  
- Eventos importantes do plugin
- Assinaturas salvas com sucesso
- Usuários autenticados

### 3. WARNING
- Situações não ideais mas não críticas
- Fallbacks ativados

### 4. ERROR
- Falhas de validação CSRF
- Problemas de permissão
- Erros de formato de dados

### 5. FATAL
- Falhas críticas do sistema
- Problemas de banco de dados

## Como Monitorar os Logs

### Monitoramento em Tempo Real
```bash
# Ver logs em tempo real
tail -f /var/log/glpi/plugins/digitalsignature.log

# Filtrar apenas erros
tail -f /var/log/glpi/plugins/digitalsignature.log | grep ERROR

# Ver últimas 50 linhas
tail -n 50 /var/log/glpi/plugins/digitalsignature.log
```

### Análise de Problemas Específicos

#### Problema: Assinatura não está aparecendo
```bash
grep "Hook.*show_pad" /var/log/glpi/plugins/digitalsignature.log
grep "solution.*context" /var/log/glpi/plugins/digitalsignature.log
```

#### Problema: Não consegue desenhar no canvas
```bash
grep "Canvas.*not found" /var/log/glpi/plugins/digitalsignature.log
grep "SignaturePad.*library" /var/log/glpi/plugins/digitalsignature.log
grep "Fallback.*drawing" /var/log/glpi/plugins/digitalsignature.log
```

#### Problema: Erro ao salvar assinatura
```bash
grep "save_signature_ajax" /var/log/glpi/plugins/digitalsignature.log
grep "CSRF.*validation" /var/log/glpi/plugins/digitalsignature.log
grep "document.*created" /var/log/glpi/plugins/digitalsignature.log
```

#### Problema: Permissões
```bash
grep "permission\|rights\|UPDATE" /var/log/glpi/plugins/digitalsignature.log
```

## Estrutura dos Logs

Cada entrada de log contém:
```
[TIMESTAMP] [LEVEL] [User:ID] [IP:ADDRESS] [URI:PATH] MESSAGE | Context: {...}
```

Exemplo:
```
[2025-07-29 18:15:32] [INFO] [User:2] [IP:192.168.1.100] [URI:/front/ticket.form.php?id=123] Displaying signature pad for ticket | Context: {"ticket_id":123,"user_id":2}
```

## Comandos Úteis

### Limpar logs antigos
```bash
# Manter apenas últimos 7 dias
find /var/log/glpi/plugins/ -name "digitalsignature.log*" -mtime +7 -delete
```

### Buscar por ticket específico
```bash
grep "ticket_id.*123" /var/log/glpi/plugins/digitalsignature.log
```

### Buscar por usuário específico
```bash
grep "User:2" /var/log/glpi/plugins/digitalsignature.log
```

### Ver estatísticas de uso
```bash
# Contar assinaturas salvas hoje
grep "$(date '+%Y-%m-%d')" /var/log/glpi/plugins/digitalsignature.log | grep "Signature saved successfully" | wc -l

# Ver usuários mais ativos
grep "Signature saved successfully" /var/log/glpi/plugins/digitalsignature.log | grep -o "User:[0-9]*" | sort | uniq -c | sort -nr
```

## Solução de Problemas Comuns

### Interface de assinatura não aparece
1. Verificar se está em um ticket existente
2. Confirmar se usuário tem permissão UPDATE em tickets
3. Verificar se está no contexto de solução (URL contém ticket.form.php)

### Canvas não permite desenhar
1. Verificar se biblioteca SignaturePad foi carregada
2. Confirmar se fallback foi ativado
3. Verificar JavaScript console no navegador

### Erro ao salvar assinatura
1. Verificar token CSRF
2. Confirmar permissões no ticket
3. Verificar se dados da assinatura estão no formato correto

### Arquivo de log não é criado
1. Verificar permissões do diretório /var/log/glpi/plugins/
2. Confirmar se GLPI_LOG_DIR está definido corretamente
3. Verificar se processo web server tem permissão de escrita

## Logs no Navegador

Além dos logs do servidor, o plugin também gera logs no console do navegador:

```javascript
// Abrir Developer Tools (F12) e verificar Console
// Procurar por mensagens que começam com "Digital Signature:"
```

Mensagens importantes no console:
- "Digital Signature: Initializing signature pad..."
- "Digital Signature: Begin stroke"
- "Digital Signature: Signature captured and stored"
- "Digital Signature: Fallback - Begin drawing"

## Configuração de Log Level

Para ajustar o nível de logging, edite o arquivo `inc/logger.class.php` e modifique a propriedade `$log_levels` para incluir apenas os níveis desejados.