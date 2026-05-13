<?php

require_once __DIR__ . "/../utils/db.php";
require_once __DIR__ . "/../utils/functions.php";

class EmployeeController
{
    private $db;

    function __construct()
    {
        $this->db = new DB();
    }

    public function create($first_name, $last_name, $email, $phone, $company_id, $department_id, $role, $join_date)
    {
        if ($first_name == "" || $last_name == "" || $email == "" || $phone == "" || $company_id == "" || $department_id == "" || $role == "" || $join_date == "") {
            sendResponse(false, "All fields are required");
        }

        $this->db->query("INSERT INTO employee (first_name, last_name, email, phone, company_id, department_id, role, join_date, status) VALUES (:first_name, :last_name, :email, :phone, :company_id, :department_id, :role, :join_date, 'active')");
        $status = $this->db->create([
            "first_name"    => $first_name,
            "last_name"     => $last_name,
            "email"         => $email,
            "phone"         => $phone,
            "company_id"    => $company_id,
            "department_id" => $department_id,
            "role"          => $role,
            "join_date"     => $join_date
        ]);

        if (!$status) sendResponse(false, "Employee creation failed");
        sendResponse(true, "Employee created successfully");
    }

    public function list($user_id, $page = 1, $per_page = 5, $status = 'active')
    {
        if ($user_id == "") sendResponse(false, "User id required");

        $page     = max(1, (int)$page);
        $per_page = max(1, (int)$per_page);
        $offset   = ($page - 1) * $per_page;

        $this->db->query("SELECT COUNT(*) as total FROM employee e JOIN company c ON c.id = e.company_id WHERE c.created_by = :user_id AND e.status = :status");
        $count       = $this->db->first(["user_id" => $user_id, "status" => $status]);
        $total       = (int)($count["total"] ?? 0);
        $total_pages = max(1, (int)ceil($total / $per_page));

        $this->db->query("SELECT e.id, e.first_name, e.last_name, e.email, e.phone, e.company_id, e.department_id, e.role, e.join_date, e.status, c.company_name, d.department_name, s.net FROM employee e JOIN company c ON c.id = e.company_id JOIN department d ON d.id = e.department_id LEFT JOIN salary s ON s.employee_id = e.id AND s.id = (SELECT MAX(id) FROM salary WHERE employee_id = e.id) WHERE c.created_by = :user_id AND e.status = :status ORDER BY e.id DESC LIMIT $per_page OFFSET $offset");
        $employees = $this->db->get(["user_id" => $user_id, "status" => $status]);

        sendResponse(true, "Employees fetched successfully", $employees, [
            "total"       => $total,
            "page"        => $page,
            "per_page"    => $per_page,
            "total_pages" => $total_pages
        ]);
    }

    public function update($employee_id, $first_name, $last_name, $email, $phone, $company_id, $department_id, $role, $status)
    {
        if (empty($employee_id) || empty($first_name) || empty($last_name) || empty($email) || empty($phone)) {
            sendResponse(false, "Required fields are missing");
        }

        $status_bool = $this->db->update(
            "employee",
            [
                "first_name"    => $first_name,
                "last_name"     => $last_name,
                "email"         => $email,
                "phone"         => $phone,
                "company_id"    => $company_id,
                "department_id" => $department_id,
                "role"          => $role,
                "status"        => $status,
                "updated_at"    => date('Y-m-d H:i:s')
            ],
            ["id" => $employee_id]
        );

        if (!$status_bool) sendResponse(false, "Failed to update employee");
        sendResponse(true, "Employee updated successfully");
    }

    public function delete($employee_id)
    {
        if ($employee_id == "") sendResponse(false, "Employee ID is required");

        $status = $this->db->update(
            "employee",
            ["status" => "inactive", "updated_at" => date('Y-m-d H:i:s')],
            ["id" => $employee_id]
        );

        if (!$status) sendResponse(false, "Failed to deactivate employee");
        sendResponse(true, "Employee deactivated successfully");
    }

    public function restore($employee_id)
    {
        if ($employee_id == "") sendResponse(false, "Employee ID is required");
        $status = $this->db->update("employee", ["status" => "active", "updated_at" => date('Y-m-d H:i:s')], ["id" => $employee_id]);
        if (!$status) sendResponse(false, "Failed to restore employee");
        sendResponse(true, "Employee restored successfully");
    }
}
