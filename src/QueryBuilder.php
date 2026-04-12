<?php

namespace Artemis;

use PDO;
use Artemis\Response;

class QueryBuilder
{
    private PDO $pdo;
    private string $table;
    private array $wheres = [];
    private array $bindings = [];

    public function __construct(PDO $pdo, string $table)
    {
        $this->pdo   = $pdo;
        $this->table = $table;
    }

    public function where(string $column, mixed $value): self
    {
        $this->wheres[]   = "$column = ?";
        $this->bindings[] = $value;
        return $this;
    }

    public function get(): array
    {
        $sql  = "SELECT * FROM {$this->table}";
        $sql .= $this->buildWhere();

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($this->bindings);
        return $stmt->fetchAll();
    }

    public function first(): ?array
    {
        $sql  = "SELECT * FROM {$this->table}";
        $sql .= $this->buildWhere();
        $sql .= " LIMIT 1";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($this->bindings);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    public function insert(array $data): bool
    {
        try {
            $columns      = implode(', ', array_keys($data));
            $placeholders = implode(', ', array_fill(0, count($data), '?'));

            $sql  = "INSERT INTO {$this->table} ($columns) VALUES ($placeholders)";
            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute(array_values($data));
        } catch (\PDOException $e) {
            if ($e->getCode() === '23000') {
                throw new \RuntimeException('Data already exists', 409);
            }
            throw new \RuntimeException('Database error', 500);
        }
    }

    public function update(array $data): bool
    {
        $sets = implode(', ', array_map(fn($col) => "$col = ?", array_keys($data)));

        $sql  = "UPDATE {$this->table} SET $sets";
        $sql .= $this->buildWhere();

        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([...array_values($data), ...$this->bindings]);
    }

    public function delete(): bool
    {
        $sql  = "DELETE FROM {$this->table}";
        $sql .= $this->buildWhere();

        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute($this->bindings);
    }

    public function lastInsertId(): string
    {
        return $this->pdo->lastInsertId();
    }

    private function buildWhere(): string
    {
        if (empty($this->wheres)) {
            return '';
        }
        return ' WHERE ' . implode(' AND ', $this->wheres);
    }
}