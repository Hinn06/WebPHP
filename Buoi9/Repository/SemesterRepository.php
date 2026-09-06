<?php

namespace App\Repository;

use PDO;

class SemesterRepository
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * Lấy tất cả học kỳ
     */
    public function findAll(): array
    {
        $sql = "
            SELECT *
            FROM semesters
            ORDER BY study_start DESC
        ";

        $stmt = $this->pdo->query($sql);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Lấy học kỳ theo ID
     */
    public function findById(int $id): ?array
    {
        $sql = "
            SELECT *
            FROM semesters
            WHERE id = :id
            LIMIT 1
        ";

        $stmt = $this->pdo->prepare($sql);

        $stmt->execute([
            'id' => $id
        ]);

        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return $result ?: null;
    }

    /**
     * Tạo học kỳ
     */
    public function create(array $d): int
    {
        $sql = "
            INSERT INTO semesters
            (
                name,
                academic_year,
                term,
                study_start,
                study_end,
                registration_start,
                registration_end,
                registration_open,
                status
            )
            VALUES
            (
                :name,
                :academic_year,
                :term,
                :study_start,
                :study_end,
                :registration_start,
                :registration_end,
                0,
                1
            )
        ";

        $stmt = $this->pdo->prepare($sql);

        $stmt->execute([
            'name'              => $d['name'],
            'academic_year'     => $d['academic_year'],
            'term'              => $d['term'],
            'study_start'       => $d['study_start'],
            'study_end'         => $d['study_end'],
            'registration_start'=> $d['registration_start'],
            'registration_end'  => $d['registration_end']
        ]);

        return (int) $this->pdo->lastInsertId();
    }

    /**
     * Cập nhật học kỳ
     */
    public function update(int $id, array $d): bool
    {
        $sql = "
            UPDATE semesters
            SET
                name = :name,
                academic_year = :academic_year,
                term = :term,
                study_start = :study_start,
                study_end = :study_end,
                registration_start = :registration_start,
                registration_end = :registration_end,
                status = :status
            WHERE id = :id
        ";

        $stmt = $this->pdo->prepare($sql);

        return $stmt->execute([
            'name'               => $d['name'],
            'academic_year'      => $d['academic_year'],
            'term'               => $d['term'],
            'study_start'        => $d['study_start'],
            'study_end'          => $d['study_end'],
            'registration_start' => $d['registration_start'],
            'registration_end'   => $d['registration_end'],
            'status'             => $d['status'],
            'id'                 => $id
        ]);
    }

    /**
     * Mở / đóng đợt đăng ký
     */
    public function toggleRegistration(int $id): bool
    {
        $sql = "
            UPDATE semesters
            SET registration_open =
                CASE
                    WHEN registration_open = 1 THEN 0
                    ELSE 1
                END
            WHERE id = :id
        ";

        $stmt = $this->pdo->prepare($sql);

        return $stmt->execute([
            'id' => $id
        ]);
    }

    /**
     * Xóa học kỳ
     */
    public function delete(int $id): bool
    {
        $sql = "
            DELETE FROM semesters
            WHERE id = :id
        ";

        $stmt = $this->pdo->prepare($sql);

        return $stmt->execute([
            'id' => $id
        ]);
    }

    /**
     * Lấy đợt đăng ký hiện đang mở
     */
    public function activeRegistration(): ?array
    {
        $sql = "
            SELECT *
            FROM semesters
            WHERE status = 1
              AND registration_open = 1
              AND CURDATE() BETWEEN registration_start
                                AND registration_end
            ORDER BY study_start DESC
            LIMIT 1
        ";

        $stmt = $this->pdo->query($sql);

        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return $result ?: null;
    }
}