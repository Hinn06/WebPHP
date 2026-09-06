<?php

$title = 'Danh sách sinh viên lớp';

include dirname(__DIR__, 2) . '/layout/header.php';

?>

<style>

.detail-toolbar {

    display: flex;

    align-items: center;

    justify-content: space-between;

    gap: 15px;

    margin-bottom: 18px;

    flex-wrap: wrap;
}

.detail-title {

    margin: 0;

    color: #315b87;
}

.back-btn {

    display: inline-flex;

    align-items: center;

    gap: 6px;

    height: 36px;

    padding: 0 13px;

    border: 1px solid #d2dde7;

    border-radius: 8px;

    background: #fff;

    color: #315b87;

    text-decoration: none;

    font-size: 13px;

    font-weight: 600;

    transition: .2s;
}

.back-btn:hover {

    background: #edf4f9;

}


/* =====================================================
   THÔNG TIN LỚP
===================================================== */

.class-info {

    background: #f5f8fb;

    border: 1px solid #e0e7ee;

    border-radius: 9px;

    padding: 15px;

    margin-bottom: 18px;

    line-height: 1.7;
}

.class-info strong {

    color: #315b87;
}


/* =====================================================
   LỊCH
===================================================== */

.schedule-list {

    display: grid;

    gap: 8px;

    margin-top: 10px;
}

.schedule-detail {

    background: #fff;

    border: 1px solid #e1e8ee;

    border-radius: 8px;

    padding: 9px 12px;

    color: #536b80;
}

.schedule-detail strong {

    color: #315b87;
}


/* =====================================================
   TABLE
===================================================== */

.student-table-wrap {

    width: 100%;

    overflow-x: auto;
}

.student-table {

    width: 100%;

    min-width: 600px;

    border-collapse: collapse;
}

.student-table th {

    background: #edf3f8;

    color: #315b87;

    padding: 12px 10px;

    text-align: left;

    border-bottom: 1px solid #dce5ee;
}

.student-table td {

    padding: 12px 10px;

    border-bottom: 1px solid #e5ebf0;
}

.student-table tbody tr:hover td {

    background: #f8fbfd;
}

.student-code {

    font-weight: 700;

    color: #315b87;
}


/* =====================================================
   EMPTY
===================================================== */

.empty-students {

    text-align: center;

    padding: 30px;

    color: #7a8794;
}

</style>


<div class="card">


    <!-- =================================================
         TIÊU ĐỀ + TRỞ LẠI
    ================================================== -->

    <div class="detail-toolbar">

        <h2 class="detail-title">
            Danh sách sinh viên
        </h2>

        <a
            class="back-btn"
            href="<?= e(BASE_URL) ?>?r=admin/classes"
        >
            ← Trở lại
        </a>

    </div>


    <!-- =================================================
         THÔNG TIN LỚP
    ================================================== -->

    <div class="class-info">

        <div>

            <strong>
                <?= e($class['class_code']) ?>
            </strong>

            ·

            <?= e($class['course_code']) ?>

            -

            <?= e($class['course_name']) ?>

        </div>


        <div>

            Học kỳ:

            <strong>
                <?= e($class['semester_name']) ?>
            </strong>

        </div>


        <div>

            Giảng viên:

            <strong>
                <?= e($class['lecturer_name']) ?>
            </strong>

        </div>


        <div>

            Sĩ số tối đa:

            <strong>
                <?= e($class['max_students']) ?>
            </strong>

        </div>


        <!-- =================================================
             LỊCH HỌC
        ================================================== -->

        <?php if (!empty($schedules)): ?>

            <div>

                <strong>
                    Lịch học:
                </strong>

                <div class="schedule-list">

                    <?php foreach ($schedules as $schedule): ?>

                        <div class="schedule-detail">

                            <strong>
                                Thứ <?= e($schedule['weekday']) ?>
                            </strong>

                            · Tiết

                            <?= e($schedule['start_period']) ?>

                            -

                            <?= e($schedule['end_period']) ?>

                            ·

                            Phòng

                            <?= e($schedule['room']) ?>

                            <br>

                            <span>
                                <?= e($schedule['start_date']) ?>

                                →

                                <?= e($schedule['end_date']) ?>
                            </span>

                        </div>

                    <?php endforeach; ?>

                </div>

            </div>

        <?php else: ?>

            <div class="muted">
                Chưa có lịch học.
            </div>

        <?php endif; ?>

    </div>


    <!-- =================================================
         DANH SÁCH SINH VIÊN
    ================================================== -->

    <h3>
        Sinh viên đã đăng ký
    </h3>


    <?php if (empty($class['students'])): ?>

        <div class="empty-students">

            Chưa có sinh viên đăng ký lớp học phần này.

        </div>

    <?php else: ?>

        <div class="student-table-wrap">

            <table class="student-table">

                <thead>

                    <tr>

                        <th>STT</th>

                        <th>Mã SV</th>

                        <th>Họ tên</th>

                        <th>Lớp hành chính</th>

                    </tr>

                </thead>


                <tbody>

                    <?php foreach ($class['students'] as $i => $s): ?>

                        <tr>

                            <td>
                                <?= $i + 1 ?>
                            </td>

                            <td>

                                <span class="student-code">
                                    <?= e($s['student_code']) ?>
                                </span>

                            </td>

                            <td>
                                <?= e($s['full_name']) ?>
                            </td>

                            <td>
                                <?= e($s['class_name']) ?>
                            </td>

                        </tr>

                    <?php endforeach; ?>

                </tbody>

            </table>

        </div>

    <?php endif; ?>


</div>


<?php

include dirname(__DIR__, 2) . '/layout/footer.php';

?>