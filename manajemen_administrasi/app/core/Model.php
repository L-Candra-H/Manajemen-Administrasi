<?php

class Model
{
    protected $db;
    protected $stmt;

    public function __construct()
    {
        try {
            $config = require __DIR__ . '/../config/config.php';
            $db = $config['db'];

            $this->db = new PDO(
                "mysql:host={$db['host']};dbname={$db['name']};charset={$db['charset']}",
                $db['user'],
                $db['pass']
            );
            $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            die("Koneksi database gagal: " . $e->getMessage());
        }
    }

    // Persiapkan query
    public function query($sql)
    {
        $this->stmt = $this->db->prepare($sql);

    }

    // Bind parameter ke query
    public function bind($param, $value, $type = null)
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
    public function execute()
    {
        return $this->stmt->execute();
    }

    // Ambil semua hasil
    public function resultSet()
    {
        $this->execute();
        return $this->stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Ambil satu baris
    public function single()
    {
        $this->execute();
        return $this->stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Hitung jumlah baris yang terpengaruh
    public function rowCount()
    {
        return $this->stmt->rowCount();
    }

    // Ambil ID terakhir yang diinsert
    public function lastInsertId()
    {
        return $this->db->lastInsertId();
    }

    // Shortcut untuk eksekusi langsung
    public function run($sql, $params = [])
    {
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }

}