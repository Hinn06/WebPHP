
<?php
session_start();

// Khởi tạo mảng học phần nếu chưa có
if (!isset($_SESSION['courses'])) {
    $_SESSION['courses'] = [];
}

$message = "";

// Xử lý khi người dùng gửi form
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Lấy dữ liệu từ form
    $maHocPhan = trim($_POST["maHocPhan"]);
    $tenHocPhan = trim($_POST["tenHocPhan"]);
    $soTinChi = (int) $_POST["soTinChi"];
    $siSo = (int) $_POST["siSo"];

    // Kiểm tra dữ liệu
    if ($maHocPhan == "" || $tenHocPhan == "") {
        $message = "Vui lòng nhập đầy đủ thông tin!";
    } elseif ($soTinChi <= 0) {
        $message = "Số tín chỉ phải lớn hơn 0!";
    } elseif ($siSo <= 0) {
        $message = "Sĩ số phải lớn hơn 0!";
    } else {

        // Kiểm tra mã học phần đã tồn tại chưa
        $trungMa = false;

        foreach ($_SESSION['courses'] as $course) {
            if ($course["ma"] == $maHocPhan) {
                $trungMa = true;
                break;
            }
        }

        if ($trungMa) {
            $message = "Mã học phần đã tồn tại!";
        } else {

            // Đưa dữ liệu vào mảng
            $_SESSION['courses'][] = [
                "ma" => $maHocPhan,
                "ten" => $tenHocPhan,
                "tinChi" => $soTinChi,
                "siSo" => $siSo
            ];

            $message = "Thêm học phần thành công!";
        }
    }
}

// Hàm tự định nghĩa để phân loại lớp
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

// Xóa toàn bộ danh sách
if (isset($_GET["reset"])) {
    $_SESSION['courses'] = [];
    header("Location: index.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

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
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
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

        .message {
            padding: 12px;
            margin-bottom: 20px;
            border-radius: 5px;
            background: #e7f3ff;
            color: #0056b3;
        }

        .table-box {
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
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

    <h1>QUẢN LÝ HỌC PHẦN</h1>

    <!-- FORM NHẬP THÔNG TIN -->
    <div class="form-box">

        <h2>Nhập thông tin học phần</h2>

        <?php if ($message != ""): ?>
            <div class="message">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="">

            <div class="form-row">

                <div class="form-group">
                    <label for="maHocPhan">Mã học phần</label>

                    <input
                        type="text"
                        id="maHocPhan"
                        name="maHocPhan"
                        placeholder="Ví dụ: PHP01"
                        required
                    >
                </div>

                <div class="form-group">
                    <label for="tenHocPhan">Tên học phần</label>

                    <input
                        type="text"
                        id="tenHocPhan"
                        name="tenHocPhan"
                        placeholder="Ví dụ: Lập trình Web"
                        required
                    >
                </div>

            </div>

            <div class="form-row">

                <div class="form-group">
                    <label for="soTinChi">Số tín chỉ</label>

                    <input
                        type="number"
                        id="soTinChi"
                        name="soTinChi"
                        min="1"
                        placeholder="Ví dụ: 3"
                        required
                    >
                </div>

                <div class="form-group">
                    <label for="siSo">Sĩ số tối đa</label>

                    <input
                        type="number"
                        id="siSo"
                        name="siSo"
                        min="1"
                        placeholder="Ví dụ: 40"
                        required
                    >
                </div>

            </div>

            <div class="button-group">

                <button type="submit" class="add-button">
                    + Thêm học phần
                </button>

                <a href="index.php?reset=1"
                   class="reset-button"
                   onclick="return confirm('Bạn có chắc muốn xóa toàn bộ danh sách?');">
                    Xóa tất cả
                </a>

            </div>

        </form>

    </div>


    <!-- DANH SÁCH HỌC PHẦN -->
    <div class="table-box">

        <h2>Danh sách học phần</h2>

        <?php if (count($_SESSION['courses']) > 0): ?>

            <table>

                <thead>

                    <tr>
                        <th>STT</th>
                        <th>Mã học phần</th>
                        <th>Tên học phần</th>
                        <th>Số tín chỉ</th>
                        <th>Sĩ số</th>
                        <th>Trạng thái</th>
                    </tr>

                </thead>

                <tbody>

                <?php

                // Vòng lặp duyệt mảng
                foreach ($_SESSION['courses'] as $index => $course):

                ?>

                    <tr>

                        <td>
                            <?php echo $index + 1; ?>
                        </td>

                        <td>
                            <?php echo htmlspecialchars($course["ma"]); ?>
                        </td>

                        <td>
                            <?php echo htmlspecialchars($course["ten"]); ?>
                        </td>

                        <td>
                            <?php echo $course["tinChi"]; ?>
                        </td>

                        <td>
                            <?php echo $course["siSo"]; ?>
                        </td>

                        <td>
                            <?php
                            echo htmlspecialchars(
                                xepLoaiHocPhan($course["siSo"])
                            );
                            ?>
                        </td>

                    </tr>

                <?php endforeach; ?>

                </tbody>

            </table>

        <?php else: ?>

            <div class="empty">
                Chưa có học phần nào. Vui lòng nhập thông tin ở phía trên.
            </div>

        <?php endif; ?>

    </div>

</div>

</body>

</html>
```
