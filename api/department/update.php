<?php
header("Content-Type: application/json");
require_once __DIR__ . "/../controllers/DepartmentController.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    echo json_encode(["status" => false, "message" => "POST method only"]);
    exit;
}

$department_id = $_POST["department_id"] ?? "";
$department_name = $_POST["department_name"] ?? "";

$dept = new DepartmentController();
$dept->update($department_id, $department_name);
