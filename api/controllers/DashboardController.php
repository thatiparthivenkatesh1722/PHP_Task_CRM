<?php

require_once __DIR__ . "/../utils/db.php";
require_once __DIR__ . "/../utils/functions.php";

class DashboardController
{
    private $db;

    function __construct()
    {
        $this->db = new DB();
    }

    public function stats($user_id)
    {
        if ($user_id == "") {
            sendResponse(false, "User id required");
        }

        $this->db->query("SELECT COUNT(*) as total FROM company WHERE created_by = :user_id");
        $companies = $this->db->first(["user_id" => $user_id]);

        $this->db->query("SELECT COUNT(DISTINCT industry) as total FROM company WHERE created_by = :user_id");
        $industries = $this->db->first(["user_id" => $user_id]);

        $this->db->query("SELECT COUNT(*) as total FROM department d JOIN company c ON c.id = d.company_id WHERE c.created_by = :user_id");
        $departments = $this->db->first(["user_id" => $user_id]);

        $this->db->query("SELECT COUNT(*) as total FROM employee e JOIN company c ON c.id = e.company_id WHERE c.created_by = :user_id");
        $employees = $this->db->first(["user_id" => $user_id]);

        $this->db->query("SELECT COUNT(*) as total FROM employee e JOIN company c ON c.id = e.company_id WHERE c.created_by = :user_id AND e.status = 'active'");
        $active_emp = $this->db->first(["user_id" => $user_id]);

        $this->db->query("SELECT COUNT(*) as total FROM salary s JOIN employee e ON e.id = s.employee_id JOIN company c ON c.id = e.company_id WHERE c.created_by = :user_id");
        $sal_count = $this->db->first(["user_id" => $user_id]);

        $this->db->query("SELECT COUNT(DISTINCT s.employee_id) as total FROM salary s JOIN employee e ON e.id = s.employee_id JOIN company c ON c.id = e.company_id WHERE c.created_by = :user_id");
        $paid_emp = $this->db->first(["user_id" => $user_id]);

        $this->db->query("SELECT COALESCE(SUM(s.net), 0) as total FROM salary s JOIN employee e ON e.id = s.employee_id JOIN company c ON c.id = e.company_id WHERE c.created_by = :user_id");
        $salary = $this->db->first(["user_id" => $user_id]);

        $this->db->query("SELECT e.id, e.first_name, e.last_name, e.email, e.role, e.join_date, e.status, c.company_name, d.department_name FROM employee e JOIN company c ON c.id = e.company_id JOIN department d ON d.id = e.department_id WHERE c.created_by = :user_id ORDER BY e.id DESC LIMIT 5");
        $recent_employees = $this->db->get(["user_id" => $user_id]);

        $this->db->query("SELECT s.id, s.salary_month, s.net, e.first_name, e.last_name, c.company_name FROM salary s JOIN employee e ON e.id = s.employee_id JOIN company c ON c.id = e.company_id WHERE c.created_by = :user_id ORDER BY s.id DESC LIMIT 5");
        $recent_salaries = $this->db->get(["user_id" => $user_id]);

        sendResponse(true, "Stats fetched", [
            "total_companies"   => intval($companies["total"]),
            "total_industries"  => intval($industries["total"]),
            "total_departments" => intval($departments["total"]),
            "total_employees"   => intval($employees["total"]),
            "active_employees"  => intval($active_emp["total"]),
            "salary_processed"  => intval($sal_count["total"]),
            "paid_employees"    => intval($paid_emp["total"]),
            "total_salary"      => floatval($salary["total"]),
            "recent_employees"  => $recent_employees,
            "recent_salaries"   => $recent_salaries
        ]);
    }
}
