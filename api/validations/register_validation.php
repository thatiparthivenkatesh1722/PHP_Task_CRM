<?php
function validate($first_name, $last_name, $email, $phone, $dob, $pan, $password){
    
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

    if (strlen($first_name) < 3 || strlen($last_name) < 3) {
        sendResponse(false, "First and Last name must be at least 3 characters");
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
}
?>
