<?php

use Phinx\Migration\AbstractMigration;

class CreateMotivosNaoConformidade extends AbstractMigration
{
    public function up(): void
    {
        $this->execute("
            CREATE TABLE motivos_nao_conformidade (
                id        INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                codigo    VARCHAR(30)   NOT NULL,
                descricao VARCHAR(150)  NOT NULL,
                ativo     TINYINT(1)    NOT NULL DEFAULT 1,
                UNIQUE KEY uq_motivos_nao_conformidade_codigo (codigo),
                KEY        idx_motivos_nao_conformidade_ativo (ativo)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
    }

    public function down(): void
    {
        $this->execute('DROP TABLE IF EXISTS motivos_nao_conformidade');
    }
}
