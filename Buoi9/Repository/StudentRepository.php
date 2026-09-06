<?php

namespace App\Repository;

use PDO;

class StudentRepository
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * Lấy sinh viên theo ID tài khoản
     */
    public function findById(int $id): ?array
    {
        $sql = "
            SELECT
                s.user_id,
                s.student_code,
                s.faculty_id,
                s.class_name,
                s.cohort,
                u.username,
                u.full_name,
                u.status,
                f.name AS faculty_name
            FROM students s
            INNER JOIN users u ON u.id = s.user_id
            INNER JOIN faculties f ON f.id = s.faculty_id
            WHERE s.user_id = :id
            LIMIT 1
        ";

        $stmt = $this->pdo->prepare($sql);

        $stmt->execute([
            ':id' => $id
        ]);

        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return $result ?: null;
    }

    /**
     * Đếm số lượng sinh viên
     * Có hỗ trợ tìm kiếm theo mã sinh viên và họ tên
     */
   /**
 * Đếm số lượng sinh viên
 * Hỗ trợ tìm kiếm:
 * - Mã sinh viên
 * - Họ tên
 * - Tài khoản
 * - Lớp
 * - Khoa
 */
public function count(string $q = ''): int
{
    $like = '%' . trim($q) . '%';

    $sql = "
        SELECT COUNT(*)
        FROM students s

        INNER JOIN users u
            ON u.id = s.user_id

        LEFT JOIN faculties f
            ON f.id = s.faculty_id

        WHERE
            s.student_code LIKE :q1
            OR u.full_name LIKE :q2
            OR u.username LIKE :q3
            OR s.class_name LIKE :q4
            OR f.name LIKE :q5
    ";

    $stmt = $this->pdo->prepare($sql);

    $stmt->execute([
        ':q1' => $like,
        ':q2' => $like,
        ':q3' => $like,
        ':q4' => $like,
        ':q5' => $like
    ]);

    return (int)$stmt->fetchColumn();
}


/**
 * Lấy danh sách sinh viên có tìm kiếm + phân trang
 */
public function findPage(
    string $q,
    int $limit,
    int $offset
): array {

    $like = '%' . trim($q) . '%';

    $limit = max(
        1,
        min((int)$limit, 100)
    );

    $offset = max(
        0,
        (int)$offset
    );

    $sql = "
        SELECT
            s.user_id,
            s.student_code,
            u.username,
            u.full_name,
            u.status,
            s.class_name,
            f.name AS faculty_name,
            s.cohort

        FROM students s

        INNER JOIN users u
            ON u.id = s.user_id

        LEFT JOIN faculties f
            ON f.id = s.faculty_id

        WHERE
            s.student_code LIKE :q1
            OR u.full_name LIKE :q2
            OR u.username LIKE :q3
            OR s.class_name LIKE :q4
            OR f.name LIKE :q5

        ORDER BY
            s.student_code ASC

        LIMIT :lim
        OFFSET :off
    ";

    $stmt = $this->pdo->prepare($sql);

    $stmt->bindValue(
        ':q1',
        $like,
        PDO::PARAM_STR
    );

    $stmt->bindValue(
        ':q2',
        $like,
        PDO::PARAM_STR
    );

    $stmt->bindValue(
        ':q3',
        $like,
        PDO::PARAM_STR
    );

    $stmt->bindValue(
        ':q4',
        $like,
        PDO::PARAM_STR
    );

    $stmt->bindValue(
        ':q5',
        $like,
        PDO::PARAM_STR
    );

    $stmt->bindValue(
        ':lim',
        $limit,
        PDO::PARAM_INT
    );

    $stmt->bindValue(
        ':off',
        $offset,
        PDO::PARAM_INT
    );

    $stmt->execute();

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

    /**
     * Tạo sinh viên + tài khoản
     *
     * Nếu tạo tài khoản hoặc tạo sinh viên thất bại
     * thì rollback toàn bộ transaction.
     */
    public function create(array $d): int
    {
        $this->pdo->beginTransaction();

        try {
            $userRepository = new UserRepository($this->pdo);

            $userId = $userRepository->create([
                'username'  => $d['username'],
                'password'  => $d['password'],
                'full_name' => $d['full_name'],
                'role'      => 'student'
            ]);

            $sql = "
                INSERT INTO students
                (
                    user_id,
                    student_code,
                    faculty_id,
                    class_name,
                    cohort
                )
                VALUES
                (
                    :user_id,
                    :student_code,
                    :faculty_id,
                    :class_name,
                    :cohort
                )
            ";

            $stmt = $this->pdo->prepare($sql);

            $stmt->execute([
                ':user_id'      => $userId,
                ':student_code' => $d['student_code'],
                ':faculty_id'   => $d['faculty_id'],
                ':class_name'   => $d['class_name'],
                ':cohort'       => $d['cohort']
            ]);

            $this->pdo->commit();

            return $userId;

        } catch (\Throwable $e) {

            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }

            throw $e;
        }
    }

    /**
     * Cập nhật sinh viên
     */
    public function update(int $id, array $d): bool
    {
        $this->pdo->beginTransaction();

        try {
            $userRepository = new UserRepository($this->pdo);

            $userData = [
                'full_name' => $d['full_name'],
                'status'    => $d['status']
            ];

            /*
             * Chỉ cập nhật mật khẩu nếu người dùng
             * thực sự nhập mật khẩu mới.
             */
            if (
                isset($d['password']) &&
                trim($d['password']) !== ''
            ) {
                $userData['password'] = $d['password'];
            }

            $userRepository->update($id, $userData);

            $sql = "
                UPDATE students
                SET
                    student_code = :student_code,
                    faculty_id = :faculty_id,
                    class_name = :class_name,
                    cohort = :cohort
                WHERE user_id = :user_id
            ";

            $stmt = $this->pdo->prepare($sql);

            $stmt->execute([
                ':student_code' => $d['student_code'],
                ':faculty_id'   => $d['faculty_id'],
                ':class_name'   => $d['class_name'],
                ':cohort'       => $d['cohort'],
                ':user_id'      => $id
            ]);

            $this->pdo->commit();

            return true;

        } catch (\Throwable $e) {

            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }

            throw $e;
        }
    }

    /**
     * Xóa sinh viên
     *
     * Xóa tài khoản users.
     * Nếu database có ON DELETE CASCADE,
     * bản ghi students tương ứng sẽ được xóa theo.
     */
    public function delete(int $id): bool
    {
        $this->pdo->beginTransaction();

        try {
            $sql = "
                DELETE FROM users
                WHERE id = :id
                  AND role = :role
            ";

            $stmt = $this->pdo->prepare($sql);

            $stmt->execute([
                ':id'   => $id,
                ':role' => 'student'
            ]);

            $deleted = $stmt->rowCount() > 0;

            $this->pdo->commit();

            return $deleted;

        } catch (\Throwable $e) {

            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }

            throw $e;
        }
    }
}