<?php

header("Content-Type: application/json");

require_once __DIR__ . "/../controllers/DepartmentController.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    sendResponse(false, "POST method only");
}

$department_name = $_POST["department_name"] ?? "";
$company_id = $_POST["company_id"] ?? "";

$department = new DepartmentController();
$department->create($department_name, $company_id);