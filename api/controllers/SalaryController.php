<?php

require_once __DIR__ . "/../utils/db.php";
require_once __DIR__ . "/../utils/functions.php";

class SalaryController
{
    private $db;

    function __construct()
    {
        $this->db = new DB();
    }

    public function list($user_id, $page = 1, $per_page = 5)
    {
        if ($user_id == "") sendResponse(false, "User id required");

        $page     = max(1, (int)$page);
        $per_page = max(1, (int)$per_page);
        $offset   = ($page - 1) * $per_page;

        $this->db->query("SELECT COUNT(*) as total FROM salary s JOIN employee e ON e.id = s.employee_id JOIN company c ON c.id = e.company_id WHERE c.created_by = :user_id");
        $count       = $this->db->first(["user_id" => $user_id]);
        $total       = (int)($count["total"] ?? 0);
        $total_pages = max(1, (int)ceil($total / $per_page));

        $this->db->query("SELECT s.id, s.salary_month, s.payment_date, s.gross, s.deduction, s.net, e.first_name, e.last_name, c.company_name, d.department_name FROM salary s JOIN employee e ON e.id = s.employee_id JOIN company c ON c.id = e.company_id JOIN department d ON d.id = e.department_id WHERE c.created_by = :user_id ORDER BY s.id DESC LIMIT $per_page OFFSET $offset");
        $rows = $this->db->get(["user_id" => $user_id]);

        foreach ($rows as &$row) {
            $this->db->query("SELECT sc.component_name AS name, CASE WHEN sc.component_type = 1 THEN 'earning' ELSE 'deduction' END AS type, sd.amount FROM salary_details sd JOIN salary_component sc ON sc.id = sd.component_id WHERE sd.salary_id = :salary_id");
            $row['components'] = $this->db->get(["salary_id" => $row["id"]]);
        }
        unset($row);

        sendResponse(true, "Salaries fetched", $rows, [
            "total"       => $total,
            "page"        => $page,
            "per_page"    => $per_page,
            "total_pages" => $total_pages
        ]);
    }


    public function components()
    {
        $this->db->query("SELECT * FROM salary_component ORDER BY id ASC");
        $components = $this->db->get();

        sendResponse(true, "Components fetched successfully", $components);
    }

