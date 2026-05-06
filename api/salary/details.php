<?php

header("Content-Type: application/json");

require_once __DIR__ . "/../controllers/SalaryController.php";

$employee_id = $_GET["employee_id"] ?? "";

$salary = new SalaryController();
$salary->details($employee_id);