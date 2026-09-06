<?php

namespace App\Repository;

use PDO;

class ClassRepository
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * Lấy tất cả lớp học phần
     */
    public function findAll(): array
    {
        $sql = "
            SELECT
                cl.*,
                co.code AS course_code,
                co.name AS course_name,
                s.name AS semester_name,
                u.full_name AS lecturer_name,

                (
                    SELECT COUNT(*)
                    FROM registrations r
                    WHERE r.class_id = cl.id
                ) AS registered_count,

                (
                    SELECT COUNT(*)
                    FROM class_schedules cs
                    WHERE cs.class_id = cl.id
                ) AS schedule_count

            FROM classes cl

            INNER JOIN courses co
                ON co.id = cl.course_id

            INNER JOIN semesters s
                ON s.id = cl.semester_id

            INNER JOIN users u
                ON u.id = cl.lecturer_id

            ORDER BY
                s.study_start DESC,
                co.code ASC,
                cl.class_code ASC
        ";

        $stmt = $this->pdo->query($sql);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }


    /**
     * Đếm số lớp
     */
  public function count(string $q = ''): int
{
    $like = '%' . trim($q) . '%';

    $sql = "
        SELECT COUNT(DISTINCT c.id)

        FROM classes c

        INNER JOIN courses co
            ON co.id = c.course_id

        INNER JOIN semesters s
            ON s.id = c.semester_id

        LEFT JOIN lecturers l
            ON l.user_id = c.lecturer_id

        LEFT JOIN users u
            ON u.id = l.user_id

        WHERE c.class_code LIKE :q1
           OR co.code LIKE :q2
           OR co.name LIKE :q3
           OR s.name LIKE :q4
           OR u.full_name LIKE :q5
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
     * Tìm kiếm + phân trang
     */
    /**
 * Tìm kiếm + phân trang lớp học phần
 */
public function findPage(
    string $q,
    int $limit,
    int $offset
): array {

    $q = trim($q);

    $limit = max(
        1,
        min($limit, 100)
    );

    $offset = max(
        0,
        $offset
    );

    $like =
        '%' . $q . '%';

    $sql = "
        SELECT
            cl.id,
            cl.class_code,
            cl.course_id,
            cl.semester_id,
            cl.lecturer_id,
            cl.max_students,
            cl.status,

            co.code AS course_code,
            co.name AS course_name,

            s.name AS semester_name,

            u.full_name AS lecturer_name,

            (
                SELECT COUNT(*)
                FROM registrations r
                WHERE r.class_id = cl.id
            ) AS registered_count

        FROM classes cl

        INNER JOIN courses co
            ON co.id = cl.course_id

        INNER JOIN semesters s
            ON s.id = cl.semester_id

        LEFT JOIN users u
            ON u.id = cl.lecturer_id

        WHERE
            cl.class_code LIKE :q1

            OR co.code LIKE :q2

            OR co.name LIKE :q3

            OR s.name LIKE :q4

            OR u.full_name LIKE :q5

        ORDER BY
            s.study_start DESC,
            co.code ASC,
            cl.class_code ASC

        LIMIT :lim OFFSET :off
    ";

    $stmt =
        $this->pdo->prepare($sql);

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

    $rows =
        $stmt->fetchAll(
            PDO::FETCH_ASSOC
        );

    /**
     * Gắn lịch học cho từng lớp
     */
    foreach ($rows as &$row) {

        $row['schedules'] =
            $this->getSchedules(
                (int)$row['id']
            );
    }

    unset($row);

    return $rows;
}
    /**
     * Lấy chi tiết lớp học phần
     */
    public function findById(int $id): ?array
    {
        $sql = "
            SELECT
                cl.*,
                co.code AS course_code,
                co.name AS course_name,
                co.credits,
                s.name AS semester_name,
                s.study_start,
                s.study_end,
                u.full_name AS lecturer_name

            FROM classes cl

            INNER JOIN courses co
                ON co.id = cl.course_id

            INNER JOIN semesters s
                ON s.id = cl.semester_id

            INNER JOIN users u
                ON u.id = cl.lecturer_id

            WHERE cl.id = :id

            LIMIT 1
        ";

        $stmt = $this->pdo->prepare($sql);

        $stmt->execute([
            ':id' => $id
        ]);

        $class = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$class) {
            return null;
        }

        /**
         * Lấy danh sách sinh viên
         */
        $sql = "
            SELECT
                r.id AS registration_id,
                u.id AS student_id,
                u.full_name,
                s.student_code,
                s.class_name

            FROM registrations r

            INNER JOIN users u
                ON u.id = r.student_id

            INNER JOIN students s
                ON s.user_id = u.id

            WHERE r.class_id = :class_id

            ORDER BY
                s.student_code ASC
        ";

        $stmt = $this->pdo->prepare($sql);

        $stmt->execute([
            ':class_id' => $id
        ]);

        $class['students'] =
            $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $class;
    }


    /**
     * Lấy toàn bộ lịch học của một lớp
     */
    public function getSchedules(int $classId): array
    {
        $sql = "
            SELECT
                id,
                class_id,
                weekday,
                start_period,
                end_period,
                start_date,
                end_date,
                room

            FROM class_schedules

            WHERE class_id = :class_id

            ORDER BY
                weekday ASC,
                start_period ASC,
                start_date ASC
        ";

        $stmt = $this->pdo->prepare($sql);

        $stmt->execute([
            ':class_id' => $classId
        ]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }


    /**
     * Lấy danh sách lớp mà giảng viên đang dạy
     */
    public function findLecturerClasses(
        int $lecturerId
    ): array {

        $sql = "
            SELECT
                cl.*,
                co.code AS course_code,
                co.name AS course_name,
                s.name AS semester_name,

                (
                    SELECT COUNT(*)
                    FROM registrations r
                    WHERE r.class_id = cl.id
                ) AS registered_count,

                (
                    SELECT GROUP_CONCAT(
                        CONCAT(
                            'Thứ ',
                            CASE
                                WHEN cs.weekday = 8
                                    THEN 'Chủ nhật'
                                ELSE cs.weekday
                            END,
                            ' - Tiết ',
                            cs.start_period,
                            '-',
                            cs.end_period,
                            ' (',
                            DATE_FORMAT(
                                cs.start_date,
                                '%d/%m/%Y'
                            ),
                            ' - ',
                            DATE_FORMAT(
                                cs.end_date,
                                '%d/%m/%Y'
                            ),
                            ') - ',
                            cs.room
                        )
                        ORDER BY
                            cs.weekday,
                            cs.start_period
                        SEPARATOR ' | '
                    )

                    FROM class_schedules cs

                    WHERE cs.class_id = cl.id
                ) AS schedule_text

            FROM classes cl

            INNER JOIN courses co
                ON co.id = cl.course_id

            INNER JOIN semesters s
                ON s.id = cl.semester_id

            WHERE cl.lecturer_id = :lecturer_id

            ORDER BY
                s.study_start DESC,
                cl.class_code ASC
        ";

        $stmt = $this->pdo->prepare($sql);

        $stmt->execute([
            ':lecturer_id' => $lecturerId
        ]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }


    /**
     * Tạo lớp học phần
     */
    public function create(
        array $d,
        array $schedules
    ): int {

        $courseId =
            (int)$d['course_id'];

        $semesterId =
            (int)$d['semester_id'];

        $teacherId =
            (int)$d['teacher_id'];


        /**
         * Kiểm tra giảng viên có thể dạy học phần
         */
        $this->validateTeacher(
            $courseId,
            $teacherId
        );


        /**
         * Kiểm tra học phần được mở
         * trong học kỳ
         */
        $this->validateCourseSemester(
            $courseId,
            $semesterId
        );


        /**
         * Kiểm tra toàn bộ lịch
         */
        $this->validateSchedules(
            $schedules,
            $teacherId,
            $semesterId,
            0
        );


        $this->pdo->beginTransaction();

        try {

            /**
             * Thêm lớp
             */
            $sql = "
                INSERT INTO classes
                (
                    class_code,
                    course_id,
                    semester_id,
                    lecturer_id,
                    max_students,
                    status
                )
                VALUES
                (
                    :class_code,
                    :course_id,
                    :semester_id,
                    :lecturer_id,
                    :max_students,
                    :status
                )
            ";

            $stmt = $this->pdo->prepare($sql);

            $stmt->execute([
                ':class_code' =>
                    $d['class_code'],

                ':course_id' =>
                    $courseId,

                ':semester_id' =>
                    $semesterId,

                ':lecturer_id' =>
                    $teacherId,

                ':max_students' =>
                    (int)$d['max_students'],

                ':status' =>
                    (int)$d['status']
            ]);

            $classId =
                (int)$this->pdo->lastInsertId();


            /**
             * Thêm các lịch
             */
            $this->insertSchedules(
                $classId,
                $schedules
            );


            $this->pdo->commit();

            return $classId;

        } catch (\Throwable $e) {

            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }

            throw $e;
        }
    }


    /**
     * Cập nhật lớp
     */
    public function update(
        int $id,
        array $d,
        array $schedules
    ): bool {

        $courseId =
            (int)$d['course_id'];

        $semesterId =
            (int)$d['semester_id'];

        $teacherId =
            (int)$d['teacher_id'];


        /**
         * Kiểm tra giảng viên
         */
        $this->validateTeacher(
            $courseId,
            $teacherId
        );


        /**
         * Kiểm tra học phần - học kỳ
         */
        $this->validateCourseSemester(
            $courseId,
            $semesterId
        );


        /**
         * Kiểm tra lịch mới
         *
         * $id được truyền vào để không
         * tự kiểm tra chính lớp đang sửa.
         */
        $this->validateSchedules(
            $schedules,
            $teacherId,
            $semesterId,
            $id
        );


        $this->pdo->beginTransaction();

        try {

            /**
             * Cập nhật thông tin lớp
             */
            $sql = "
                UPDATE classes

                SET
                    class_code = :class_code,
                    course_id = :course_id,
                    semester_id = :semester_id,
                    lecturer_id = :lecturer_id,
                    max_students = :max_students,
                    status = :status

                WHERE id = :id
            ";

            $stmt = $this->pdo->prepare($sql);

            $stmt->execute([
                ':class_code' =>
                    $d['class_code'],

                ':course_id' =>
                    $courseId,

                ':semester_id' =>
                    $semesterId,

                ':lecturer_id' =>
                    $teacherId,

                ':max_students' =>
                    (int)$d['max_students'],

                ':status' =>
                    (int)$d['status'],

                ':id' =>
                    $id
            ]);


            /**
             * Xóa lịch cũ
             */
            $stmt = $this->pdo->prepare("
                DELETE FROM class_schedules
                WHERE class_id = :class_id
            ");

            $stmt->execute([
                ':class_id' => $id
            ]);


            /**
             * Thêm lịch mới
             */
            $this->insertSchedules(
                $id,
                $schedules
            );


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
     * Thêm nhiều lịch học
     */
    private function insertSchedules(
        int $classId,
        array $schedules
    ): void {

        $sql = "
            INSERT INTO class_schedules
            (
                class_id,
                weekday,
                start_period,
                end_period,
                start_date,
                end_date,
                room
            )
            VALUES
            (
                :class_id,
                :weekday,
                :start_period,
                :end_period,
                :start_date,
                :end_date,
                :room
            )
        ";

        $stmt = $this->pdo->prepare($sql);

        foreach ($schedules as $schedule) {

            $stmt->execute([
                ':class_id' =>
                    $classId,

                ':weekday' =>
                    (int)$schedule['weekday'],

                ':start_period' =>
                    (int)$schedule['start_period'],

                ':end_period' =>
                    (int)$schedule['end_period'],

                ':start_date' =>
                    $schedule['start_date'],

                ':end_date' =>
                    $schedule['end_date'],

                ':room' =>
                    $schedule['room']
            ]);
        }
    }


    /**
     * Kiểm tra giảng viên được phép dạy học phần
     */
    private function validateTeacher(
        int $courseId,
        int $teacherId
    ): void {

        $sql = "
            SELECT 1

            FROM course_lecturers

            WHERE course_id = :course_id

              AND lecturer_id = :lecturer_id

            LIMIT 1
        ";

        $stmt = $this->pdo->prepare($sql);

        $stmt->execute([
            ':course_id' =>
                $courseId,

            ':lecturer_id' =>
                $teacherId
        ]);

        if (!$stmt->fetchColumn()) {

            throw new \RuntimeException(
                'Giảng viên này chưa được khai báo là người có thể dạy học phần.'
            );
        }
    }


    /**
     * Kiểm tra học phần được mở trong học kỳ
     */
    private function validateCourseSemester(
        int $courseId,
        int $semesterId
    ): void {

        $sql = "
            SELECT 1

            FROM course_semesters

            WHERE course_id = :course_id

              AND semester_id = :semester_id

            LIMIT 1
        ";

        $stmt = $this->pdo->prepare($sql);

        $stmt->execute([
            ':course_id' =>
                $courseId,

            ':semester_id' =>
                $semesterId
        ]);

        if (!$stmt->fetchColumn()) {

            throw new \RuntimeException(
                'Học phần chưa được mở trong học kỳ đã chọn.'
            );
        }
    }


    /**
     * Kiểm tra toàn bộ lịch học
     *
     * QUY TẮC TRÙNG LỊCH:
     *
     * Cùng học kỳ
     * + cùng thứ
     * + khoảng tiết giao nhau
     * + khoảng ngày giao nhau
     *
     * Trong đó khoảng tiết BAO GỒM
     * cả tiết bắt đầu và tiết kết thúc.
     */
    private function validateSchedules(
        array $schedules,
        int $teacherId,
        int $semesterId,
        int $excludeClassId
    ): void {

        if (!$schedules) {

            throw new \RuntimeException(
                'Lớp phải có ít nhất một lịch học.'
            );
        }


        /**
         * Lấy thời gian học kỳ
         */
        $stmt = $this->pdo->prepare("
            SELECT
                study_start,
                study_end

            FROM semesters

            WHERE id = :id

            LIMIT 1
        ");

        $stmt->execute([
            ':id' => $semesterId
        ]);

        $semester =
            $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$semester) {

            throw new \RuntimeException(
                'Học kỳ không tồn tại.'
            );
        }


        /**
         * Kiểm tra từng dòng lịch
         */
        foreach ($schedules as $index => $schedule) {

            $number = $index + 1;

            $weekday =
                (int)$schedule['weekday'];

            $startPeriod =
                (int)$schedule['start_period'];

            $endPeriod =
                (int)$schedule['end_period'];

            $startDate =
                trim($schedule['start_date']);

            $endDate =
                trim($schedule['end_date']);

            $room =
                trim($schedule['room']);


            /**
             * Kiểm tra thứ
             *
             * 2 = Thứ 2
             * ...
             * 7 = Thứ 7
             * 8 = Chủ nhật
             */
            if (
                $weekday < 2 ||
                $weekday > 8
            ) {

                throw new \RuntimeException(
                    "Lịch {$number}: Thứ không hợp lệ."
                );
            }


            /**
             * Kiểm tra khoảng tiết
             */
            if (
                $startPeriod < 1 ||
                $startPeriod > 15 ||
                $endPeriod < 1 ||
                $endPeriod > 15 ||
                $endPeriod < $startPeriod
            ) {

                throw new \RuntimeException(
                    "Lịch {$number}: Khoảng tiết không hợp lệ."
                );
            }


            /**
             * Kiểm tra ngày
             */
            if (
                !$this->validDate($startDate) ||
                !$this->validDate($endDate)
            ) {

                throw new \RuntimeException(
                    "Lịch {$number}: Ngày học không hợp lệ."
                );
            }


            /**
             * Ngày bắt đầu không được
             * sau ngày kết thúc
             */
            if ($startDate > $endDate) {

                throw new \RuntimeException(
                    "Lịch {$number}: Ngày kết thúc không được nhỏ hơn ngày bắt đầu."
                );
            }


            /**
             * Ngày học phải nằm trong học kỳ
             */
            if (
                $startDate <
                $semester['study_start']
                ||
                $endDate >
                $semester['study_end']
            ) {

                throw new \RuntimeException(
                    "Lịch {$number}: Ngày học phải nằm trong thời gian của học kỳ."
                );
            }


            /**
             * Kiểm tra phòng
             */
            if (
                $room === '' ||
                mb_strlen($room) > 100
            ) {

                throw new \RuntimeException(
                    "Lịch {$number}: Phòng học bắt buộc và tối đa 100 ký tự."
                );
            }
        }


        /**
         * =========================================================
         * KIỂM TRA CÁC DÒNG LỊCH TRONG CÙNG MỘT LỚP
         * =========================================================
         *
         * Dùng <= và >= cho khoảng tiết.
         *
         * Ví dụ:
         *
         * Lịch A: 3-4
         * Lịch B: 4-6
         *
         * => TRÙNG vì cùng sử dụng tiết 4.
         */
        $count =
            count($schedules);

        for ($i = 0; $i < $count; $i++) {

            for ($j = $i + 1; $j < $count; $j++) {

                $a =
                    $schedules[$i];

                $b =
                    $schedules[$j];


                /**
                 * Cùng thứ
                 */
                $sameWeekday =
                    (int)$a['weekday']
                    ===
                    (int)$b['weekday'];


                /**
                 * Khoảng tiết giao nhau
                 *
                 * BAO GỒM hai đầu.
                 */
                $periodOverlap =
                    (int)$a['start_period']
                    <=
                    (int)$b['end_period']

                    &&

                    (int)$a['end_period']
                    >=
                    (int)$b['start_period'];


                /**
                 * Khoảng ngày giao nhau
                 */
                $dateOverlap =
                    $a['start_date']
                    <=
                    $b['end_date']

                    &&

                    $a['end_date']
                    >=
                    $b['start_date'];


                /**
                 * Nếu đồng thời thỏa cả 3
                 * điều kiện => trùng
                 */
                if (
                    $sameWeekday &&
                    $periodOverlap &&
                    $dateOverlap
                ) {

                    throw new \RuntimeException(
                        'Lịch ' .
                        ($i + 1) .
                        ' và lịch ' .
                        ($j + 1) .
                        ' của lớp đang bị trùng thời gian.'
                    );
                }
            }
        }


        /**
         * =========================================================
         * KIỂM TRA TRÙNG LỊCH GIẢNG VIÊN
         * =========================================================
         */
        foreach ($schedules as $schedule) {

            $this->validateTeacherScheduleConflict(
                $teacherId,
                $semesterId,
                (int)$schedule['weekday'],
                (int)$schedule['start_period'],
                (int)$schedule['end_period'],
                $schedule['start_date'],
                $schedule['end_date'],
                $excludeClassId
            );
        }


        /**
         * =========================================================
         * KIỂM TRA TRÙNG PHÒNG
         * =========================================================
         */
        foreach ($schedules as $schedule) {

            $this->validateRoomConflict(
                trim($schedule['room']),
                $semesterId,
                (int)$schedule['weekday'],
                (int)$schedule['start_period'],
                (int)$schedule['end_period'],
                $schedule['start_date'],
                $schedule['end_date'],
                $excludeClassId
            );
        }
    }


    /**
     * Kiểm tra giảng viên có bị trùng lịch
     *
     * Điều kiện:
     *
     * cùng học kỳ
     * + cùng thứ
     * + trùng tiết
     * + giao nhau về ngày
     */
    private function validateTeacherScheduleConflict(
        int $teacherId,
        int $semesterId,
        int $weekday,
        int $startPeriod,
        int $endPeriod,
        string $startDate,
        string $endDate,
        int $excludeClassId
    ): void {

        $sql = "
            SELECT
                cl.class_code

            FROM classes cl

            INNER JOIN class_schedules cs
                ON cs.class_id = cl.id

            WHERE cl.lecturer_id = :teacher_id

              AND cl.semester_id = :semester_id

              AND cl.status = 1

              AND cl.id <> :exclude_class_id

              /*
               * Cùng thứ
               */
              AND cs.weekday = :weekday

              /*
               * TRÙNG TIẾT
               *
               * Bao gồm cả hai đầu.
               *
               * Ví dụ:
               * 3-4 và 4-6 => TRÙNG
               */
              AND cs.start_period <= :end_period
              AND cs.end_period >= :start_period

              /*
               * GIAO NHAU VỀ NGÀY
               */
              AND cs.start_date <= :end_date
              AND cs.end_date >= :start_date

            LIMIT 1
        ";

        $stmt =
            $this->pdo->prepare($sql);

        $stmt->execute([
            ':teacher_id' =>
                $teacherId,

            ':semester_id' =>
                $semesterId,

            ':exclude_class_id' =>
                $excludeClassId,

            ':weekday' =>
                $weekday,

            ':start_period' =>
                $startPeriod,

            ':end_period' =>
                $endPeriod,

            ':start_date' =>
                $startDate,

            ':end_date' =>
                $endDate
        ]);

        $classCode =
            $stmt->fetchColumn();

        if ($classCode) {

            throw new \RuntimeException(
                'Giảng viên bị trùng lịch với lớp ' .
                $classCode .
                '.'
            );
        }
    }


    /**
     * Kiểm tra phòng có bị trùng
     *
     * Điều kiện:
     *
     * cùng phòng
     * + cùng học kỳ
     * + cùng thứ
     * + trùng tiết
     * + giao nhau về ngày
     */
    private function validateRoomConflict(
        string $room,
        int $semesterId,
        int $weekday,
        int $startPeriod,
        int $endPeriod,
        string $startDate,
        string $endDate,
        int $excludeClassId
    ): void {

        $sql = "
            SELECT
                cl.class_code

            FROM classes cl

            INNER JOIN class_schedules cs
                ON cs.class_id = cl.id

            WHERE cs.room = :room

              AND cl.semester_id = :semester_id

              AND cl.status = 1

              AND cl.id <> :exclude_class_id

              /*
               * Cùng thứ
               */
              AND cs.weekday = :weekday

              /*
               * TRÙNG TIẾT
               *
               * Bao gồm cả hai đầu.
               */
              AND cs.start_period <= :end_period
              AND cs.end_period >= :start_period

              /*
               * GIAO NHAU VỀ NGÀY
               */
              AND cs.start_date <= :end_date
              AND cs.end_date >= :start_date

            LIMIT 1
        ";

        $stmt =
            $this->pdo->prepare($sql);

        $stmt->execute([
            ':room' =>
                $room,

            ':semester_id' =>
                $semesterId,

            ':exclude_class_id' =>
                $excludeClassId,

            ':weekday' =>
                $weekday,

            ':start_period' =>
                $startPeriod,

            ':end_period' =>
                $endPeriod,

            ':start_date' =>
                $startDate,

            ':end_date' =>
                $endDate
        ]);

        $classCode =
            $stmt->fetchColumn();

        if ($classCode) {

            throw new \RuntimeException(
                'Phòng học bị trùng với lớp ' .
                $classCode .
                '.'
            );
        }
    }


    /**
     * Kiểm tra ngày có đúng định dạng
     * YYYY-MM-DD hay không
     */
    private function validDate(
        string $date
    ): bool {

        $d =
            \DateTime::createFromFormat(
                'Y-m-d',
                $date
            );

        return
            $d !== false
            &&
            $d->format('Y-m-d') === $date;
    }


    /**
     * Xóa lớp
     *
     * class_schedules sẽ được xóa
     * nhờ ON DELETE CASCADE.
     */
    public function delete(int $id): bool
    {
        $stmt = $this->pdo->prepare("
            DELETE FROM classes
            WHERE id = :id
        ");

        return $stmt->execute([
            ':id' => $id
        ]);
    }
}