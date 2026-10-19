
<?php

session_start();

require_once "db.php";

$message = "";
$success = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $username = $_POST["username"];
    $email = $_POST["email"];
    $password = $_POST["password"];

    // التحقق من أن البريد غير مستخدم
    $check_sql = "SELECT user_id FROM users WHERE email = ?";

    $check_stmt = $conn->prepare($check_sql);

    $check_stmt->bind_param("s", $email);

    $check_stmt->execute();

    $check_result = $check_stmt->get_result();

    if ($check_result->num_rows > 0) {

        $message = "البريد الإلكتروني مستخدم بالفعل";

    } else {

        // إضافة المستخدم إلى قاعدة البيانات
        $sql = "INSERT INTO users (username, email, password)
                VALUES (?, ?, ?)";

        $stmt = $conn->prepare($sql);

        $stmt->bind_param("sss", $username, $email, $password);

        if ($stmt->execute()) {

            $success = "تم إنشاء الحساب بنجاح";

        } else {

            $message = "حدث خطأ أثناء إنشاء الحساب";

        }
    }
}

?>

<!DOCTYPE html>

<html lang="ar" dir="rtl">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>إنشاء حساب</title>

    <link rel="stylesheet" href="css/style.css">

</head>

<body>

    <div class="login-container">

        <h1>إنشاء حساب جديد</h1>

        <?php if ($message != ""): ?>

            <div class="error">
                <?php echo htmlspecialchars($message); ?>
            </div>

        <?php endif; ?>

        <?php if ($success != ""): ?>

            <div class="success">
                <?php echo htmlspecialchars($success); ?>
            </div>

            <p>
                <a href="index.php">الانتقال إلى تسجيل الدخول</a>
            </p>

        <?php endif; ?>

        <?php if ($success == ""): ?>

            <form method="POST">

                <label>اسم المستخدم</label>

                <input
                    type="text"
                    name="username"
                    placeholder="أدخل اسم المستخدم"
                    required
                >

                <label>البريد الإلكتروني</label>

                <input
                    type="email"
                    name="email"
                    placeholder="أدخل البريد الإلكتروني"
                    required
                >

                <label>كلمة المرور</label>

                <input
                    type="password"
                    name="password"
                    placeholder="أدخل كلمة المرور"
                    required
                >

                <button type="submit">
                    إنشاء الحساب
                </button>

            </form>

            <p>
                لديك حساب بالفعل؟
                <a href="index.php">تسجيل الدخول</a>
            </p>

        <?php endif; ?>

    </div>

    <script src="js/script.js"></script>

</body>

</html>

