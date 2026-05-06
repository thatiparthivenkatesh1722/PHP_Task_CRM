<?php

header("Content-Type: application/json");

require_once __DIR__ . "/../controllers/CompanyController.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    sendResponse(false, "POST method only");
}

$company_name = $_POST["company_name"] ?? "";
$industry = $_POST["industry"] ?? "";
$created_by = $_POST["created_by"] ?? "";

$company = new CompanyController();
$company->create($company_name, $industry,$created_by);