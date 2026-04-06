<?php

class Database extends PDO
{
    private $stmt;

    public function __construct()
    {
        $config = require __DIR__ . '/../config/config.php';
        $db = $config['db'];

        parent::__construct(
            "mysql:host={$db['host']};dbname={$db['name']};charset={$db['charset']}",
            $db['user'],
            $db['pass']
        );

        $this->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    // Persiapkan query
    public function prepareQuery(string $query): void
    {
        $this->stmt = $this->prepare($query);
    }

    // Bind parameter ke query
    public function bind($param, $value, $type = null): void
    {
        if (is_null($type)) {
            switch (true) {
                case is_int($value):
                    $type = PDO::PARAM_INT;
                    break;
                case is_bool($value):
                    $type = PDO::PARAM_BOOL;
                    break;
                case is_null($value):
                    $type = PDO::PARAM_NULL;
                    break;
                default:
                    $type = PDO::PARAM_STR;
            }
        }
        $this->stmt->bindValue($param, $value, $type);
    }

    // Eksekusi query
    public function execute(): bool
    {
        return $this->stmt->execute();
    }

    // Ambil semua hasil
    public function resultSet(): array
    {
        $this->execute();
        return $this->stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Ambil satu baris
    public function single(): array|false
    {
        $this->execute();
        return $this->stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Hitung jumlah baris
    public function rowCount(): int
    {
        return $this->stmt->rowCount();
    }

    // Ambil ID terakhir yang diinsert
    public function lastInsertId(?string $name = null): string|false
    {
        return parent::lastInsertId($name);
    }
}