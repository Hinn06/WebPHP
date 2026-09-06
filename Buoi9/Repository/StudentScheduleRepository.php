<?php

namespace App\Repository;

class StudentScheduleRepository
{
    private \PDO $pdo;

    public function __construct(\PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * Lấy lịch học của sinh viên
     *
     * Chỉ lấy những lớp học phần mà sinh viên
     * đã đăng ký trong bảng registrations.
     */
    public function getStudentSchedule(
        int $studentId,
        ?int $semesterId = null
    ): array {

        $sql = "
            SELECT
                c.id AS class_id,
                c.class_code,

                co.id AS course_id,
                co.code AS course_code,
                co.name AS course_name,
                co.credits,

                u.full_name AS lecturer_name,

                cs.id AS schedule_id,
                cs.weekday,
                cs.start_period,
                cs.end_period,
                cs.start_date,
                cs.end_date,
                cs.room,

                sem.id AS semester_id,
                sem.name AS semester_name,
                sem.academic_year

            FROM registrations r

            INNER JOIN classes c
                ON c.id = r.class_id

            INNER JOIN courses co
                ON co.id = c.course_id

            LEFT JOIN users u
                ON u.id = c.lecturer_id

            INNER JOIN class_schedules cs
                ON cs.class_id = c.id

            INNER JOIN semesters sem
                ON sem.id = c.semester_id

            WHERE r.student_id = :student_id
        ";

        $params = [
            ':student_id' => $studentId
        ];

        /*
         * Nếu sinh viên chọn học kỳ
         * thì chỉ lấy lịch của học kỳ đó.
         */
        if ($semesterId !== null && $semesterId > 0) {

            $sql .= "
                AND c.semester_id = :semester_id
            ";

            $params[':semester_id'] = $semesterId;
        }

        /*
         * Sắp xếp:
         * 1. Học phần
         * 2. Thứ
         * 3. Tiết bắt đầu
         * 4. Ngày bắt đầu
         */
        $sql .= "
            ORDER BY
                co.name ASC,
                cs.weekday ASC,
                cs.start_period ASC,
                cs.start_date ASC
        ";

        $stmt = $this->pdo->prepare($sql);

        $stmt->execute($params);

        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
}