<?php

use Phinx\Migration\AbstractMigration;

class CreateRemetentes extends AbstractMigration
{
    public function up(): void
    {
        $this->execute("
            CREATE TABLE remetentes (
                id         INT UNSIGNED  AUTO_INCREMENT PRIMARY KEY,
                cnpj       VARCHAR(14)   NOT NULL,
                nome       VARCHAR(100)  NOT NULL,
                cidade     VARCHAR(100)  NOT NULL,
                uf         CHAR(2)       NOT NULL,
                created_at DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
                UNIQUE KEY uq_remetentes_cnpj (cnpj)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
    }

    public function down(): void
    {
        $this->execute('DROP TABLE IF EXISTS remetentes');
    }
}
