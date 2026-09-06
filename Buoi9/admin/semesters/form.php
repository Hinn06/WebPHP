<?php
$title = $create ? 'Thêm học kỳ' : 'Sửa học kỳ';
include dirname(__DIR__, 2) . '/layout/header.php';
?>

<div class="card semester-form-card">

    <div class="page-heading">
        <div>
            <h2><?= e($title) ?></h2>
            <p class="muted">
                <?= $create
                    ? 'Nhập thông tin để tạo học kỳ mới.'
                    : 'Cập nhật thông tin học kỳ.'
                ?>
            </p>
        </div>
    </div>

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

        <div class="form-grid">

            <!-- Tên học kỳ -->
            <div class="field">
                <label>Tên học kỳ *</label>

                <input
                    class="input"
                    type="text"
                    name="name"
                    maxlength="100"
                    value="<?= e($data['name']) ?>"
                    placeholder="Ví dụ: Học kỳ 1 năm 2026"
                >

                <?php if (isset($errors['name'])): ?>
                    <div class="err">
                        <?= e($errors['name']) ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Năm học -->
            <div class="field">
                <label>Năm học *</label>

                <input
                    class="input"
                    type="text"
                    name="academic_year"
                    value="<?= e($data['academic_year']) ?>"
                    placeholder="2026-2027"
                >

                <?php if (isset($errors['academic_year'])): ?>
                    <div class="err">
                        <?= e($errors['academic_year']) ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Học kỳ -->
            <div class="field">
                <label>Học kỳ *</label>

                <select name="term">
                    <option value="1"
                        <?= $data['term'] == '1' ? 'selected' : '' ?>>
                        Học kỳ 1
                    </option>

                    <option value="2"
                        <?= $data['term'] == '2' ? 'selected' : '' ?>>
                        Học kỳ 2
                    </option>

                    <option value="3"
                        <?= $data['term'] == '3' ? 'selected' : '' ?>>
                        Học kỳ hè
                    </option>
                </select>

                <?php if (isset($errors['term'])): ?>
                    <div class="err">
                        <?= e($errors['term']) ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Trạng thái -->
            <div class="field">
                <label>Trạng thái học kỳ</label>

                <label class="checkbox-label">
                    <input
                        type="checkbox"
                        name="status"
                        <?= $data['status'] ? 'checked' : '' ?>
                    >

                    <span>Hoạt động</span>
                </label>
            </div>

            <!-- Ngày bắt đầu học -->
            <div class="field">
                <label>Ngày bắt đầu học *</label>

                <input
                    class="input"
                    type="date"
                    name="study_start"
                    value="<?= e($data['study_start']) ?>"
                >

                <?php if (isset($errors['study_start'])): ?>
                    <div class="err">
                        <?= e($errors['study_start']) ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Ngày kết thúc học -->
            <div class="field">
                <label>Ngày kết thúc học *</label>

                <input
                    class="input"
                    type="date"
                    name="study_end"
                    value="<?= e($data['study_end']) ?>"
                >

                <?php if (isset($errors['study_end'])): ?>
                    <div class="err">
                        <?= e($errors['study_end']) ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Bắt đầu đăng ký -->
            <div class="field">
                <label>Bắt đầu đăng ký *</label>

                <input
                    class="input"
                    type="date"
                    name="registration_start"
                    value="<?= e($data['registration_start']) ?>"
                >

                <?php if (isset($errors['registration_start'])): ?>
                    <div class="err">
                        <?= e($errors['registration_start']) ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Kết thúc đăng ký -->
            <div class="field">
                <label>Kết thúc đăng ký *</label>

                <input
                    class="input"
                    type="date"
                    name="registration_end"
                    value="<?= e($data['registration_end']) ?>"
                >

                <?php if (isset($errors['registration_end'])): ?>
                    <div class="err">
                        <?= e($errors['registration_end']) ?>
                    </div>
                <?php endif; ?>
            </div>

        </div>

        <div class="form-note">
            <strong>Lưu ý:</strong>
            Thời gian đăng ký được sử dụng để xác định sinh viên có thể đăng ký học phần hay không.
        </div>

        <div class="form-actions">
            <button type="submit" class="btn">
                <?= $create ? 'Thêm học kỳ' : 'Lưu thay đổi' ?>
            </button>

            <a
                class="btn secondary"
                href="<?= e(BASE_URL) ?>?r=admin/semesters"
            >
                Hủy
            </a>
        </div>

    </form>
</div>

<style>
.semester-form-card {
    max-width: 1100px;
    margin: 0 auto;
}

.page-heading {
    margin-bottom: 22px;
}

.page-heading h2 {
    margin: 0 0 6px;
    color: #315b87;
}

.page-heading .muted {
    margin: 0;
}

.field {
    display: flex;
    flex-direction: column;
}

.field label:first-child {
    margin-bottom: 7px;
    font-weight: 600;
    color: #34495e;
}

.field select,
.field .input {
    width: 100%;
    box-sizing: border-box;
}

.checkbox-label {
    display: flex !important;
    flex-direction: row !important;
    align-items: center;
    gap: 8px;
    min-height: 42px;
    margin: 0 !important;
    font-weight: 500 !important;
    color: #34495e;
}

.checkbox-label input {
    width: 17px;
    height: 17px;
    margin: 0;
}

.err {
    margin-top: 6px;
    color: #c0392b;
    font-size: 13px;
}

.form-note {
    margin-top: 20px;
    padding: 12px 15px;
    background: #f1f6fa;
    border: 1px solid #dce7ef;
    border-radius: 10px;
    color: #536779;
    font-size: 14px;
}

.form-actions {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-top: 22px;
}

.form-actions .btn {
    white-space: nowrap;
}

@media (max-width: 700px) {
    .semester-form-card {
        margin: 0;
    }

    .form-actions {
        flex-wrap: wrap;
    }
}
</style>

<?php include dirname(__DIR__, 2) . '/layout/footer.php'; ?>