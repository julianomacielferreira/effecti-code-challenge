-- Effect ERP - Schema Inicial
-- É executado toda vez que o container é iniciado

CREATE DATABASE IF NOT EXISTS effecti_erp;
USE effecti_erp;

CREATE TABLE IF NOT EXISTS clientes (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(255) NOT NULL COMMENT 'Regra 1: obrigatório',
    cpf_cnpj VARCHAR(14) NOT NULL COMMENT 'Regra 1: apenas números, 11=CPF ou 14=CNPJ',
    email VARCHAR(255) NOT NULL,
    status ENUM('Ativo','Inativo') NOT NULL DEFAULT 'Ativo' COMMENT 'Regra 1: só Ativo pode ter contrato',
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uniq_cpf_cnpj (cpf_cnpj),
    UNIQUE KEY uniq_email (email),
    CONSTRAINT chk_cpf_cnpj_tamanho CHECK (CHAR_LENGTH(cpf_cnpj) IN (11,14))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS servicos (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(255) NOT NULL,
    valor_base_mensal DECIMAL(10,2) NOT NULL COMMENT 'Regra 2: base para sugestão no contrato',
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uniq_servico_nome (nome),
    CONSTRAINT chk_valor_base_positivo CHECK (valor_base_mensal > 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS contratos (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    cliente_id BIGINT UNSIGNED NOT NULL,
    data_inicio DATE NOT NULL COMMENT 'Regra 3: obrigatória',
    data_termino DATE NULL COMMENT 'Regra 3: opcional, se preenchida >= inicio',
    status ENUM('Ativo','Cancelado') NOT NULL DEFAULT 'Ativo' COMMENT 'Regra 3 e Extra: Cancelado trava edição',
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_contrato_cliente FOREIGN KEY (cliente_id) REFERENCES clientes(id)
        ON DELETE RESTRICT  -- Regra: não deixa apagar cliente com contrato
        ON UPDATE CASCADE,
    CONSTRAINT chk_datas_validas CHECK (data_termino IS NULL OR data_termino >= data_inicio),
    INDEX idx_cliente_status (cliente_id, status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS contrato_itens (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    contrato_id BIGINT UNSIGNED NOT NULL,
    servico_id BIGINT UNSIGNED NOT NULL,
    quantidade INT UNSIGNED NOT NULL DEFAULT 1,
    valor_unitario DECIMAL(10,2) NOT NULL COMMENT 'Regra 4: pode ser diferente do valor_base',
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_item_contrato FOREIGN KEY (contrato_id) REFERENCES contratos(id)
        ON DELETE CASCADE,  -- apaga itens se contrato for apagado
    CONSTRAINT fk_item_servico FOREIGN KEY (servico_id) REFERENCES servicos(id)
        ON DELETE RESTRICT  -- Regra: não deixa apagar serviço em uso
        ON UPDATE CASCADE,
    UNIQUE KEY uniq_contrato_servico (contrato_id, servico_id) COMMENT 'Regra 4: evita duplicar serviço',
    CONSTRAINT chk_quantidade_positiva CHECK (quantidade > 0),
    CONSTRAINT chk_valor_unit_positivo CHECK (valor_unitario > 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE OR REPLACE VIEW contratos_valor_view AS
SELECT c.id AS contrato_id,
       c.cliente_id,
       c.status,
       COALESCE(SUM(ci.quantidade * ci.valor_unitario), 0) AS total_mensal
  FROM contratos c
LEFT JOIN contrato_itens ci ON ci.contrato_id = c.id
GROUP BY c.id, c.cliente_id, c.status;