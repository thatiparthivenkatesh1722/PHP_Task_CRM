<?php
header("Content-Type: application/json");
require_once __DIR__ . "/../controllers/SalaryController.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    echo json_encode(["status" => false, "message" => "POST method only"]);
    exit;
}

$salary_id = $_POST["salary_id"] ?? "";

$sal = new SalaryController();
$sal->delete($salary_id);
