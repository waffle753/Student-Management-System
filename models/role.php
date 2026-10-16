<?php

include_once __DIR__ . "/../config/database.php";

class Role
{
    private $conn;

    public function __construct()
    {
        global $conn;
        $this->conn = $conn;
    }

    // Display all roles
    public function getAllRoles()
    {
        $sql = "SELECT *
                FROM role
                ORDER BY id DESC";

        return $this->conn->query($sql);
    }

    // Get one role
    public function getRoleById($id)
    {
        $stmt = $this->conn->prepare(
            "SELECT *
             FROM role
             WHERE id = ?"
        );

        if (!$stmt) {
            return false;
        }

        $stmt->bind_param("i", $id);

        if (!$stmt->execute()) {
            $stmt->close();
            return false;
        }

        $role = $stmt->get_result()->fetch_assoc();

        $stmt->close();

        return $role;
    }

    // Add role
    public function addRole($rolename)
    {
        $sql = "INSERT INTO role
                (rolename)
                VALUES (?)";

        $stmt = $this->conn->prepare($sql);

        if (!$stmt) {
            return false;
        }

        $stmt->bind_param("s", $rolename);

        $success = $stmt->execute();

        $stmt->close();

        return $success;
    }

    // Update role
    public function updateRole($id, $rolename)
    {
        $sql = "UPDATE role
                SET rolename = ?
                WHERE id = ?";

        $stmt = $this->conn->prepare($sql);

        if (!$stmt) {
            return false;
        }

        $stmt->bind_param(
            "si",
            $rolename,
            $id
        );

        $success = $stmt->execute();

        $stmt->close();

        return $success;
    }

    // Delete role permanently
    public function deleteRole($id)
    {
        $stmt = $this->conn->prepare(
            "DELETE FROM role WHERE id = ?"
        );

        if (!$stmt) {
            return false;
        }

        $stmt->bind_param("i", $id);

        $success = $stmt->execute();

        $stmt->close();

        return $success;
    }

    // Check duplicate role name
    public function roleExists($rolename, $excludeId = 0)
    {
        $stmt = $this->conn->prepare(
            "SELECT id
             FROM role
             WHERE rolename = ?
             AND id != ?"
        );

        if (!$stmt) {
            return false;
        }

        $stmt->bind_param("si", $rolename, $excludeId);

        if (!$stmt->execute()) {
            $stmt->close();
            return false;
        }

        $result = $stmt->get_result();

        $exists = $result->num_rows > 0;

        $stmt->close();

        return $exists;
    }
}