<?php

use Phinx\Migration\AbstractMigration;

class CreateDestinatarios extends AbstractMigration
{
    public function up(): void
    {
        $this->execute("
            CREATE TABLE destinatarios (
                id          INT UNSIGNED  AUTO_INCREMENT PRIMARY KEY,
                cpf_cnpj    VARCHAR(14)   NOT NULL,
                nome        VARCHAR(100)  NOT NULL,
                logradouro  VARCHAR(150)  NOT NULL,
                numero      VARCHAR(20)   NOT NULL,
                complemento VARCHAR(100)           DEFAULT NULL,
                bairro      VARCHAR(100)  NOT NULL,
                cidade      VARCHAR(100)  NOT NULL,
                uf          CHAR(2)       NOT NULL,
                cep         CHAR(8)       NOT NULL,
                created_at  DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
                UNIQUE KEY uq_destinatarios_cpf_cnpj  (cpf_cnpj),
                KEY        idx_destinatarios_cpf_cnpj (cpf_cnpj)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
    }

    public function down(): void
    {
        $this->execute('DROP TABLE IF EXISTS destinatarios');
    }
}
