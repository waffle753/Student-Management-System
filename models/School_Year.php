<?php

include_once __DIR__ . "/../config/database.php";

class School_Year
{
    private $conn;

    public function __construct()
    {
        global $conn;
        $this->conn = $conn;
    }

    public function getAll()
    {
        $sql = "SELECT id, SchoolYear AS school_year, Active AS active FROM school_year ORDER BY id DESC";

        return $this->conn->query($sql);
    }

    public function getSchoolYearOptions()
    {
        $stmt = $this->conn->prepare(
            "SELECT SchoolYear AS school_year FROM school_year WHERE LOWER(Active) = 'yes' ORDER BY id DESC"
        );

        if (!$stmt) {
            return [];
        }

        $stmt->execute();
        $result = $stmt->get_result();
        $options = [];

        if (!$result || $result->num_rows === 0) {
            $stmt->close();
            return $options;
        }

        while ($row = $result->fetch_assoc()) {
            $options[] = $row['school_year'];
        }

        $stmt->close();

        return $options;
    }

    public function create($school_year)
    {
        return $this->add($school_year);
    }

    public function schoolYearExists($school_year, $excludeId = null)
    {
        $check = $this->conn->prepare(
            "SELECT id FROM school_year WHERE LOWER(SchoolYear) = LOWER(?) AND id != ?"
        );

        if (!$check) {
            return true;
        }

        $excludeId = $excludeId !== null ? (int) $excludeId : -1;
        $check->bind_param("si", $school_year, $excludeId);
        $check->execute();
        $result = $check->get_result();
        $exists = $result && $result->num_rows > 0;
        $check->close();

        return $exists;
    }

    public function add($school_year)
    {
        $school_year = trim((string) $school_year);

        if ($school_year === '' || $this->schoolYearExists($school_year)) {
            return false;
        }

        $nextIdResult = $this->conn->query("SELECT COALESCE(MAX(id), 0) + 1 AS next_id FROM school_year");
        $nextId = $nextIdResult && $nextIdResult->num_rows > 0 ? (int) $nextIdResult->fetch_assoc()['next_id'] : 1;

        $stmt = $this->conn->prepare(
            "INSERT INTO school_year (id, SchoolYear, Active) VALUES (?, ?, 'No')"
        );

        if (!$stmt) {
            return false;
        }

        $stmt->bind_param("is", $nextId, $school_year);
        $success = $stmt->execute();
        $stmt->close();

        return $success;
    }

    public function update($id, $school_year, $active = 'no')
    {
        $school_year = trim((string) $school_year);

        if ($school_year === '' || $this->schoolYearExists($school_year, $id)) {
            return false;
        }

        $active = ($active === 'yes') ? 'Yes' : 'No';

        if ($active === 'Yes') {
            $this->conn->query("UPDATE school_year SET active = 'no'");
        }

        $stmt = $this->conn->prepare(
            "UPDATE school_year SET SchoolYear = ?, Active = ? WHERE id = ?"
        );

        if (!$stmt) {
            return false;
        }

        $stmt->bind_param("ssi", $school_year, $active, $id);
        $success = $stmt->execute();
        $stmt->close();

        return $success;
    }

    public function delete($id)
    {
        $stmt = $this->conn->prepare("DELETE FROM school_year WHERE id = ?");

        if (!$stmt) {
            return false;
        }

        $stmt->bind_param("i", $id);
        $success = $stmt->execute();
        $stmt->close();

        return $success;
    }

    public function setActive($id)
    {
        $this->conn->query("UPDATE school_year SET Active = 'No'");

        $stmt = $this->conn->prepare(
            "UPDATE school_year SET Active = 'Yes' WHERE id = ?"
        );

        if (!$stmt) {
            return false;
        }

        $stmt->bind_param("i", $id);
        $success = $stmt->execute();
        $stmt->close();

        return $success;
    }

    public function getActive()
    {
        $sql = "SELECT id, SchoolYear AS school_year, Active AS active FROM school_year WHERE Active = 'Yes' LIMIT 1";

        return $this->conn->query($sql);
    }
}