<?php
include 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  
        
        $instituteName = htmlspecialchars(trim($_POST['instituteName']));
        $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
        $hashedPassword = password_hash($_POST['confirmPassword'], PASSWORD_DEFAULT);

        $sql = "INSERT INTO users (name, email, password) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($sql);

        if ($stmt) {
            $stmt->bind_param("sss", $instituteName, $email, $hashedPassword);
            if ($stmt->execute()) {
                echo "User registered successfully!";
                // You can redirect here if needed
                // header("Location: success.html"); exit;
            } else {
                echo "Error executing statement: " . $stmt->error;
            }
            $stmt->close();
        } else {
            echo "Error preparing statement: " . $conn->error;
        }

        $conn->close();
    
} else {
    echo "Invalid request method.";
}
?>
