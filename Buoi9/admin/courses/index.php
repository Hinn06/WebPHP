<?php
$title = 'Quản lý học phần';
include dirname(__DIR__, 2) . '/layout/header.php';
$endpoint = BASE_URL . '?r=api/admin/search';
?>

<style>
.course-toolbar{display:flex;align-items:center;justify-content:space-between;gap:15px;flex-wrap:wrap;margin-bottom:15px}.course-toolbar h2{margin:0}
.smart-search{position:relative;flex:1 1 450px;max-width:700px}.smart-search .search-icon{position:absolute;left:13px;top:50%;transform:translateY(-50%);pointer-events:none}.smart-search .input{padding-left:40px;padding-right:40px;width:100%;min-width:0}.clear-search{position:absolute;right:8px;top:50%;transform:translateY(-50%);width:28px;height:28px;border:0;border-radius:50%;background:transparent;color:#718191;font-size:18px;cursor:pointer}.clear-search:hover{background:#edf3f8;color:#315b87}.search-row{display:flex;gap:12px;align-items:center;flex-wrap:wrap}.search-status{margin-top:10px}.course-table-wrap{width:100%;overflow-x:auto;margin-top:14px}.course-table{width:100%;min-width:850px;border-collapse:collapse}.course-table th{background:#edf3f8;color:#315b87;font-weight:600;padding:12px 10px;text-align:left;border-bottom:1px solid #dce5ee}.course-table td{padding:12px 10px;border-bottom:1px solid #e5ebf0;vertical-align:middle}.course-table tr:hover td{background:#f8fbfd}.course-code{font-weight:600;color:#315b87;white-space:nowrap}.course-name{font-weight:500;color:#354b60}.faculty-text{max-width:260px;line-height:1.5}.action-row{display:flex;align-items:center;gap:5px;flex-wrap:nowrap;white-space:nowrap}.actions{white-space:nowrap}.actions form{margin:0;display:inline-flex}.icon-btn{width:34px;height:34px;padding:0;display:inline-flex;align-items:center;justify-content:center;border:1px solid #d5e0ea;border-radius:7px;background:#fff;color:#315b87;cursor:pointer;text-decoration:none;font-size:15px}.icon-btn:hover{background:#dbe8f3}.icon-btn.danger{color:#a04444}.icon-btn.danger:hover{background:#f8e8e8}.empty-row{text-align:center;padding:30px!important;color:#7a8794}.error-text{color:#a04444!important}[data-admin-search].is-loading{opacity:.65;pointer-events:none}.pagination{display:flex;justify-content:center;align-items:center;gap:6px;margin-top:18px;flex-wrap:wrap}.pagination a{min-width:34px;height:34px;display:inline-flex;align-items:center;justify-content:center;padding:0 8px;border-radius:7px;border:1px solid #d5e0ea;background:#fff;color:#315b87;text-decoration:none}.pagination a:hover{background:#dbe8f3}.pagination a.active{background:#5d89b2;border-color:#5d89b2;color:#fff}@media(max-width:700px){.smart-search{flex-basis:100%;max-width:none}.course-table{min-width:760px}}
</style>

<div data-admin-search data-type="courses" data-endpoint="<?= e($endpoint) ?>" data-base-url="<?= e(BASE_URL) ?>" data-csrf="<?= e(csrf_token()) ?>">
    <div class="course-toolbar">
        <h2>Quản lý học phần</h2>
        <a class="btn" href="<?= e(BASE_URL) ?>?r=admin/course/create">+ Thêm học phần</a>
    </div>

    <div class="card">
        <div class="search-row">
            <div class="smart-search">
                <span class="search-icon">🔍</span>
                <input class="input" type="text" data-search-input value="<?= e($q ?? '') ?>" placeholder="Tìm mã, tên học phần hoặc khoa..." autocomplete="off">
                <button type="button" class="clear-search" data-search-clear title="Xóa tìm kiếm" aria-label="Xóa tìm kiếm">×</button>
            </div>
        </div>
        <div class="search-status muted" data-search-result>
            <?= !empty($q) ? 'Kết quả tìm kiếm cho “' . e($q) . '”.' : 'Danh sách học phần hiện có.' ?>
        </div>

        <div class="course-table-wrap">
            <table class="course-table">
                <thead><tr><th style="width:110px">Mã HP</th><th>Tên học phần</th><th style="width:70px">TC</th><th>Khoa</th><th style="width:120px">Trạng thái</th><th style="width:110px">Thao tác</th></tr></thead>
                <tbody data-search-body>
                    <?php if (empty($rows)): ?>
                        <tr><td colspan="6" class="empty-row">Không tìm thấy học phần phù hợp.</td></tr>
                    <?php else: ?>
                        <?php foreach ($rows as $r): ?>
                            <tr>
                                <td><span class="course-code"><?= e($r['code']) ?></span></td>
                                <td><span class="course-name"><?= e($r['name']) ?></span></td>
                                <td><?= e($r['credits']) ?></td>
                                <td><div class="faculty-text"><?= e($r['faculties'] ?? '') ?></div></td>
                                <td><span class="badge <?= !$r['status'] ? 'off' : '' ?>"><?= $r['status'] ? 'Hoạt động' : 'Khóa' ?></span></td>
                                <td class="actions"><div class="action-row">
                                    <a class="icon-btn" title="Sửa" aria-label="Sửa" href="<?= e(BASE_URL) ?>?r=admin/course/edit&id=<?= (int)$r['id'] ?>">✎</a>
                                    <form method="post" action="<?= e(BASE_URL) ?>?r=admin/course/delete" onsubmit="return confirm('Bạn có chắc muốn xóa học phần này?');">
                                        <input type="hidden" name="_csrf" value="<?= e(csrf_token()) ?>">
                                        <input type="hidden" name="id" value="<?= (int)$r['id'] ?>">
                                        <button class="icon-btn danger" type="submit" title="Xóa" aria-label="Xóa">🗑</button>
                                    </form>
                                </div></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <div class="pagination" data-search-pagination>
            <?php for ($i = 1; $i <= ($pages ?? 1); $i++): ?>
                <a class="<?= $i === ($page ?? 1) ? 'active' : '' ?>" href="<?= e(BASE_URL) ?>?r=admin/courses&q=<?= urlencode($q ?? '') ?>&page=<?= $i ?>"><?= $i ?></a>
            <?php endfor; ?>
        </div>
    </div>
</div>

<script src="/BTL_web/public/assets/js/admin-search.js"></script>
<?php include dirname(__DIR__, 2) . '/layout/footer.php'; ?>
