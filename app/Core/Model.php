<?php

namespace App\Core;

use PDO;

abstract class Model
{
    protected string $table = '';
    protected string $primaryKey = 'id';
    protected array $fillable = [];

    public function getTable(): string
    {
        return $this->table;
    }

    public function find(int|string $id): ?array
    {
        $sql = "SELECT * FROM `{$this->table}` WHERE `{$this->primaryKey}` = :id LIMIT 1";
        return Database::fetch($sql, ['id' => $id]);
    }

    public function all(array $columns = ['*'], string $orderBy = 'id DESC', int $limit = 0): array
    {
        $cols = implode(', ', array_map(fn($c) => $c === '*' ? '*' : "`$c`", $columns));
        $sql = "SELECT {$cols} FROM `{$this->table}` ORDER BY {$orderBy}";
        if ($limit > 0) {
            $sql .= " LIMIT {$limit}";
        }
        return Database::fetchAll($sql);
    }

    public function where(string $column, mixed $value, string $operator = '='): array
    {
        $sql = "SELECT * FROM `{$this->table}` WHERE `{$column}` {$operator} :val";
        return Database::fetchAll($sql, ['val' => $value]);
    }

    public function firstWhere(string $column, mixed $value, string $operator = '='): ?array
    {
        $sql = "SELECT * FROM `{$this->table}` WHERE `{$column}` {$operator} :val LIMIT 1";
        return Database::fetch($sql, ['val' => $value]);
    }

    public function create(array $data): string|false
    {
        // Filter by fillable if defined
        if (!empty($this->fillable)) {
            $data = array_intersect_key($data, array_flip($this->fillable));
        }

        if (empty($data)) {
            return false;
        }

        $fields = array_keys($data);
        $placeholders = array_map(fn($f) => ":{$f}", $fields);

        $sql = sprintf(
            "INSERT INTO `%s` (`%s`) VALUES (%s)",
            $this->table,
            implode('`, `', $fields),
            implode(', ', $placeholders)
        );

        return Database::insert($sql, $data);
    }

    public function update(int|string $id, array $data): int
    {
        if (!empty($this->fillable)) {
            $data = array_intersect_key($data, array_flip($this->fillable));
        }

        if (empty($data)) {
            return 0;
        }

        $setParts = [];
        $params = ['_id' => $id];

        foreach ($data as $col => $val) {
            $setParts[] = "`{$col}` = :{$col}";
            $params[$col] = $val;
        }

        $sql = sprintf(
            "UPDATE `%s` SET %s WHERE `%s` = :_id",
            $this->table,
            implode(', ', $setParts),
            $this->primaryKey
        );

        return Database::execute($sql, $params);
    }

    public function delete(int|string $id): int
    {
        $sql = "DELETE FROM `{$this->table}` WHERE `{$this->primaryKey}` = :id";
        return Database::execute($sql, ['id' => $id]);
    }

    public function count(array $conditions = []): int
    {
        $sql = "SELECT COUNT(*) as total FROM `{$this->table}`";
        $params = [];

        if (!empty($conditions)) {
            $clauses = [];
            foreach ($conditions as $col => $val) {
                $clauses[] = "`{$col}` = :{$col}";
                $params[$col] = $val;
            }
            $sql .= " WHERE " . implode(' AND ', $clauses);
        }

        $res = Database::fetch($sql, $params);
        return (int)($res['total'] ?? 0);
    }

    public function paginate(int $page = 1, int $perPage = 15, array $conditions = [], string $orderBy = 'id DESC'): array
    {
        $page = max(1, $page);
        $offset = ($page - 1) * $perPage;

        $total = $this->count($conditions);
        $lastPage = (int)ceil($total / $perPage);

        $sql = "SELECT * FROM `{$this->table}`";
        $params = [];

        if (!empty($conditions)) {
            $clauses = [];
            foreach ($conditions as $col => $val) {
                $clauses[] = "`{$col}` = :{$col}";
                $params[$col] = $val;
            }
            $sql .= " WHERE " . implode(' AND ', $clauses);
        }

        $sql .= " ORDER BY {$orderBy} LIMIT {$perPage} OFFSET {$offset}";
        $data = Database::fetchAll($sql, $params);

        return [
            'data'         => $data,
            'total'        => $total,
            'per_page'     => $perPage,
            'current_page' => $page,
            'last_page'    => $lastPage,
            'has_more'     => $page < $lastPage
        ];
    }
}
