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
        $company_id = trim($company_id);

        if ($department_name == "" || $company_id == "") {
            sendResponse(false, "All fields are required");
        }

        $this->db->query("
            INSERT INTO department (department_name, company_id)
            VALUES (:department_name, :company_id)
        ");

        $status = $this->db->create([
            "department_name" => $department_name,
            "company_id"      => $company_id
        ]);

        if (!$status) {
            sendResponse(false, "Department creation failed");
        }

        sendResponse(true, "Department created successfully");
    }

    public function list($user_id)
    {
        if ($user_id == "") {
            sendResponse(false, "User id required");
        }

        $this->db->query("
            SELECT d.id, d.department_name, d.company_id, c.company_name
            FROM department d
            JOIN company c ON c.id = d.company_id
            WHERE c.created_by = :user_id
            ORDER BY d.id DESC
        ");

        $departments = $this->db->get(["user_id" => $user_id]);

        sendResponse(true, "Departments fetched successfully", $departments);
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
        if (!$status) {
            sendResponse(false, "Failed to update department");
        }
        sendResponse(true, "Department updated successfully");
    }

    public function delete($department_id)
    {
        if (empty($department_id)) {
            sendResponse(false, "Department id is required");
        }

        // Get all employees in this department
        $this->db->query("SELECT id FROM employee WHERE department_id = :department_id");
        $employees = $this->db->get(["department_id" => $department_id]);

        foreach ($employees as $emp) {
            // Delete salary_details first (FK)
            $this->db->query("DELETE FROM salary_details WHERE salary_id IN (SELECT id FROM salary WHERE employee_id = :employee_id)");
            $this->db->delete(["employee_id" => $emp["id"]]);

            // Delete salaries
            $this->db->delete(["employee_id" => $emp["id"]], "salary");
        }

        // Delete employees
        $this->db->delete(["department_id" => $department_id], "employee");

        // Delete department
        $status = $this->db->delete(["id" => $department_id], "department");
        if (!$status) {
            sendResponse(false, "Failed to delete department");
        }
        sendResponse(true, "Department deleted successfully");
    }
}