<?php

namespace App\Controller;

use App\Core\{Auth, Controller};
use App\Repository\{
    ClassRepository,
    CourseRepository,
    LecturerRepository,
    SemesterRepository
};

class ClassController extends Controller
{
    public function __construct(private \PDO $pdo)
    {
    }

    private function repo(): ClassRepository
    {
        return new ClassRepository($this->pdo);
    }

    /**
     * Kiểm tra thông tin chung của lớp học phần
     */
    private function validate(array $d): array
    {
        $e = [];

        if ($d['class_code'] === '') {
            $e['class_code'] = 'Mã lớp bắt buộc.';
        } elseif (!preg_match('/^[A-Za-z0-9._-]{2,50}$/', $d['class_code'])) {
            $e['class_code'] = 'Mã lớp 2-50 ký tự.';
        }

        foreach (
            [
                'course_id' => 'học phần',
                'semester_id' => 'học kỳ',
                'teacher_id' => 'giảng viên'
            ] as $k => $n
        ) {
            if (!$d[$k]) {
                $e[$k] = "Vui lòng chọn $n.";
            }
        }

        if (
            !$d['max_students'] ||
            $d['max_students'] < 1 ||
            $d['max_students'] > 500
        ) {
            $e['max_students'] = 'Sĩ số từ 1 đến 500.';
        }

        return $e;
    }

    /**
     * Kiểm tra danh sách lịch học
     */
    private function validateSchedules(array $schedules): array
    {
        $errors = [];

        if (count($schedules) === 0) {
            $errors['schedules'] = 'Lớp phải có ít nhất một lịch học.';
            return $errors;
        }

        foreach ($schedules as $index => $schedule) {

            $number = $index + 1;

            $weekday = (int)($schedule['weekday'] ?? 0);
            $startPeriod = (int)($schedule['start_period'] ?? 0);
            $endPeriod = (int)($schedule['end_period'] ?? 0);
            $startDate = trim($schedule['start_date'] ?? '');
            $endDate = trim($schedule['end_date'] ?? '');
            $room = trim($schedule['room'] ?? '');

            if ($weekday < 2 || $weekday > 8) {
                $errors["schedule_{$index}_weekday"] =
                    "Lịch {$number}: Thứ không hợp lệ.";
            }

            if ($startPeriod < 1 || $startPeriod > 15) {
                $errors["schedule_{$index}_start_period"] =
                    "Lịch {$number}: Tiết bắt đầu từ 1 đến 15.";
            }

            if (
                $endPeriod < 1 ||
                $endPeriod > 15 ||
                $endPeriod < $startPeriod
            ) {
                $errors["schedule_{$index}_end_period"] =
                    "Lịch {$number}: Tiết kết thúc phải từ 1 đến 15 và không nhỏ hơn tiết bắt đầu.";
            }

            if ($startDate === '') {
                $errors["schedule_{$index}_start_date"] =
                    "Lịch {$number}: Vui lòng chọn ngày bắt đầu.";
            } elseif (!$this->validDate($startDate)) {
                $errors["schedule_{$index}_start_date"] =
                    "Lịch {$number}: Ngày bắt đầu không hợp lệ.";
            }

            if ($endDate === '') {
                $errors["schedule_{$index}_end_date"] =
                    "Lịch {$number}: Vui lòng chọn ngày kết thúc.";
            } elseif (!$this->validDate($endDate)) {
                $errors["schedule_{$index}_end_date"] =
                    "Lịch {$number}: Ngày kết thúc không hợp lệ.";
            }

            if (
                $startDate !== '' &&
                $endDate !== '' &&
                $this->validDate($startDate) &&
                $this->validDate($endDate) &&
                $startDate > $endDate
            ) {
                $errors["schedule_{$index}_end_date"] =
                    "Lịch {$number}: Ngày kết thúc không được nhỏ hơn ngày bắt đầu.";
            }

            if ($room === '' || mb_strlen($room) > 100) {
                $errors["schedule_{$index}_room"] =
                    "Lịch {$number}: Phòng học bắt buộc và tối đa 100 ký tự.";
            }
        }

        return $errors;
    }

    /**
     * Kiểm tra ngày YYYY-MM-DD
     */
    private function validDate(string $date): bool
    {
        $d = \DateTime::createFromFormat('Y-m-d', $date);

        return $d !== false &&
            $d->format('Y-m-d') === $date;
    }

    /**
     * Danh sách lớp học phần
     */
    public function index(): void
    {
        Auth::requireRole('admin');

        $q = trim($_GET['q'] ?? '');
        $page = max(1, (int)($_GET['page'] ?? 1));

        $r = $this->repo();

        $total = $r->count($q);

        [$page, $per, $off, $pages] =
            paginate($page, 8, $total);

        $this->view(
            'admin/classes/index',
            [
                'rows' => $r->findPage($q, $per, $off),
                'q' => $q,
                'page' => $page,
                'pages' => $pages
            ]
        );
    }

    public function create(): void
    {
        $this->form(true);
    }

    public function edit(): void
    {
        $this->form(false);
    }

