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

    public static function store(array $params): void
    {
        $data = body();
        $db   = Database::connection();

        if (empty($data['id_motivo'])) {
            json(['erro' => 'Campo obrigatório: id_motivo'], 422);
        }

        $stmt = $db->prepare('SELECT id, codigo FROM entregas WHERE id = ?');
        $stmt->execute([$params['id']]);
        $entrega = $stmt->fetch();

        if (!$entrega) {
            json(['erro' => 'Entrega não encontrada'], 404);
        }

        $stmt = $db->prepare('SELECT id, codigo, descricao, ativo FROM motivos_nao_conformidade WHERE id = ?');
        $stmt->execute([$data['id_motivo']]);
        $motivo = $stmt->fetch();

        if (!$motivo) {
            json(['erro' => 'Motivo de não conformidade não encontrado'], 404);
        }

        if ((int) $motivo['ativo'] !== 1) {
            json(['erro' => 'Motivo de não conformidade inativo'], 422);
        }

        $descricao = isset($data['descricao']) ? trim($data['descricao']) : null;
        $descricao = $descricao === '' ? null : $descricao;

        $stmt = $db->prepare('
            INSERT INTO nao_conformidades (id_entrega, id_motivo, descricao)
            VALUES (?, ?, ?)
        ');
        $stmt->execute([
            (int) $params['id'],
            (int) $data['id_motivo'],
            $descricao,
        ]);

        $id = $db->lastInsertId();

        $stmt = $db->prepare('SELECT id, descricao, created_at FROM nao_conformidades WHERE id = ?');
        $stmt->execute([$id]);
        $naoConformidade = $stmt->fetch();

        json([
            'id' => (int) $naoConformidade['id'],
            'entrega' => [
                'id'     => (int) $entrega['id'],
                'codigo' => $entrega['codigo'],
            ],
            'motivo' => [
                'id'        => (int) $motivo['id'],
                'codigo'    => $motivo['codigo'],
                'descricao' => $motivo['descricao'],
            ],
            'descricao'  => $naoConformidade['descricao'],
            'created_at' => $naoConformidade['created_at'],
        ], 201);
    }

    public static function indexByEntrega(array $params): void
    {
        $db = Database::connection();

        $stmt = $db->prepare('SELECT id, codigo FROM entregas WHERE id = ?');
        $stmt->execute([$params['id']]);
        $entrega = $stmt->fetch();

        if (!$entrega) {
            json(['erro' => 'Entrega não encontrada'], 404);
        }

        $stmt = $db->prepare('
            SELECT nc.id, nc.descricao, nc.created_at,
                   m.id AS motivo_id, m.codigo AS motivo_codigo, m.descricao AS motivo_descricao
            FROM nao_conformidades nc
            JOIN motivos_nao_conformidade m ON m.id = nc.id_motivo
            WHERE nc.id_entrega = ?
            ORDER BY nc.created_at ASC, nc.id ASC
        ');
        $stmt->execute([$params['id']]);
        $rows = $stmt->fetchAll();

        json([
            'entrega' => [
                'id'     => (int) $entrega['id'],
                'codigo' => $entrega['codigo'],
            ],
            'nao_conformidades' => array_map(fn($r) => [
                'id' => (int) $r['id'],
                'motivo' => [
                    'id'        => (int) $r['motivo_id'],
                    'codigo'    => $r['motivo_codigo'],
                    'descricao' => $r['motivo_descricao'],
                ],
                'descricao'  => $r['descricao'],
                'created_at' => $r['created_at'],
            ], $rows),
        ]);
    }
}
