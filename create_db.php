<?php
try {
    $pdo = new PDO('mysql:host=127.0.0.1', 'root', '');
    $pdo->exec('DROP DATABASE IF EXISTS xbuy');
    $pdo->exec('CREATE DATABASE xbuy CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
    echo "Database recreated successfully.\n";
} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage() . "\n";
}
