<?php

$title = $create ? 'Tạo lớp học phần' : 'Sửa lớp học phần';

include dirname(__DIR__, 2) . '/layout/header.php';

$data = $data ?? [];

$errors = $errors ?? [];

$schedules = $schedules ?? [];

/*
 * Nếu tạo mới mà chưa có lịch,
 * tạo sẵn 1 dòng lịch.
 */
if (empty($schedules)) {
    $schedules = [
        [
            'weekday' => '',
            'start_period' => '',
            'end_period' => '',
            'start_date' => '',
            'end_date' => '',
            'room' => $data['room'] ?? ''
        ]
    ];
}

?>

<style>

    .form-section {
        margin-top: 25px;
        padding: 20px;
        background: #f8fafc;
        border: 1px solid #dce5ee;
        border-radius: 12px;
    }

    .form-section-title {
        margin: 0 0 15px;
        color: #315b87;
        font-size: 19px;
        font-weight: 700;
    }

    .schedule-item {
        position: relative;
        background: #ffffff;
        border: 1px solid #dce5ee;
        border-radius: 10px;
        padding: 18px;
        margin-bottom: 15px;
    }

    .schedule-item-title {
        color: #315b87;
        font-weight: 700;
        margin-bottom: 15px;
    }

    .schedule-grid {
        display: grid;
        grid-template-columns:
            1.2fr
            1fr
            1fr
            1.3fr
            1.3fr
            1.5fr;

        gap: 12px;
        align-items: end;
    }

    .schedule-remove {
        margin-top: 10px;
    }

    .schedule-add {
        margin-top: 5px;
    }

    .schedule-note {
        margin-top: 12px;
        padding: 10px 12px;
        background: #eef5fb;
        border-radius: 8px;
        color: #315b87;
        font-size: 13px;
        line-height: 1.5;
    }

    .field-error {
        display: block;
        margin-top: 5px;
        color: #c0392b;
        font-size: 13px;
    }

    @media (max-width: 1100px) {

        .schedule-grid {
            grid-template-columns:
                repeat(3, 1fr);
        }

    }

    @media (max-width: 700px) {

        .schedule-grid {
            grid-template-columns: 1fr;
        }

    }

</style>


