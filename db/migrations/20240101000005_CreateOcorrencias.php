<?php

use Phinx\Migration\AbstractMigration;

class CreateOcorrencias extends AbstractMigration
{
    public function up(): void
    {
        $this->execute("
            CREATE TABLE ocorrencias (
                id         INT UNSIGNED  AUTO_INCREMENT PRIMARY KEY,
                id_entrega INT UNSIGNED  NOT NULL,
                status     ENUM(
                               'CRIADA',
                               'COLETADA',
                               'EM_TRANSITO',
                               'SAIU_ENTREGA',
                               'ENTREGUE',
                               'DEVOLVIDA'
                           )             NOT NULL,
                descricao  VARCHAR(255)  NOT NULL DEFAULT '',
                cidade     VARCHAR(100)  NOT NULL DEFAULT '',
                uf         CHAR(2)       NOT NULL DEFAULT '',
                created_at DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
                KEY idx_ocorrencias_entrega (id_entrega),
                CONSTRAINT fk_ocorrencias_entrega FOREIGN KEY (id_entrega) REFERENCES entregas (id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
    }

    public function down(): void
    {
        $this->execute('DROP TABLE IF EXISTS ocorrencias');
    }
}
