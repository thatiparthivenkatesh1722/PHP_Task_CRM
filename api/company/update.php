<?php
header("Content-Type: application/json");
require_once __DIR__ . "/../controllers/CompanyController.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    echo json_encode(["status" => false, "message" => "POST method only"]);
    exit;
}

$company_id = $_POST["company_id"] ?? "";
$company_name = $_POST["company_name"] ?? "";
$industry = $_POST["industry"] ?? "";

$company = new CompanyController();
$company->update($company_id, $company_name, $industry);
