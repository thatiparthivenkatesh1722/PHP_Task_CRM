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

        $gross = 0;
        $deduction = 0;
        $net = 0;

        foreach ($components as $component) {
            $component_id = $component["component_id"];
            $amount = floatval($component["amount"]);

            if ($component_id == "" || $amount == "" || $amount < 0) {
                sendResponse(false, "Invalid component data");
            }

            $this->db->query("SELECT component_type FROM salary_component WHERE id = :id");
            $componentDetails = $this->db->first([
                "id" => $component_id
            ]);

            if (!$componentDetails) {
                sendResponse(false, "Invalid salary component");
            }

            if ($componentDetails["component_type"] == 1) {
                $gross += $amount;
                $net += $amount;
            } else {
                $deduction += $amount;
                $net -= $amount;
            }
        }

        $salaryDate = $salary_month . "-01";
        $salary_year = date("Y", strtotime($salaryDate));
        $salary_month_emp = date("m", strtotime($salaryDate));

        $this->db->query("
            INSERT INTO salary
            (employee_id, salary_month, payment_date, gross, deduction, net, salary_year, salary_month_emp, status_id)
            VALUES
            (:employee_id, :salary_month, :payment_date, :gross, :deduction, :net, :salary_year, :salary_month_emp, :status_id)
            RETURNING id
        ");

        $salary = $this->db->first([
            "employee_id" => $employee_id,
            "salary_month" => $salaryDate,
            "payment_date" => $payment_date,
            "gross" => $gross,
            "deduction" => $deduction,
            "net" => $net,
            "salary_year" => $salary_year,
            "salary_month_emp" => $salary_month_emp,
            "status_id" => 2
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
                "salary_id" => $salary_id,
                "component_id" => $component["component_id"],
                "amount" => $component["amount"]
            ]);
        }

        sendResponse(true, "Salary created successfully", [
            "salary_id" => $salary_id,
            "gross" => $gross,
            "deduction" => $deduction,
            "net" => $net
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
}
