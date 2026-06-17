<?php
namespace RyTM\Models;

use RyTM\Core\Database;

class User
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    public function findByUsername($username)
    {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }

    public function findByEmail($email)
    {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }

    public function findByUsernameOrEmail($usernameOrEmail)
    {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE username = ? OR email = ?");
        $stmt->bind_param("ss", $usernameOrEmail, $usernameOrEmail);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }

    public function create($data)
    {
        $stmt = $this->db->prepare("
            INSERT INTO users 
            (username, email, password_hash, role, is_verified, is_active, is_banned, subscription_type, total_tracks_published, total_sales, total_earnings) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $role = $data['role'] ?? 'buyer';
        $is_verified = 0;
        $is_active = 1;
        $is_banned = 0;
        $subscription_type = 'free';
        $total_tracks_published = 0;
        $total_sales = 0;
        $total_earnings = 0.00;

        $stmt->bind_param(
            "ssssiiisidd",
            $data['username'],
            $data['email'],
            $data['password_hash'],
            $role,
            $is_verified,
            $is_active,
            $is_banned,
            $subscription_type,
            $total_tracks_published,
            $total_sales,
            $total_earnings
        );

        if ($stmt->execute()) {
            $id = $this->db->insert_id;
            $stmt->close();
            return $id;
        }
        $stmt->close();
        return false;
    }

    public function updateAvatar($userId, $avatarUrl)
    {
        $stmt = $this->db->prepare("UPDATE users SET avatar_url = ? WHERE id = ?");
        $stmt->bind_param("si", $avatarUrl, $userId);
        return $stmt->execute();
    }

    public function updateLastLogin($userId)
    {
        $stmt = $this->db->prepare("UPDATE users SET last_login_at = NOW() WHERE id = ?");
        $stmt->bind_param("i", $userId);
        return $stmt->execute();
    }

    public function getById($id)
    {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }
}