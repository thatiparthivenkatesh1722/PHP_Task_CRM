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
        $industry     = trim($industry);

        if ($company_name == "" || $industry == "" || $created_by == "") {
            sendResponse(false, "All fields are required");
        }
        if (strlen($company_name) < 2) {
            sendResponse(false, "Company name must be at least 2 characters");
        }

        $this->db->query("INSERT INTO company (company_name, industry, created_by, status) VALUES (:company_name, :industry, :created_by, 'active')");
        $status = $this->db->create([
            "company_name" => $company_name,
            "industry"     => $industry,
            "created_by"   => $created_by
        ]);

        if (!$status) sendResponse(false, "Company creation failed");
        sendResponse(true, "Company created successfully");
    }

    public function list($user_id, $page = 1, $per_page = 5, $status = 'active')
    {
        if ($user_id == "") sendResponse(false, "User id required");

        $page     = max(1, (int)$page);
        $per_page = max(1, (int)$per_page);
        $offset   = ($page - 1) * $per_page;

        $this->db->query("SELECT COUNT(*) as total FROM company WHERE created_by = :user_id AND status = :status");
        $count       = $this->db->first(["user_id" => $user_id, "status" => $status]);
        $total       = (int)($count["total"] ?? 0);
        $total_pages = max(1, (int)ceil($total / $per_page));

        $this->db->query("SELECT * FROM company WHERE created_by = :user_id AND status = :status ORDER BY id DESC LIMIT $per_page OFFSET $offset");
        $companies = $this->db->get(["user_id" => $user_id, "status" => $status]);

        sendResponse(true, "Companies fetched successfully", $companies, [
            "total"       => $total,
            "page"        => $page,
            "per_page"    => $per_page,
            "total_pages" => $total_pages
        ]);
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
        if (!$status) sendResponse(false, "Failed to update company");
        sendResponse(true, "Company updated successfully");
    }

    public function delete($company_id)
    {
        if ($company_id == "") sendResponse(false, "Company ID is required");

        $this->db->update("employee",   ["status" => "inactive", "updated_at" => date('Y-m-d H:i:s')], ["company_id" => $company_id]);
        $this->db->update("department", ["status" => "inactive", "updated_at" => date('Y-m-d H:i:s')], ["company_id" => $company_id]);
        $status = $this->db->update("company", ["status" => "inactive", "updated_at" => date('Y-m-d H:i:s')], ["id" => $company_id]);

        if (!$status) sendResponse(false, "Failed to deactivate company");
        sendResponse(true, "Company deactivated successfully");
    }

    public function restore($company_id)
    {
        if ($company_id == "") sendResponse(false, "Company ID is required");

        $status = $this->db->update("company", ["status" => "active", "updated_at" => date('Y-m-d H:i:s')], ["id" => $company_id]);
        if (!$status) sendResponse(false, "Failed to restore company");
        sendResponse(true, "Company restored successfully");
    }
}
