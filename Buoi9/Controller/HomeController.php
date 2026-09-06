<?php
namespace App\Controller;
use App\Core\Auth; use App\Core\Controller;
class HomeController extends Controller {public function __construct(private \PDO $pdo){} public function index():void{if(!Auth::check())redirect('auth/login');redirect(match(Auth::role()){ 'admin'=>'admin/dashboard','teacher'=>'teacher/dashboard',default=>'student/register'});}}
