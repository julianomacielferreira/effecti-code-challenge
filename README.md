# Effecti Code Challenge - Teste Técnico PHP (ERP – Contratos e Serviços)

Desenvolvimento de um ERP simplificado focado em Contratos e Serviços recorrentes. 
O projeto é monorepo com backend e frontend separados, roda no Docker utilizando as tecnologias Laravel (API REST) em PHP 8.2 + Vue.js 3 e MySQL.

## Stack

- __Backend__: Laravel 8.0 (PHP 7.4) - API REST
- __Frontend__: Vue.js 3 + Vite
- __Banco__: MySQL 8
- __Docker / docker-compose__


## Banco de Dados


O arquivo [schema.sql](./docker/mysql/schema.sql) contém o código para criação das tabelas é executado toda vez que o container é iniciado.

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