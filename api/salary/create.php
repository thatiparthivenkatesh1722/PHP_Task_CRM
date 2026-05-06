<?php

header("Content-Type: application/json");

require_once __DIR__ . "/../controllers/SalaryController.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    sendResponse(false, "POST method only");
}

$employee_id = $_POST["employee_id"] ?? "";
$salary_month = $_POST["salary_month"] ?? "";
$payment_date = $_POST["payment_date"] ?? "";
$components = json_decode($_POST["components"] ?? "[]", true);

$salary = new SalaryController();
$salary->create($employee_id, $salary_month, $payment_date, $components);