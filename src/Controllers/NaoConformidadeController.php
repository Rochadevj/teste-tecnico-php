<?php

namespace App\Controllers;

use App\Database;

class NaoConformidadeController
{
    public static function motivos(array $params): void
    {
        $db = Database::connection();

        $stmt = $db->query('
            SELECT id, codigo, descricao
            FROM motivos_nao_conformidade
            WHERE ativo = 1
            ORDER BY id ASC
        ');

        $rows = $stmt->fetchAll();

        json(array_map(fn($r) => [
            'id'        => (int) $r['id'],
            'codigo'    => $r['codigo'],
            'descricao' => $r['descricao'],
        ], $rows));
    }
}