    /**
     * Form tạo / sửa lớp học phần
     */
    private function form(bool $create): void
    {
        Auth::requireRole('admin');

        $id = (int)($_GET['id'] ?? 0);

        $repo = $this->repo();

        $ex = $create
            ? null
            : $repo->findById($id);

        if (!$create && !$ex) {
            http_response_code(404);
            exit('Không tìm thấy lớp.');
        }

        /*
         * Dữ liệu chung của lớp
         */
        $d = $ex ?: [
            'class_code' => '',
            'course_id' => '',
            'semester_id' => '',
            'teacher_id' => '',
            'max_students' => '',
            'status' => 1
        ];

        /*
         * Nếu tạo mới và có course_id trên URL
         */
        if (
            $create &&
            isset($_GET['course_id']) &&
            ctype_digit((string)$_GET['course_id'])
        ) {
            $d['course_id'] = (int)$_GET['course_id'];
        }

        /*
         * Danh sách lịch học
         */
        $schedules = [];

        /*
         * Khi sửa:
         * lấy các lịch hiện tại của lớp
         */
        if (!$create && $id > 0) {
            if (method_exists($repo, 'getSchedules')) {
                $schedules = $repo->getSchedules($id);
            }
        }

        /*
         * Nếu chưa có lịch thì tạo một dòng trống
         */
        if (!$schedules) {
            $schedules = [
                [
                    'weekday' => 2,
                    'start_period' => 1,
                    'end_period' => 3,
                    'start_date' => '',
                    'end_date' => '',
                    'room' => ''
                ]
            ];
        }

        $errors = [];
        $lecturers = [];

        /*
         * Xử lý POST
         */
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {

            verify_csrf();

            /*
             * Lấy dữ liệu chung
             */
            $d = [
                'class_code' => strtoupper(
                    post_string('class_code')
                ),

                'course_id' => post_int('course_id'),

                'semester_id' => post_int('semester_id'),

                'teacher_id' => post_int('teacher_id'),

                'max_students' => post_int('max_students'),

                'status' => isset($_POST['status']) ? 1 : 0
            ];

            /*
             * Lấy nhiều lịch học
             */
            $schedules = [];

            $postedSchedules =
                $_POST['schedules'] ?? [];

            if (is_array($postedSchedules)) {

                foreach ($postedSchedules as $schedule) {

                    if (!is_array($schedule)) {
                        continue;
                    }

                    $schedules[] = [
                        'weekday' => (int)($schedule['weekday'] ?? 0),

                        'start_period' =>
                            (int)($schedule['start_period'] ?? 0),

                        'end_period' =>
                            (int)($schedule['end_period'] ?? 0),

                        'start_date' =>
                            trim($schedule['start_date'] ?? ''),

                        'end_date' =>
                            trim($schedule['end_date'] ?? ''),

                        'room' =>
                            strtoupper(
                                trim($schedule['room'] ?? '')
                            )
                    ];
                }
            }

            /*
             * Validate thông tin chung
             */
            $errors = $this->validate($d);

            /*
             * Validate lịch
             */
            $scheduleErrors =
                $this->validateSchedules($schedules);

            $errors = array_merge(
                $errors,
                $scheduleErrors
            );

            /*
             * Nếu không có lỗi thì lưu DB
             */
            if (!$errors) {

                try {

                    if ($create) {

                        /*
                         * Repository create phải hỗ trợ
                         * lưu class + schedules trong transaction
                         */
                        $repo->create(
                            $d,
                            $schedules
                        );

                        flash(
                            'success',
                            'Đã tạo lớp học phần.'
                        );

                    } else {

                        /*
                         * Repository update phải hỗ trợ
                         * cập nhật class + schedules
                         */
                        $repo->update(
                            $id,
                            $d,
                            $schedules
                        );

                        flash(
                            'success',
                            'Đã cập nhật lớp.'
                        );
                    }

                    clearFormMemory();

                    redirect('admin/classes');

                } catch (\Throwable $e) {

                    $errors['general'] =
                        $e->getMessage();
                }
            }

            /*
             * Giữ dữ liệu khi form có lỗi
             */
            rememberForm(
                [
                    ...$d,
                    'schedules' => $schedules
                ],
                $errors
            );

        } else {

            clearFormMemory();
        }

        /*
         * Lấy danh sách giảng viên có thể dạy
         * học phần đã chọn
         */
        if ($d['course_id']) {

            $lecturers =
                (new LecturerRepository($this->pdo))
                    ->byCourse(
                        (int)$d['course_id']
                    );
        }

        /*
         * Hiển thị form
         */
        $this->view(
            'admin/classes/form',
            [
                'create' => $create,

                'data' => $d,

                'errors' => $errors,

                'schedules' => $schedules,

                'courses' =>
                    (new CourseRepository($this->pdo))
                        ->findAll(),

                'semesters' =>
                    (new SemesterRepository($this->pdo))
                        ->findAll(),

                'lecturers' => $lecturers
            ]
        );
    }

    /**
     * Chi tiết lớp
     */
    public function detail(): void
    {
        Auth::requireRole('admin');

        $id = (int)($_GET['id'] ?? 0);

        $class = $this->repo()->findById($id);

        if (!$class) {
            http_response_code(404);
            exit('Không tìm thấy lớp.');
        }

        /*
         * Lấy nhiều lịch học
         */
        $schedules = [];

        if (method_exists($this->repo(), 'getSchedules')) {
            $schedules =
                $this->repo()->getSchedules($id);
        }

        $this->view(
            'admin/classes/detail',
            [
                'class' => $class,
                'schedules' => $schedules
            ]
        );
    }

    /**
     * Xóa lớp
     */
    public function delete(): void
    {
        Auth::requireRole('admin');

        verify_csrf();

        try {

            $this->repo()->delete(
                post_int('id')
            );

            flash(
                'success',
                'Đã xóa lớp.'
            );

        } catch (\Throwable $e) {

            flash(
                'error',
                'Không thể xóa lớp.'
            );
        }

        redirect('admin/classes');
    }
}