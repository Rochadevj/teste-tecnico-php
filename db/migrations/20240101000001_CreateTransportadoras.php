<?php

use Phinx\Migration\AbstractMigration;

class CreateTransportadoras extends AbstractMigration
{
    public function up(): void
    {
        $this->execute("
            CREATE TABLE transportadoras (
                id            INT UNSIGNED     AUTO_INCREMENT PRIMARY KEY,
                cnpj          VARCHAR(14)      NOT NULL,
                nome_fantasia VARCHAR(100)     NOT NULL,
                created_at    DATETIME         NOT NULL DEFAULT CURRENT_TIMESTAMP,
                updated_at    DATETIME         NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                deleted_at    DATETIME                  DEFAULT NULL,
                UNIQUE KEY uq_transportadoras_cnpj    (cnpj),
                KEY        idx_transportadoras_deleted (deleted_at)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
    }

    public function down(): void
    {
        $this->execute('DROP TABLE IF EXISTS transportadoras');
    }
}
