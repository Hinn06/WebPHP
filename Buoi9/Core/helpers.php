<?php
function e(mixed $value): string { return htmlspecialchars((string)$value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); }
function redirect(string $route = 'home', array $params = []): never {
    $query = http_build_query(array_merge(['r' => $route], $params));
    header('Location: ' . (defined('BASE_URL') ? BASE_URL : '/BTLON/public/index.php') . '?' . $query);
    exit;
}
function flash(string $key, ?string $message = null): ?string {
    if ($message !== null) { $_SESSION['_flash'][$key] = $message; return null; }
    $value = $_SESSION['_flash'][$key] ?? null; unset($_SESSION['_flash'][$key]); return $value;
}
function old(string $key, mixed $default = ''): mixed { return $_SESSION['_old'][$key] ?? $default; }
function errors(): array { return $_SESSION['_errors'] ?? []; }
function rememberForm(array $data, array $errs): void { $_SESSION['_old'] = $data; $_SESSION['_errors'] = $errs; }
function clearFormMemory(): void { unset($_SESSION['_old'], $_SESSION['_errors']); }
function csrf_token(): string { return $_SESSION['_csrf'] ??= bin2hex(random_bytes(32)); }
function verify_csrf(): void {
    $given = $_POST['_csrf'] ?? '';
    if (!$given || !hash_equals($_SESSION['_csrf'] ?? '', $given)) { http_response_code(419); exit('CSRF token không hợp lệ.'); }
}
function post_string(string $key): string { return trim((string)($_POST[$key] ?? '')); }
function post_int(string $key): ?int { $v = filter_input(INPUT_POST, $key, FILTER_VALIDATE_INT); return $v === false || $v === null ? null : (int)$v; }
function selectedIds(string $key): array { $values = $_POST[$key] ?? []; if (!is_array($values)) return []; $out=[]; foreach($values as $v){ if(ctype_digit((string)$v)) $out[]=(int)$v; } return array_values(array_unique($out)); }
function paginate(int $page, int $perPage, int $total): array { $perPage=max(1,min(50,$perPage)); $pages=max(1,(int)ceil($total/$perPage)); $page=max(1,min($pages,$page)); return [$page,$perPage,($page-1)*$perPage,$pages]; }
