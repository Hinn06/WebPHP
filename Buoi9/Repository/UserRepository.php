<?php

namespace App\Repository;

class UserRepository
{
    private \PDO $pdo;

    public function __construct(\PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * Tìm tài khoản theo username
     */
    public function findByUsername(string $username): ?array
    {
        $sql = "
            SELECT *
            FROM users
            WHERE username = :username
            LIMIT 1
        ";

        $stmt = $this->pdo->prepare($sql);

        $stmt->execute([
            ':username' => $username
        ]);

        return $stmt->fetch() ?: null;
    }

    /**
     * Tìm tài khoản theo ID
     */
    public function findById(int $id): ?array
    {
        $sql = "
            SELECT *
            FROM users
            WHERE id = :id
            LIMIT 1
        ";

        $stmt = $this->pdo->prepare($sql);

        $stmt->execute([
            ':id' => $id
        ]);

        return $stmt->fetch() ?: null;
    }

    /**
     * Kiểm tra username đã tồn tại chưa
     *
     * $except là ID tài khoản được bỏ qua khi sửa.
     */
    public function usernameExists(
        string $username,
        int $except = 0
    ): bool {
        $sql = "
            SELECT id
            FROM users
            WHERE username = :username
              AND id <> :except_id
            LIMIT 1
        ";

        $stmt = $this->pdo->prepare($sql);

        $stmt->execute([
            ':username' => $username,
            ':except_id' => $except
        ]);

        return (bool) $stmt->fetchColumn();
    }

    /**
     * Tạo tài khoản mới
     */
    public function create(array $d): int
    {
        $sql = "
            INSERT INTO users
            (
                username,
                password_hash,
                full_name,
                role,
                status
            )
            VALUES
            (
                :username,
                :password_hash,
                :full_name,
                :role,
                1
            )
        ";

        $stmt = $this->pdo->prepare($sql);

        $passwordHash = password_hash(
            $d['password'],
            PASSWORD_DEFAULT
        );

        $stmt->execute([
            ':username' => $d['username'],
            ':password_hash' => $passwordHash,
            ':full_name' => $d['full_name'],
            ':role' => $d['role']
        ]);

        return (int) $this->pdo->lastInsertId();
    }

    /**
     * Cập nhật tài khoản
     */
    public function update(int $id, array $d): bool
    {
        $sql = "
            UPDATE users
            SET
                full_name = :full_name,
                status = :status
        ";

        $params = [
            ':full_name' => $d['full_name'],
            ':status' => $d['status'],
            ':id' => $id
        ];

        // Nếu người dùng nhập mật khẩu mới
        // thì mới cập nhật password.
        if (!empty($d['password'])) {

            $sql .= ",
                password_hash = :password_hash
            ";

            $params[':password_hash'] = password_hash(
                $d['password'],
                PASSWORD_DEFAULT
            );
        }

        $sql .= "
            WHERE id = :id
        ";

        $stmt = $this->pdo->prepare($sql);

        return $stmt->execute($params);
    }

    /**
     * Đếm số tài khoản
     *
     * SỬA LỖI HY093:
     * Không dùng :q cho cả 2 điều kiện.
     */
    public function count(string $q = ''): int
    {
        $like = '%' . trim($q) . '%';

        $sql = "
            SELECT COUNT(*)
            FROM users
            WHERE full_name LIKE :q1
               OR username LIKE :q2
        ";

        $stmt = $this->pdo->prepare($sql);

        $stmt->execute([
            ':q1' => $like,
            ':q2' => $like
        ]);

        return (int) $stmt->fetchColumn();
    }

    /**
     * Lấy danh sách tài khoản có tìm kiếm + phân trang
     */
    public function findAll(
        string $q = '',
        int $limit = 10,
        int $offset = 0
    ): array {
        $like = '%' . trim($q) . '%';

        $sql = "
            SELECT *
            FROM users
            WHERE full_name LIKE :q1
               OR username LIKE :q2
            ORDER BY id DESC
            LIMIT :lim OFFSET :off
        ";

        $stmt = $this->pdo->prepare($sql);

        $stmt->bindValue(
            ':q1',
            $like,
            \PDO::PARAM_STR
        );

        $stmt->bindValue(
            ':q2',
            $like,
            \PDO::PARAM_STR
        );

        $stmt->bindValue(
            ':lim',
            $limit,
            \PDO::PARAM_INT
        );

        $stmt->bindValue(
            ':off',
            $offset,
            \PDO::PARAM_INT
        );

        $stmt->execute();

        return $stmt->fetchAll();
    }
}