<?php

namespace App\Controller;

use App\Core\Auth;
use App\Core\Controller;
use App\Repository\UserRepository;

class AuthController extends Controller
{
    private \PDO $pdo;

    public function __construct(\PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function login(): void
    {
        $errors = [];

        $data = [
            'username' => ''
        ];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {

            verify_csrf();

            $data['username'] = strtolower(
                trim(post_string('username'))
            );

            $password = (string) ($_POST['password'] ?? '');

            /*
             * Kiểm tra username
             */
            if ($data['username'] === '') {

                $errors['username'] =
                    'Vui lòng nhập tên đăng nhập.';

            } elseif (strlen($data['username']) > 50) {

                $errors['username'] =
                    'Tên đăng nhập tối đa 50 ký tự.';
            }

            /*
             * Kiểm tra mật khẩu
             */
            if ($password === '') {

                $errors['password'] =
                    'Vui lòng nhập mật khẩu.';
            }

            /*
             * Nếu dữ liệu hợp lệ thì kiểm tra tài khoản
             */
            if (!$errors) {

                $userRepository =
                    new UserRepository($this->pdo);

                $user = $userRepository
                    ->findByUsername($data['username']);

                if (
                    !$user ||
                    (int) $user['status'] !== 1 ||
                    !password_verify(
                        $password,
                        $user['password_hash']
                    )
                ) {

                    $errors['general'] =
                        'Tên đăng nhập hoặc mật khẩu không đúng.';

                } else {

                    /*
                     * Đăng nhập
                     */
                    Auth::login($user);

                    /*
                     * Không vào Dashboard.
                     *
                     * Admin → Quản lý sinh viên
                     * Giảng viên → Lịch dạy
                     * Sinh viên → Đăng ký học phần
                     */
                    if ($user['role'] === 'admin') {

                        redirect('admin/students');

                    } elseif ($user['role'] === 'teacher') {

                        redirect('teacher/dashboard');

                    } else {

                        redirect('student/register');
                    }
                }
            }

            /*
             * Giữ lại dữ liệu form nếu có lỗi
             */
            rememberForm($data, $errors);

        } else {

            clearFormMemory();
        }

        $this->view(
            'auth/login',
            [
                'errors' => $errors,
                'data'   => $data
            ]
        );
    }

    public function logout(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {

            http_response_code(405);

            exit('Phương thức không hợp lệ.');
        }

        verify_csrf();

        Auth::logout();

        redirect('auth/login');
    }
}