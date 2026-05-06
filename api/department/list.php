<?php

header("Content-Type: application/json");

require_once __DIR__ . "/../controllers/DepartmentController.php";

$department = new DepartmentController();
$department->list();