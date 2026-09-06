<?php
$title = 'Quản lý giảng viên';
include dirname(__DIR__, 2) . '/layout/header.php';
$endpoint = BASE_URL . '?r=api/admin/search';
?>

<style>
.smart-search { position:relative; flex:1 1 420px; max-width:650px; }
.smart-search .search-icon { position:absolute; left:13px; top:50%; transform:translateY(-50%); pointer-events:none; }
.smart-search .input { padding-left:40px; padding-right:40px; width:100%; min-width:0; }
.clear-search { position:absolute; right:8px; top:50%; transform:translateY(-50%); width:28px; height:28px; border:0; border-radius:50%; background:transparent; color:#718191; font-size:18px; cursor:pointer; }
.clear-search:hover { background:#edf3f8; color:#315b87; }
.admin-toolbar { display:flex; align-items:center; justify-content:space-between; gap:15px; flex-wrap:wrap; margin-bottom:15px; }
.admin-toolbar h2 { margin:0; }
.search-row { display:flex; align-items:center; gap:12px; flex-wrap:wrap; }
.search-status { margin-top:10px; }
.action-row { display:flex; align-items:center; gap:5px; flex-wrap:nowrap; white-space:nowrap; }
.actions { white-space:nowrap; }
.actions form { margin:0; display:inline-flex; }
.icon-btn { width:34px; height:34px; padding:0; display:inline-flex; align-items:center; justify-content:center; border:1px solid #d5e0ea; border-radius:7px; background:#fff; color:#315b87; cursor:pointer; text-decoration:none; font-size:15px; }
.icon-btn:hover { background:#dbe8f3; }
.icon-btn.danger { color:#a04444; }
.icon-btn.danger:hover { background:#f8e8e8; }
.empty-row { text-align:center; padding:28px !important; color:#7a8794; }
.error-text { color:#a04444 !important; }
[data-admin-search].is-loading { opacity:.65; pointer-events:none; }
@media(max-width:700px){ .smart-search{flex-basis:100%;max-width:none} table{display:block;overflow-x:auto;white-space:nowrap;} }
</style>

<div data-admin-search data-type="lecturers" data-endpoint="<?= e($endpoint) ?>" data-base-url="<?= e(BASE_URL) ?>" data-csrf="<?= e(csrf_token()) ?>">
    <div class="admin-toolbar">
        <h2>Quản lý giảng viên</h2>
        <a class="btn" href="<?= e(BASE_URL) ?>?r=admin/lecturer/create">+ Thêm giảng viên</a>
    </div>

    <div class="card">
        <div class="search-row">
            <div class="smart-search">
                <span class="search-icon">🔍</span>
                <input class="input" type="text" data-search-input value="<?= e($q ?? '') ?>" placeholder="Tìm mã GV, họ tên, tài khoản, khoa..." autocomplete="off">
                <button type="button" class="clear-search" data-search-clear title="Xóa tìm kiếm" aria-label="Xóa tìm kiếm">×</button>
            </div>
        </div>
        <div class="search-status muted" data-search-result>
            <?= !empty($q) ? 'Kết quả tìm kiếm cho “' . e($q) . '”.' : 'Tổng số: ' . count($rows ?? []) . ' bản ghi trên trang hiện tại.' ?>
        </div>

        <div style="overflow-x:auto; margin-top:14px;">
            <table>
                <thead><tr><th>Mã GV</th><th>Họ tên</th><th>Tài khoản</th><th>Khoa</th><th>Trạng thái</th><th>Thao tác</th></tr></thead>
                <tbody data-search-body>
                    <?php if (empty($rows)): ?>
                        <tr><td colspan="6" class="empty-row">Không tìm thấy giảng viên phù hợp.</td></tr>
                    <?php else: ?>
                        <?php foreach ($rows as $r): ?>
                            <tr>
                                <td><?= e($r['lecturer_code']) ?></td>
                                <td><?= e($r['full_name']) ?></td>
                                <td><?= e($r['username']) ?></td>
                                <td><?= e($r['faculty_name']) ?></td>
                                <td><span class="badge <?= !$r['status'] ? 'off' : '' ?>"><?= $r['status'] ? 'Hoạt động' : 'Khóa' ?></span></td>
                                <td class="actions"><div class="action-row">
                                    <a class="icon-btn" title="Sửa" aria-label="Sửa" href="<?= e(BASE_URL) ?>?r=admin/lecturer/edit&id=<?= (int)$r['user_id'] ?>">✎</a>
                                    <form method="post" action="<?= e(BASE_URL) ?>?r=admin/lecturer/delete" onsubmit="return confirm('Bạn có chắc muốn xóa giảng viên này?');">
                                        <input type="hidden" name="_csrf" value="<?= e(csrf_token()) ?>">
                                        <input type="hidden" name="id" value="<?= (int)$r['user_id'] ?>">
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
                <a class="<?= $i === ($page ?? 1) ? 'active' : '' ?>" href="<?= e(BASE_URL) ?>?r=admin/lecturers&q=<?= urlencode($q ?? '') ?>&page=<?= $i ?>"><?= $i ?></a>
            <?php endfor; ?>
        </div>
    </div>
</div>

<script src="/BTL_web/public/assets/js/admin-search.js"></script>
<?php include dirname(__DIR__, 2) . '/layout/footer.php'; ?>
