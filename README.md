# Effecti Code Challenge - Teste Técnico PHP (ERP – Contratos e Serviços)

Desenvolvimento de um ERP simplificado focado em Contratos e Serviços recorrentes.
O projeto é monorepo com backend e frontend separados, roda no Docker utilizando as tecnologias Laravel (API REST) em PHP 8.2 + Vue.js 3 e MySQL.

## Stack

- **Backend**: Laravel 8.0 (PHP 7.4) - API REST
- **Frontend**: Vue.js 3 + Vite
- **Banco**: MySQL 8
- **Docker / docker-compose**

## Configuração do arquivo .ENV

Renomeie o arquivo [.env.example](https://github.com/julianomacielferreira/effecti-code-challenge/blob/main/backend/.env.example) para **.env**
e altere as seguintes variáveis de ambiente:

```bash
APP_NAME=Effecti_ERP
APP_ENV=local
APP_KEY=base64:Jwj0pOe8Bo8vkP4uYi5rMeEGKLEAtzs4DjslwbTqx/M= # use the output of the `php -r "echo md5(uniqid()).\"\n\";" command`
APP_DEBUG=true
APP_URL=http://localhost
APP_TIMEZONE=UTC

DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=effecti_erp
DB_USERNAME=effecti
DB_PASSWORD=z0x9c8v7

```

## Banco de Dados

O arquivo [schema.sql](./docker/mysql/schema.sql) contém o código DDL tabelas.

Abaixo segue o diagrama que mostra as tabelas para modelo de dados do projeto.

![](db_model_effecti_erp.png)

As seguintes regras são garantidas através da modelagem de dados com as respectivas restrições:

Tabela `clientes`

- `cpf_cnpj VARCHAR(14) UNIQUE`
  - Garante unicidade.
  - O `CHECK` aceita só 11 ou 14 dígitos.
  - A validação do dígito verificador (CPF/CNPJ) é feita no Laravel com Rule, porque SQL não calcula Digito Verificador.
- `email UNIQUE`
  - impede duplicidade.
- `status ENUM('Ativo','Inativo')`
  - No código é bloqueado `INSERT INTO contratos` se cliente == "Inativo".
  - `ON DELETE RESTRICT` no contrato não deixa excluir nenhum cliente que tenha contrato.

Tabela `servicos`

- `valor_base_mensal DECIMAL(10,2) CHECK > 0`
  - Impede serviço grátis ou negativo.
  - É o valor padrão, mas o contrato pode mudar o preço (negociação).
- `UNIQUE nome`
  - Evita nomes de serviço duplicados.

Tabela `contratos`

- `cliente_id FK`
  - Um cliente pode ter vários contratos.
- `data_termino NULL`
  - Contrato indeterminado é permitido.
- `CHECK data_termino >= data_inicio`
  - Consistência temporal.
- `status`
  - quando virar 'Cancelado', no Laravel é travado o endpoint de update (Regra Extra: "impedir edição de contrato cancelado").

Tabela `contrato_itens`

- `UNIQUE (contrato_id, servico_id)`
  - Não deixa adicionar o mesmo serviço duas vezes, a quantidade é somada dinamicamente.
- `quantidade > 0 e valor_unitario > 0`
  - Restrições.
- `ON DELETE RESTRICT` em servico
  - Não deixa apagar um Serviço se ele está vinculado a um contrato ativo.
- `ON DELETE CASCADE` em contrato
  - Se deletar um contrato, os itens somem juntos.

## Docker

**Tanto o [Docker](https://docs.docker.com/install/) como o [Docker Compose](https://docs.docker.com/compose/install/) devem estar previamente instalados na sua máquina.**

## Migrations

As migrations (diretório `/backend/database/migrations`) rodam sempre que o container é iniciado. Para executá-las manualmente baste rodar o comando:

```bash
$ docker-compose exec backend php artisan migrate --env=testing
```

## Testes Unitários

Para executar os testes unitários localizados no diretório `backend/app/tests` basta executar o seguinte comando na raiz do projeto:

```bash
$ docker-compose exec backend php artisan test
```

```bash
   PASS  Tests\Unit\Models\ClienteTest
  ✓ status padrao e ativo
  ✓ relacionamento contratos retorna has many

   PASS  Tests\Unit\Models\ContratoItemTest
  ✓ subtotal calcula quantidade vezes valor

   PASS  Tests\Unit\Models\ContratoTest
  ✓ accessor total mensal soma itens regra 5
  ✓ contrato inicia como ativo

   PASS  Tests\Unit\Models\ServicoTest
  ✓ cast valor base para decimal

   PASS  Tests\Unit\Repositories\ClienteRepositoryTest
  ✓ cria cliente limpando mascara
  ✓ busca por cpf ignorando mascara
  ✓ nao deleta cliente com contrato ativo
  ✓ deleta cliente sem contrato

   PASS  Tests\Unit\Requests\StoreClienteRequestTest
  ✓ rules tem cpfcnpj e unique
  ✓ prepare limpa mascara antes de validar

   PASS  Tests\Unit\Requests\UpdateClienteRequestTest
  ✓ rules tem cpfcnpj e unique ignore
  ✓ prepare limpa mascara
  ✓ permite manter mesmo cpf no update
  ✓ nao permite cpf de outro cliente

   PASS  Tests\Unit\Rules\CpfCnpjRuleTest
  ✓ aceita cpf valido sem mascara
  ✓ aceita cpf valido com mascara
  ✓ rejeita cpf com dv errado
  ✓ rejeita cpf sequencia repetida
  ✓ aceita cnpj valido
  ✓ aceita cnpj com mascara
  ✓ rejeita cnpj invalido
  ✓ rejeita tamanho invalido

   PASS  Tests\Unit\Services\ClienteServiceTest
  ✓ excluir lanca excecao se tem contrato

   PASS  Tests\Feature\Api\ClienteControllerTest
  ✓ lista paginado
  ✓ cria cliente
  ✓ nao cria duplicado
  ✓ mostra com contagem
  ✓ atualiza
  ✓ nao exclui com contrato
  ✓ exclui sem contrato

   PASS  Tests\Feature\Api\ClienteTest
  ✓ nao permite cadastrar cpf duplicado

  Tests:  33 passed
  Time:   0.80s

```

## Documentação Swagger

Para acessar documentação Swagger basta acessar http://localhost:8000/api/documentation.

![](effecti-erp-swagger-docs.png)


## Endpoints

### Clientes - Listar com filtro (GET): 

- **api/clientes?status={{Ativo|Inativo}}&search={{search}}**

Exemplo:

```bash
$ curl -s "http://localhost:8000/api/clientes?status=Ativo&search=MLOCKS"
```

<details>
<summary><b>Resposta</b></summary>

```json
{
   "current_page":1,
   "data":[
      {
         "id":8,
         "nome":"MLOCKS CONSULTORIA",
         "cpf_cnpj":"11222333000181",
         "email":"contato@email.com",
         "status":"Ativo",
         "created_at":"2026-06-11T23:09:24.000000Z",
         "updated_at":"2026-06-11T23:09:24.000000Z",
         "contratos_count":0
      }
   ],
   "first_page_url":"http:\/\/localhost:8000\/api\/clientes?page=1",
   "from":1,
   "last_page":1,
   "last_page_url":"http:\/\/localhost:8000\/api\/clientes?page=1",
   "links":[
      {
         "url":null,
         "label":"&laquo; Previous",
         "active":false
      },
      {
         "url":"http:\/\/localhost:8000\/api\/clientes?page=1",
         "label":"1",
         "active":true
      },
      {
         "url":null,
         "label":"Next &raquo;",
         "active":false
      }
   ],
   "next_page_url":null,
   "path":"http:\/\/localhost:8000\/api\/clientes",
   "per_page":15,
   "prev_page_url":null,
   "to":1,
   "total":1
}
```
</details>

---

### Clientes - Criar (POST): 

- **api/clientes**

Exemplo:

```bash
curl -s -X POST http://localhost:8000/api/clientes \
 -H "Content-Type: application/json" \
 -d '{"nome":"MLOCKS CONSULTORIA","cpf_cnpj":"11.222.333/0001-81","email":"contato@email.com"}'
```

<details>
<summary><b>Resposta</b></summary>

```json
{
   "status":"Ativo",
   "nome":"MLOCKS CONSULTORIA",
   "cpf_cnpj":"11222333000181",
   "email":"contato@email.com",
   "updated_at":"2026-06-11T23:09:24.000000Z",
   "created_at":"2026-06-11T23:09:24.000000Z",
   "id":8
}
```

</details>

---

### Clientes - Recuperar por Id (GET): 

- **api/clientes/{{id}}**

Exemplo:

```bash
curl -s http://localhost:8000/api/clientes/1
```

<details>
<summary><b>Resposta</b></summary>

```json
{
   "id":8,
   "nome":"MLOCKS CONSULTORIA",
   "cpf_cnpj":"11222333000181",
   "email":"contato@acme.com",
   "status":"Ativo",
   "created_at":"2026-06-11T23:09:24.000000Z",
   "updated_at":"2026-06-11T23:09:24.000000Z",
   "contratos_count":0
}
```

</details>

---

### Clientes - Atualizar por Id (PUT): 

- **api/clientes/{{id}}**

Exemplo:

```bash
curl -s -X PUT http://localhost:8000/api/clientes/1 \
 -H "Content-Type: application/json" \
 -d '{"status":"Inativo"}'
```

<details>
<summary><b>Resposta</b></summary>

```json
{
   "id":8,
   "nome":"MLOCKS CONSULTORIA",
   "cpf_cnpj":"11222333000181",
   "email":"contato@acme.com",
   "status":"Inativo",
   "created_at":"2026-06-11T23:09:24.000000Z",
   "updated_at":"2026-06-11T23:17:45.000000Z",
   "contratos_count":0
}
```

</details>

---

### Clientes - Deletar por Id, falha se tiver contrato. (DELETE): 

- **api/clientes/{{id}}**

Exemplo:

```bash
curl -i -X DELETE http://localhost:8000/api/clientes/8
```

<details>
<summary><b>Resposta</b></summary>

```bash
HTTP/1.1 204 No Content
Server: nginx/1.31.1
Connection: keep-alive
X-Powered-By: PHP/7.4.33
Cache-Control: no-cache, private
Date: Thu, 11 Jun 2026 23:19:29 GMT
X-RateLimit-Limit: 60
X-RateLimit-Remaining: 58
Access-Control-Allow-Origin: *
```

</details>

---