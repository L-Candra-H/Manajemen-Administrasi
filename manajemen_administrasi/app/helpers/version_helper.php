<?php
require_once __DIR__ . '/../core/Database.php';

function getAppVersion(): array|false {
    $db = new Database(); // buat koneksi baru
    $stmt = $db->query("SELECT version, release_date, description 
                        FROM app_version 
                        ORDER BY id DESC LIMIT 1");
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function getAllVersions(): array {
    $db = new Database(); // buat koneksi baru
    $stmt = $db->query("SELECT version, release_date, description 
                        FROM app_version 
                        ORDER BY id ASC");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function addVersion(string $version, string $release_date, string $description): bool {
    $db = new Database();
    $stmt = $db->prepare("INSERT INTO app_version (version, release_date, description) 
                          VALUES (:version, :release_date, :description)");
    return $stmt->execute([
        ':version' => $version,
        ':release_date' => $release_date,
        ':description' => $description
    ]);
}