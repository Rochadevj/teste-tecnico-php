<?php

namespace App\Controllers;

use App\Database;

class TransportadoraController
{
    public static function index(array $params): void
    {
        $db             = Database::connection();
        $incluirInativas = ($_GET['incluir_inativas'] ?? 'false') === 'true';

        $sql  = 'SELECT * FROM transportadoras';
        $sql .= $incluirInativas ? '' : ' WHERE deleted_at IS NULL';
        $sql .= ' ORDER BY nome_fantasia ASC';

        $rows = $db->query($sql)->fetchAll();

        json(array_map([self::class, 'format'], $rows));
    }

    public static function store(array $params): void
    {
        $data = body();
        $db   = Database::connection();

        $cnpj         = trim($data['cnpj'] ?? '');
        $nomeFantasia = trim($data['nome_fantasia'] ?? '');

        if (!self::validarCnpj($cnpj)) {
            json(['erro' => 'CNPJ inválido. Deve ter exatamente 14 caracteres alfanuméricos [A-Z0-9].'], 422);
        }

        if ($nomeFantasia === '') {
            json(['erro' => 'Campo obrigatório: nome_fantasia'], 422);
        }

        $stmt = $db->prepare('SELECT id FROM transportadoras WHERE cnpj = ?');
        $stmt->execute([$cnpj]);
        if ($stmt->fetch()) {
            json(['erro' => 'CNPJ já cadastrado'], 422);
        }

        $stmt = $db->prepare('INSERT INTO transportadoras (cnpj, nome_fantasia) VALUES (?, ?)');
        $stmt->execute([$cnpj, $nomeFantasia]);

        $id   = $db->lastInsertId();
        $stmt = $db->prepare('SELECT * FROM transportadoras WHERE id = ?');
        $stmt->execute([$id]);

        json(self::format($stmt->fetch()), 201);
    }

    public static function show(array $params): void
    {
        $db   = Database::connection();
        $stmt = $db->prepare('SELECT * FROM transportadoras WHERE id = ?');
        $stmt->execute([$params['id']]);
        $row = $stmt->fetch();

        if (!$row) {
            json(['erro' => 'Transportadora não encontrada'], 404);
        }

        json(self::format($row));
    }

    public static function update(array $params): void
    {
        $data = body();
        $db   = Database::connection();

        $stmt = $db->prepare('SELECT * FROM transportadoras WHERE id = ?');
        $stmt->execute([$params['id']]);
        $row = $stmt->fetch();

        if (!$row) {
            json(['erro' => 'Transportadora não encontrada'], 404);
        }

        $cnpj         = trim($data['cnpj'] ?? $row['cnpj']);
        $nomeFantasia = trim($data['nome_fantasia'] ?? $row['nome_fantasia']);

        if (!self::validarCnpj($cnpj)) {
            json(['erro' => 'CNPJ inválido. Deve ter exatamente 14 caracteres alfanuméricos [A-Z0-9].'], 422);
        }

        // Verificar duplicidade de CNPJ (exceto o próprio registro)
        $stmt = $db->prepare('SELECT id FROM transportadoras WHERE cnpj = ? AND id != ?');
        $stmt->execute([$cnpj, $params['id']]);
        if ($stmt->fetch()) {
            json(['erro' => 'CNPJ já cadastrado em outra transportadora'], 422);
        }

        $stmt = $db->prepare('UPDATE transportadoras SET cnpj = ?, nome_fantasia = ? WHERE id = ?');
        $stmt->execute([$cnpj, $nomeFantasia, $params['id']]);

        $stmt = $db->prepare('SELECT * FROM transportadoras WHERE id = ?');
        $stmt->execute([$params['id']]);

        json(self::format($stmt->fetch()));
    }

    public static function desativar(array $params): void
    {
        $db   = Database::connection();
        $stmt = $db->prepare('SELECT * FROM transportadoras WHERE id = ?');
        $stmt->execute([$params['id']]);
        $row = $stmt->fetch();

        if (!$row) {
            json(['erro' => 'Transportadora não encontrada'], 404);
        }

        if ($row['deleted_at'] !== null) {
            json(['erro' => 'Transportadora já está inativa'], 422);
        }

        $stmt = $db->prepare('UPDATE transportadoras SET deleted_at = NOW() WHERE id = ?');
        $stmt->execute([$params['id']]);

        $stmt = $db->prepare('SELECT * FROM transportadoras WHERE id = ?');
        $stmt->execute([$params['id']]);

        json(self::format($stmt->fetch()));
    }

    public static function reativar(array $params): void
    {
        $db   = Database::connection();
        $stmt = $db->prepare('SELECT * FROM transportadoras WHERE id = ?');
        $stmt->execute([$params['id']]);
        $row = $stmt->fetch();

        if (!$row) {
            json(['erro' => 'Transportadora não encontrada'], 404);
        }

        if ($row['deleted_at'] === null) {
            json(['erro' => 'Transportadora já está ativa'], 422);
        }

        $stmt = $db->prepare('UPDATE transportadoras SET deleted_at = NULL WHERE id = ?');
        $stmt->execute([$params['id']]);

        $stmt = $db->prepare('SELECT * FROM transportadoras WHERE id = ?');
        $stmt->execute([$params['id']]);

        json(self::format($stmt->fetch()));
    }

    private static function validarCnpj(string $cnpj): bool
    {
        return strlen($cnpj) === 14 && preg_match('/^[A-Z0-9]+$/', $cnpj) === 1;
    }

    private static function format(array $row): array
    {
        return [
            'id'           => (int) $row['id'],
            'cnpj'         => $row['cnpj'],
            'nome_fantasia' => $row['nome_fantasia'],
            'ativa'        => $row['deleted_at'] === null,
            'created_at'   => $row['created_at'],
            'updated_at'   => $row['updated_at'],
            'deleted_at'   => $row['deleted_at'],
        ];
    }
}
