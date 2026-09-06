<?php

namespace App\Controller;

use App\Core\Auth;
use App\Repository\StudentRepository;
use App\Repository\LecturerRepository;
use App\Repository\CourseRepository;
use App\Repository\ClassRepository;

class ApiController
{
    public function __construct(private \PDO $pdo)
    {
    }

    /**
     * API lấy danh sách giảng viên có thể dạy một học phần
     */
    public function lecturersByCourse(): void
    {
        Auth::requireRole('admin');

        header('Content-Type: application/json; charset=utf-8');

        $id = filter_input(
            INPUT_GET,
            'course_id',
            FILTER_VALIDATE_INT
        );

        if (!$id) {
            http_response_code(422);

            echo json_encode([
                'ok' => false,
                'message' => 'course_id không hợp lệ'
            ], JSON_UNESCAPED_UNICODE);

            return;
        }

        try {
            $repo = new LecturerRepository($this->pdo);

            echo json_encode([
                'ok' => true,
                'data' => $repo->byCourse($id)
            ], JSON_UNESCAPED_UNICODE);

        } catch (\Throwable $e) {

            http_response_code(500);

            echo json_encode([
                'ok' => false,
                'message' => 'Không thể tải danh sách giảng viên.'
            ], JSON_UNESCAPED_UNICODE);
        }
    }

    /**
     * API tìm kiếm dữ liệu dành cho Admin
     *
     * type:
     * - students
     * - lecturers
     * - courses
     * - classes
     */
    public function adminSearch(): void
    {
        Auth::requireRole('admin');

        header('Content-Type: application/json; charset=utf-8');

        try {

            $type = trim(
                (string)($_GET['type'] ?? '')
            );

            $q = trim(
                (string)($_GET['q'] ?? '')
            );

            $page = (int)(
                $_GET['page'] ?? 1
            );

            if ($page < 1) {
                $page = 1;
            }

            /**
             * Số bản ghi mỗi trang
             */
            $limit = 8;

            /**
             * Chỉ cho phép 4 loại tìm kiếm
             */
            $allowedTypes = [
                'students',
                'lecturers',
                'courses',
                'classes'
            ];

            if (!in_array($type, $allowedTypes, true)) {

                http_response_code(400);

                echo json_encode([
                    'success' => false,
                    'ok' => false,
                    'message' => 'Loại tìm kiếm không hợp lệ.'
                ], JSON_UNESCAPED_UNICODE);

                return;
            }

            /**
             * Chọn Repository tương ứng
             */
            switch ($type) {

                case 'students':
                    $repository =
                        new StudentRepository($this->pdo);
                    break;

                case 'lecturers':
                    $repository =
                        new LecturerRepository($this->pdo);
                    break;

                case 'courses':
                    $repository =
                        new CourseRepository($this->pdo);
                    break;

                case 'classes':
                    $repository =
                        new ClassRepository($this->pdo);
                    break;

                default:

                    http_response_code(400);

                    echo json_encode([
                        'success' => false,
                        'ok' => false,
                        'message' => 'Loại tìm kiếm không hợp lệ.'
                    ], JSON_UNESCAPED_UNICODE);

                    return;
            }

            /**
             * Tổng số bản ghi
             */
            $total = $repository->count($q);

            /**
             * Tổng số trang
             */
            $pages = (int)ceil(
                $total / $limit
            );

            if ($pages < 1) {
                $pages = 1;
            }

            /**
             * Nếu page vượt quá số trang
             * thì đưa về trang cuối
             */
            if ($page > $pages) {
                $page = $pages;
            }

            /**
             * Tính OFFSET
             */
            $offset =
                ($page - 1) * $limit;

            /**
             * Lấy dữ liệu
             */
            $rows =
                $repository->findPage(
                    $q,
                    $limit,
                    $offset
                );

            /**
             * Trả JSON
             *
             * rows và data cùng chứa
             * danh sách kết quả để tương thích
             * với JavaScript hiện tại.
             */
            echo json_encode([
                'success' => true,
                'ok' => true,

                'type' => $type,

                'q' => $q,

                'rows' => $rows,

                'data' => $rows,

                'total' => $total,

                'page' => $page,

                'pages' => $pages,

                'limit' => $limit

            ], JSON_UNESCAPED_UNICODE);

        } catch (\Throwable $e) {

            http_response_code(500);

            echo json_encode([
                'success' => false,
                'ok' => false,
                'message' => 'Không thể tìm kiếm dữ liệu.'
            ], JSON_UNESCAPED_UNICODE);
        }
    }
}