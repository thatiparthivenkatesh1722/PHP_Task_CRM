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
            "first_name" => $first_name,
            "last_name" => $last_name,
            "email" => $email,
            "phone" => $phone,
            "company_id" => $company_id,
            "department_id" => $department_id,
            "role" => $role,
            "join_date" => $join_date
        ]);

        if (!$status) {
            sendResponse(false, "Employee creation failed");
        }

        sendResponse(true, "Employee created successfully");
    }

    public function list()
    {
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
        ORDER BY e.id DESC
    ");

        $employees = $this->db->get();

        sendResponse(true, "Employees fetched successfully", $employees);
    }
}
