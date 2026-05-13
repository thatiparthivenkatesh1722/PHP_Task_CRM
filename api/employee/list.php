<?php
header("Content-Type: application/json");
require_once __DIR__ . "/../controllers/EmployeeController.php";

$user_id  = $_GET["user_id"]  ?? "";
$page     = $_GET["page"]     ?? 1;
$per_page = $_GET["per_page"] ?? 5;
$status   = $_GET["status"]   ?? "active";

$emp = new EmployeeController();
$emp->list($user_id, $page, $per_page, $status);