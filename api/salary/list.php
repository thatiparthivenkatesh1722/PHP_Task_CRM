<?php

header("Content-Type: application/json");

require_once __DIR__ . "/../controllers/SalaryController.php";

$user_id = $_GET["user_id"] ?? "";

$salary = new SalaryController();
$salary->list($user_id);
