# BUGFIX.md

**Data da correção:** 22/05/2026
**Corrigido por:** Henrique Rocha (Time de TI)

---

## O que era o bug

O bug estava no arquivo `src/Controllers/EntregaController.php`, no método `store`.

Ao criar uma entrega, o sistema verificava apenas se a transportadora existia:

```php
SELECT id FROM transportadoras WHERE id = ?
```

Com isso, uma transportadora desativada continuava sendo aceita, porque ela ainda existia no banco. A desativação no sistema é controlada pelo campo `deleted_at`.

A correção foi buscar também o campo `deleted_at` e bloquear a criação da entrega quando ele estiver preenchido:

```php
SELECT id, deleted_at FROM transportadoras WHERE id = ?
```

Agora o comportamento ficou assim:

- transportadora inexistente retorna `404`;
- transportadora desativada retorna `422`;
- transportadora ativa continua permitindo o cadastro da entrega.

---

## Resposta para a Camila (Operações)

Olá Camila! identifiquei o problema.

O sistema estava deixando criar novas entregas para uma transportadora que já tinha sido desativada. Isso acontecia porque, na hora do cadastro da entrega, ele só conferia se a transportadora existia, mas não conferia se ela ainda estava ativa.

A correção já foi feita. A partir de agora, se alguém tentar cadastrar uma entrega usando uma transportadora desativada, o sistema vai bloquear a operação.

As entregas antigas não foram alteradas automaticamente. Como algumas podem ter sido cadastradas antes da correção, o ideal é o time revisar as entregas vinculadas à Logística Norte Ltda e decidir se elas devem ser canceladas, ajustadas para outra transportadora ou tratadas manualmente.

Att, 

Henrique Rocha *Desenvolvedor*

---

## Como reproduzir (antes da correção)

1. Listar as transportadoras incluindo as inativas e confirmar que a `Logística Norte Ltda` está desativada:

```no terminal com o servidor rodando 
(Invoke-WebRequest -UseBasicParsing "http://localhost:8000/transportadoras?incluir_inativas=true").Content
```

2. Tentar criar uma entrega usando `id_transportadora = 3`, que é a transportadora desativada:

```no terminal com o servidor rodando
(Invoke-WebRequest -UseBasicParsing -Method POST "http://localhost:8000/entregas" `
  -ContentType "application/json" `
  -Body '{"id_transportadora":3,"id_remetente":1,"id_destinatario":1,"data_prazo":"2026-05-30","peso_kg":10.5,"volumes":2}').Content
```

Antes da correção, a entrega era criada mesmo com a transportadora desativada.

## Como verificar que está corrigido

1. Rodar novamente o mesmo cadastro de entrega com `id_transportadora = 3`:

```no terminal com o servidor rodando
try {
  $response = Invoke-WebRequest -UseBasicParsing -Method POST "http://localhost:8000/entregas" `
    -ContentType "application/json" `
    -Body '{"id_transportadora":3,"id_remetente":1,"id_destinatario":1,"data_prazo":"2026-05-30","peso_kg":10.5,"volumes":2}'

  $response.StatusCode
  $response.Content
} catch {
  $_.Exception.Response.StatusCode.value__
  $reader = New-Object System.IO.StreamReader($_.Exception.Response.GetResponseStream())
  $reader.ReadToEnd()
}
```

2. O retorno esperado agora é:

```text
422
{"erro":"Transportadora inativa"}
```
