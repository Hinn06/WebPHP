<?php

use App\Core\Auth;

$title = $title ?? 'Hệ thống quản lý khóa học và đăng ký học phần';

$currentRoute = $_GET['r'] ?? '';

/*
|--------------------------------------------------------------------------
| THÔNG TIN TÀI KHOẢN
|--------------------------------------------------------------------------
*/

$user = Auth::user();

$fullName = $user['full_name'] ?? 'Người dùng';

$role = Auth::role();

if ($role === 'admin') {

    $roleName = 'Quản trị viên';
    $avatarText = 'A';

} elseif ($role === 'teacher') {

    $roleName = 'Giảng viên';
    $avatarText = 'GV';

} elseif ($role === 'student') {

    $roleName = 'Sinh viên';
    $avatarText = 'SV';

} else {

    $roleName = 'Người dùng';
    $avatarText = 'U';
}

?>

<!doctype html>

<html lang="vi">

<head>

    <meta charset="utf-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1"
    >

    <title><?= e($title) ?></title>


    <style>

        /* =====================================================
           RESET
        ===================================================== */

        * {
            box-sizing: border-box;
        }


        html,
        body {
            margin: 0;
            padding: 0;
        }


        html {
            height: 100%;
        }


        body {

            min-height: 100vh;

            font-family: Arial, sans-serif;

            background: #eef3f8;

            color: #334155;

            line-height: 1.5;

            overflow-x: hidden;
        }


        a {
            text-decoration: none;
            color: #315b87;
        }


        button,
        input,
        select,
        textarea {
            font-family: Arial, sans-serif;
        }


        /* =====================================================
           HEADER CỐ ĐỊNH
        ===================================================== */

        .top {

            position: fixed;

            top: 0;

            left: 0;

            right: 0;

            height: 68px;

            background: #dfe8f1;

            border-bottom: 1px solid #cfdbe6;

            display: flex;

            align-items: center;

            justify-content: space-between;

            gap: 20px;

            padding: 0 30px;

            z-index: 1000;

            box-shadow:
                0 2px 8px rgba(
                    49,
                    91,
                    135,
                    0.08
                );
        }


        /* =====================================================
           TÊN HỆ THỐNG
        ===================================================== */

        .brand {

            font-weight: 700;

            color: #315b87;

            font-size: 18px;

            line-height: 1.25;

            letter-spacing: .2px;

            white-space: nowrap;

            overflow: hidden;

            text-overflow: ellipsis;

            flex: 1;

            min-width: 0;
        }


        /* =====================================================
           KHU VỰC TÀI KHOẢN
        ===================================================== */

        .account {

            display: flex;

            align-items: center;

            justify-content: flex-end;

            gap: 11px;

            height: 100%;

            flex-shrink: 0;

            white-space: nowrap;
        }


        /* =====================================================
           AVATAR HÌNH TRÒN
        ===================================================== */

        .account-avatar {

            width: 42px;

            height: 42px;

            min-width: 42px;

            border-radius: 50%;

            background: #5d89b2;

            color: #ffffff;

            display: flex;

            align-items: center;

            justify-content: center;

            font-size: 12px;

            font-weight: 700;

            letter-spacing: .2px;

            border: 3px solid rgba(
                255,
                255,
                255,
                .75
            );

            box-shadow:
                0 3px 9px rgba(
                    49,
                    91,
                    135,
                    .18
                );
        }


        /* =====================================================
           THÔNG TIN TÀI KHOẢN
        ===================================================== */

        .account-info {

            display: flex;

            flex-direction: column;

            justify-content: center;

            min-width: 90px;

            line-height: 1.2;
        }


        .account-name {

            color: #294b6d;

            font-size: 14px;

            font-weight: 700;

            max-width: 180px;

            overflow: hidden;

            text-overflow: ellipsis;

            white-space: nowrap;
        }


        .account-role {

            color: #6b7b8c;

            font-size: 12px;

            margin-top: 3px;

            white-space: nowrap;
        }


        /* =====================================================
           NÚT ĐĂNG XUẤT
        ===================================================== */

        .logout-form {
            margin: 0;
        }


        .logout {

            border: 1px solid #c5d3df;

            background: #ffffff;

            color: #315b87;

            min-height: 36px;

            padding: 7px 13px;

            border-radius: 8px;

            cursor: pointer;

            font-size: 13px;

            font-weight: 600;

            transition:
                background .2s ease,
                color .2s ease,
                transform .15s ease,
                box-shadow .2s ease;
        }


        .logout:hover {

            background: #315b87;

            color: #ffffff;

            transform: translateY(-1px);

            box-shadow:
                0 3px 8px rgba(
                    49,
                    91,
                    135,
                    .18
                );
        }


        /* =====================================================
           MENU BÊN TRÁI - CỐ ĐỊNH
        ===================================================== */

        .nav {

            position: fixed;

            left: 18px;

            top: 86px;

            bottom: 18px;

            width: 235px;

            background: #ffffff;

            border: 1px solid #dce5ee;

            border-radius: 16px;

            padding: 18px 12px;

            box-shadow:
                0 5px 20px rgba(
                    49,
                    91,
                    135,
                    .08
                );

            display: flex;

            flex-direction: column;

            gap: 7px;

            z-index: 900;

            overflow-y: auto;
        }


        /* =====================================================
           TIÊU ĐỀ MENU
        ===================================================== */

        .nav-title {

            color: #315b87;

            font-size: 16px;

            font-weight: 700;

            text-align: center;

            padding: 8px 12px 15px;

            border-bottom: 1px solid #edf1f5;

            margin-bottom: 5px;
        }


        /* =====================================================
           MENU ITEM
        ===================================================== */

        .nav a {

            display: flex;

            align-items: center;

            min-height: 46px;

            padding: 0 15px;

            border-radius: 10px;

            color: #526579;

            background: transparent;

            font-size: 14px;

            font-weight: 600;

            transition:
                background .2s ease,
                color .2s ease,
                transform .2s ease,
                box-shadow .2s ease;

            flex-shrink: 0;
        }


        /* HOVER */

        .nav a:hover {

            background: #dbe8f3;

            color: #234d75;

            transform: translateX(3px);
        }


        /* ACTIVE */

        .nav a.active {

            background: #5d89b2;

            color: #ffffff;

            box-shadow:
                0 4px 10px rgba(
                    93,
                    137,
                    178,
                    .22
                );
        }


        .nav a.active:hover {

            background: #4f7da6;

            color: #ffffff;

            transform: translateX(3px);
        }


        /* =====================================================
           NỘI DUNG CHÍNH
        ===================================================== */

        .wrap {

            width: auto;

            max-width: none;

            margin-left: 280px;

            margin-right: 25px;

            padding-left: 20px;

            padding-right: 20px;

            padding-top: 92px;

            padding-bottom: 30px;
        }


        /* =====================================================
           TIÊU ĐỀ
        ===================================================== */

        h1,
        h2,
        h3 {

            color: #315b87;
        }


        h1 {

            margin-top: 0;
        }


        h2 {

            margin-top: 0;
        }


        /* =====================================================
           CARD
        ===================================================== */

        .card {

            background: #ffffff;

            border: 1px solid #e0e6ed;

            border-radius: 12px;

            padding: 20px;

            box-shadow:
                0 2px 8px rgba(
                    49,
                    91,
                    135,
                    .06
                );

            margin-bottom: 18px;
        }


        /* =====================================================
           GRID
        ===================================================== */

        .grid {

            display: grid;

            grid-template-columns:
                repeat(3, 1fr);

            gap: 16px;
        }


        /* =====================================================
           THỐNG KÊ
        ===================================================== */

        .stat {

            padding: 20px;

            background: #ffffff;

            border: 1px solid #e0e6ed;

            border-radius: 12px;
        }


        .stat b {

            font-size: 28px;

            display: block;

            margin-top: 8px;

            color: #315b87;
        }


        /* =====================================================
           TOOLBAR
        ===================================================== */

        .toolbar {

            display: flex;

            gap: 12px;

            justify-content: space-between;

            align-items: center;

            margin-bottom: 15px;

            flex-wrap: wrap;
        }


        /* =====================================================
           THANH TÌM KIẾM
           INPUT + NÚT LUÔN CÙNG DÒNG
        ===================================================== */

        .search {

            display: flex;

            flex-direction: row;

            align-items: center;

            gap: 10px;

            width: 100%;

            margin: 0 0 18px 0;

            flex-wrap: nowrap;
        }


        .search .input {

            flex: 1 1 auto;

            min-width: 0;

            width: auto;

            height: 40px;

            margin: 0;

            box-sizing: border-box;
        }


        .search .btn {

            flex: 0 0 auto;

            width: auto;

            min-width: 110px;

            height: 40px;

            min-height: 40px;

            margin: 0;

            padding: 8px 18px;

            white-space: nowrap;

            display: inline-flex;

            align-items: center;

            justify-content: center;
        }


        /* =====================================================
           INPUT
        ===================================================== */

        .input,
        select,
        textarea {

            width: 100%;

            padding: 10px 11px;

            border: 1px solid #ccd7e3;

            border-radius: 7px;

            background: #ffffff;

            color: #334155;

            font-family: Arial, sans-serif;

            font-size: 14px;
        }


        .input:focus,
        select:focus,
        textarea:focus {

            outline: none;

            border-color: #5d89b2;

            box-shadow:
                0 0 0 2px rgba(
                    93,
                    137,
                    178,
                    .12
                );
        }


        /* =====================================================
           BUTTON
        ===================================================== */

        .btn {

            min-width: 110px;

            min-height: 40px;

            padding: 9px 18px;

            border: none;

            border-radius: 9px;

            background: #5d89b2;

            color: #ffffff;

            cursor: pointer;

            display: inline-flex;

            align-items: center;

            justify-content: center;

            text-align: center;

            font-size: 14px;

            font-weight: 600;

            transition:
                background .2s ease,
                transform .15s ease,
                box-shadow .2s ease;

            white-space: nowrap;
        }


        .btn:hover {

            background: #426f98;

            transform: translateY(-1px);

            box-shadow:
                0 4px 10px rgba(
                    49,
                    91,
                    135,
                    .18
                );
        }


        .btn:active {

            transform: translateY(0);
        }


        .btn.secondary {

            background: #8997a6;
        }


        .btn.secondary:hover {

            background: #687786;
        }


        .btn.danger {

            background: #c75b5b;
        }


        .btn.danger:hover {

            background: #a94343;
        }


        .btn.green {

            background: #5b9677;
        }


        .btn.green:hover {

            background: #467b60;
        }


        /* =====================================================
           TABLE
        ===================================================== */

        table {

            width: 100%;

            border-collapse: collapse;
        }


        th,
        td {

            padding: 11px 9px;

            border-bottom: 1px solid #edf0f4;

            text-align: left;

            font-size: 14px;

            vertical-align: top;
        }


        th {

            background: #f4f7fa;

            font-size: 13px;

            color: #415a73;
        }


        tr:hover td {

            background: #fafcfe;
        }


        /* =====================================================
           BADGE
        ===================================================== */

        .badge {

            display: inline-block;

            padding: 4px 8px;

            border-radius: 20px;

            font-size: 12px;

            background: #e7f2ec;

            color: #28704d;
        }


        .badge.off {

            background: #f8e8e8;

            color: #a04444;
        }


        /* =====================================================
           THÔNG BÁO
        ===================================================== */

        .alert {

            padding: 11px 13px;

            border-radius: 7px;

            margin-bottom: 15px;
        }


        .alert.success {

            background: #e8f4ed;

            color: #286b4b;
        }


        .alert.error {

            background: #f9eaea;

            color: #9d3d3d;
        }


        /* =====================================================
           FORM
        ===================================================== */

        .form-grid {

            display: grid;

            grid-template-columns:
                1fr 1fr;

            gap: 15px;
        }


        .field {

            margin-bottom: 12px;
        }


        .field label {

            font-weight: 600;

            font-size: 13px;

            display: block;

            margin-bottom: 6px;

            color: #415a73;
        }


        .err {

            color: #b33d3d;

            font-size: 12px;

            margin-top: 4px;
        }


        /* =====================================================
           CHECKBOX
        ===================================================== */

        .checks {

            display: grid;

            grid-template-columns:
                repeat(2, 1fr);

            gap: 7px;

            max-height: 190px;

            overflow: auto;

            border: 1px solid #e0e6ed;

            padding: 10px;

            border-radius: 7px;
        }


        .checks label {

            font-weight: 400;
        }


        /* =====================================================
           PHÂN TRANG
        ===================================================== */

        .pagination {

            display: flex;

            gap: 7px;

            margin-top: 15px;

            flex-wrap: wrap;
        }


        .pagination a,
        .pagination span {

            padding: 7px 10px;

            border: 1px solid #d7e0e9;

            border-radius: 6px;
        }


        .pagination .active {

            background: #5d89b2;

            color: #ffffff;
        }


        /* =====================================================
           TEXT
        ===================================================== */

        .muted {

            color: #6b7b8c;

            font-size: 13px;
        }


        /* =====================================================
           ACTION
        ===================================================== */

        .actions {

            display: flex;

            gap: 6px;

            flex-wrap: wrap;
        }


        /* =====================================================
           LỊCH GIẢNG DẠY
        ===================================================== */

        .schedule {

            display: grid;

            grid-template-columns:
                repeat(7, 1fr);

            gap: 8px;
        }


        .day {

            background: #f7f9fb;

            border: 1px solid #e0e6ed;

            border-radius: 8px;

            min-height: 170px;

            padding: 8px;
        }


        .day h4 {

            margin: 0 0 8px;

            font-size: 13px;

            color: #315b87;
        }


        .slot {

            background: #eaf2f9;

            border-left: 3px solid #5d89b2;

            border-radius: 5px;

            padding: 8px;

            margin-bottom: 7px;

            font-size: 12px;
        }


        .empty {

            color: #9aa6b2;

            font-size: 12px;
        }


        /* =====================================================
           TABLET
        ===================================================== */

        @media (max-width: 1000px) {

            .grid {

                grid-template-columns:
                    repeat(2, 1fr);
            }


            .schedule {

                grid-template-columns:
                    repeat(4, 1fr);
            }
        }


        /* =====================================================
           MOBILE
        ===================================================== */

        @media (max-width: 800px) {

            .top {

                height: auto;

                min-height: 68px;

                padding: 10px 15px;

                gap: 10px;
            }


            .brand {

                font-size: 15px;

                max-width: 55%;
            }


            .account {

                gap: 7px;
            }


            .account-avatar {

                width: 36px;

                height: 36px;

                min-width: 36px;

                font-size: 10px;
            }


            .account-name {

                max-width: 100px;

                font-size: 12px;
            }


            .account-role {

                font-size: 11px;
            }


            .logout {

                font-size: 11px;

                padding: 6px 9px;
            }


            /* MENU */

            .nav {

                position: fixed;

                top: 80px;

                left: 10px;

                right: 10px;

                bottom: auto;

                width: auto;

                min-height: auto;

                max-height: 65px;

                flex-direction: row;

                overflow-x: auto;

                overflow-y: hidden;

                padding: 10px;

                border-radius: 12px;
            }


            .nav-title {

                display: none;
            }


            .nav a {

                white-space: nowrap;

                min-height: 42px;

                padding: 0 14px;
            }


            /* NỘI DUNG */

            .wrap {

                margin: 0 15px;

                padding-left: 0;

                padding-right: 0;

                padding-top: 165px;
            }


            .grid {

                grid-template-columns: 1fr;
            }


            .form-grid {

                grid-template-columns: 1fr;
            }


            .checks {

                grid-template-columns: 1fr;
            }


            .schedule {

                grid-template-columns:
                    1fr 1fr;
            }


            /*
             * Tìm kiếm vẫn cùng dòng
             */

            .search {

                display: flex;

                flex-direction: row;

                align-items: center;

                flex-wrap: nowrap;

                width: 100%;
            }


            .search .input {

                flex: 1 1 auto;

                min-width: 0;

                width: auto;
            }


            .search .btn {

                flex: 0 0 auto;

                min-width: 95px;

                padding: 8px 12px;

                white-space: nowrap;
            }
        }


        /* =====================================================
           MOBILE NHỎ
        ===================================================== */

        @media (max-width: 500px) {

            .top {

                align-items: center;
            }


            .brand {

                max-width: 45%;

                font-size: 13px;

                white-space: nowrap;

                overflow: hidden;

                text-overflow: ellipsis;
            }


            .account-info {

                display: none;
            }


            .logout {

                font-size: 11px;

                padding: 6px 9px;
            }


            .schedule {

                grid-template-columns: 1fr;
            }


            .toolbar {

                align-items: stretch;
            }


            /*
             * Tìm kiếm trên màn hình nhỏ
             * vẫn nằm cùng dòng
             */

            .search {

                display: flex;

                flex-direction: row;

                align-items: center;

                gap: 7px;

                width: 100%;

                flex-wrap: nowrap;

                margin-bottom: 15px;
            }


            .search .input {

                flex: 1 1 auto;

                min-width: 0;

                width: auto;

                height: 40px;
            }


            .search .btn {

                flex: 0 0 auto;

                min-width: 85px;

                width: auto;

                height: 40px;

                padding: 8px 10px;

                font-size: 13px;

                white-space: nowrap;
            }


            table {

                display: block;

                overflow-x: auto;

                white-space: nowrap;
            }
        }

    </style>

