<?php

define("DB_HOST", "localhost");
define("DB_PORT", "5432");
define("DB_NAME", "crm");
define("DB_USERNAME", "postgres");
define("DB_PASSWORD", "postgres");

function getPDO()
{
    try {
        $pdo = new PDO(
            "pgsql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME,
            DB_USERNAME,
            DB_PASSWORD
        );

        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $pdo;

    } catch (PDOException $e) {
        echo json_encode([
            "status" => false,
            "message" => "Database connection failed",
            "error" => $e->getMessage()
        ]);
        exit();
    }
}