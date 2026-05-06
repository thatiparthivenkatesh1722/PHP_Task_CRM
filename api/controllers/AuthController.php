<?php

require_once __DIR__ . "/../utils/db.php";
require_once __DIR__ . "/../utils/functions.php";

class AuthController
{
    private $db;

    function __construct()
    {
        $this->db = new DB();
    }

    private function generateToken()
    {
        return bin2hex(random_bytes(10));
    }

    public function register($first_name, $last_name, $email, $phone, $dob, $pan, $password)
{
    $first_name = trim($first_name);
    $last_name = trim($last_name);
    $email = strtolower(trim($email));
    $phone = trim($phone);
    $dob = trim($dob);
    $pan = strtoupper(trim($pan));
    $password = trim($password);

    if ($first_name == "" || $last_name == "" || $email == "" || $phone == "" || $dob == "" || $pan == "" || $password == "") {
        sendResponse(false, "All fields are required");
    }

    if (strlen($first_name) < 2 || strlen($last_name) < 2) {
        sendResponse(false, "First and Last name must be at least 2 characters");
    }

    if (!preg_match("/^[a-zA-Z]+$/", $first_name) || !preg_match("/^[a-zA-Z]+$/", $last_name)) {
        sendResponse(false, "Name should contain only letters");
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        sendResponse(false, "Invalid email format");
    }

    if (!preg_match("/^[6-9][0-9]{9}$/", $phone)) {
        sendResponse(false, "Invalid phone number");
    }

    if (!strtotime($dob)) {
        sendResponse(false, "Invalid date of birth");
    }

    $age = date_diff(date_create($dob), date_create("today"))->y;
    if ($age < 18) {
        sendResponse(false, "User must be at least 18 years old");
    }

    if (!preg_match("/^[A-Z]{5}[0-9]{4}[A-Z]$/", $pan)) {
        sendResponse(false, "Invalid PAN format");
    }

    if (strlen($password) < 6) {
        sendResponse(false, "Password must be at least 6 characters");
    }

    $this->db->query("SELECT id FROM users WHERE email = :email OR phone = :phone OR pan = :pan");

    $existingUser = $this->db->first([
        "email" => $email,
        "phone" => $phone,
        "pan" => $pan
    ]);

    if ($existingUser) {
        sendResponse(false, "Email, phone or PAN already registered");
    }

    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    $this->db->query("
        INSERT INTO users (first_name, last_name, email, phone, dob, pan, password)
        VALUES (:first_name, :last_name, :email, :phone, :dob, :pan, :password)
    ");

    $status = $this->db->create([
        "first_name" => $first_name,
        "last_name" => $last_name,
        "email" => $email,
        "phone" => $phone,
        "dob" => $dob,
        "pan" => $pan,
        "password" => $hashedPassword
    ]);

    if (!$status) {
        sendResponse(false, "Registration failed");
    }

    sendResponse(true, "Registered successfully");
}

    public function login($email, $password)
    {
        $email = strtolower(trim($email));
        $password = trim($password);

        if ($email == "" || $password == "") {
            sendResponse(false, "Email and password are required");
        }

        $this->db->query("SELECT * FROM users WHERE email = :email AND status = 'active'");

        $user = $this->db->first([
            "email" => $email
        ]);

        if (!$user) {
            sendResponse(false, "Invalid email or password");
        }

        if (!password_verify($password, $user["password"])) {
            sendResponse(false, "Invalid email or password");
        }

        $token = $this->generateToken();
        $expiry_at = date("Y-m-d H:i:s", strtotime("+1 hour"));

        $this->db->query("
            INSERT INTO user_tokens (user_id, token, expiry_at)
            VALUES (:user_id, :token, :expiry_at)
        ");

        $tokenStatus = $this->db->create([
            "user_id" => $user["id"],
            "token" => $token,
            "expiry_at" => $expiry_at
        ]);

        if (!$tokenStatus) {
            sendResponse(false, "Token generation failed");
        }

        unset($user["password"]);

        $user["token"] = $token;
        $user["token_expiry"] = $expiry_at;

        sendResponse(true, "Login successful", $user);
    }
}