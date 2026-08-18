<?php

session_start();

/*
|--------------------------------------------------------------------------
| KHỞI TẠO DỮ LIỆU
|--------------------------------------------------------------------------
*/

// Khởi tạo mảng học phần nếu chưa có
if (!isset($_SESSION["courses"])) {
    $_SESSION["courses"] = [];
}

// Thông báo chung
$message = "";

// Danh sách lỗi theo từng trường
$errors = [];

// Lưu lại dữ liệu người dùng đã nhập
$old = [
    "maHocPhan" => "",
    "tenHocPhan" => "",
    "soTinChi" => "",
    "siSo" => ""
];


/*
|--------------------------------------------------------------------------
| XÓA TOÀN BỘ DANH SÁCH
|--------------------------------------------------------------------------
*/

if (isset($_GET["reset"])) {

    $_SESSION["courses"] = [];

    header("Location: index.php");
    exit;
}


/*
|--------------------------------------------------------------------------
| XỬ LÝ FORM
|--------------------------------------------------------------------------
*/

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    /*
    |--------------------------------------------------------------------------
    | 1. TIẾP NHẬN VÀ CHUẨN HÓA DỮ LIỆU
    |--------------------------------------------------------------------------
    */

    $old["maHocPhan"] = trim($_POST["maHocPhan"] ?? "");

    $old["tenHocPhan"] = trim($_POST["tenHocPhan"] ?? "");

    $old["soTinChi"] = trim($_POST["soTinChi"] ?? "");

    $old["siSo"] = trim($_POST["siSo"] ?? "");


    /*
    |--------------------------------------------------------------------------
    | 2. KIỂM TRA MÃ HỌC PHẦN
    |--------------------------------------------------------------------------
    */

    if ($old["maHocPhan"] === "") {

        $errors["maHocPhan"] =
            "Mã học phần không được để trống.";

    } elseif (
        mb_strlen($old["maHocPhan"]) < 2 ||
        mb_strlen($old["maHocPhan"]) > 20
    ) {

        $errors["maHocPhan"] =
            "Mã học phần phải có từ 2 đến 20 ký tự.";

    } elseif (
        !preg_match(
            '/^[A-Za-z0-9_-]+$/',
            $old["maHocPhan"]
        )
    ) {

        $errors["maHocPhan"] =
            "Mã học phần chỉ được chứa chữ cái, số, dấu - hoặc _.";

    }


    /*
    |--------------------------------------------------------------------------
    | 3. KIỂM TRA TÊN HỌC PHẦN
    |--------------------------------------------------------------------------
    */

    if ($old["tenHocPhan"] === "") {

        $errors["tenHocPhan"] =
            "Tên học phần không được để trống.";

    } elseif (
        mb_strlen($old["tenHocPhan"]) < 2 ||
        mb_strlen($old["tenHocPhan"]) > 100
    ) {

        $errors["tenHocPhan"] =
            "Tên học phần phải có từ 2 đến 100 ký tự.";

    }


    /*
    |--------------------------------------------------------------------------
    | 4. KIỂM TRA SỐ TÍN CHỈ
    |--------------------------------------------------------------------------
    */

    if ($old["soTinChi"] === "") {

        $errors["soTinChi"] =
            "Vui lòng nhập số tín chỉ.";

    } elseif (
        filter_var(
            $old["soTinChi"],
            FILTER_VALIDATE_INT
        ) === false
    ) {

        $errors["soTinChi"] =
            "Số tín chỉ phải là số nguyên.";

    } elseif (
        (int)$old["soTinChi"] < 1 ||
        (int)$old["soTinChi"] > 6
    ) {

        $errors["soTinChi"] =
            "Số tín chỉ phải từ 1 đến 6.";

    }


    /*
    |--------------------------------------------------------------------------
    | 5. KIỂM TRA SĨ SỐ
    |--------------------------------------------------------------------------
    */

    if ($old["siSo"] === "") {

        $errors["siSo"] =
            "Vui lòng nhập sĩ số tối đa.";

    } elseif (
        filter_var(
            $old["siSo"],
            FILTER_VALIDATE_INT
        ) === false
    ) {

        $errors["siSo"] =
            "Sĩ số phải là số nguyên.";

    } elseif (
        (int)$old["siSo"] < 1 ||
        (int)$old["siSo"] > 500
    ) {

        $errors["siSo"] =
            "Sĩ số phải từ 1 đến 500.";

    }


    /*
    |--------------------------------------------------------------------------
    | 6. KIỂM TRA MÃ HỌC PHẦN BỊ TRÙNG
    |--------------------------------------------------------------------------
    */

    if (!isset($errors["maHocPhan"])) {

        foreach ($_SESSION["courses"] as $course) {

            if (
                strcasecmp(
                    $course["ma"],
                    $old["maHocPhan"]
                ) === 0
            ) {

                $errors["maHocPhan"] =
                    "Mã học phần đã tồn tại.";

                break;
            }
        }
    }


    /*
    |--------------------------------------------------------------------------
    | 7. NẾU KHÔNG CÓ LỖI → THÊM HỌC PHẦN
    |--------------------------------------------------------------------------
    */

    if (empty($errors)) {

        /*
        | Chuẩn hóa dữ liệu trước khi xử lý
        */

        $maHocPhan = strtoupper(
            $old["maHocPhan"]
        );

        $tenHocPhan = $old["tenHocPhan"];

        $soTinChi = (int)$old["soTinChi"];

        $siSo = (int)$old["siSo"];


        /*
        | Đưa dữ liệu vào mảng
        */

        $_SESSION["courses"][] = [

            "ma" => $maHocPhan,

            "ten" => $tenHocPhan,

            "tinChi" => $soTinChi,

            "siSo" => $siSo

        ];


        /*
        | Thông báo thành công
        */

        $message =
            "Thêm học phần thành công!";


        /*
        | Xóa dữ liệu form sau khi thêm thành công
        */

        $old = [

            "maHocPhan" => "",

            "tenHocPhan" => "",

            "soTinChi" => "",

            "siSo" => ""

        ];

    } else {

        $message =
            "Vui lòng kiểm tra lại thông tin đã nhập.";

    }
}


