<?php

header("Content-Type: application/json");

require_once __DIR__ . "/controllers/AuthController.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    sendResponse(false, "POST method only");
}

$first_name = $_POST["first_name"] ?? "";
$last_name = $_POST["last_name"] ?? "";
$email = $_POST["email"] ?? "";
$phone = $_POST["phone"] ?? "";
$dob = $_POST["dob"] ?? "";
$pan = $_POST["pan"] ?? "";
$password = $_POST["password"] ?? "";

$auth = new AuthController();
$auth->register($first_name, $last_name, $email, $phone, $dob, $pan, $password);