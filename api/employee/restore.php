<?php
header("Content-Type: application/json");
require_once __DIR__ . "/../controllers/EmployeeController.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    sendResponse(false, "POST method only");
}
$employee_id = $_POST["employee_id"] ?? "";
$emp = new EmployeeController();
$emp->restore($employee_id);
