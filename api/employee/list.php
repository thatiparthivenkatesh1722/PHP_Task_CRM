<?php

header("Content-Type: application/json");

require_once __DIR__ . "/../controllers/EmployeeController.php";

$user_id = $_GET["user_id"] ?? "";

$employee = new EmployeeController();
$employee->list($user_id);