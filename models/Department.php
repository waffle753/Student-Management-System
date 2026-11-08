<?php

include_once __DIR__ . "/../config/database.php";

class Department
{
    private $conn;

    public function __construct()
    {
        global $conn;
        $this->conn = $conn;
    }

    public function getAllDepartments()
    {
        return $this->conn->query(
            "SELECT department_id, department_code, department_name
             FROM departments
             ORDER BY department_id DESC"
        );
    }

    public function departmentExists($departmentCode, $excludeId = 0)
    {
        $stmt = $this->conn->prepare(
            "SELECT department_id
             FROM departments
             WHERE department_code = ? AND department_id != ?
             LIMIT 1"
        );
        if (!$stmt) {
            return false;
        }

        $stmt->bind_param("si", $departmentCode, $excludeId);
        if (!$stmt->execute()) {
            $stmt->close();
            return false;
        }

        $exists = $stmt->get_result()->num_rows > 0;
        $stmt->close();

        return $exists;
    }

    public function departmentNameExists($departmentName)
    {
        $stmt = $this->conn->prepare(
            "SELECT department_id
             FROM departments
             WHERE department_name = ?
             LIMIT 1"
        );
        if (!$stmt) {
            return false;
        }

        $stmt->bind_param("s", $departmentName);
        if (!$stmt->execute()) {
            $stmt->close();
            return false;
        }

        $exists = $stmt->get_result()->num_rows > 0;
        $stmt->close();

        return $exists;
    }

    public function addDepartment($departmentCode, $departmentName)
    {
        $stmt = $this->conn->prepare(
            "INSERT INTO departments (department_code, department_name)
             VALUES (?, ?)"
        );
        if (!$stmt) {
            return false;
        }

        $stmt->bind_param("ss", $departmentCode, $departmentName);
        $success = $stmt->execute();
        $stmt->close();

        return $success;
    }

    public function updateDepartment($id, $departmentCode, $departmentName)
    {
        $stmt = $this->conn->prepare(
            "UPDATE departments
             SET department_code = ?, department_name = ?
             WHERE department_id = ?"
        );
        if (!$stmt) {
            return false;
        }

        $stmt->bind_param("ssi", $departmentCode, $departmentName, $id);
        $success = $stmt->execute();
        $stmt->close();

        return $success;
    }

    public function deleteDepartment($id)
    {
        $stmt = $this->conn->prepare(
            "DELETE FROM departments WHERE department_id = ?"
        );
        if (!$stmt) {
            return false;
        }

        $stmt->bind_param("i", $id);
        $success = $stmt->execute();
        $stmt->close();

        return $success;
    }
}