</head>


<body>


<!-- =====================================================
     HEADER
===================================================== -->

<header class="top">


    <!-- TÊN HỆ THỐNG -->

    <div class="brand">

        HỆ THỐNG QUẢN LÝ KHÓA HỌC VÀ ĐĂNG KÝ HỌC PHẦN

    </div>


    <?php if (Auth::check()): ?>

        <!-- =================================================
             TÀI KHOẢN
        ================================================== -->

        <div class="account">


            <!-- AVATAR -->

            <div
                class="account-avatar"
                title="<?= e($roleName) ?>"
            >
                <?= e($avatarText) ?>
            </div>


            <!-- TÊN + VAI TRÒ -->

            <div class="account-info">

                <div class="account-name">

                    <?= e($fullName) ?>

                </div>

                <div class="account-role">

                    <?= e($roleName) ?>

                </div>

            </div>


            <!-- ĐĂNG XUẤT -->

            <form
                class="logout-form"
                method="post"
                action="<?= e(BASE_URL) ?>?r=auth/logout"
            >

                <input
                    type="hidden"
                    name="_csrf"
                    value="<?= e(csrf_token()) ?>"
                >

                <button
                    class="logout"
                    type="submit"
                >
                    Đăng xuất
                </button>

            </form>


        </div>

    <?php endif; ?>


