<?php

class ScheduleRepository
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    /**
     * Lấy tất cả lịch của một lớp học phần
     */
    public function getByClass(int $classId): array
    {
        $sql = "
            SELECT
                cs.id,
                cs.class_id,
                cs.weekday,
                cs.start_period,
                cs.end_period,
                cs.start_date,
                cs.end_date,
                cs.room
            FROM class_schedules cs
            WHERE cs.class_id = :class_id
            ORDER BY cs.weekday, cs.start_period, cs.start_date
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':class_id' => $classId
        ]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Thêm lịch học
     */
    public function create(int $classId, array $data): bool
    {
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

        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            ':class_id' => $classId,
            ':weekday' => $data['weekday'],
            ':start_period' => $data['start_period'],
            ':end_period' => $data['end_period'],
            ':start_date' => $data['start_date'],
            ':end_date' => $data['end_date'],
            ':room' => $data['room']
        ]);
    }

    /**
     * Cập nhật lịch học
     */
    public function update(int $id, array $data): bool
    {
        $sql = "
            UPDATE class_schedules
            SET
                weekday = :weekday,
                start_period = :start_period,
                end_period = :end_period,
                start_date = :start_date,
                end_date = :end_date,
                room = :room
            WHERE id = :id
        ";

        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            ':id' => $id,
            ':weekday' => $data['weekday'],
            ':start_period' => $data['start_period'],
            ':end_period' => $data['end_period'],
            ':start_date' => $data['start_date'],
            ':end_date' => $data['end_date'],
            ':room' => $data['room']
        ]);
    }

    /**
     * Xóa lịch học
     */
    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare("
            DELETE FROM class_schedules
            WHERE id = :id
        ");

        return $stmt->execute([
            ':id' => $id
        ]);
    }

    /**
     * Xóa toàn bộ lịch của một lớp
     */
    public function deleteByClass(int $classId): bool
    {
        $stmt = $this->db->prepare("
            DELETE FROM class_schedules
            WHERE class_id = :class_id
        ");

        return $stmt->execute([
            ':class_id' => $classId
        ]);
    }
}