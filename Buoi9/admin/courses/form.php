<?php
$title = $create ? 'Tạo học phần' : 'Sửa học phần';

include dirname(__DIR__, 2) . '/layout/header.php';
?>

<style>
    .select-box {
        border: 1px solid #d7e1eb;
        border-radius: 12px;
        background: #f9fbfd;
        padding: 12px;
        margin-top: 8px;
    }

    .search-input {
        width: 100%;
        box-sizing: border-box;
        padding: 10px 12px;
        border: 1px solid #cbd8e5;
        border-radius: 8px;
        font-size: 14px;
        outline: none;
        margin-bottom: 10px;
        background: #fff;
    }

    .search-input:focus {
        border-color: #5d89b2;
        box-shadow: 0 0 0 3px rgba(93, 137, 178, .12);
    }

    .select-actions {
        display: flex;
        gap: 8px;
        margin-bottom: 10px;
        flex-wrap: wrap;
    }

    .small-btn {
        border: 1px solid #cbd8e5;
        background: #fff;
        color: #315b87;
        padding: 7px 12px;
        border-radius: 7px;
        cursor: pointer;
        font-size: 13px;
        transition: .2s;
    }

    .small-btn:hover {
        background: #dbe8f3;
        border-color: #b7cce0;
    }

    .option-list {
        max-height: 230px;
        overflow-y: auto;
        padding-right: 5px;
    }

    .option-item {
        display: flex;
        align-items: center;
        gap: 9px;
        padding: 9px 10px;
        margin-bottom: 5px;
        border-radius: 8px;
        cursor: pointer;
        transition: .2s;
        background: #fff;
        border: 1px solid transparent;
    }

    .option-item:hover {
        background: #eaf2f8;
        border-color: #d3e1ed;
    }

    .option-item input {
        width: 16px;
        height: 16px;
        cursor: pointer;
        flex-shrink: 0;
    }

    .option-text {
        line-height: 1.4;
    }

    .option-code {
        font-weight: 600;
        color: #315b87;
    }

    .option-sub {
        color: #68798a;
        font-size: 13px;
    }

    .selected-count {
        margin-top: 8px;
        color: #68798a;
        font-size: 13px;
    }

    .empty-result {
        display: none;
        padding: 12px;
        text-align: center;
        color: #7a8794;
        font-size: 14px;
    }

    .required-note {
        color: #777;
        font-size: 13px;
        margin-top: 5px;
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

        <!-- =========================
             THÔNG TIN HỌC PHẦN
        ========================== -->

        <div class="form-grid">

            <div class="field">
                <label>Mã học phần *</label>

                <input
                    class="input"
                    name="code"
                    maxlength="50"
                    value="<?= e($data['code']) ?>"
                >

                <?php if (isset($errors['code'])): ?>
                    <div class="err">
                        <?= e($errors['code']) ?>
                    </div>
                <?php endif; ?>
            </div>


            <div class="field">
                <label>Tên học phần *</label>

                <input
                    class="input"
                    maxlength="150"
                    name="name"
                    value="<?= e($data['name']) ?>"
                >

                <?php if (isset($errors['name'])): ?>
                    <div class="err">
                        <?= e($errors['name']) ?>
                    </div>
                <?php endif; ?>
            </div>


            <div class="field">
                <label>Số tín chỉ *</label>

                <input
                    class="input"
                    type="number"
                    min="1"
                    max="10"
                    name="credits"
                    value="<?= e($data['credits']) ?>"
                >

                <?php if (isset($errors['credits'])): ?>
                    <div class="err">
                        <?= e($errors['credits']) ?>
                    </div>
                <?php endif; ?>
            </div>


            <div class="field">
                <label>Trạng thái</label>

                <label>
                    <input
                        type="checkbox"
                        name="status"
                        <?= $data['status'] ? 'checked' : '' ?>
                    >
                    Hoạt động
                </label>
            </div>

        </div>


        <!-- =========================
             MÔ TẢ
        ========================== -->

        <div class="field">

            <label>Mô tả</label>

            <textarea
                name="description"
                maxlength="2000"
                rows="4"
            ><?= e($data['description']) ?></textarea>

        </div>


        <!-- =========================
             KHOA + HỌC KỲ
        ========================== -->

        <div class="form-grid">

            <!-- KHOA -->

            <div class="field">

                <label>
                    Khoa được phép học *
                </label>

                <div class="select-box">

                    <input
                        type="text"
                        class="search-input"
                        id="faculty-search"
                        placeholder="🔍 Tìm kiếm khoa..."
                        autocomplete="off"
                    >

                    <div class="select-actions">

                        <button
                            type="button"
                            class="small-btn"
                            id="faculty-select-all"
                        >
                            Chọn tất cả
                        </button>

                        <button
                            type="button"
                            class="small-btn"
                            id="faculty-clear"
                        >
                            Bỏ chọn
                        </button>

                    </div>


                    <div
                        class="option-list"
                        id="faculty-list"
                    >

                        <?php foreach ($faculties as $f): ?>

                            <label
                                class="option-item faculty-item"
                                data-search="<?= e(
                                    strtolower(
                                        $f['name'] . ' ' . $f['id']
                                    )
                                ) ?>"
                            >

                                <input
                                    type="checkbox"
                                    name="faculty_ids[]"
                                    value="<?= $f['id'] ?>"
                                    <?= in_array(
                                        (int)$f['id'],
                                        $data['faculty_ids'],
                                        true
                                    ) ? 'checked' : '' ?>
                                >

                                <span class="option-text">

                                    <span class="option-code">
                                        <?= e($f['name']) ?>
                                    </span>

                                </span>

                            </label>

                        <?php endforeach; ?>

                        <div
                            class="empty-result"
                            id="faculty-empty"
                        >
                            Không tìm thấy khoa phù hợp.
                        </div>

                    </div>


                    <div class="selected-count">
                        Đã chọn:
                        <strong id="faculty-count">0</strong>
                        khoa
                    </div>

                </div>


                <?php if (isset($errors['faculty_ids'])): ?>

                    <div class="err">
                        <?= e($errors['faculty_ids']) ?>
                    </div>

                <?php endif; ?>

                <div class="required-note">
                    Có thể chọn nhiều khoa.
                </div>

            </div>


            <!-- HỌC KỲ -->

            <div class="field">

                <label>
                    Học kỳ được mở *
                </label>

                <div class="select-box">

                    <div class="option-list">

                        <?php foreach ($semesters as $s): ?>

                            <label class="option-item">

                                <input
                                    type="checkbox"
                                    name="semester_ids[]"
                                    value="<?= $s['id'] ?>"
                                    <?= in_array(
                                        (int)$s['id'],
                                        $data['semester_ids'],
                                        true
                                    ) ? 'checked' : '' ?>
                                >

                                <span class="option-text">

                                    <span class="option-code">
                                        <?= e($s['name']) ?>
                                    </span>

                                    <?php if (!empty($s['academic_year'])): ?>

                                        <span class="option-sub">
                                            - <?= e($s['academic_year']) ?>
                                        </span>

                                    <?php endif; ?>

                                </span>

                            </label>

                        <?php endforeach; ?>

                    </div>

                </div>


                <?php if (isset($errors['semester_ids'])): ?>

                    <div class="err">
                        <?= e($errors['semester_ids']) ?>
                    </div>

                <?php endif; ?>

                <div class="required-note">
                    Có thể chọn nhiều học kỳ.
                </div>

            </div>

        </div>


        <!-- =========================
             GIẢNG VIÊN
        ========================== -->

        <div class="field">

            <label>
                Giảng viên có thể dạy học phần *
            </label>

            <div class="select-box">

                <input
                    type="text"
                    class="search-input"
                    id="lecturer-search"
                    placeholder="🔍 Tìm theo mã hoặc tên giảng viên..."
                    autocomplete="off"
                >


                <div class="select-actions">

                    <button
                        type="button"
                        class="small-btn"
                        id="lecturer-select-all"
                    >
                        Chọn tất cả
                    </button>

                    <button
                        type="button"
                        class="small-btn"
                        id="lecturer-clear"
                    >
                        Bỏ chọn
                    </button>

                </div>


                <div
                    class="option-list"
                    id="lecturer-list"
                >

                    <?php foreach ($lecturers as $l): ?>

                        <label
                            class="option-item lecturer-item"
                            data-search="<?= e(
                                strtolower(
                                    $l['lecturer_code']
                                    . ' '
                                    . $l['full_name']
                                    . ' '
                                    . $l['faculty_name']
                                )
                            ) ?>"
                        >

                            <input
                                type="checkbox"
                                name="lecturer_ids[]"
                                value="<?= $l['user_id'] ?>"
                                <?= in_array(
                                    (int)$l['user_id'],
                                    $data['lecturer_ids'],
                                    true
                                ) ? 'checked' : '' ?>
                            >

                            <span class="option-text">

                                <span class="option-code">
                                    <?= e($l['lecturer_code']) ?>
                                </span>

                                -

                                <?= e($l['full_name']) ?>

                                <span class="option-sub">
                                    - <?= e($l['faculty_name']) ?>
                                </span>

                            </span>

                        </label>

                    <?php endforeach; ?>


                    <div
                        class="empty-result"
                        id="lecturer-empty"
                    >
                        Không tìm thấy giảng viên phù hợp.
                    </div>

                </div>


                <div class="selected-count">

                    Đã chọn:

                    <strong id="lecturer-count">
                        0
                    </strong>

                    giảng viên

                </div>

            </div>


            <?php if (isset($errors['lecturer_ids'])): ?>

                <div class="err">
                    <?= e($errors['lecturer_ids']) ?>
                </div>

            <?php endif; ?>

            <div class="required-note">
                Có thể chọn nhiều giảng viên có khả năng dạy học phần này.
            </div>

        </div>


        <!-- =========================
             BUTTON
        ========================== -->

        <div style="margin-top:20px;">

            <button
                type="submit"
                class="btn"
            >
                Lưu học phần
            </button>

            <a
                class="btn secondary"
                href="<?= e(BASE_URL) ?>?r=admin/courses"
            >
                Hủy
            </a>

        </div>

    </form>

