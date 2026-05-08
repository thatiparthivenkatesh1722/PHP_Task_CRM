<?php

header("Content-Type: application/json");

require_once __DIR__ . "/../controllers/CompanyController.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    sendResponse(false, "POST method only");
}

$company_id = $_POST["company_id"] ?? "";

$company = new CompanyController();
$company->delete($company_id);