<div class="card">

    <h2><?= e($title) ?></h2>


    <?php if (isset($errors['general'])): ?>

        <div class="alert error">
            <?= e($errors['general']) ?>
        </div>

    <?php endif; ?>


    <form method="post">

        <input
            type="hidden"
            name="_csrf"
            value="<?= e(csrf_token()) ?>"
        >


        <!-- ============================= -->
        <!-- THÔNG TIN LỚP HỌC PHẦN -->
        <!-- ============================= -->

        <div class="form-grid">


            <!-- Mã lớp -->

            <div class="field">

                <label>
                    Mã lớp *
                </label>

                <input
                    class="input"
                    name="class_code"
                    value="<?= e($data['class_code'] ?? '') ?>"
                    maxlength="50"
                    required
                >

                <?php if (isset($errors['class_code'])): ?>

                    <span class="field-error">
                        <?= e($errors['class_code']) ?>
                    </span>

                <?php endif; ?>

            </div>


            <!-- Học phần -->

            <div class="field">

                <label>
                    Học phần *
                </label>

                <select
                    name="course_id"
                    id="course_id"
                    required
                >

                    <option value="">
                        -- Chọn học phần --
                    </option>

                    <?php foreach ($courses as $c): ?>

                        <option
                            value="<?= $c['id'] ?>"
                            <?= (($data['course_id'] ?? '') == $c['id']) ? 'selected' : '' ?>
                        >
                            <?= e($c['code'] . ' - ' . $c['name']) ?>
                        </option>

                    <?php endforeach; ?>

                </select>

            </div>


            <!-- Học kỳ -->

            <div class="field">

                <label>
                    Học kỳ *
                </label>

                <select
                    name="semester_id"
                    required
                >

                    <option value="">
                        -- Chọn học kỳ --
                    </option>

                    <?php foreach ($semesters as $s): ?>

                        <option
                            value="<?= $s['id'] ?>"
                            <?= (($data['semester_id'] ?? '') == $s['id']) ? 'selected' : '' ?>
                        >
                            <?= e($s['name']) ?>
                        </option>

                    <?php endforeach; ?>

                </select>

            </div>


            <!-- Giảng viên -->

            <div class="field">

                <label>
                    Giảng viên *
                </label>

                <select
                    id="teacher_id"
                    name="teacher_id"
                    required
                >

                    <option value="">
                        -- Chọn GV có thể dạy --
                    </option>

                    <?php foreach ($lecturers as $l): ?>

                        <option
                            value="<?= $l['user_id'] ?>"
                            <?= (($data['teacher_id'] ?? '') == $l['user_id']) ? 'selected' : '' ?>
                        >
                            <?= e(
                                $l['lecturer_code']
                                . ' - '
                                . $l['full_name']
                            ) ?>
                        </option>

                    <?php endforeach; ?>

                </select>

                <div
                    id="teacher-help"
                    class="muted"
                >

                    <?php if (empty($data['course_id'])): ?>

                    <?php else: ?>


                    <?php endif; ?>

                </div>

            </div>


            <!-- Sĩ số -->

            <div class="field">

                <label>
                    Sĩ số tối đa *
                </label>

                <input
                    class="input"
                    type="number"
                    min="1"
                    max="500"
                    name="max_students"
                    value="<?= e($data['max_students'] ?? '') ?>"
                    required
                >

            </div>


            <!-- Trạng thái -->

            <div class="field">

                <label>

                    <input
                        type="checkbox"
                        name="status"
                        <?= !empty($data['status']) ? 'checked' : '' ?>
                    >



                    
                    Lớp đang hoạt động

                </label>

            </div>

        </div>


        <!-- ============================= -->
        <!-- LỊCH HỌC -->
        <!-- ============================= -->

        <div class="form-section">

            <h3 class="form-section-title">
                Lịch học
            </h3>


            <div id="schedule-container">


                <?php foreach ($schedules as $index => $schedule): ?>

                    <div class="schedule-item">

                        <div class="schedule-item-title">

                            Lịch học <?= $index + 1 ?>

                        </div>


                        <div class="schedule-grid">


                            <!-- Thứ -->

                            <div class="field">

                                <label>
                                    Thứ *
                                </label>

                                <select
                                    name="schedules[<?= $index ?>][weekday]"
                                    required
                                >

                                    <option value="">
                                        -- Chọn thứ --
                                    </option>

                                    <?php for ($i = 2; $i <= 8; $i++): ?>

                                        <option
                                            value="<?= $i ?>"
                                            <?= (($schedule['weekday'] ?? '') == $i) ? 'selected' : '' ?>
                                        >

                                            <?= $i === 8
                                                ? 'Chủ nhật'
                                                : 'Thứ ' . $i ?>

                                        </option>

                                    <?php endfor; ?>

                                </select>

                            </div>


                            <!-- Tiết bắt đầu -->

                            <div class="field">

                                <label>
                                    Tiết bắt đầu *
                                </label>

                                <input
                                    class="input"
                                    type="number"
                                    name="schedules[<?= $index ?>][start_period]"
                                    value="<?= e($schedule['start_period'] ?? '') ?>"
                                    min="1"
                                    max="15"
                                    required
                                >

                            </div>


                            <!-- Tiết kết thúc -->

                            <div class="field">

                                <label>
                                    Tiết kết thúc *
                                </label>

                                <input
                                    class="input"
                                    type="number"
                                    name="schedules[<?= $index ?>][end_period]"
                                    value="<?= e($schedule['end_period'] ?? '') ?>"
                                    min="1"
                                    max="15"
                                    required
                                >

                            </div>


                            <!-- Từ ngày -->

                            <div class="field">

                                <label>
                                    Từ ngày *
                                </label>

                                <input
                                    class="input"
                                    type="date"
                                    name="schedules[<?= $index ?>][start_date]"
                                    value="<?= e($schedule['start_date'] ?? '') ?>"
                                    required
                                >

                            </div>


                            <!-- Đến ngày -->

                            <div class="field">

                                <label>
                                    Đến ngày *
                                </label>

                                <input
                                    class="input"
                                    type="date"
                                    name="schedules[<?= $index ?>][end_date]"
                                    value="<?= e($schedule['end_date'] ?? '') ?>"
                                    required
                                >

                            </div>


                            <!-- Phòng -->

                            <div class="field">

                                <label>
                                    Phòng học *
                                </label>

                                <input
                                    class="input"
                                    type="text"
                                    name="schedules[<?= $index ?>][room]"
                                    value="<?= e($schedule['room'] ?? '') ?>"
                                    maxlength="100"
                                    required
                                >

                            </div>


                        </div>


                        <?php if ($index > 0): ?>

                            <button
                                type="button"
                                class="btn secondary schedule-remove"
                                onclick="removeSchedule(this)"
                            >
                                Xóa lịch này
                            </button>

                        <?php endif; ?>


                    </div>

                <?php endforeach; ?>


            </div>


            <!-- Nút thêm lịch -->

            <button
                type="button"
                class="btn schedule-add"
                onclick="addSchedule()"
            >
                + Thêm lịch học
            </button>


            <div class="schedule-note">

                <strong>Lưu ý:</strong>

                Một lớp học phần có thể có nhiều lịch học.

                Ví dụ:

                <br>

                Thứ 6 - Tiết (3, 4) -
                14/08/2026 đến 18/09/2026 -
                P.Trực tuyến 2

                <br>

                Thứ 7 - Tiết (13, 14, 15) -
                15/08/2026 đến 19/09/2026 -
                P.Trực tuyến 4

                <br><br>

                Không nhập giờ cụ thể.
                Hệ thống chỉ quản lý thứ, tiết,
                ngày bắt đầu, ngày kết thúc và phòng học.

            </div>

        </div>


        <!-- ============================= -->
        <!-- NÚT -->
        <!-- ============================= -->

        <div style="margin-top:20px;">

            <button
                type="submit"
                class="btn"
            >
                Lưu lớp
            </button>

            <a
                class="btn secondary"
                href="<?= e(BASE_URL) ?>?r=admin/classes"
            >
                Hủy
            </a>

        </div>


    </form>


    <script>

        /*
         * ============================
         * XỬ LÝ GIẢNG VIÊN
         * ============================
         */

        const courseSelect =
            document.querySelector(
                'select[name="course_id"]'
            );

        const teacherSelect =
            document.getElementById(
                'teacher_id'
            );

        const teacherHelp =
            document.getElementById(
                'teacher-help'
            );


        if (courseSelect) {

            courseSelect.addEventListener(
                'change',
                async function () {

                    const id =
                        courseSelect.value;

                    teacherSelect.innerHTML =
                        '<option value="">Đang tải...</option>';


                    if (!id) {

                        teacherSelect.innerHTML =
                            '<option value="">-- Chọn GV có thể dạy --</option>';

                        teacherHelp.textContent =
                            'Hãy chọn học phần trước.';

                        return;
                    }


                    try {

                        const res =
                            await fetch(
                                '<?= e(BASE_URL) ?>?r=api/lecturers-by-course&course_id='
                                + encodeURIComponent(id),
                                {
                                    headers: {
                                        'Accept':
                                            'application/json'
                                    }
                                }
                            );


                        const json =
                            await res.json();


                        teacherSelect.innerHTML =
                            '<option value="">-- Chọn GV có thể dạy --</option>';


                        if (!json.ok) {

                            throw new Error(
                                json.message
                                || 'Không tải được danh sách giảng viên.'
                            );

                        }


                        json.data.forEach(
                            function (l) {

                                const option =
                                    document.createElement(
                                        'option'
                                    );

                                option.value =
                                    l.user_id;

                                option.textContent =
                                    l.lecturer_code
                                    + ' - '
                                    + l.full_name;

                                teacherSelect.appendChild(
                                    option
                                );

                            }
                        );


                        teacherHelp.textContent =
                            json.data.length
                                ? 'Đã tải '
                                    + json.data.length
                                    + ' giảng viên có thể dạy.'
                                : 'Học phần chưa có giảng viên được gán.';


                    } catch (err) {

                        teacherSelect.innerHTML =
                            '<option value="">Không tải được danh sách</option>';

                        teacherHelp.textContent =
                            err.message;

                    }

                }
            );

        }


        /*
         * ============================
         * THÊM LỊCH HỌC
         * ============================
         */

        let scheduleIndex =
            <?= count($schedules) ?>;


        function addSchedule() {

            const container =
                document.getElementById(
                    'schedule-container'
                );


            const index =
                scheduleIndex;


            const item =
                document.createElement(
                    'div'
                );


            item.className =
                'schedule-item';


            item.innerHTML = `

                <div class="schedule-item-title">

                    Lịch học ${index + 1}

                </div>


                <div class="schedule-grid">


                    <div class="field">

                        <label>
                            Thứ *
                        </label>

                        <select
                            name="schedules[${index}][weekday]"
                            required
                        >

                            <option value="">
                                -- Chọn thứ --
                            </option>

                            <option value="2">
                                Thứ 2
                            </option>

                            <option value="3">
                                Thứ 3
                            </option>

                            <option value="4">
                                Thứ 4
                            </option>

                            <option value="5">
                                Thứ 5
                            </option>

                            <option value="6">
                                Thứ 6
                            </option>

                            <option value="7">
                                Thứ 7
                            </option>

                            <option value="8">
                                Chủ nhật
                            </option>

                        </select>

                    </div>


                    <div class="field">

                        <label>
                            Tiết bắt đầu *
                        </label>

                        <input
                            class="input"
                            type="number"
                            name="schedules[${index}][start_period]"
                            min="1"
                            max="15"
                            required
                        >

                    </div>


                    <div class="field">

                        <label>
                            Tiết kết thúc *
                        </label>

                        <input
                            class="input"
                            type="number"
                            name="schedules[${index}][end_period]"
                            min="1"
                            max="15"
                            required
                        >

                    </div>


                    <div class="field">

                        <label>
                            Từ ngày *
                        </label>

                        <input
                            class="input"
                            type="date"
                            name="schedules[${index}][start_date]"
                            required
                        >

                    </div>


                    <div class="field">

                        <label>
                            Đến ngày *
                        </label>

                        <input
                            class="input"
                            type="date"
                            name="schedules[${index}][end_date]"
                            required
                        >

                    </div>


                    <div class="field">

                        <label>
                            Phòng học *
                        </label>

                        <input
                            class="input"
                            type="text"
                            name="schedules[${index}][room]"
                            maxlength="100"
                            required
                        >

                    </div>


                </div>


                <button
                    type="button"
                    class="btn secondary schedule-remove"
                    onclick="removeSchedule(this)"
                >
                    Xóa lịch này
                </button>

            `;


            container.appendChild(item);

            scheduleIndex++;

        }


        /*
         * ============================
         * XÓA LỊCH
         * ============================
         */

        function removeSchedule(button) {

            const item =
                button.closest(
                    '.schedule-item'
                );


            if (item) {

                item.remove();

            }


            /*
             * Đánh lại số thứ tự
             */

            const items =
                document.querySelectorAll(
                    '#schedule-container .schedule-item'
                );


            items.forEach(
                function (item, index) {

                    const title =
                        item.querySelector(
                            '.schedule-item-title'
                        );

                    if (title) {

                        title.textContent =
                            'Lịch học '
                            + (index + 1);

                    }

                }
            );

        }

    </script>

</div>


<?php

include dirname(__DIR__, 2) . '/layout/footer.php';

?>