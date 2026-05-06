<?php

header("Content-Type: application/json");

require_once __DIR__ . "/controllers/AuthController.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    sendResponse(false, "POST method only");
}

$email = $_POST["email"] ?? "";
$password = $_POST["password"] ?? "";

$auth = new AuthController();
$auth->login($email, $password);