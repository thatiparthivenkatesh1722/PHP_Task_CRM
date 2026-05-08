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

        $this->db->query("
            INSERT INTO employee 
            (first_name, last_name, email, phone, company_id, department_id, role, join_date)
            VALUES 
            (:first_name, :last_name, :email, :phone, :company_id, :department_id, :role, :join_date)
        ");

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

        if (!$status) {
            sendResponse(false, "Employee creation failed");
        }

        sendResponse(true, "Employee created successfully");
    }

    public function list($user_id)
    {
        if ($user_id == "") {
            sendResponse(false, "User id required");
        }

        $this->db->query("
        SELECT 
            e.id,
            e.first_name,
            e.last_name,
            e.email,
            e.phone,
            e.company_id,
            e.department_id,
            e.role,
            e.join_date,
            e.status,
            c.company_name,
            d.department_name,
            s.net
        FROM employee e
        JOIN company c ON c.id = e.company_id
        JOIN department d ON d.id = e.department_id
        LEFT JOIN salary s ON s.employee_id = e.id
            AND s.id = (
                SELECT MAX(id)
                FROM salary
                WHERE employee_id = e.id
            )
        WHERE c.created_by = :user_id
        ORDER BY e.id DESC
    ");

        $employees = $this->db->get(["user_id" => $user_id]);

        sendResponse(true, "Employees fetched successfully", $employees);
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

        if (!$status_bool) {
            sendResponse(false, "Failed to update employee");
        }
        sendResponse(true, "Employee updated successfully");
    }

    public function delete($employee_id)
    {
        if ($employee_id == "") {
            sendResponse(false, "Employee ID is required");
        }

        // Delete salary_details first (FK constraint)
        $this->db->query("DELETE FROM salary_details WHERE salary_id IN (SELECT id FROM salary WHERE employee_id = :employee_id)");
        $this->db->delete(["employee_id" => $employee_id]);

        // Delete salaries
        $this->db->delete(["employee_id" => $employee_id], "salary");

        // Delete the employee
        $status = $this->db->delete(["id" => $employee_id], "employee");

        if (!$status) {
            sendResponse(false, "Failed to delete employee");
        }

        sendResponse(true, "Employee deleted successfully");
    }
}
