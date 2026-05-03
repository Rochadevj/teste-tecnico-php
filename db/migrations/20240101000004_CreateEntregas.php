<?php

use Phinx\Migration\AbstractMigration;

class CreateEntregas extends AbstractMigration
{
    public function up(): void
    {
        $this->execute("
            CREATE TABLE entregas (
                id                INT UNSIGNED      AUTO_INCREMENT PRIMARY KEY,
                codigo            VARCHAR(20)       NOT NULL,
                id_transportadora INT UNSIGNED      NOT NULL,
                id_remetente      INT UNSIGNED      NOT NULL,
                id_destinatario   INT UNSIGNED      NOT NULL,
                status            ENUM(
                                      'CRIADA',
                                      'COLETADA',
                                      'EM_TRANSITO',
                                      'SAIU_ENTREGA',
                                      'ENTREGUE',
                                      'DEVOLVIDA'
                                  )                 NOT NULL DEFAULT 'CRIADA',
                data_prazo        DATE              NOT NULL,
                peso_kg           DECIMAL(8,2)      NOT NULL,
                volumes           SMALLINT UNSIGNED NOT NULL DEFAULT 1,
                created_at        DATETIME          NOT NULL DEFAULT CURRENT_TIMESTAMP,
                updated_at        DATETIME          NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                UNIQUE KEY uq_entregas_codigo               (codigo),
                KEY        idx_entregas_transportadora      (id_transportadora),
                KEY        idx_entregas_destinatario        (id_destinatario),
                KEY        idx_entregas_status              (status),
                KEY        idx_entregas_data_prazo          (data_prazo),
                KEY        idx_entregas_transportadora_data (id_transportadora, data_prazo),
                CONSTRAINT fk_entregas_transportadora FOREIGN KEY (id_transportadora) REFERENCES transportadoras (id),
                CONSTRAINT fk_entregas_remetente      FOREIGN KEY (id_remetente)      REFERENCES remetentes      (id),
                CONSTRAINT fk_entregas_destinatario   FOREIGN KEY (id_destinatario)   REFERENCES destinatarios   (id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
    }

    public function down(): void
    {
        $this->execute('DROP TABLE IF EXISTS entregas');
    }
}
