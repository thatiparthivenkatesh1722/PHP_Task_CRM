<?php

require_once __DIR__ . "/../utils/db.php";
require_once __DIR__ . "/../utils/functions.php";

class DepartmentController
{
    private $db;

    function __construct()
    {
        $this->db = new DB();
    }

    public function create($department_name, $company_id)
    {
        $department_name = trim($department_name);
        $company_id      = trim($company_id);

        if ($department_name == "" || $company_id == "") {
            sendResponse(false, "All fields are required");
        }

        $this->db->query("INSERT INTO department (department_name, company_id, status) VALUES (:department_name, :company_id, 'active')");
        $status = $this->db->create([
            "department_name" => $department_name,
            "company_id"      => $company_id
        ]);

        if (!$status) sendResponse(false, "Department creation failed");
        sendResponse(true, "Department created successfully");
    }

    public function list($user_id, $page = 1, $per_page = 5, $status = 'active')
    {
        if ($user_id == "") sendResponse(false, "User id required");

        $page     = max(1, (int)$page);
        $per_page = max(1, (int)$per_page);
        $offset   = ($page - 1) * $per_page;

        $this->db->query("SELECT COUNT(*) as total FROM department d JOIN company c ON c.id = d.company_id WHERE c.created_by = :user_id AND d.status = :status");
        $count       = $this->db->first(["user_id" => $user_id, "status" => $status]);
        $total       = (int)($count["total"] ?? 0);
        $total_pages = max(1, (int)ceil($total / $per_page));

        $this->db->query("SELECT d.id, d.department_name, d.company_id, d.status, c.company_name FROM department d JOIN company c ON c.id = d.company_id WHERE c.created_by = :user_id AND d.status = :status ORDER BY d.id DESC LIMIT $per_page OFFSET $offset");
        $departments = $this->db->get(["user_id" => $user_id, "status" => $status]);

        sendResponse(true, "Departments fetched successfully", $departments, [
            "total"       => $total,
            "page"        => $page,
            "per_page"    => $per_page,
            "total_pages" => $total_pages
        ]);
    }

    public function update($department_id, $department_name)
    {
        if (empty($department_id) || empty($department_name)) {
            sendResponse(false, "Department name is required");
        }
        $status = $this->db->update(
            "department",
            ["department_name" => $department_name, "updated_at" => date('Y-m-d H:i:s')],
            ["id" => $department_id]
        );
        if (!$status) sendResponse(false, "Failed to update department");
        sendResponse(true, "Department updated successfully");
    }

    public function delete($department_id)
    {
        if (empty($department_id)) sendResponse(false, "Department id is required");

        $this->db->update("employee",   ["status" => "inactive", "updated_at" => date('Y-m-d H:i:s')], ["department_id" => $department_id]);
        $status = $this->db->update("department", ["status" => "inactive", "updated_at" => date('Y-m-d H:i:s')], ["id" => $department_id]);

        if (!$status) sendResponse(false, "Failed to deactivate department");
        sendResponse(true, "Department deactivated successfully");
    }

    public function restore($department_id)
    {
        if (empty($department_id)) sendResponse(false, "Department id is required");
        $status = $this->db->update("department", ["status" => "active", "updated_at" => date('Y-m-d H:i:s')], ["id" => $department_id]);
        if (!$status) sendResponse(false, "Failed to restore department");
        sendResponse(true, "Department restored successfully");
    }
}