</div>


<script>

/* =====================================================
   HÀM LỌC DANH SÁCH
===================================================== */

function setupSearch(searchId, itemSelector, emptyId)
{
    const searchInput = document.getElementById(searchId);
    const items = document.querySelectorAll(itemSelector);
    const empty = document.getElementById(emptyId);

    if (!searchInput) {
        return;
    }

    searchInput.addEventListener('input', function () {

        const keyword = this.value
            .trim()
            .toLowerCase();

        let visible = 0;

        items.forEach(function (item) {

            const text = item.dataset.search || '';

            if (
                keyword === ''
                || text.includes(keyword)
            ) {
                item.style.display = 'flex';
                visible++;
            } else {
                item.style.display = 'none';
            }

        });

        if (empty) {
            empty.style.display =
                visible === 0 ? 'block' : 'none';
        }

    });
}


/* =====================================================
   CẬP NHẬT SỐ LƯỢNG ĐÃ CHỌN
===================================================== */

function updateCount(selector, countId)
{
    const checkboxes =
        document.querySelectorAll(selector);

    const countElement =
        document.getElementById(countId);

    if (!countElement) {
        return;
    }

    let count = 0;

    checkboxes.forEach(function (checkbox) {

        if (checkbox.checked) {
            count++;
        }

    });

    countElement.textContent = count;
}


