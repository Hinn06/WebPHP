<?php

namespace App\Controller;

use App\Core\Auth;
use App\Core\Controller;
use App\Repository\StudentRepository;
use App\Repository\LecturerRepository;
use App\Repository\CourseRepository;
use App\Repository\SemesterRepository;
use App\Repository\ClassRepository;

class AdminController extends Controller
{
    private \PDO $pdo;

    public function __construct(\PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * Dashboard quản trị
     */
    public function dashboard(): void
    {
        // Chỉ Admin mới được truy cập
        Auth::requireRole('admin');

        // Khởi tạo các Repository
        $studentRepository  = new StudentRepository($this->pdo);
        $lecturerRepository = new LecturerRepository($this->pdo);
        $courseRepository   = new CourseRepository($this->pdo);
        $semesterRepository = new SemesterRepository($this->pdo);
        $classRepository    = new ClassRepository($this->pdo);

        // Thống kê hệ thống
        $stats = [
            'students' => $studentRepository->count(),

            'lecturers' => $lecturerRepository->count(),

            'courses' => count(
                $courseRepository->findAll()
            ),

            'semesters' => count(
                $semesterRepository->findAll()
            ),

            'classes' => count(
                $classRepository->findAll()
            )
        ];

        // Gửi dữ liệu sang View
        $this->view(
            'admin/dashboard',
            [
                'stats' => $stats
            ]
        );
    }
}