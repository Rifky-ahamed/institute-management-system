<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = htmlspecialchars(trim($_POST['email']));
    $password = $_POST['password'];
    $role = $_POST['role'];

    if ($role === 'admin') {
        $sql = "SELECT * FROM users WHERE email = ?";
    } elseif ($role === 'student') {
        $sql = "SELECT * FROM student WHERE email = ?";
    } elseif ($role === 'teacher') {
        $sql = "SELECT * FROM teachers WHERE email = ?";
    } else {
        header("Location: log.php?error=unknown_role");
        exit();
    }

    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        header("Location: log.php?error=stmt_error");
        exit();
    }

    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows === 1) {
        $user = $result->fetch_assoc();

        $valid = false;
        if ($role === 'admin' && password_verify($password, $user['password'])) {
            $valid = true;
            $redirect = 'dashboard.php';
        } elseif ($role === 'student' && $password === $user['stupassword']) {
            $valid = true;
            $redirect = 'studentdashboard.php';
        } elseif ($role === 'teacher' && $password === $user['teacher_code']) {
            $valid = true;
            $redirect = 'teacherdashboard.php';
        }

        if ($valid) {
            $_SESSION['logged_in'] = true;
            $_SESSION['email'] = $user['email'];
            header("Location: $redirect");
            exit();
        } else {
            header("Location: log.php?error=invalid_password");
            exit();
        }

    } else {
        header("Location: log.php?error=user_not_found");
        exit();
    }

    $stmt->close();
    $conn->close();
}
?>
