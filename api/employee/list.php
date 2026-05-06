<?php

header("Content-Type: application/json");

require_once __DIR__ . "/../controllers/EmployeeController.php";

$employee = new EmployeeController();
$employee->list();