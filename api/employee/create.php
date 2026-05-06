<?php

header("Content-Type: application/json");

require_once __DIR__ . "/../controllers/EmployeeController.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    sendResponse(false, "POST method only");
}

$first_name = $_POST["first_name"] ?? "";
$last_name = $_POST["last_name"] ?? "";
$email = $_POST["email"] ?? "";
$phone = $_POST["phone"] ?? "";
$company_id = $_POST["company_id"] ?? "";
$department_id = $_POST["department_id"] ?? "";
$role = $_POST["role"] ?? "";
$join_date = $_POST["join_date"] ?? "";

$employee = new EmployeeController();
$employee->create($first_name, $last_name, $email, $phone, $company_id, $department_id, $role, $join_date);