/*
|--------------------------------------------------------------------------
| HÀM TỰ ĐỊNH NGHĨA
|--------------------------------------------------------------------------
|
| Phân loại học phần dựa vào sĩ số
|
*/

function xepLoaiHocPhan($siSo)
{

    if ($siSo >= 50) {

        return "Lớp đông";

    } elseif ($siSo >= 30) {

        return "Lớp vừa";

    } else {

        return "Lớp ít sinh viên";

    }
}


/*
|--------------------------------------------------------------------------
| HÀM CHỐNG XSS KHI HIỂN THỊ
|--------------------------------------------------------------------------
*/

function e($value)
{
    return htmlspecialchars(
        (string)$value,
        ENT_QUOTES,
        "UTF-8"
    );
}

?>

<!DOCTYPE html>

<html lang="vi">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Quản lý học phần</title>


    <style>

        * {
            box-sizing: border-box;
        }


        body {

            margin: 0;

            padding: 30px;

            font-family: Arial, sans-serif;

            background: #f4f6f8;

            color: #333;

        }


        .container {

            width: 900px;

            max-width: 100%;

            margin: auto;

        }


        h1 {

            text-align: center;

            color: #333;

            margin-bottom: 25px;

        }


        .form-box {

            background: white;

            padding: 25px;

            border-radius: 10px;

            box-shadow:
                0 2px 8px rgba(0, 0, 0, 0.1);

            margin-bottom: 25px;

        }


        .form-box h2 {

            margin-top: 0;

            color: #333;

        }


        .form-row {

            display: flex;

            gap: 15px;

            margin-bottom: 15px;

        }


        .form-group {

            flex: 1;

        }


        label {

            display: block;

            margin-bottom: 7px;

            font-weight: bold;

            color: #444;

        }


        input {

            width: 100%;

            padding: 10px;

            border: 1px solid #ccc;

            border-radius: 5px;

            font-size: 15px;

        }


        input:focus {

            outline: none;

            border-color: #007bff;

        }


        /*
        | Ô nhập bị lỗi
        */

        input.input-error {

            border-color: #dc3545;

            background: #fff5f5;

        }


        /*
        | Thông báo lỗi tại trường
        */

        .error {

            color: #dc3545;

            font-size: 14px;

            margin-top: 6px;

        }


        /*
        | Thông báo chung
        */

        .message {

            padding: 12px;

            margin-bottom: 20px;

            border-radius: 5px;

            background: #e7f3ff;

            color: #0056b3;

        }


        .message.success {

            background: #d4edda;

            color: #155724;

        }


        .message.error-message {

            background: #f8d7da;

            color: #721c24;

        }


        .button-group {

            margin-top: 20px;

        }


        button,
        .reset-button {

            border: none;

            padding: 11px 20px;

            border-radius: 5px;

            cursor: pointer;

            font-size: 15px;

        }


        .add-button {

            background: #007bff;

            color: white;

        }


        .add-button:hover {

            background: #0056b3;

        }


        .reset-button {

            background: #dc3545;

            color: white;

            text-decoration: none;

            margin-left: 10px;

        }


        .reset-button:hover {

            background: #b02a37;

        }


        .table-box {

            background: white;

            padding: 25px;

            border-radius: 10px;

            box-shadow:
                0 2px 8px rgba(0, 0, 0, 0.1);

            overflow-x: auto;

        }


        .table-box h2 {

            margin-top: 0;

        }


        table {

            width: 100%;

            border-collapse: collapse;

            margin-top: 15px;

        }


        th,
        td {

            border: 1px solid #ddd;

            padding: 12px;

            text-align: center;

        }


        th {

            background: #007bff;

            color: white;

        }


        tr:nth-child(even) {

            background: #f8f9fa;

        }


        .empty {

            text-align: center;

            color: #777;

            padding: 20px;

        }


        .badge {

            display: inline-block;

            padding: 5px 10px;

            border-radius: 15px;

            font-size: 13px;

        }


        .success-badge {

            background: #d4edda;

            color: #155724;

        }


        .warning-badge {

            background: #fff3cd;

            color: #856404;

        }


        .danger-badge {

            background: #f8d7da;

            color: #721c24;

        }


        @media (max-width: 700px) {

            .form-row {

                flex-direction: column;

            }


            body {

                padding: 15px;

            }

        }

    </style>

