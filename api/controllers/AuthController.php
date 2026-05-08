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

    public function forgotPassword($email)
    {
        if (empty($email)) {
            sendResponse(false, "Email is required");
        }

        $this->db->query("SELECT id FROM users WHERE email = :email");
        $user = $this->db->first(["email" => $email]);

        if (!$user) {
            sendResponse(false, "No account found with this email");
        }

        $otp = sprintf("%06d", mt_rand(1, 999999));
        $expiry = date("Y-m-d H:i:s", strtotime("+15 minutes"));

        $this->db->query("UPDATE users SET reset_otp = :otp, reset_otp_expiry = :expiry WHERE email = :email");
        $this->db->create([
            "otp" => $otp,
            "expiry" => $expiry,
            "email" => $email
        ]);

        error_log("===== OTP FOR $email is $otp =====");

        sendResponse(true, "OTP has been generated. Check the PHP console/logs.", ["otp" => $otp]);
    }

    public function resetPassword($email, $otp, $new_password)
    {
        if (empty($email) || empty($otp) || empty($new_password)) {
            sendResponse(false, "All fields are required");
        }

        if (strlen($new_password) < 6) {
            sendResponse(false, "Password must be at least 6 characters");
        }

        $this->db->query("SELECT id, reset_otp, reset_otp_expiry FROM users WHERE email = :email");
        $user = $this->db->first(["email" => $email]);

        if (!$user || $user["reset_otp"] !== $otp) {
            sendResponse(false, "Invalid OTP");
        }

        if (strtotime($user["reset_otp_expiry"]) < time()) {
            sendResponse(false, "OTP has expired");
        }

        $hashed = password_hash($new_password, PASSWORD_DEFAULT);

        $this->db->query("UPDATE users SET password = :password, reset_otp = NULL, reset_otp_expiry = NULL WHERE email = :email");
        $status = $this->db->create([
            "password" => $hashed,
            "email" => $email
        ]);

        if (!$status) {
            sendResponse(false, "Failed to reset password");
        }

        sendResponse(true, "Password has been reset successfully. You can now log in.");
    }
}