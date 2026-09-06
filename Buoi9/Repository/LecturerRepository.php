<?php

namespace App\Repository;

use PDO;

class LecturerRepository
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * Tìm giảng viên theo ID tài khoản
     */
    public function findById(int $id): ?array
    {
        $sql = "
            SELECT
                l.user_id,
                l.lecturer_code,
                l.faculty_id,
                u.username,
                u.full_name,
                u.status,
                f.name AS faculty_name
            FROM lecturers l
            INNER JOIN users u
                ON u.id = l.user_id
            LEFT JOIN faculties f
                ON f.id = l.faculty_id
            WHERE l.user_id = :id
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
     * Đếm số lượng giảng viên
     *
     * Có tìm kiếm theo:
     * - Mã giảng viên
     * - Họ tên
     */
  /**
 * Đếm số lượng giảng viên
 *
 * Hỗ trợ tìm kiếm:
 * - Mã giảng viên
 * - Họ tên
 * - Tài khoản
 * - Khoa
 */
public function count(string $q = ''): int
{
    $like = '%' . trim($q) . '%';

    $sql = "
        SELECT COUNT(*)
        FROM lecturers l

        INNER JOIN users u
            ON u.id = l.user_id

        LEFT JOIN faculties f
            ON f.id = l.faculty_id

        WHERE
            l.lecturer_code LIKE :q1
            OR u.full_name LIKE :q2
            OR u.username LIKE :q3
            OR f.name LIKE :q4
    ";

    $stmt = $this->pdo->prepare($sql);

    $stmt->execute([
        ':q1' => $like,
        ':q2' => $like,
        ':q3' => $like,
        ':q4' => $like
    ]);

    return (int)$stmt->fetchColumn();
}


/**
 * Lấy danh sách giảng viên có tìm kiếm + phân trang
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
            l.user_id,
            l.lecturer_code,
            u.username,
            u.full_name,
            u.status,
            l.faculty_id,
            f.name AS faculty_name

        FROM lecturers l

        INNER JOIN users u
            ON u.id = l.user_id

        LEFT JOIN faculties f
            ON f.id = l.faculty_id

        WHERE
            l.lecturer_code LIKE :q1
            OR u.full_name LIKE :q2
            OR u.username LIKE :q3
            OR f.name LIKE :q4

        ORDER BY
            l.lecturer_code ASC

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
     * Lấy tất cả giảng viên đang hoạt động.
     *
     * Hàm này được CourseController sử dụng
     * khi tạo/chỉnh sửa học phần.
     */
    public function allActive(): array
    {
        $sql = "
            SELECT
                l.user_id,
                l.lecturer_code,
                u.username,
                u.full_name,
                u.status,
                l.faculty_id,
                f.name AS faculty_name
            FROM lecturers l
            INNER JOIN users u
                ON u.id = l.user_id
            LEFT JOIN faculties f
                ON f.id = l.faculty_id
            WHERE u.status = 1
            ORDER BY u.full_name ASC
        ";

        $stmt = $this->pdo->prepare($sql);

        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Lấy danh sách giảng viên có thể dạy một học phần.
     *
     * Dựa vào bảng course_lecturers.
     */
    public function byCourse(int $courseId): array
    {
        $sql = "
            SELECT
                l.user_id,
                l.lecturer_code,
                u.full_name,
                l.faculty_id,
                f.name AS faculty_name
            FROM course_lecturers cl
            INNER JOIN lecturers l
                ON l.user_id = cl.lecturer_id
            INNER JOIN users u
                ON u.id = l.user_id
            LEFT JOIN faculties f
                ON f.id = l.faculty_id
            WHERE cl.course_id = :course_id
              AND u.status = 1
            ORDER BY u.full_name ASC
        ";

        $stmt = $this->pdo->prepare($sql);

        $stmt->execute([
            ':course_id' => $courseId
        ]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Tạo giảng viên + tài khoản
     */
    public function create(array $d): int
    {
        $this->pdo->beginTransaction();

        try {

            $userRepository = new UserRepository($this->pdo);

            $userId = $userRepository->create([
                'username' => $d['username'],
                'password' => $d['password'],
                'full_name' => $d['full_name'],
                'role' => 'teacher'
            ]);

            $sql = "
                INSERT INTO lecturers
                (
                    user_id,
                    lecturer_code,
                    faculty_id
                )
                VALUES
                (
                    :user_id,
                    :lecturer_code,
                    :faculty_id
                )
            ";

            $stmt = $this->pdo->prepare($sql);

            $stmt->execute([
                ':user_id' => $userId,
                ':lecturer_code' => $d['lecturer_code'],
                ':faculty_id' => $d['faculty_id']
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
     * Cập nhật giảng viên
     */
    public function update(int $id, array $d): bool
    {
        $this->pdo->beginTransaction();

        try {

            $userRepository = new UserRepository($this->pdo);

            $userData = [
                'full_name' => $d['full_name'],
                'status' => $d['status']
            ];

            if (
                isset($d['password']) &&
                trim($d['password']) !== ''
            ) {
                $userData['password'] = $d['password'];
            }

            $userRepository->update(
                $id,
                $userData
            );

            $sql = "
                UPDATE lecturers
                SET
                    lecturer_code = :lecturer_code,
                    faculty_id = :faculty_id
                WHERE user_id = :user_id
            ";

            $stmt = $this->pdo->prepare($sql);

            $stmt->execute([
                ':lecturer_code' => $d['lecturer_code'],
                ':faculty_id' => $d['faculty_id'],
                ':user_id' => $id
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
     * Xóa giảng viên
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
                ':id' => $id,
                ':role' => 'teacher'
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

    /**
     * Lấy lịch dạy của giảng viên
     */
    public function schedule(int $lecturerId): array
    {
        $sql = "
            SELECT
                c.class_code,
                cs.weekday,
                cs.start_period,
                cs.end_period,
                cs.room,
                cs.start_date,
                cs.end_date,
                co.code AS course_code,
                co.name AS course_name,
                s.name AS semester_name,
                s.academic_year,
                s.study_start,
                s.study_end
            FROM classes c
            INNER JOIN class_schedules cs
                ON cs.class_id = c.id
            INNER JOIN courses co
                ON co.id = c.course_id
            INNER JOIN semesters s
                ON s.id = c.semester_id
            WHERE c.lecturer_id = :lecturer_id
              AND c.status = 1
            ORDER BY
                cs.weekday ASC,
                cs.start_period ASC,
                cs.start_date ASC
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':lecturer_id' => $lecturerId
        ]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}