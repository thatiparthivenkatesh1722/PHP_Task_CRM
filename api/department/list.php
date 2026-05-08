<?php

header("Content-Type: application/json");

require_once __DIR__ . "/../controllers/DepartmentController.php";

$user_id = $_GET["user_id"] ?? "";

$department = new DepartmentController();
$department->list($user_id);