    public function create($employee_id, $salary_month, $payment_date, $components)
    {
        if ($employee_id == "" || $salary_month == "" || $payment_date == "") {
            sendResponse(false, "Employee, salary month and payment date are required");
        }

        if (empty($components)) {
            sendResponse(false, "At least one salary component is required");
        }

        $gross     = 0;
        $deduction = 0;
        $net       = 0;

        foreach ($components as $component) {
            $component_id = $component["component_id"];
            $amount       = floatval($component["amount"]);

            if ($component_id == "" || $amount < 0) {
                sendResponse(false, "Invalid component data");
            }

            $this->db->query("SELECT component_type FROM salary_component WHERE id = :id");
            $componentDetails = $this->db->first(["id" => $component_id]);

            if (!$componentDetails) {
                sendResponse(false, "Invalid salary component");
            }

            if ($componentDetails["component_type"] == 1) {
                $gross += $amount;
                $net   += $amount;
            } else {
                $deduction += $amount;
                $net       -= $amount;
            }
        }

        $salaryDate        = $salary_month . "-01";
        $salary_year       = date("Y", strtotime($salaryDate));
        $salary_month_emp  = date("m", strtotime($salaryDate));

        $this->db->query("
            INSERT INTO salary
            (employee_id, salary_month, payment_date, gross, deduction, net, salary_year, salary_month_emp, status_id)
            VALUES
            (:employee_id, :salary_month, :payment_date, :gross, :deduction, :net, :salary_year, :salary_month_emp, :status_id)
            RETURNING id
        ");

        $salary = $this->db->first([
            "employee_id"      => $employee_id,
            "salary_month"     => $salaryDate,
            "payment_date"     => $payment_date,
            "gross"            => $gross,
            "deduction"        => $deduction,
            "net"              => $net,
            "salary_year"      => $salary_year,
            "salary_month_emp" => $salary_month_emp,
            "status_id"        => 2
        ]);

        if (!$salary) {
            sendResponse(false, "Salary creation failed");
        }

        $salary_id = $salary["id"];

        foreach ($components as $component) {
            $this->db->query("
                INSERT INTO salary_details (salary_id, component_id, amount)
                VALUES (:salary_id, :component_id, :amount)
            ");

            $this->db->create([
                "salary_id"    => $salary_id,
                "component_id" => $component["component_id"],
                "amount"       => $component["amount"]
            ]);
        }

        sendResponse(true, "Salary created successfully", [
            "salary_id" => $salary_id,
            "gross"     => $gross,
            "deduction" => $deduction,
            "net"       => $net
        ]);
    }

    public function details($employee_id)
    {
        if ($employee_id == "") {
            sendResponse(false, "Employee id required");
        }

        $this->db->query("
        SELECT 
            sc.component_name,
            sc.component_type,
            sd.amount,
            s.net,
            s.gross,
            s.deduction,
            s.salary_month
        FROM salary s
        JOIN salary_details sd ON sd.salary_id = s.id
        JOIN salary_component sc ON sc.id = sd.component_id
        WHERE s.employee_id = :employee_id
          AND s.id = (
              SELECT MAX(id) FROM salary WHERE employee_id = :employee_id
          )
    ");

        $data = $this->db->get([
            "employee_id" => $employee_id
        ]);

        sendResponse(true, "Salary details fetched", $data);
    }

    public function update($salary_id, $payment_date, $components)
    {
        if (empty($salary_id)) {
            sendResponse(false, "Salary id is required");
        }
        if (empty($components)) {
            sendResponse(false, "At least one salary component is required");
        }

        // Recalculate totals from updated components
        $gross     = 0;
        $deduction = 0;
        $net       = 0;

        foreach ($components as $component) {
            $component_id = $component["component_id"];
            $amount       = floatval($component["amount"]);

            if (empty($component_id) || $amount < 0) {
                sendResponse(false, "Invalid component data");
            }

            $this->db->query("SELECT component_type FROM salary_component WHERE id = :id");
            $details = $this->db->first(["id" => $component_id]);

            if (!$details) {
                sendResponse(false, "Invalid salary component");
            }

            if ($details["component_type"] == 1) {
                $gross += $amount;
                $net   += $amount;
            } else {
                $deduction += $amount;
                $net       -= $amount;
            }
        }

        // Replace salary_details
        $this->db->delete(["salary_id" => $salary_id], "salary_details");

        foreach ($components as $component) {
            $this->db->query("
                INSERT INTO salary_details (salary_id, component_id, amount)
                VALUES (:salary_id, :component_id, :amount)
            ");
            $this->db->create([
                "salary_id"    => $salary_id,
                "component_id" => $component["component_id"],
                "amount"       => $component["amount"]
            ]);
        }

        // Update the salary header row
        $fields = ["gross" => $gross, "deduction" => $deduction, "net" => $net, "updated_at" => date('Y-m-d H:i:s')];
        if (!empty($payment_date)) {
            $fields["payment_date"] = $payment_date;
        }
        $status = $this->db->update("salary", $fields, ["id" => $salary_id]);

        if (!$status) {
            sendResponse(false, "Failed to update salary");
        }
        sendResponse(true, "Salary updated successfully", [
            "gross"     => $gross,
            "deduction" => $deduction,
            "net"       => $net
        ]);
    }

    public function delete($salary_id)
    {
        if (empty($salary_id)) {
            sendResponse(false, "Salary id is required");
        }

        // Delete salary_details first (FK constraint)
        $this->db->delete(["salary_id" => $salary_id], "salary_details");

        // Delete the salary record
        $status = $this->db->delete(["id" => $salary_id], "salary");

        if (!$status) {
            sendResponse(false, "Failed to delete salary");
        }
        sendResponse(true, "Salary deleted successfully");
    }
}
