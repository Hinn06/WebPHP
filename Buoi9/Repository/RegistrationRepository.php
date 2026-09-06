<?php

namespace App\Repository;

use PDO;

class RegistrationRepository
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * Lấy lịch sử đăng ký của sinh viên
     *
     * Một lớp có thể có nhiều lịch học,
     * vì vậy mỗi lịch sẽ hiển thị thành một dòng.
     */
    public function history(int $studentId): array
    {
        $sql = "
            SELECT
                r.id,
                co.code AS course_code,
                co.name AS course_name,
                co.credits,
                cl.class_code,

                cs.room,
                cs.weekday,
                cs.start_period,
                cs.end_period,
                cs.start_date,
                cs.end_date,

                sem.name AS semester_name,
                r.registered_at

            FROM registrations r

            JOIN classes cl
                ON cl.id = r.class_id

            JOIN courses co
                ON co.id = cl.course_id

            JOIN semesters sem
                ON sem.id = cl.semester_id

            LEFT JOIN class_schedules cs
                ON cs.class_id = cl.id

            WHERE r.student_id = :id

            ORDER BY
                sem.study_start DESC,
                co.name ASC,
                cl.class_code ASC,
                cs.weekday ASC,
                cs.start_period ASC,
                cs.start_date ASC,
                r.registered_at DESC
        ";

        $stmt = $this->pdo->prepare($sql);

        $stmt->execute([
            ':id' => $studentId
        ]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }


    /**
     * Đăng ký học phần
     *
     * Kiểm tra:
     *
     * 1. Lớp có tồn tại
     * 2. Lớp đang hoạt động
     * 3. Học phần đang hoạt động
     * 4. Đợt đăng ký đang mở
     * 5. Đúng thời gian đăng ký
     * 6. Học phần thuộc khoa sinh viên
     * 7. Lớp còn chỗ
     * 8. Sinh viên chưa đăng ký lớp này
     * 9. Sinh viên chưa đăng ký học phần này
     *    trong cùng học kỳ
     * 10. Không trùng lịch
     */
    public function register(
        int $studentId,
        int $classId
    ): void {

        $this->pdo->beginTransaction();

        try {

            /*
             * =====================================================
             * 1. LẤY THÔNG TIN LỚP
             * =====================================================
             *
             * FOR UPDATE:
             * khóa bản ghi lớp trong transaction.
             *
             * Điều này giúp tránh trường hợp hai sinh viên
             * cùng đăng ký vào chỗ cuối cùng.
             */
            $sql = "
                SELECT
                    cl.id,
                    cl.class_code,
                    cl.course_id,
                    cl.semester_id,
                    cl.lecturer_id,
                    cl.max_students,
                    cl.status,

                    sem.name AS semester_name,
                    sem.registration_open,
                    sem.registration_start,
                    sem.registration_end,

                    co.name AS course_name,
                    co.status AS course_status

                FROM classes cl

                JOIN semesters sem
                    ON sem.id = cl.semester_id

                JOIN courses co
                    ON co.id = cl.course_id

                WHERE cl.id = :id

                FOR UPDATE
            ";

            $stmt = $this->pdo->prepare($sql);

            $stmt->execute([
                ':id' => $classId
            ]);

            $class =
                $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$class) {

                throw new \RuntimeException(
                    'Lớp học phần không tồn tại.'
                );
            }


            /*
             * =====================================================
             * 2. KIỂM TRA LỚP ĐANG HOẠT ĐỘNG
             * =====================================================
             */
            if ((int)$class['status'] !== 1) {

                throw new \RuntimeException(
                    'Lớp học phần hiện không hoạt động.'
                );
            }


            /*
             * =====================================================
             * 3. KIỂM TRA HỌC PHẦN ĐANG HOẠT ĐỘNG
             * =====================================================
             */
            if ((int)$class['course_status'] !== 1) {

                throw new \RuntimeException(
                    'Học phần hiện không hoạt động.'
                );
            }


            /*
             * =====================================================
             * 4. KIỂM TRA ĐỢT ĐĂNG KÝ
             * =====================================================
             */
            $today = date('Y-m-d');

            if ((int)$class['registration_open'] !== 1) {

                throw new \RuntimeException(
                    'Đợt đăng ký hiện đã đóng.'
                );
            }

            if (
                $today < $class['registration_start'] ||
                $today > $class['registration_end']
            ) {

                throw new \RuntimeException(
                    'Hiện không nằm trong thời gian đăng ký.'
                );
            }


            /*
             * =====================================================
             * 5. LẤY KHOA CỦA SINH VIÊN
             * =====================================================
             */
            $stmt = $this->pdo->prepare("
                SELECT faculty_id

                FROM students

                WHERE user_id = :id

                LIMIT 1
            ");

            $stmt->execute([
                ':id' => $studentId
            ]);

            $facultyId =
                $stmt->fetchColumn();

            if (!$facultyId) {

                throw new \RuntimeException(
                    'Không tìm thấy thông tin sinh viên.'
                );
            }


            /*
             * =====================================================
             * 6. KIỂM TRA HỌC PHẦN THUỘC KHOA
             * =====================================================
             */
            $stmt = $this->pdo->prepare("
                SELECT 1

                FROM course_faculties

                WHERE course_id = :course_id

                  AND faculty_id = :faculty_id

                LIMIT 1
            ");

            $stmt->execute([
                ':course_id' =>
                    $class['course_id'],

                ':faculty_id' =>
                    $facultyId
            ]);

            if (!$stmt->fetchColumn()) {

                throw new \RuntimeException(
                    'Học phần không thuộc khoa của sinh viên.'
                );
            }


            /*
             * =====================================================
             * 7. LẤY CÁC LỊCH HỌC CỦA LỚP MỚI
             * =====================================================
             *
             * Một lớp có thể có nhiều dòng lịch.
             */
            $stmt = $this->pdo->prepare("
                SELECT
                    id,
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
            ");

            $stmt->execute([
                ':class_id' => $classId
            ]);

            $newSchedules =
                $stmt->fetchAll(PDO::FETCH_ASSOC);

            if (!$newSchedules) {

                throw new \RuntimeException(
                    'Lớp học phần chưa có lịch học.'
                );
            }


            /*
             * =====================================================
             * 8. KIỂM TRA SĨ SỐ
             * =====================================================
             */
            $stmt = $this->pdo->prepare("
                SELECT COUNT(*)

                FROM registrations

                WHERE class_id = :class_id
            ");

            $stmt->execute([
                ':class_id' => $classId
            ]);

            $registered =
                (int)$stmt->fetchColumn();

            if (
                $registered >=
                (int)$class['max_students']
            ) {

                throw new \RuntimeException(
                    'Lớp học phần đã đủ sĩ số.'
                );
            }


            /*
             * =====================================================
             * 9. KIỂM TRA ĐÃ ĐĂNG KÝ ĐÚNG LỚP NÀY CHƯA
             * =====================================================
             */
            $stmt = $this->pdo->prepare("
                SELECT 1

                FROM registrations

                WHERE student_id = :student_id

                  AND class_id = :class_id

                LIMIT 1
            ");

            $stmt->execute([
                ':student_id' => $studentId,
                ':class_id' => $classId
            ]);

            if ($stmt->fetchColumn()) {

                throw new \RuntimeException(
                    'Sinh viên đã đăng ký lớp học phần này.'
                );
            }


            /*
             * =====================================================
             * 10. KIỂM TRA ĐÃ ĐĂNG KÝ HỌC PHẦN NÀY
             *     TRONG CÙNG HỌC KỲ CHƯA
             * =====================================================
             *
             * Ví dụ:
             *
             * Học phần Lập trình Web
             *
             * Lớp WEB01 -> đã đăng ký
             *
             * Không được đăng ký thêm:
             *
             * Lớp WEB02
             */
            $stmt = $this->pdo->prepare("
                SELECT 1

                FROM registrations r

                JOIN classes old
                    ON old.id = r.class_id

                WHERE r.student_id = :student_id

                  AND old.semester_id = :semester_id

                  AND old.course_id = :course_id

                LIMIT 1
            ");

            $stmt->execute([
                ':student_id' =>
                    $studentId,

                ':semester_id' =>
                    $class['semester_id'],

                ':course_id' =>
                    $class['course_id']
            ]);

            if ($stmt->fetchColumn()) {

                throw new \RuntimeException(
                    'Sinh viên đã đăng ký học phần này trong học kỳ.'
                );
            }


            /*
             * =====================================================
             * 11. KIỂM TRA TRÙNG LỊCH
             * =====================================================
             *
             * Đây là phần QUAN TRỌNG NHẤT.
             *
             * Hai lịch chỉ trùng khi ĐỒNG THỜI:
             *
             * 1. Cùng học kỳ
             * 2. Cùng thứ
             * 3. Khoảng tiết giao nhau
             * 4. Khoảng ngày giao nhau
             *
             * Không sử dụng room ở đây vì sinh viên
             * bị trùng theo thời gian học, không phải theo phòng.
             *
             * Một lớp có thể có nhiều lịch,
             * nên phải kiểm tra từng lịch của lớp mới
             * với tất cả lịch của các lớp cũ.
             */
            foreach ($newSchedules as $newSchedule) {

                $this->checkScheduleConflict(
                    $studentId,
                    (int)$class['semester_id'],
                    $newSchedule
                );
            }


            /*
             * =====================================================
             * 12. THỰC HIỆN ĐĂNG KÝ
             * =====================================================
             */
            $stmt = $this->pdo->prepare("
                INSERT INTO registrations
                (
                    student_id,
                    class_id
                )
                VALUES
                (
                    :student_id,
                    :class_id
                )
            ");

            $stmt->execute([
                ':student_id' =>
                    $studentId,

                ':class_id' =>
                    $classId
            ]);


            /*
             * =====================================================
             * 13. HOÀN TẤT TRANSACTION
             * =====================================================
             */
            $this->pdo->commit();

        } catch (\Throwable $e) {

            if ($this->pdo->inTransaction()) {

                $this->pdo->rollBack();
            }

            throw $e;
        }
    }


    /**
     * Kiểm tra một lịch mới có trùng với
     * lịch mà sinh viên đã đăng ký hay không.
     *
     * QUY TẮC:
     *
     * Cùng học kỳ
     * AND cùng thứ
     * AND giao nhau về tiết
     * AND giao nhau về ngày
     */
    private function checkScheduleConflict(
        int $studentId,
        int $semesterId,
        array $newSchedule
    ): void {

        $sql = "
            SELECT
                old_cl.class_code,
                old_co.name AS course_name,

                old_cs.weekday,
                old_cs.start_period,
                old_cs.end_period,
                old_cs.start_date,
                old_cs.end_date

            FROM registrations r

            INNER JOIN classes old_cl
                ON old_cl.id = r.class_id

            INNER JOIN courses old_co
                ON old_co.id = old_cl.course_id

            INNER JOIN class_schedules old_cs
                ON old_cs.class_id = old_cl.id

            WHERE r.student_id = :student_id

              /*
               * Cùng học kỳ
               */
              AND old_cl.semester_id = :semester_id

              /*
               * Cùng thứ
               */
              AND old_cs.weekday = :weekday

              /*
               * Khoảng tiết giao nhau
               *
               * new_start < old_end
               * AND
               * new_end > old_start
               */
              AND old_cs.start_period < :end_period
              AND old_cs.end_period > :start_period

              /*
               * Khoảng ngày giao nhau
               *
               * new_start_date <= old_end_date
               * AND
               * new_end_date >= old_start_date
               */
              AND old_cs.start_date <= :end_date
              AND old_cs.end_date >= :start_date

            LIMIT 1
        ";

        $stmt = $this->pdo->prepare($sql);

        $stmt->execute([
            ':student_id' =>
                $studentId,

            ':semester_id' =>
                $semesterId,

            ':weekday' =>
                (int)$newSchedule['weekday'],

            ':start_period' =>
                (int)$newSchedule['start_period'],

            ':end_period' =>
                (int)$newSchedule['end_period'],

            ':start_date' =>
                $newSchedule['start_date'],

            ':end_date' =>
                $newSchedule['end_date']
        ]);

        $conflict =
            $stmt->fetch(PDO::FETCH_ASSOC);

        if ($conflict) {

            throw new \RuntimeException(
                'Lịch học bị trùng với lớp ' .
                $conflict['class_code'] .
                ' (' .
                $conflict['course_name'] .
                '). ' .
                'Thứ ' .
                $this->weekdayText(
                    (int)$conflict['weekday']
                ) .
                ', tiết ' .
                $conflict['start_period'] .
                '-' .
                $conflict['end_period'] .
                ', từ ' .
                date(
                    'd/m/Y',
                    strtotime($conflict['start_date'])
                ) .
                ' đến ' .
                date(
                    'd/m/Y',
                    strtotime($conflict['end_date'])
                ) .
                '.'
            );
        }
    }


    /**
     * Chuyển mã thứ thành tên hiển thị
     */
    private function weekdayText(int $weekday): string
    {
        if ($weekday === 8) {
            return 'Chủ nhật';
        }

        return (string)$weekday;
    }


    /**
     * Hủy đăng ký học phần
     */
    public function cancel(
        int $studentId,
        int $registrationId
    ): void {

        $sql = "
            DELETE r

            FROM registrations r

            JOIN classes c
                ON c.id = r.class_id

            JOIN semesters s
                ON s.id = c.semester_id

            WHERE r.id = :registration_id

              AND r.student_id = :student_id

              AND s.registration_open = 1

              AND CURDATE()
                  BETWEEN s.registration_start
                  AND s.registration_end
        ";

        $stmt = $this->pdo->prepare($sql);

        $stmt->execute([
            ':registration_id' =>
                $registrationId,

            ':student_id' =>
                $studentId
        ]);

        if ($stmt->rowCount() === 0) {

            throw new \RuntimeException(
                'Không thể hủy đăng ký: sai quyền, đăng ký không tồn tại hoặc đã hết hạn.'
            );
        }
    }
}