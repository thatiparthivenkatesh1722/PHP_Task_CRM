<?php
header("Content-Type: application/json");
require_once __DIR__ . "/../utils/db.php";
require_once __DIR__ . "/../utils/functions.php";

$company_id = $_GET["company_id"] ?? "";

if (empty($company_id)) {
    sendResponse(false, "Company ID required");
}

$db = new DB();
$db->query("SELECT id, department_name FROM department WHERE company_id = :company_id ORDER BY department_name ASC");
$data = $db->get(["company_id" => $company_id]);

sendResponse(true, "Departments fetched", $data);
