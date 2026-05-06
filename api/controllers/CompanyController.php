<?php

require_once __DIR__ . "/../utils/db.php";
require_once __DIR__ . "/../utils/functions.php";

class CompanyController
{
    private $db;

    function __construct()
    {
        $this->db = new DB();
    }

    public function create($company_name, $industry, $created_by)
    {
        $company_name = trim($company_name);
        $industry = trim($industry);

        if ($company_name == "" || $industry == "" || $created_by == "") {
            sendResponse(false, "All fields are required");
        }

        if (strlen($company_name) < 2) {
            sendResponse(false, "Company name must be at least 2 characters");
        }

        $this->db->query("INSERT INTO company (company_name, industry, created_by)
        VALUES (:company_name, :industry, :created_by)
    ");

        $status = $this->db->create([
            "company_name" => $company_name,
            "industry" => $industry,
            "created_by" => $created_by
        ]);

        if (!$status) {
            sendResponse(false, "Company creation failed");
        }

        sendResponse(true, "Company created successfully");
    }

    public function list($user_id)
    {
        if ($user_id == "") {
            sendResponse(false, "User id required");
        }

        $this->db->query("
        SELECT * FROM company
        WHERE created_by = :user_id
        ORDER BY id DESC
    ");

        $companies = $this->db->get([
            "user_id" => $user_id
        ]);

        sendResponse(true, "Companies fetched successfully", $companies);
    }
}
