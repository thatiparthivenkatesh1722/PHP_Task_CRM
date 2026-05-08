<?php
header("Content-Type: application/json");
require_once __DIR__ . "/../controllers/AuthController.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    echo json_encode(["status" => false, "message" => "POST method only"]);
    exit;
}

$email = $_POST["email"] ?? "";

$auth = new AuthController();
$auth->forgotPassword($email);
