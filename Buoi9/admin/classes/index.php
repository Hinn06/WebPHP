<?php

$title = 'Quản lý lớp học phần';

include dirname(__DIR__, 2) . '/layout/header.php';

$endpoint = BASE_URL . '?r=api/admin/search';

/**
 * Hiển thị thứ trong tuần
 */
function weekdayLabel(int $weekday): string
{
    $days = [
        2 => 'Thứ 2',
        3 => 'Thứ 3',
        4 => 'Thứ 4',
        5 => 'Thứ 5',
        6 => 'Thứ 6',
        7 => 'Thứ 7',
        8 => 'Chủ nhật'
    ];

    return $days[$weekday] ?? ('Thứ ' . $weekday);
}

/**
 * Hiển thị lịch học
 * Ví dụ: Thứ 2 · Tiết 1-3
 */
function scheduleDayLabel(array $schedule): string
{
    $weekday = (int)($schedule['weekday'] ?? 0);

    $startPeriod = $schedule['start_period'] ?? '';
    $endPeriod = $schedule['end_period'] ?? '';

    $day = weekdayLabel($weekday);

    if ($startPeriod !== '' && $endPeriod !== '') {
        return $day . ' · Tiết ' . $startPeriod . '-' . $endPeriod;
    }

    return $day;
}

/**
 * Hiển thị thời gian học
 * Ví dụ: 2026-08-10 - 2026-09-17
 */
function scheduleDateLabel(array $schedule): string
{
    $startDate = $schedule['start_date'] ?? '';
    $endDate = $schedule['end_date'] ?? '';

    if ($startDate && $endDate) {
        return $startDate . ' - ' . $endDate;
    }

    if ($startDate) {
        return $startDate;
    }

    if ($endDate) {
        return $endDate;
    }

    return '—';
}

/**
 * Hiển thị phòng học
 */
function scheduleRoomLabel(array $schedule): string
{
    return !empty($schedule['room'])
        ? $schedule['room']
        : '—';
}

?>

<style>
/* =========================
   TOOLBAR
========================= */

.class-toolbar {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 15px;
    flex-wrap: wrap;
    margin-bottom: 15px;
}

.class-toolbar h2 {
    margin: 0;
}


/* =========================
   SEARCH
========================= */

.smart-search {
    position: relative;
    flex: 1 1 450px;
    max-width: 800px;
}

.smart-search .search-icon {
    position: absolute;
    left: 13px;
    top: 50%;
    transform: translateY(-50%);
    pointer-events: none;
    z-index: 2;
}

.smart-search .input {
    padding-left: 40px;
    padding-right: 40px;
    width: 100%;
    min-width: 0;
    box-sizing: border-box;
}

.clear-search {
    position: absolute;
    right: 8px;
    top: 50%;
    transform: translateY(-50%);
    width: 28px;
    height: 28px;
    border: 0;
    border-radius: 50%;
    background: transparent;
    color: #718191;
    font-size: 18px;
    cursor: pointer;
}

.clear-search:hover {
    background: #edf3f8;
    color: #315b87;
}

.search-row {
    display: flex;
    gap: 12px;
    align-items: center;
    flex-wrap: wrap;
}

.search-status {
    margin-top: 10px;
}


/* =========================
   TABLE
========================= */

.class-table-wrap {
    overflow-x: auto;
    margin-top: 14px;
}

.class-table {
    width: 100%;
    min-width: 1250px;
    border-collapse: collapse;
    table-layout: fixed;
}

/* Header */

.class-table th {
    background: #edf3f8;
    color: #315b87;
    font-weight: 600;
    padding: 11px 9px;
    text-align: left;
    border-bottom: 1px solid #dce5ee;
    vertical-align: middle;
}

/* Body */

.class-table td {
    padding: 11px 9px;
    border-bottom: 1px solid #e5ebf0;
    vertical-align: top;
    line-height: 1.45;
}

.class-table tr:hover td {
    background: #f8fbfd;
}


/* =========================
   COLUMN WIDTH
========================= */

.class-table th:nth-child(1),
.class-table td:nth-child(1) {
    width: 90px;
}

.class-table th:nth-child(2),
.class-table td:nth-child(2) {
    width: 185px;
}

.class-table th:nth-child(3),
.class-table td:nth-child(3) {
    width: 175px;
}

.class-table th:nth-child(4),
.class-table td:nth-child(4) {
    width: 130px;
}

.class-table th:nth-child(5),
.class-table td:nth-child(5) {
    width: 145px;
}

.class-table th:nth-child(6),
.class-table td:nth-child(6) {
    width: 175px;
}

.class-table th:nth-child(7),
.class-table td:nth-child(7) {
    width: 105px;
}

.class-table th:nth-child(8),
.class-table td:nth-child(8) {
    width: 70px;
}

.class-table th:nth-child(9),
.class-table td:nth-child(9) {
    width: 125px;
}


/* =========================
   SCHEDULE
========================= */

.schedule-line {
    margin-bottom: 5px;
    line-height: 1.45;
}

.schedule-line:last-child {
    margin-bottom: 0;
}

