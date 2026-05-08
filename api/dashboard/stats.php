<?php

header("Content-Type: application/json");

require_once __DIR__ . "/../controllers/DashboardController.php";

$user_id = $_GET["user_id"] ?? "";

$dashboard = new DashboardController();
$dashboard->stats($user_id);
