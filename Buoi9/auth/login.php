<?php

$title = 'Đăng nhập';

?>

<!DOCTYPE html>

<html lang="vi">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Đăng nhập</title>

    <style>

        * {
            box-sizing: border-box;
        }

        body {

            margin: 0;

            min-height: 100vh;

            display: flex;

            align-items: center;

            justify-content: center;

            font-family: Arial, sans-serif;

            background:
                linear-gradient(
                    135deg,
                    #e8f0f7,
                    #f5f8fb
                );

            color: #334155;
        }


        .login-container {

            width: 100%;

            max-width: 430px;

            padding: 20px;
        }


        .login-card {

            background: #ffffff;

            border:
                1px solid #dce5ee;

            border-radius: 18px;

            padding: 38px 36px;

            box-shadow:
                0 12px 35px
                rgba(49, 91, 135, .12);
        }


        .logo {

            width: 70px;

            height: 70px;

            margin: 0 auto 18px;

            border-radius: 18px;

            background: #dce9f4;

            display: flex;

            align-items: center;

            justify-content: center;

            color: #315b87;

            font-size: 25px;

            font-weight: 700;
        }


        h1 {

            margin: 0;

            text-align: center;

            color: #315b87;

            font-size: 25px;
        }


        .subtitle {

            text-align: center;

            color: #718096;

            font-size: 14px;

            margin:
                9px 0 28px;
        }


        .field {

            margin-bottom: 18px;
        }


        .field label {

            display: block;

            margin-bottom: 7px;

            color: #40566d;

            font-size: 14px;

            font-weight: 600;
        }


        .field input {

            width: 100%;

            height: 46px;

            padding: 0 14px;

            border:
                1px solid #ccd8e4;

            border-radius: 9px;

            outline: none;

            font-size: 14px;

            color: #334155;

            transition:
                border .2s ease,
                box-shadow .2s ease;
        }


        .field input:focus {

            border-color: #5d89b2;

            box-shadow:
                0 0 0 3px
                rgba(93, 137, 178, .13);
        }


        .login-btn {

            width: 100%;

            height: 46px;

            border: none;

            border-radius: 9px;

            background: #5d89b2;

            color: #ffffff;

            font-size: 15px;

            font-weight: 700;

            cursor: pointer;

            transition:
                background .2s ease,
                transform .15s ease,
                box-shadow .2s ease;
        }


        .login-btn:hover {

            background: #426f98;

            transform: translateY(-1px);

            box-shadow:
                0 5px 12px
                rgba(49, 91, 135, .2);
        }


        .error {

            background: #faeeee;

            border:
                1px solid #efd1d1;

            color: #a33e3e;

            padding: 11px 13px;

            border-radius: 8px;

            margin-bottom: 18px;

            font-size: 13px;
        }


        .field-error {

            color: #ad4141;

            font-size: 12px;

            margin-top: 5px;
        }


        .footer-text {

            text-align: center;

            margin-top: 22px;

            color: #8a98a7;

            font-size: 12px;
        }


        @media (max-width: 500px) {

            .login-card {

                padding: 30px 23px;

            }

        }

    </style>

</head>


<body>

<div class="login-container">

    <div class="login-card">

        <div class="logo">
            QL
        </div>

        <h1>
            Đăng nhập
        </h1>

        <div class="subtitle">

            Hệ thống quản lý khóa học
            và đăng ký học phần

        </div>


        <?php if (!empty($errors['general'])): ?>

            <div class="error">

                <?= e($errors['general']) ?>

            </div>

        <?php endif; ?>


        <form
            method="post"
            action="<?= e(BASE_URL) ?>?r=auth/login"
        >

            <input
                type="hidden"
                name="_csrf"
                value="<?= e(csrf_token()) ?>"
            >


            <div class="field">

                <label for="username">
                    Tên đăng nhập
                </label>

                <input
                    id="username"
                    type="text"
                    name="username"
                    value="<?= e($data['username'] ?? '') ?>"
                    placeholder="Nhập tên đăng nhập"
                    autocomplete="username"
                    autofocus
                >

                <?php if (!empty($errors['username'])): ?>

                    <div class="field-error">

                        <?= e($errors['username']) ?>

                    </div>

                <?php endif; ?>

            </div>


            <div class="field">

                <label for="password">
                    Mật khẩu
                </label>

                <input
                    id="password"
                    type="password"
                    name="password"
                    placeholder="Nhập mật khẩu"
                    autocomplete="current-password"
                >

                <?php if (!empty($errors['password'])): ?>

                    <div class="field-error">

                        <?= e($errors['password']) ?>

                    </div>

                <?php endif; ?>

            </div>


            <button
                type="submit"
                class="login-btn"
            >
                Đăng nhập
            </button>

        </form>


        <div class="footer-text">

            Hệ thống quản lý khóa học
            & đăng ký học phần

        </div>

    </div>

</div>

</body>

</html>