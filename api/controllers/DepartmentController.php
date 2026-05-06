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
            "company_id" => $company_id
        ]);

        if (!$status) {
            sendResponse(false, "Department creation failed");
        }

        sendResponse(true, "Department created successfully");
    }

    public function list()
    {
        $this->db->query("
            SELECT d.id, d.department_name, d.company_id, c.company_name
            FROM department d
            JOIN company c ON c.id = d.company_id
            ORDER BY d.id DESC
        ");

        $departments = $this->db->get();

        sendResponse(true, "Departments fetched successfully", $departments);
    }
}