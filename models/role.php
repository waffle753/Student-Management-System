<?php

include_once __DIR__ . "/../config/database.php";

class Role
{
    public static function getPermissionOptions()
    {
        return [
            'students' => 'Students',
            'users' => 'Users',
            'instructors' => 'Instructors',
            'programs' => 'Programs',
            'departments' => 'Departments',
            'sections' => 'Sections',
            'roles' => 'Roles',
            'school_years' => 'School Years',
            'semesters' => 'Semesters',
            'subjects' => 'Subjects',
            'curriculum_subjects' => 'Curriculum Subjects',
            'class_schedules' => 'Class Schedules',
        ];
    }

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
    public function addRole($rolename, array $permissions = [])
    {
        if (!$this->conn->begin_transaction()) {
            return false;
        }

        $stmt = $this->conn->prepare("INSERT INTO role (rolename) VALUES (?)");
        if (!$stmt) {
            $this->conn->rollback();
            return false;
        }
        $stmt->bind_param("s", $rolename);
        if (!$stmt->execute()) {
            $stmt->close();
            $this->conn->rollback();
            return false;
        }
        $roleId = (int) $stmt->insert_id;
        $stmt->close();

        if (!$this->replacePermissions($roleId, $permissions)) {
            $this->conn->rollback();
            return false;
        }

        return $this->conn->commit();
    }

    // Update role
    public function updateRole($id, $rolename, array $permissions = [])
    {
        if (!$this->conn->begin_transaction()) {
            return false;
        }

        $stmt = $this->conn->prepare("UPDATE role SET rolename = ? WHERE id = ?");
        if (!$stmt) {
            $this->conn->rollback();
            return false;
        }
        $stmt->bind_param("si", $rolename, $id);
        if (!$stmt->execute()) {
            $stmt->close();
            $this->conn->rollback();
            return false;
        }
        $stmt->close();

        if (!$this->replacePermissions($id, $permissions)) {
            $this->conn->rollback();
            return false;
        }

        return $this->conn->commit();
    }

    public function getPermissions($roleId)
    {
        $stmt = $this->conn->prepare(
            "SELECT permission_key FROM role_permissions WHERE role_id = ?"
        );
        if (!$stmt) {
            return false;
        }
        $stmt->bind_param("i", $roleId);
        if (!$stmt->execute()) {
            $stmt->close();
            return false;
        }

        $permissions = [];
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $permissions[] = $row['permission_key'];
        }
        $stmt->close();

        return $permissions;
    }

    private function replacePermissions($roleId, array $permissions)
    {
        $deleteStmt = $this->conn->prepare("DELETE FROM role_permissions WHERE role_id = ?");
        if (!$deleteStmt) {
            return false;
        }
        $deleteStmt->bind_param("i", $roleId);
        if (!$deleteStmt->execute()) {
            $deleteStmt->close();
            return false;
        }
        $deleteStmt->close();

        if ($permissions === []) {
            return true;
        }

        $insertStmt = $this->conn->prepare(
            "INSERT INTO role_permissions (role_id, permission_key) VALUES (?, ?)"
        );
        if (!$insertStmt) {
            return false;
        }
        foreach ($permissions as $permission) {
            $insertStmt->bind_param("is", $roleId, $permission);
            if (!$insertStmt->execute()) {
                $insertStmt->close();
                return false;
            }
        }
        $insertStmt->close();

        return true;
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
             WHERE TRIM(rolename) = TRIM(?)
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