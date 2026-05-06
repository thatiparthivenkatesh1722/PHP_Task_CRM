<?php

header("Content-Type: application/json");

require_once __DIR__ . "/../controllers/CompanyController.php";
$user_id = $_GET["user_id"] ?? "";

$company = new CompanyController();
$company->list($user_id);