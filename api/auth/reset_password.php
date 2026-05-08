<?php
header("Content-Type: application/json");
require_once __DIR__ . "/../controllers/AuthController.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    echo json_encode(["status" => false, "message" => "POST method only"]);
    exit;
}

$email = $_POST["email"] ?? "";
$otp = $_POST["otp"] ?? "";
$new_password = $_POST["new_password"] ?? "";

$auth = new AuthController();
$auth->resetPassword($email, $otp, $new_password);