/* =====================================================
   KHOA
===================================================== */

setupSearch(
    'faculty-search',
    '.faculty-item',
    'faculty-empty'
);


document
    .getElementById('faculty-select-all')
    .addEventListener('click', function () {

        document
            .querySelectorAll('.faculty-item')
            .forEach(function (item) {

                if (item.style.display !== 'none') {

                    const checkbox =
                        item.querySelector('input[type="checkbox"]');

                    checkbox.checked = true;
                }

            });

        updateCount(
            'input[name="faculty_ids[]"]',
            'faculty-count'
        );

    });


document
    .getElementById('faculty-clear')
    .addEventListener('click', function () {

        document
            .querySelectorAll('input[name="faculty_ids[]"]')
            .forEach(function (checkbox) {

                checkbox.checked = false;

            });

        updateCount(
            'input[name="faculty_ids[]"]',
            'faculty-count'
        );

    });


document
    .querySelectorAll('input[name="faculty_ids[]"]')
    .forEach(function (checkbox) {

        checkbox.addEventListener('change', function () {

            updateCount(
                'input[name="faculty_ids[]"]',
                'faculty-count'
            );

        });

    });


/* =====================================================
   GIẢNG VIÊN
===================================================== */

setupSearch(
    'lecturer-search',
    '.lecturer-item',
    'lecturer-empty'
);


document
    .getElementById('lecturer-select-all')
    .addEventListener('click', function () {

        document
            .querySelectorAll('.lecturer-item')
            .forEach(function (item) {

                if (item.style.display !== 'none') {

                    const checkbox =
                        item.querySelector('input[type="checkbox"]');

                    checkbox.checked = true;
                }

            });

        updateCount(
            'input[name="lecturer_ids[]"]',
            'lecturer-count'
        );

    });


document
    .getElementById('lecturer-clear')
    .addEventListener('click', function () {

        document
            .querySelectorAll('input[name="lecturer_ids[]"]')
            .forEach(function (checkbox) {

                checkbox.checked = false;

            });

        updateCount(
            'input[name="lecturer_ids[]"]',
            'lecturer-count'
        );

    });


document
    .querySelectorAll('input[name="lecturer_ids[]"]')
    .forEach(function (checkbox) {

        checkbox.addEventListener('change', function () {

            updateCount(
                'input[name="lecturer_ids[]"]',
                'lecturer-count'
            );

        });

    });


/* =====================================================
   KHỞI TẠO SỐ LƯỢNG KHI MỞ TRANG
===================================================== */

updateCount(
    'input[name="faculty_ids[]"]',
    'faculty-count'
);

updateCount(
    'input[name="lecturer_ids[]"]',
    'lecturer-count'
);

</script>


<?php
include dirname(__DIR__, 2) . '/layout/footer.php';
?>