</header>


<!-- =====================================================
     MENU BÊN TRÁI
===================================================== -->

<?php if (Auth::check()): ?>

    <nav class="nav">


        <div class="nav-title">

            MENU QUẢN LÝ

        </div>


        <!-- =================================================
             ADMIN
        ================================================== -->

        <?php if (Auth::role() === 'admin'): ?>


            <a
                href="<?= e(BASE_URL) ?>?r=admin/students"
                class="<?= str_contains(
                    $currentRoute,
                    'admin/students'
                ) ? 'active' : '' ?>"
            >
                Quản lý sinh viên
            </a>


            <a
                href="<?= e(BASE_URL) ?>?r=admin/lecturers"
                class="<?= str_contains(
                    $currentRoute,
                    'admin/lecturers'
                ) ? 'active' : '' ?>"
            >
                Quản lý giảng viên
            </a>


            <a
                href="<?= e(BASE_URL) ?>?r=admin/courses"
                class="<?= str_contains(
                    $currentRoute,
                    'admin/courses'
                ) ? 'active' : '' ?>"
            >
                Quản lý học phần
            </a>


            <a
                href="<?= e(BASE_URL) ?>?r=admin/classes"
                class="<?= str_contains(
                    $currentRoute,
                    'admin/classes'
                ) ? 'active' : '' ?>"
            >
                Quản lý lớp học phần
            </a>


            <a
                href="<?= e(BASE_URL) ?>?r=admin/semesters"
                class="<?= str_contains(
                    $currentRoute,
                    'admin/semesters'
                ) ? 'active' : '' ?>"
            >
                Quản lý học kỳ
            </a>


        <!-- =================================================
             GIẢNG VIÊN
        ================================================== -->

        <?php elseif (Auth::role() === 'teacher'): ?>


            <a
                href="<?= e(BASE_URL) ?>?r=teacher/dashboard"
                class="<?= str_contains(
                    $currentRoute,
                    'teacher'
                ) ? 'active' : '' ?>"
            >
                Lịch dạy
            </a>


        <!-- =================================================
             SINH VIÊN
        ================================================== -->

        <?php elseif (Auth::role() === 'student'): ?>


            <a
                href="<?= e(BASE_URL) ?>?r=student/register"
                class="<?= str_contains(
                    $currentRoute,
                    'student/register'
                ) ? 'active' : '' ?>"
            >
                Đăng ký học phần
            </a>

<a
    href="<?= BASE_URL ?>?r=student/schedule"
    class="<?= $currentRoute === 'student/schedule' ? 'active' : '' ?>"
>
    Lịch học
</a>
            <a
                href="<?= e(BASE_URL) ?>?r=student/history"
                class="<?= str_contains(
                    $currentRoute,
                    'student/history'
                ) ? 'active' : '' ?>"
            >
                Lịch sử đăng ký
            </a>


        <?php endif; ?>


    </nav>

<?php endif; ?>


<!-- =====================================================
     NỘI DUNG CHÍNH
===================================================== -->

<main class="wrap">


    <!-- THÔNG BÁO THÀNH CÔNG -->

    <?php if ($m = flash('success')): ?>

        <div class="alert success">

            <?= e($m) ?>

        </div>

    <?php endif; ?>


    <!-- THÔNG BÁO LỖI -->

    <?php if ($m = flash('error')): ?>

        <div class="alert error">

            <?= e($m) ?>

        </div>

    <?php endif; ?>