<?php
header("Content-Type: application/json");
require_once __DIR__ . "/../controllers/CompanyController.php";

$user_id  = $_GET["user_id"]  ?? "";
$page     = $_GET["page"]     ?? 1;
$per_page = $_GET["per_page"] ?? 5;
$status   = $_GET["status"]   ?? "active";

$company = new CompanyController();
$company->list($user_id, $page, $per_page, $status);