.schedule-date {
    margin-bottom: 5px;
    line-height: 1.45;
}

.schedule-date:last-child {
    margin-bottom: 0;
}

.schedule-room {
    margin-bottom: 5px;
    line-height: 1.45;
}

.schedule-room:last-child {
    margin-bottom: 0;
}


/* =========================
   ACTION BUTTON
========================= */

.action-row {
    display: flex;
    align-items: center;
    gap: 5px;
    flex-wrap: nowrap;
    white-space: nowrap;
}

.actions {
    white-space: nowrap;
}

.actions form {
    margin: 0;
    display: inline-flex;
}

.icon-btn {
    width: 34px;
    height: 34px;
    padding: 0;
    display: inline-flex;
    align-items: center;
    justify-content: center;

    border: 1px solid #d5e0ea;
    border-radius: 7px;

    background: #fff;
    color: #315b87;

    cursor: pointer;
    text-decoration: none;
    font-size: 15px;

    transition:
        background .15s ease,
        border-color .15s ease;
}

.icon-btn:hover {
    background: #dbe8f3;
    border-color: #c4d5e3;
}

.icon-btn.danger {
    color: #a04444;
}

.icon-btn.danger:hover {
    background: #f8e8e8;
    border-color: #e6caca;
}


/* =========================
   EMPTY / ERROR
========================= */

.empty-row {
    text-align: center;
    padding: 30px !important;
    color: #7a8794;
}

.error-text {
    color: #a04444 !important;
}


/* =========================
   LOADING
========================= */

[data-admin-search].is-loading {
    opacity: .65;
    pointer-events: none;
}


/* =========================
   PAGINATION
========================= */

.pagination {
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 6px;
    margin-top: 18px;
    flex-wrap: wrap;
}

.pagination a {
    min-width: 34px;
    height: 34px;

    display: inline-flex;
    align-items: center;
    justify-content: center;

    padding: 0 8px;

    border-radius: 7px;
    border: 1px solid #d5e0ea;

    background: #fff;
    color: #315b87;

    text-decoration: none;
}

.pagination a:hover {
    background: #dbe8f3;
}

.pagination a.active {
    background: #5d89b2;
    border-color: #5d89b2;
    color: #fff;
}


/* =========================
   RESPONSIVE
========================= */

@media (max-width: 700px) {

    .smart-search {
        flex-basis: 100%;
        max-width: none;
    }

    .class-toolbar {
        align-items: stretch;
    }

    .class-toolbar .btn {
        width: 100%;
        text-align: center;
    }
}
</style>


<div
    data-admin-search
    data-type="classes"
    data-endpoint="<?= e($endpoint) ?>"
    data-base-url="<?= e(BASE_URL) ?>"
    data-csrf="<?= e(csrf_token()) ?>"
