<?php

header("Content-Type: application/json");

require_once __DIR__ . "/../controllers/SalaryController.php";

$salary = new SalaryController();
$salary->components();