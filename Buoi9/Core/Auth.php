<?php
namespace App\Core;
class Auth {
    public static function check(): bool { return isset($_SESSION['user_id']); }
    public static function id(): ?int { return isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : null; }
    public static function role(): ?string { return $_SESSION['role'] ?? null; }
    public static function user(): array { return $_SESSION['user'] ?? []; }
    public static function login(array $user): void { session_regenerate_id(true); $_SESSION['user_id']=(int)$user['id']; $_SESSION['role']=$user['role']; $_SESSION['user']=$user; }
    public static function logout(): void { $_SESSION=[]; if(ini_get('session.use_cookies')){ $p=session_get_cookie_params(); setcookie(session_name(),'',['expires'=>time()-42000,'path'=>$p['path'],'domain'=>$p['domain'],'secure'=>$p['secure'],'httponly'=>$p['httponly'],'samesite'=>$p['samesite'] ?? 'Lax']); } session_destroy(); }
    public static function requireLogin(): void { if(!self::check()) redirect('auth/login'); }
    public static function requireRole(string|array $roles): void { self::requireLogin(); $roles=(array)$roles; if(!in_array(self::role(),$roles,true)){ http_response_code(403); exit('Bạn không có quyền truy cập chức năng này.'); } }
}
