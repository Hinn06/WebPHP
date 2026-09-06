<?php

namespace App\Repository;

use PDO;

class CourseRepository
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * Lấy tất cả học phần
     */
    public function findAll(): array
    {
        $sql = "
            SELECT
                c.*,
                GROUP_CONCAT(
                    DISTINCT f.name
                    ORDER BY f.name
                    SEPARATOR ', '
                ) AS faculties
            FROM courses c
            LEFT JOIN course_faculties cf
                ON cf.course_id = c.id
            LEFT JOIN faculties f
                ON f.id = cf.faculty_id
            GROUP BY c.id
            ORDER BY c.code ASC
        ";
        $stmt = $this->pdo->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
/**
 * Đếm số lượng học phần
 *
 * Hỗ trợ tìm kiếm:
 * - Mã học phần
 * - Tên học phần
 * - Khoa
 */
public function count(string $q = ''): int
{
    $like = '%' . trim($q) . '%';

    $sql = "
        SELECT COUNT(DISTINCT c.id)

        FROM courses c

        LEFT JOIN course_faculties cf
            ON cf.course_id = c.id

        LEFT JOIN faculties f
            ON f.id = cf.faculty_id

        WHERE
            c.code LIKE :q1
            OR c.name LIKE :q2
            OR f.name LIKE :q3
    ";

    $stmt = $this->pdo->prepare($sql);

    $stmt->execute([
        ':q1' => $like,
        ':q2' => $like,
        ':q3' => $like
    ]);

    return (int)$stmt->fetchColumn();
}


/**
 * Lấy danh sách học phần có tìm kiếm + phân trang
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
            c.id,
            c.code,
            c.name,
            c.credits,
            c.status,

            GROUP_CONCAT(
                DISTINCT f.name
                ORDER BY f.name
                SEPARATOR ', '
            ) AS faculties

        FROM courses c

        LEFT JOIN course_faculties cf
            ON cf.course_id = c.id

        LEFT JOIN faculties f
            ON f.id = cf.faculty_id

        WHERE
            c.code LIKE :q1
            OR c.name LIKE :q2
            OR f.name LIKE :q3

        GROUP BY
            c.id,
            c.code,
            c.name,
            c.credits,
            c.status

        ORDER BY
            c.code ASC

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
     * Lấy học phần theo ID
     */
    public function findById(int $id): ?array
    {
        $sql = "
            SELECT *
            FROM courses
            WHERE id = :id
            LIMIT 1
        ";

        $stmt = $this->pdo->prepare($sql);

        $stmt->execute([
            'id' => $id
        ]);

        $course = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$course) {
            return null;
        }

        // Khoa
        $stmt = $this->pdo->prepare("
            SELECT faculty_id
            FROM course_faculties
            WHERE course_id = :course_id
        ");

        $stmt->execute([
            'course_id' => $id
        ]);

        $course['faculty_ids'] = array_map(
            'intval',
            $stmt->fetchAll(PDO::FETCH_COLUMN)
        );

        // Học kỳ
        $stmt = $this->pdo->prepare("
            SELECT semester_id
            FROM course_semesters
            WHERE course_id = :course_id
        ");

        $stmt->execute([
            'course_id' => $id
        ]);

        $course['semester_ids'] = array_map(
            'intval',
            $stmt->fetchAll(PDO::FETCH_COLUMN)
        );

        // Giảng viên có thể dạy
        $stmt = $this->pdo->prepare("
            SELECT lecturer_id
            FROM course_lecturers
            WHERE course_id = :course_id
        ");

        $stmt->execute([
            'course_id' => $id
        ]);

        $course['lecturer_ids'] = array_map(
            'intval',
            $stmt->fetchAll(PDO::FETCH_COLUMN)
        );

        return $course;
    }

    /**
     * Kiểm tra mã học phần đã tồn tại
     */
    public function codeExists(
        string $code,
        int $except = 0
    ): bool {
        $sql = "
            SELECT id
            FROM courses
            WHERE code = :code
              AND id <> :except_id
            LIMIT 1
        ";

        $stmt = $this->pdo->prepare($sql);

        $stmt->execute([
            'code'      => $code,
            'except_id' => $except
        ]);

        return (bool) $stmt->fetchColumn();
    }

    /**
     * Tạo học phần
     */
    public function create(array $d): int
    {
        $this->pdo->beginTransaction();

        try {

            $sql = "
                INSERT INTO courses
                (
                    code,
                    name,
                    credits,
                    description,
                    status
                )
                VALUES
                (
                    :code,
                    :name,
                    :credits,
                    :description,
                    1
                )
            ";

            $stmt = $this->pdo->prepare($sql);

            $stmt->execute([
                'code'        => $d['code'],
                'name'        => $d['name'],
                'credits'     => $d['credits'],
                'description' => $d['description']
            ]);

            $id = (int) $this->pdo->lastInsertId();

            $this->syncRelations($id, $d);

            $this->pdo->commit();

            return $id;

        } catch (\Throwable $e) {

            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }

            throw $e;
        }
    }

    /**
     * Cập nhật học phần
     */
    public function update(int $id, array $d): bool
    {
        $this->pdo->beginTransaction();

        try {

            $sql = "
                UPDATE courses
                SET
                    code = :code,
                    name = :name,
                    credits = :credits,
                    description = :description,
                    status = :status
                WHERE id = :id
            ";

            $stmt = $this->pdo->prepare($sql);

            $stmt->execute([
                'code'        => $d['code'],
                'name'        => $d['name'],
                'credits'     => $d['credits'],
                'description' => $d['description'],
                'status'      => $d['status'],
                'id'          => $id
            ]);

            $this->syncRelations($id, $d);

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
     * Đồng bộ khoa + học kỳ + giảng viên
     */
    private function syncRelations(
        int $courseId,
        array $d
    ): void {

        // =========================
        // KHOA
        // =========================

        $stmt = $this->pdo->prepare("
            DELETE FROM course_faculties
            WHERE course_id = :course_id
        ");

        $stmt->execute([
            'course_id' => $courseId
        ]);

        $stmt = $this->pdo->prepare("
            INSERT INTO course_faculties
            (
                course_id,
                faculty_id
            )
            VALUES
            (
                :course_id,
                :faculty_id
            )
        ");

        foreach ($d['faculty_ids'] ?? [] as $facultyId) {

            $stmt->execute([
                'course_id' => $courseId,
                'faculty_id' => (int) $facultyId
            ]);
        }

        // =========================
        // HỌC KỲ
        // =========================

        $stmt = $this->pdo->prepare("
            DELETE FROM course_semesters
            WHERE course_id = :course_id
        ");

        $stmt->execute([
            'course_id' => $courseId
        ]);

        $stmt = $this->pdo->prepare("
            INSERT INTO course_semesters
            (
                course_id,
                semester_id
            )
            VALUES
            (
                :course_id,
                :semester_id
            )
        ");

        foreach ($d['semester_ids'] ?? [] as $semesterId) {

            $stmt->execute([
                'course_id'  => $courseId,
                'semester_id'=> (int) $semesterId
            ]);
        }

        // =========================
        // GIẢNG VIÊN CÓ THỂ DẠY
        // =========================

        $stmt = $this->pdo->prepare("
            DELETE FROM course_lecturers
            WHERE course_id = :course_id
        ");

        $stmt->execute([
            'course_id' => $courseId
        ]);

        $stmt = $this->pdo->prepare("
            INSERT INTO course_lecturers
            (
                course_id,
                lecturer_id
            )
            VALUES
            (
                :course_id,
                :lecturer_id
            )
        ");

        foreach ($d['lecturer_ids'] ?? [] as $lecturerId) {

            $stmt->execute([
                'course_id'   => $courseId,
                'lecturer_id' => (int) $lecturerId
            ]);
        }
    }

    /**
     * Xóa học phần
     */
    public function delete(int $id): bool
    {
        $stmt = $this->pdo->prepare("
            DELETE FROM courses
            WHERE id = :id
        ");

        return $stmt->execute([
            'id' => $id
        ]);
    }

public function availableForStudent(
    int $studentId,
    int $semesterId,
    string $keyword = ''
): array {

    $keyword = trim($keyword);

    $sql = "
        SELECT
            cl.*,

            co.code AS course_code,
            co.name AS course_name,
            co.credits,

            u.full_name AS lecturer_name,

            (
                SELECT COUNT(*)
                FROM registrations r
                WHERE r.class_id = cl.id
            ) AS registered_count

        FROM classes cl

        INNER JOIN courses co
            ON co.id = cl.course_id

        INNER JOIN users u
            ON u.id = cl.lecturer_id

        INNER JOIN students st
            ON st.user_id = :student_id

        INNER JOIN course_faculties cf
            ON cf.course_id = co.id
           AND cf.faculty_id = st.faculty_id

        INNER JOIN course_semesters csm
            ON csm.course_id = co.id
           AND csm.semester_id = cl.semester_id

        INNER JOIN semesters sem
            ON sem.id = cl.semester_id

        WHERE cl.semester_id = :semester_id

          AND cl.status = 1

          AND co.status = 1

          AND sem.status = 1

          AND sem.registration_open = 1

          AND CURDATE()
              BETWEEN sem.registration_start
              AND sem.registration_end
    ";

    /*
     * Nếu sinh viên nhập từ khóa thì tìm theo mã hoặc tên học phần.
     */
    if ($keyword !== '') {

        $sql .= "
            AND (
                co.code LIKE :keyword_code
                OR co.name LIKE :keyword_name
            )
        ";
    }

    $sql .= "
        ORDER BY
            co.code ASC,
            cl.class_code ASC
    ";

    $stmt = $this->pdo->prepare($sql);

    $params = [
        'student_id'  => $studentId,
        'semester_id' => $semesterId
    ];

    if ($keyword !== '') {

        $like = '%' . $keyword . '%';

        $params['keyword_code'] = $like;
        $params['keyword_name'] = $like;
    }

    $stmt->execute($params);

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
}