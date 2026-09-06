<?php

namespace App\Controller;

use App\Core\{Auth, Controller};
use App\Repository\{
    CourseRepository,
    RegistrationRepository,
    SemesterRepository,
    StudentRepository,
    StudentScheduleRepository
};

class RegistrationController extends Controller
{
    public function __construct(private \PDO $pdo)
    {
    }

    /**
     * Lấy thông tin sinh viên đang đăng nhập
     */
    private function student(): array
    {
        $student = (new StudentRepository($this->pdo))
            ->findById(Auth::id());

        if (!$student) {
            http_response_code(403);
            exit('Tài khoản chưa có hồ sơ sinh viên.');
        }

        return $student;
    }

    /**
     * Trang đăng ký học phần
     */
    public function index(): void
    {
        Auth::requireRole('student');

        $student = $this->student();

        $semRepo = new SemesterRepository($this->pdo);

        // Học kỳ đang mở đăng ký
        $active = $semRepo->activeRegistration();

        // Nếu sinh viên chọn học kỳ thì lấy học kỳ đó,
        // nếu không thì lấy học kỳ đang mở
        $semesterId = (int)(
            $_GET['semester_id']
            ?? ($active['id'] ?? 0)
        );

        $semester = $semesterId
            ? $semRepo->findById($semesterId)
            : null;

        // Danh sách học phần/lớp học phần sinh viên được đăng ký
       /*
 * Từ khóa tìm kiếm lớp học phần
 */
$keyword = trim(
    $_GET['keyword'] ?? ''
);


/*
 * Danh sách lớp học phần sinh viên được đăng ký
 */
$rows = $semester
    ? (new CourseRepository($this->pdo))
        ->availableForStudent(
            Auth::id(),
            $semesterId,
            $keyword
        )
    : [];

        // Lịch sử đăng ký
        $history = (new RegistrationRepository($this->pdo))
            ->history(Auth::id());

        $this->view(
    'student/register',
    [
        'student' => $student,
        'active' => $active,
        'semester' => $semester,
        'rows' => $rows,
        'history' => $history,
        'semesters' => $semRepo->findAll(),
        'keyword' => $keyword
    ]
);
    }

    /**
     * Đăng ký một lớp học phần
     */
    public function add(): void
    {
        Auth::requireRole('student');

        verify_csrf();

        $classId = post_int('class_id');

        try {

            (new RegistrationRepository($this->pdo))
                ->register(
                    Auth::id(),
                    $classId
                );

            flash(
                'success',
                'Đăng ký học phần thành công.'
            );

        } catch (\Throwable $e) {

            flash(
                'error',
                $e->getMessage()
            );
        }

        redirect('student/register');
    }

    /**
     * Hủy đăng ký học phần
     */
    public function cancel(): void
    {
        Auth::requireRole('student');

        verify_csrf();

        try {

            (new RegistrationRepository($this->pdo))
                ->cancel(
                    Auth::id(),
                    post_int('registration_id')
                );

            flash(
                'success',
                'Đã hủy đăng ký.'
            );

        } catch (\Throwable $e) {

            flash(
                'error',
                $e->getMessage()
            );
        }

        redirect('student/register');
    }

    /**
     * Lịch sử đăng ký học phần
     */
    public function history(): void
    {
        Auth::requireRole('student');

        $this->view(
            'student/history',
            [
                'rows' => (new RegistrationRepository($this->pdo))
                    ->history(Auth::id())
            ]
        );
    }

    /**
     * Lịch học của sinh viên
     */
    public function schedule(): void
    {
        Auth::requireRole('student');

        // Lấy thông tin sinh viên
        $student = $this->student();

        /*
         * Nếu URL có:
         * ?semester_id=1
         * thì lọc theo học kỳ 1.
         *
         * Nếu không có semester_id
         * thì lấy toàn bộ lịch đã đăng ký.
         */
        $semesterId = null;

        if (
            isset($_GET['semester_id'])
            && $_GET['semester_id'] !== ''
        ) {
            $semesterId = (int) $_GET['semester_id'];
        }

        // Lấy lịch học
        $scheduleRepository = new StudentScheduleRepository(
            $this->pdo
        );

        $scheduleRows = $scheduleRepository->getStudentSchedule(
            Auth::id(),
            $semesterId
        );

        // Lấy danh sách học kỳ
        $semesterRepository = new SemesterRepository(
            $this->pdo
        );

        $semesters = $semesterRepository->findAll();

        // Hiển thị giao diện
        $this->view(
            'student/schedule',
            [
                'student' => $student,
                'scheduleRows' => $scheduleRows,
                'semesters' => $semesters,
                'semesterId' => $semesterId
            ]
        );
    }
}