</head>


<body>


<div class="container">


    <h1>
        QUẢN LÝ HỌC PHẦN
    </h1>


    <!-- =========================
         FORM NHẬP THÔNG TIN
    ========================== -->

    <div class="form-box">


        <h2>
            Nhập thông tin học phần
        </h2>


        <?php if ($message !== ""): ?>

            <div
                class="message
                <?php
                echo empty($errors)
                    ? 'success'
                    : 'error-message';
                ?>"
            >

                <?php echo e($message); ?>

            </div>

        <?php endif; ?>


        <form
            method="POST"
            action=""
            novalidate
        >


            <!-- MÃ HỌC PHẦN -->

            <div class="form-row">


                <div class="form-group">


                    <label for="maHocPhan">

                        Mã học phần

                    </label>


                    <input
                        type="text"
                        id="maHocPhan"
                        name="maHocPhan"
                        placeholder="Ví dụ: PHP01"
                        maxlength="20"
                        value="<?php
                            echo e(
                                $old["maHocPhan"]
                            );
                        ?>"
                        class="<?php
                            echo isset(
                                $errors["maHocPhan"]
                            )
                                ? "input-error"
                                : "";
                        ?>"
                    >


                    <?php
                    if (
                        isset(
                            $errors["maHocPhan"]
                        )
                    ):
                    ?>

                        <div class="error">

                            <?php
                            echo e(
                                $errors["maHocPhan"]
                            );
                            ?>

                        </div>

                    <?php endif; ?>


                </div>


                <!-- TÊN HỌC PHẦN -->

                <div class="form-group">


                    <label for="tenHocPhan">

                        Tên học phần

                    </label>


                    <input
                        type="text"
                        id="tenHocPhan"
                        name="tenHocPhan"
                        placeholder="Ví dụ: Lập trình Web"
                        maxlength="100"
                        value="<?php
                            echo e(
                                $old["tenHocPhan"]
                            );
                        ?>"
                        class="<?php
                            echo isset(
                                $errors["tenHocPhan"]
                            )
                                ? "input-error"
                                : "";
                        ?>"
                    >


                    <?php
                    if (
                        isset(
                            $errors["tenHocPhan"]
                        )
                    ):
                    ?>

                        <div class="error">

                            <?php
                            echo e(
                                $errors["tenHocPhan"]
                            );
                            ?>

                        </div>

                    <?php endif; ?>


                </div>


            </div>


            <!-- SỐ TÍN CHỈ + SĨ SỐ -->

            <div class="form-row">


                <!-- SỐ TÍN CHỈ -->

                <div class="form-group">


                    <label for="soTinChi">

                        Số tín chỉ

                    </label>


                    <input
                        type="number"
                        id="soTinChi"
                        name="soTinChi"
                        min="1"
                        max="6"
                        step="1"
                        placeholder="Ví dụ: 3"
                        value="<?php
                            echo e(
                                $old["soTinChi"]
                            );
                        ?>"
                        class="<?php
                            echo isset(
                                $errors["soTinChi"]
                            )
                                ? "input-error"
                                : "";
                        ?>"
                    >


                    <?php
                    if (
                        isset(
                            $errors["soTinChi"]
                        )
                    ):
                    ?>

                        <div class="error">

                            <?php
                            echo e(
                                $errors["soTinChi"]
                            );
                            ?>

                        </div>

                    <?php endif; ?>


                </div>


                <!-- SĨ SỐ -->

                <div class="form-group">


                    <label for="siSo">

                        Sĩ số tối đa

                    </label>


                    <input
                        type="number"
                        id="siSo"
                        name="siSo"
                        min="1"
                        max="500"
                        step="1"
                        placeholder="Ví dụ: 40"
                        value="<?php
                            echo e(
                                $old["siSo"]
                            );
                        ?>"
                        class="<?php
                            echo isset(
                                $errors["siSo"]
                            )
                                ? "input-error"
                                : "";
                        ?>"
                    >


                    <?php
                    if (
                        isset(
                            $errors["siSo"]
                        )
                    ):
                    ?>

                        <div class="error">

                            <?php
                            echo e(
                                $errors["siSo"]
                            );
                            ?>

                        </div>

                    <?php endif; ?>


                </div>


            </div>


            <!-- NÚT -->

            <div class="button-group">


                <button
                    type="submit"
                    class="add-button"
                >

                    + Thêm học phần

                </button>


                <a
                    href="index.php?reset=1"
                    class="reset-button"
                    onclick="
                        return confirm(
                            'Bạn có chắc muốn xóa toàn bộ danh sách?'
                        );
                    "
                >

                    Xóa tất cả

                </a>


            </div>


        </form>


    </div>


    <!-- =========================
         DANH SÁCH HỌC PHẦN
    ========================== -->

    <div class="table-box">


        <h2>
            Danh sách học phần
        </h2>


        <?php
        if (count($_SESSION["courses"]) > 0):
        ?>


            <table>


                <thead>

                    <tr>

                        <th>
                            STT
                        </th>

                        <th>
                            Mã học phần
                        </th>

                        <th>
                            Tên học phần
                        </th>

                        <th>
                            Số tín chỉ
                        </th>

                        <th>
                            Sĩ số
                        </th>

                        <th>
                            Trạng thái
                        </th>

                    </tr>

                </thead>


                <tbody>


                <?php
                foreach (
                    $_SESSION["courses"]
                    as $index => $course
                ):
                ?>


                    <tr>


                        <td>

                            <?php
                            echo $index + 1;
                            ?>

                        </td>


                        <td>

                            <?php
                            echo e(
                                $course["ma"]
                            );
                            ?>

                        </td>


                        <td>

                            <?php
                            echo e(
                                $course["ten"]
                            );
                            ?>

                        </td>


                        <td>

                            <?php
                            echo e(
                                $course["tinChi"]
                            );
                            ?>

                        </td>


                        <td>

                            <?php
                            echo e(
                                $course["siSo"]
                            );
                            ?>

                        </td>


                        <td>


                            <?php

                            $xepLoai =
                                xepLoaiHocPhan(
                                    $course["siSo"]
                                );


                            if (
                                $xepLoai ===
                                "Lớp đông"
                            ) {

                                $class =
                                    "danger-badge";

                            } elseif (
                                $xepLoai ===
                                "Lớp vừa"
                            ) {

                                $class =
                                    "warning-badge";

                            } else {

                                $class =
                                    "success-badge";

                            }

                            ?>


                            <span
                                class="
                                badge
                                <?php
                                echo $class;
                                ?>"
                            >

                                <?php
                                echo e(
                                    $xepLoai
                                );
                                ?>

                            </span>


                        </td>


                    </tr>


                <?php endforeach; ?>


                </tbody>


            </table>


        <?php else: ?>


            <div class="empty">

                Chưa có học phần nào.
                Vui lòng nhập thông tin ở phía trên.

            </div>


        <?php endif; ?>


    </div>


</div>


</body>

</html>