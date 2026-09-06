<?php

namespace App\Core;

class Router
{
    public function __construct(private \PDO $pdo)
    {
    }

    public function dispatch(string $route): void
    {
        $map = [

            // Trang chủ
            'home' => [
                'App\\Controller\\HomeController',
                'index'
            ],

            // Đăng nhập / đăng xuất
            'auth/login' => [
                'App\\Controller\\AuthController',
                'login'
            ],

            'auth/logout' => [
                'App\\Controller\\AuthController',
                'logout'
            ],


            // =========================
            // ADMIN
            // =========================

            'admin/dashboard' => [
                'App\\Controller\\AdminController',
                'dashboard'
            ],


            // Sinh viên
            'admin/students' => [
                'App\\Controller\\StudentController',
                'index'
            ],

            'admin/student/create' => [
                'App\\Controller\\StudentController',
                'create'
            ],

            'admin/student/edit' => [
                'App\\Controller\\StudentController',
                'edit'
            ],

            'admin/student/delete' => [
                'App\\Controller\\StudentController',
                'delete'
            ],


            // Giảng viên
            'admin/lecturers' => [
                'App\\Controller\\LecturerController',
                'index'
            ],

            'admin/lecturer/create' => [
                'App\\Controller\\LecturerController',
                'create'
            ],

            'admin/lecturer/edit' => [
                'App\\Controller\\LecturerController',
                'edit'
            ],

            'admin/lecturer/delete' => [
                'App\\Controller\\LecturerController',
                'delete'
            ],


            // Học phần
            'admin/courses' => [
                'App\\Controller\\CourseController',
                'index'
            ],

            'admin/course/create' => [
                'App\\Controller\\CourseController',
                'create'
            ],

            'admin/course/edit' => [
                'App\\Controller\\CourseController',
                'edit'
            ],

            'admin/course/delete' => [
                'App\\Controller\\CourseController',
                'delete'
            ],


            // Học kỳ
            'admin/semesters' => [
                'App\\Controller\\SemesterController',
                'index'
            ],

            'admin/semester/create' => [
                'App\\Controller\\SemesterController',
                'create'
            ],

            'admin/semester/edit' => [
                'App\\Controller\\SemesterController',
                'edit'
            ],

            'admin/semester/toggle' => [
                'App\\Controller\\SemesterController',
                'toggle'
            ],

            'admin/semester/delete' => [
                'App\\Controller\\SemesterController',
                'delete'
            ],


            // Lớp học phần
            'admin/classes' => [
                'App\\Controller\\ClassController',
                'index'
            ],

            'admin/class/create' => [
                'App\\Controller\\ClassController',
                'create'
            ],

            'admin/class/edit' => [
                'App\\Controller\\ClassController',
                'edit'
            ],

            'admin/class/delete' => [
                'App\\Controller\\ClassController',
                'delete'
            ],

            'admin/class/detail' => [
                'App\\Controller\\ClassController',
                'detail'
            ],


            // =========================
            // SINH VIÊN
            // =========================

            // Đăng ký học phần
            'student/register' => [
                'App\\Controller\\RegistrationController',
                'index'
            ],

            // Thực hiện đăng ký
            'student/register/add' => [
                'App\\Controller\\RegistrationController',
                'add'
            ],

            // Hủy đăng ký
            'student/register/cancel' => [
                'App\\Controller\\RegistrationController',
                'cancel'
            ],

            // Lịch sử đăng ký
            'student/history' => [
                'App\\Controller\\RegistrationController',
                'history'
            ],

            // Lịch học
            'student/schedule' => [
                'App\\Controller\\RegistrationController',
                'schedule'
            ],


            // =========================
            // GIẢNG VIÊN
            // =========================

            'teacher/dashboard' => [
                'App\\Controller\\TeacherController',
                'dashboard'
            ],

            'teacher/class' => [
                'App\\Controller\\TeacherController',
                'classDetail'
            ],

            'teacher/grades' => [
                'App\\Controller\\TeacherController',
                'grades'
            ],


           // =========================
// API
// =========================

'api/lecturers-by-course' => [
    'App\\Controller\\ApiController',
    'lecturersByCourse'
],

'api/admin/search' => [
    'App\\Controller\\ApiController',
    'adminSearch'
],
        ];


        // Không tồn tại route
        if (!isset($map[$route])) {

            http_response_code(404);

            exit('Không tìm thấy trang.');
        }


        // Lấy Controller và method
        [$class, $method] = $map[$route];


        // Khởi tạo Controller
        $obj = new $class($this->pdo);


        // Gọi method
        $obj->$method();
    }
}