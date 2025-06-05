<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
} // ✅ Required for session variables

include 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = htmlspecialchars(trim($_POST['email']));
    $password = $_POST['password'];

    $sql = "SELECT * FROM users WHERE email = ?";
    $stmt = $conn->prepare($sql);

    if ($stmt) {
        $stmt->bind_param("s", $email);
        if ($stmt->execute()) {
            $result = $stmt->get_result();
        } else {
            echo "Error executing statement: " . $stmt->error;
            exit();
        }
    } else {
        echo "Error preparing statement: " . $conn->error;
        exit();
    }

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        $hashedPassword = $user['password'];

        if (password_verify($password, $hashedPassword)) {
            // ✅ Store session variables
            $_SESSION['logged_in'] = true;
            $_SESSION['email'] = $user['email']; // Store institute name

            // ✅ Redirect to dashboard
            header("Location: dashboard.php");
            exit();
        } else {
            // ❌ Password wrong
            header("Location: log.php?error=invalid_password");
            exit();
        }
    } else {
        // ❌ User not found
        header("Location: log.php?error=user_not_found");
        exit();
    }

    $stmt->close();
    $conn->close();
} else {
    echo "Invalid request method.";
}