>

    <!-- =========================
         TIÊU ĐỀ
    ========================== -->

    <div class="class-toolbar">

        <h2>Quản lý lớp học phần</h2>

        <a
            class="btn"
            href="<?= e(BASE_URL) ?>?r=admin/class/create"
        >
            + Tạo lớp học phần
        </a>

    </div>


    <!-- =========================
         CARD
    ========================== -->

    <div class="card">

        <!-- SEARCH -->

        <div class="search-row">

            <div class="smart-search">

                <span class="search-icon">🔍</span>

                <input
                    class="input"
                    type="text"
                    data-search-input
                    value="<?= e($q ?? '') ?>"
                    placeholder="Tìm mã lớp, học phần, giảng viên, học kỳ..."
                    autocomplete="off"
                >

                <button
                    type="button"
                    class="clear-search"
                    data-search-clear
                    title="Xóa tìm kiếm"
                    aria-label="Xóa tìm kiếm"
                >
                    ×
                </button>

            </div>

        </div>


        <!-- SEARCH STATUS -->

        <div
            class="search-status muted"
            data-search-result
        >

            <?php if (!empty($q)): ?>

                Kết quả tìm kiếm cho
                “<?= e($q) ?>”.

            <?php else: ?>

                Danh sách lớp học phần hiện có.

            <?php endif; ?>

        </div>


        <!-- =========================
             TABLE
        ========================== -->

        <div class="class-table-wrap">

            <table class="class-table">

                <thead>

                    <tr>

                        <th>Mã lớp</th>

                        <th>Học phần</th>

                        <th>Học kỳ</th>

                        <th>Giảng viên</th>

                        <th>Lịch học</th>

                        <th>Thời gian</th>

                        <th>Phòng</th>

                        <th>Sĩ số</th>

                        <th>Thao tác</th>

                    </tr>

                </thead>


                <tbody data-search-body>

                    <?php if (empty($rows)): ?>

                        <tr>

                            <td
                                colspan="9"
                                class="empty-row"
                            >
                                Không tìm thấy lớp học phần phù hợp.
                            </td>

                        </tr>

                    <?php else: ?>


                        <?php foreach ($rows as $r): ?>

                            <tr>

                                <!-- =========================
                                     MÃ LỚP
                                ========================== -->

                                <td>
                                    <?= e($r['class_code'] ?? '') ?>
                                </td>


                                <!-- =========================
                                     HỌC PHẦN
                                ========================== -->

                                <td>

                                    <?=
                                        e(
                                            ($r['course_code'] ?? '')
                                            . ' - '
                                            . ($r['course_name'] ?? '')
                                        )
                                    ?>

                                </td>


                                <!-- =========================
                                     HỌC KỲ
                                ========================== -->

                                <td>
                                    <?= e($r['semester_name'] ?? '') ?>
                                </td>


                                <!-- =========================
                                     GIẢNG VIÊN
                                ========================== -->

                                <td>
                                    <?= e($r['lecturer_name'] ?? 'Chưa phân công') ?>
                                </td>


                                <!-- =========================
                                     LỊCH HỌC
                                ========================== -->

                                <td>

                                    <?php if (empty($r['schedules'])): ?>

                                        <span class="muted">
                                            Chưa có lịch
                                        </span>

                                    <?php else: ?>

                                        <?php foreach ($r['schedules'] as $s): ?>

                                            <div class="schedule-line">

                                                <?= e(scheduleDayLabel($s)) ?>

                                            </div>

                                        <?php endforeach; ?>

                                    <?php endif; ?>

                                </td>


                                <!-- =========================
                                     THỜI GIAN
                                ========================== -->

                                <td>

                                    <?php if (empty($r['schedules'])): ?>

                                        <span class="muted">
                                            —
                                        </span>

                                    <?php else: ?>

                                        <?php foreach ($r['schedules'] as $s): ?>

                                            <div class="schedule-date">

                                                <?= e(scheduleDateLabel($s)) ?>

                                            </div>

                                        <?php endforeach; ?>

                                    <?php endif; ?>

                                </td>


                                <!-- =========================
                                     PHÒNG
                                ========================== -->

                                <td>

                                    <?php if (empty($r['schedules'])): ?>

                                        <span class="muted">
                                            —
                                        </span>

                                    <?php else: ?>

                                        <?php foreach ($r['schedules'] as $s): ?>

                                            <div class="schedule-room">

                                                <?= e(scheduleRoomLabel($s)) ?>

                                            </div>

                                        <?php endforeach; ?>

                                    <?php endif; ?>

                                </td>


                                <!-- =========================
                                     SĨ SỐ
                                ========================== -->

                                <td>

                                    <?= e(
                                        ($r['registered_count'] ?? 0)
                                        . '/'
                                        . ($r['max_students'] ?? 0)
                                    ) ?>

                                </td>


                                <!-- =========================
                                     THAO TÁC
                                ========================== -->

                                <td class="actions">

                                    <div class="action-row">

                                        <!-- Danh sách sinh viên -->

                                        <a
                                            class="icon-btn"
                                            title="Danh sách sinh viên"
                                            aria-label="Danh sách sinh viên"
                                            href="<?= e(BASE_URL) ?>?r=admin/class/detail&id=<?= (int)($r['id'] ?? 0) ?>"
                                        >
                                            👥
                                        </a>


                                        <!-- Sửa -->

                                        <a
                                            class="icon-btn"
                                            title="Sửa"
                                            aria-label="Sửa"
                                            href="<?= e(BASE_URL) ?>?r=admin/class/edit&id=<?= (int)($r['id'] ?? 0) ?>"
                                        >
                                            ✎
                                        </a>


                                        <!-- Xóa -->

                                        <form
                                            method="post"
                                            action="<?= e(BASE_URL) ?>?r=admin/class/delete"
                                            onsubmit="return confirm('Bạn có chắc muốn xóa lớp học phần này?');"
                                        >

                                            <input
                                                type="hidden"
                                                name="_csrf"
                                                value="<?= e(csrf_token()) ?>"
                                            >

                                            <input
                                                type="hidden"
                                                name="id"
                                                value="<?= (int)($r['id'] ?? 0) ?>"
                                            >

                                            <button
                                                class="icon-btn danger"
                                                type="submit"
                                                title="Xóa"
                                                aria-label="Xóa"
                                            >
                                                🗑
                                            </button>

                                        </form>

                                    </div>

                                </td>

                            </tr>

                        <?php endforeach; ?>

                    <?php endif; ?>

                </tbody>

            </table>

        </div>


        <!-- =========================
             PAGINATION
        ========================== -->

        <div
            class="pagination"
            data-search-pagination
        >

            <?php

            $currentPage = (int)($page ?? 1);
            $totalPages = (int)($pages ?? 1);

            if ($totalPages < 1) {
                $totalPages = 1;
            }

            ?>

            <?php for ($i = 1; $i <= $totalPages; $i++): ?>

                <a
                    class="<?= $i === $currentPage ? 'active' : '' ?>"
                    href="<?= e(BASE_URL) ?>?r=admin/classes&q=<?= urlencode($q ?? '') ?>&page=<?= $i ?>"
                >
                    <?= $i ?>
                </a>

            <?php endfor; ?>

        </div>

    </div>

</div>


<!-- =========================
     AJAX SEARCH
========================= -->

<script src="assets/js/admin-search.js"></script>


<?php include dirname(__DIR__, 2) . '/layout/footer.php'; ?>