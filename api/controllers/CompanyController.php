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
            "industry"     => $industry,
            "created_by"   => $created_by
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

    public function update($company_id, $company_name, $industry)
    {
        if (empty($company_id) || empty($company_name) || empty($industry)) {
            sendResponse(false, "All fields are required");
        }
        $status = $this->db->update(
            "company",
            ["company_name" => $company_name, "industry" => $industry, "updated_at" => date('Y-m-d H:i:s')],
            ["id" => $company_id]
        );
        if (!$status) {
            sendResponse(false, "Failed to update company");
        }
        sendResponse(true, "Company updated successfully");
    }

    public function delete($company_id)
    {
        if ($company_id == "") {
            sendResponse(false, "Company ID is required");
        }

        // Delete salary_details linked to employees of this company
        $this->db->query("DELETE FROM salary_details WHERE salary_id IN (SELECT id FROM salary WHERE employee_id IN (SELECT id FROM employee WHERE company_id = :company_id))");
        $this->db->delete(["company_id" => $company_id]);

        // Delete salaries linked to employees of this company
        $this->db->query("DELETE FROM salary WHERE employee_id IN (SELECT id FROM employee WHERE company_id = :company_id)");
        $this->db->delete(["company_id" => $company_id]);

        // Delete associated employees
        $this->db->delete(["company_id" => $company_id], "employee");

        // Delete associated departments
        $this->db->delete(["company_id" => $company_id], "department");

        // Delete the company
        $status = $this->db->delete(["id" => $company_id], "company");

        if (!$status) {
            sendResponse(false, "Failed to delete company");
        }

        sendResponse(true, "Company deleted successfully");
    }
}
