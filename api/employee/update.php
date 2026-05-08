<?php
header("Content-Type: application/json");
require_once __DIR__ . "/../controllers/EmployeeController.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    echo json_encode(["status" => false, "message" => "POST method only"]);
    exit;
}

$employee_id = $_POST["employee_id"] ?? "";
$first_name = $_POST["first_name"] ?? "";
$last_name = $_POST["last_name"] ?? "";
$email = $_POST["email"] ?? "";
$phone = $_POST["phone"] ?? "";
$company_id = $_POST["company_id"] ?? "";
$department_id = $_POST["department_id"] ?? "";
$role = $_POST["role"] ?? "";
$status = $_POST["status"] ?? "";

$emp = new EmployeeController();
$emp->update($employee_id, $first_name, $last_name, $email, $phone, $company_id, $department_id, $role, $status);
