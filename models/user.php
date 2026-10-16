<?php

include_once __DIR__ . "/../config/database.php";

class User
{
    private $conn;

    public function __construct()
    {
        global $conn;

        $this->conn = $conn;
    }


    // =========================================================
    // GET ALL USERS
    // =========================================================

    public function getAllUsers()
    {
        $sql = "SELECT
                    u.id,
                    u.username,
                    u.firstname,
                    u.lastname,
                    u.role_id,
                    u.student_id,
                    r.rolename
                FROM `user` AS u
                LEFT JOIN `role` AS r
                    ON u.role_id = r.id
                ORDER BY u.id DESC";

        $result = $this->conn->query($sql);

        if (!$result) {
            die("Failed to get users: " . $this->conn->error);
        }

        return $result;
    }


    // =========================================================
    // GET ONE USER
    // =========================================================

    public function getUserById($id)
    {
        $sql = "SELECT
                    u.id,
                    u.username,
                    u.password,
                    u.firstname,
                    u.lastname,
                    u.role_id,
                    u.student_id,
                    r.rolename
                FROM `user` AS u
                LEFT JOIN `role` AS r
                    ON u.role_id = r.id
                WHERE u.id = ?";

        $stmt = $this->conn->prepare($sql);

        if (!$stmt) {
            return false;
        }

        $stmt->bind_param("i", $id);

        if (!$stmt->execute()) {
            $stmt->close();
            return false;
        }

        $result = $stmt->get_result();

        $user = $result->fetch_assoc();

        $stmt->close();

        return $user;
    }

    public function getUserByUsername($username)
    {
        $stmt = $this->conn->prepare(
            "SELECT u.id, u.username, u.password, u.firstname, u.lastname,
                    u.role_id, u.student_id, r.rolename
             FROM `user` AS u
             LEFT JOIN `role` AS r ON u.role_id = r.id
             WHERE u.username = ?
             LIMIT 1"
        );

        if (!$stmt) {
            return false;
        }

        $stmt->bind_param("s", $username);

        if (!$stmt->execute()) {
            $stmt->close();
            return false;
        }

        $user = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        return $user;
    }


    // =========================================================
    // GET ALL ROLES
    // =========================================================

    public function getRoles()
    {
        $sql = "SELECT
                    id,
                    rolename
                FROM `role`
                ORDER BY id ASC";

        $result = $this->conn->query($sql);

        if (!$result) {
            die("Failed to get roles: " . $this->conn->error);
        }

        return $result;
    }

    public function getRoleById($id)
    {
        $stmt = $this->conn->prepare("SELECT id, rolename FROM `role` WHERE id = ?");
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

    public function getStudentOptions()
    {
        return $this->conn->query(
            "SELECT ID, Student_Number, First_Name, Last_Name
             FROM information
             WHERE Active = 0
             ORDER BY Last_Name, First_Name"
        );
    }

    public function studentIdAvailable($studentId, $excludeUserId = 0)
    {
        $stmt = $this->conn->prepare(
            "SELECT i.ID
             FROM information AS i
             LEFT JOIN `user` AS u ON u.student_id = i.ID AND u.id != ?
             WHERE i.ID = ? AND i.Active = 0 AND u.id IS NULL"
        );
        if (!$stmt) {
            return false;
        }

        $stmt->bind_param("ii", $excludeUserId, $studentId);
        if (!$stmt->execute()) {
            $stmt->close();
            return false;
        }

        $exists = $stmt->get_result()->num_rows > 0;
        $stmt->close();

        return $exists;
    }


    // =========================================================
    // ADD USER
    // =========================================================

    public function addUser(
        $username,
        $password,
        $firstname,
        $lastname,
        $role_id,
        $student_id = null
    ) {

        $sql = "INSERT INTO `user`
                (
                    username,
                    password,
                    firstname,
                    lastname,
                    role_id,
                    student_id
                )
                VALUES (?, ?, ?, ?, ?, ?)";

        $stmt = $this->conn->prepare($sql);

        if (!$stmt) {
            die("Database error: " . $this->conn->error);
        }

        $stmt->bind_param(
            "ssssii",
            $username,
            $password,
            $firstname,
            $lastname,
            $role_id,
            $student_id
        );

        if (!$stmt->execute()) {
            die("Failed to create user: " . $stmt->error);
        }

        $stmt->close();

        return true;
    }


    // =========================================================
    // UPDATE USER
    // =========================================================

    public function updateUser(
        $id,
        $username,
        $firstname,
        $lastname,
        $role_id,
        $student_id = null
    ) {

        $sql = "UPDATE `user`
                SET
                    username = ?,
                    firstname = ?,
                    lastname = ?,
                    role_id = ?,
                    student_id = ?
                WHERE id = ?";

        $stmt = $this->conn->prepare($sql);

        if (!$stmt) {
            return false;
        }

        $stmt->bind_param(
            "sssiii",
            $username,
            $firstname,
            $lastname,
            $role_id,
            $student_id,
            $id
        );

        $success = $stmt->execute();

        $stmt->close();

        return $success;
    }


    // =========================================================
    // UPDATE PASSWORD
    // =========================================================

    public function updatePassword(
        $id,
        $password
    ) {

        $sql = "UPDATE `user`
                SET password = ?
                WHERE id = ?";

        $stmt = $this->conn->prepare($sql);

        if (!$stmt) {
            return false;
        }

        $stmt->bind_param(
            "si",
            $password,
            $id
        );

        $success = $stmt->execute();

        $stmt->close();

        return $success;
    }


    // =========================================================
    // DELETE USER
    // =========================================================

    public function deleteUser($id)
    {
        $sql = "DELETE FROM `user`
                WHERE id = ?";

        $stmt = $this->conn->prepare($sql);

        if (!$stmt) {
            return false;
        }

        $stmt->bind_param("i", $id);

        $success = $stmt->execute();

        $stmt->close();

        return $success;
    }


    // =========================================================
    // CHECK DUPLICATE USERNAME
    // =========================================================

    public function userExists(
        $username,
        $excludeId = 0
    ) {

        $sql = "SELECT id
                FROM `user`
                WHERE username = ?
                AND id != ?";

        $stmt = $this->conn->prepare($sql);

        if (!$stmt) {
            return false;
        }

        $stmt->bind_param(
            "si",
            $username,
            $excludeId
        );

        if (!$stmt->execute()) {
            $stmt->close();
            return false;
        }

        $result = $stmt->get_result();

        $exists =
            $result->num_rows > 0;

        $stmt->close();

        return $exists;
    }
}

?>