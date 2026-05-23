# Teste Técnico — Desenvolvedor PHP Júnior

## Contexto

Você acabou de entrar no time de desenvolvimento de um TMS (Transportation Management System). No seu primeiro dia, chegou um bug reportado pelo time de operações e uma nova funcionalidade para implementar.

Seu trabalho: **corrigir o bug e entregar a feature**.

---

## Prazo

**5 dias corridos** a partir do recebimento deste desafio.

---

## Stack

PHP 8.1+ · PDO · MySQL 8+ · [Phinx](https://phinx.org) (migrations e seeds)

---

## Como rodar

```bash
# 1. Configure o ambiente
cp .env.example .env
# edite .env com suas credenciais MySQL

# 2. Instale as dependências
composer install

# 3. Crie as tabelas
vendor/bin/phinx migrate

# 4. Popule os dados iniciais
vendor/bin/phinx seed:run

# 5. Suba o servidor
php -S localhost:8000 public/index.php
```

---

## Sistema atual

Endpoints disponíveis:

```
GET   /transportadoras
POST  /transportadoras
GET   /transportadoras/{id}
PATCH /transportadoras/{id}/desativar
PATCH /transportadoras/{id}/reativar

GET   /entregas
POST  /entregas
GET   /entregas/{id}
PATCH /entregas/{id}/status
```

Dados de seed disponíveis (use os IDs para testar):
- 3 transportadoras (2 ativas, 1 inativa)
- 2 remetentes
- 3 destinatários
- 3 entregas em status variados com histórico de ocorrências

**Fluxo de status:**
```
CRIADA → COLETADA → EM_TRANSITO → SAIU_ENTREGA → ENTREGUE
                                               ↘ DEVOLVIDA
```
Transições inválidas devem retornar `422`.

---

## Suas tarefas

### Tarefa 1 — Corrigir o bug

Leia o arquivo [`BUG_REPORT.md`](./BUG_REPORT.md), reproduza o problema, corrija e preencha o [`BUGFIX.md`](./BUGFIX.md).

### Tarefa 2 — Não conformidades

O time de operações precisa registrar ocorrências de entregas com problema (avaria, recusa, endereço errado, etc.).

**Crie as migrations:**

```
motivos_nao_conformidade
  id        INT UNSIGNED PK AUTO_INCREMENT
  codigo    VARCHAR(30) UNIQUE NOT NULL
  descricao VARCHAR(150) NOT NULL
  ativo     TINYINT(1) NOT NULL DEFAULT 1

nao_conformidades
  id         INT UNSIGNED PK AUTO_INCREMENT
  id_entrega INT UNSIGNED NOT NULL  →  FK entregas.id
  id_motivo  INT UNSIGNED NOT NULL  →  FK motivos_nao_conformidade.id
  descricao  VARCHAR(500) NULL
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
```

**Crie o seeder `MotivosNaoConformidadeSeeder.php`** com:

| codigo | descricao |
|--------|-----------|
| `AVARIA_PRODUTO` | Produto com avaria ou dano |
| `NAO_ENTREGUE` | Destinatário ausente |
| `ENDERECO_INCORRETO` | Endereço incorreto ou não localizado |
| `RECUSADO` | Recusado pelo destinatário |
| `EXTRAVIO` | Produto extraviado |
| `OUTROS` | Outros motivos |

**Implemente os endpoints:**

```
GET  /motivos-nao-conformidade
     → retorna lista dos motivos com ativo = 1

POST /entregas/{id}/nao-conformidades
     body: { "id_motivo": 1, "descricao": "..." }
     → registra a não conformidade
     → id_motivo obrigatório; entrega e motivo devem existir
```

---

## Commits esperados

Queremos ver o raciocínio em etapas — não um único commit com tudo.

```
fix:   correção do bug
feat:  migration motivos_nao_conformidade
feat:  migration nao_conformidades
feat:  seeder MotivosNaoConformidadeSeeder
feat:  GET /motivos-nao-conformidade
feat:  POST /entregas/{id}/nao-conformidades
docs:  BUGFIX.md preenchido
```

---

## Bônus

- `GET /rastreamento/{codigo}` — rastreamento público pelo código da entrega (ex: `BRD-2024-00001`)
- `GET /entregas/{id}/nao-conformidades` — listar NCs de uma entrega
- Docker + docker-compose funcional
- Testes automatizados

---

## Critérios de avaliação

| O que avaliamos | Peso |
|-----------------|------|
| Identificação e correção do bug | Alto |
| BUGFIX.md — clareza técnica + resposta para o time | Alto |
| Migrations corretas (FKs, índices, tipos) | Alto |
| Endpoints de não conformidade funcionando | Alto |
| Qualidade de código e organização | Médio |
| Tratamento de erro e HTTP status codes | Médio |
| Granularidade dos commits | Médio |

---

## Entrega

1. Suba em repositório **público** no GitHub (sem BRUDAM no nome)
2. README do seu projeto com: como rodar, exemplos de requisição, decisões técnicas
3. Envie ao recrutador: nome completo · link do repo · LinkedIn

---

## Dúvidas

Se algo estiver ambíguo, documente sua interpretação e siga. Decisão sob incerteza também é avaliada.

---

## Autor

**Michel Mileski** — [@eusouomichel](https://github.com/eusouomichel)

---

## Implementação

### Como rodar com Docker

```bash
docker compose up -d --build
docker compose exec app vendor/bin/phinx migrate
docker compose exec app vendor/bin/phinx seed:run
```

A API fica disponível em `http://localhost:8000`.

O MySQL do Docker fica exposto em `localhost:3307`. Dentro do Docker, a aplicação usa o host `db` e a porta `3306`.

### Endpoints adicionados

```
GET  /motivos-nao-conformidade
POST /entregas/{id}/nao-conformidades
GET  /entregas/{id}/nao-conformidades
GET  /rastreamento/{codigo}
```

### Exemplos de requisição

Listar motivos ativos de não conformidade:

```bash
curl "http://localhost:8000/motivos-nao-conformidade"
```

Registrar uma não conformidade em uma entrega:

```bash
curl -X POST "http://localhost:8000/entregas/1/nao-conformidades" \
  -H "Content-Type: application/json" \
  -d '{
    "id_motivo": 1,
    "descricao": "Produto com embalagem avariada"
  }'
```

Listar não conformidades de uma entrega:

```bash
curl "http://localhost:8000/entregas/1/nao-conformidades"
```

Rastrear uma entrega pelo código:

```bash
curl "http://localhost:8000/rastreamento/BRD-2024-00001"
```

Validar o bloqueio de transportadora inativa:

```bash
curl -X POST "http://localhost:8000/entregas" \
  -H "Content-Type: application/json" \
  -d '{
    "id_transportadora": 3,
    "id_remetente": 1,
    "id_destinatario": 1,
    "data_prazo": "2026-05-30",
    "peso_kg": 10.5,
    "volumes": 2
  }'
```

Resposta esperada:

```json
{
  "erro": "Transportadora inativa"
}
```

### Decisões

- Mantive a estrutura original do projeto, usando PHP puro, controllers simples e PDO.
- Usei o campo `deleted_at` para identificar transportadoras inativas, seguindo a regra que já existia no `TransportadoraController`.
- A criação de entrega agora retorna `422` quando a transportadora existe, mas está inativa.
- Criei a tabela `motivos_nao_conformidade` para manter os motivos separados dos registros de ocorrência.
- Criei a tabela `nao_conformidades` vinculada por chave estrangeira à entrega e ao motivo.
- O endpoint `GET /motivos-nao-conformidade` retorna apenas motivos com `ativo = 1`.
- O endpoint `POST /entregas/{id}/nao-conformidades` valida campo obrigatório, entrega existente e motivo existente.
- O endpoint `GET /rastreamento/{codigo}` reaproveita a busca de entrega por código já existente no `EntregaController`.
- O Docker usa variáveis de ambiente definidas no `docker-compose.yml`; localmente o projeto continua funcionando com `.env`.

### Validações realizadas

- Criação de entrega com transportadora ativa.
- Bloqueio de criação de entrega com transportadora inativa.
- Listagem de motivos ativos.
- Registro de não conformidade.
- Listagem de não conformidades por entrega.
- Rastreamento por código da entrega.
- Retorno `404` para entrega inexistente.
- Retorno `422` para `id_motivo` ausente.
