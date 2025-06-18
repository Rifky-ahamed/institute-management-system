

<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['logged_in'])) {
    header("Location: log.php");
    exit();
}

include('db_connect.php');

$user_email = $_SESSION['email'];

// Get institute_id
$stmt_user = $conn->prepare("SELECT id FROM users WHERE email = ?");
$stmt_user->bind_param("s", $user_email);
$stmt_user->execute();
$result_user = $stmt_user->get_result();
$row_user = $result_user->fetch_assoc();

if (!$row_user) {
    die("Error: User not found.");
}

$institute_id = $row_user['id'];

// Handle form submission
$success_message = $error_message = '';
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $selected_theme = $_POST['theme'];
    $_SESSION['theme'] = $selected_theme;
    $theme = $selected_theme;

    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $new_password = $_POST['password'];

    // Only update password if password field is not empty
    if (!empty($new_password)) {
        if (!empty($name) && !empty($email)) {
            $stmt_check = $conn->prepare("SELECT id FROM users WHERE name = ? AND email = ?");
            $stmt_check->bind_param("ss", $name, $email);
            $stmt_check->execute();
            $result_check = $stmt_check->get_result();

            if ($result_check->num_rows > 0) {
                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                $stmt_update = $conn->prepare("UPDATE users SET password = ? WHERE email = ?");
                $stmt_update->bind_param("ss", $hashed_password, $email);
                if ($stmt_update->execute()) {
                    $success_message = "Password updated successfully.";
                } else {
                    $error_message = "Failed to update password.";
                }
            } else {
                $error_message = "No user found with the given name and email.";
            }
        } else {
            $error_message = "To change password, name and email are required.";
        }
    } else {
        $success_message = "Theme updated successfully.";
    }
} else {
    $theme = isset($_SESSION['theme']) ? $_SESSION['theme'] : 'light';
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Settings</title>
 <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    body {
      font-family: Arial, sans-serif;
      background-color: <?php echo $theme === 'dark' ? '#2c3e50' : '#f4f4f4'; ?>;
      color: <?php echo $theme === 'dark' ? '#ecf0f1' : '#333'; ?>;
      display: flex;
    }

    .sidebar {
      width: 220px;
      background-color: <?php echo $theme === 'dark' ? '#1a252f' : '#2c3e50'; ?>;
      min-height: 100vh;
      padding-top: 20px;
    }

    .sidebar h2 {
      color: #fff;
      text-align: center;
      margin-bottom: 30px;
    }

    .sidebar a {
      display: block;
      color: <?php echo $theme === 'dark' ? '#bdc3c7' : '#ecf0f1'; ?>;
      padding: 15px 20px;
      text-decoration: none;
      transition: 0.3s;
    }

    .sidebar a:hover {
      background-color: #34495e;
    }

    .main-content {
      flex: 1;
      padding: 30px;
      background-color: <?php echo $theme === 'dark' ? '#34495e' : '#fff'; ?>;
    }

    .main-content h1 {
      margin-bottom: 25px;
    }

    label {
      display: block;
      margin-top: 15px;
      font-weight: bold;
    }

    input[type="text"],
    input[type="email"],
    input[type="password"],
    select {
      width: 100%;
      padding: 10px;
      margin-top: 5px;
      border: 1px solid #ccc;
      border-radius: 4px;
      background-color: <?php echo $theme === 'dark' ? '#bdc3c7' : '#fff'; ?>;
      color: <?php echo $theme === 'dark' ? '#2c3e50' : '#000'; ?>;
    }

    button {
      margin-top: 25px;
      padding: 10px 20px;
      background-color: #2980b9;
      color: white;
      border: none;
      border-radius: 4px;
      cursor: pointer;
    }

    button:hover {
      background-color: #1f618d;
    }
  </style>
</head>
<body>

  <div class="sidebar">
    <h2>Settings</h2>
    <a href="#">⚙️ Profile Settings</a>
    <a href="student_details.php">🎓 Student Details</a>
    <a href="teachers_details.php">👩‍🏫 Teachers Details</a>
    <a href="generate_report.php">📄 Generate Report</a>
    <a href="logout.php">🚪 Logout</a>
  </div>

  <div class="main-content">
    <h1>Profile Settings</h1>

    <?php if ($success_message): ?>
      <p style="color: green;"><?php echo $success_message; ?></p>
    <?php elseif ($error_message): ?>
      <p style="color: red;"><?php echo $error_message; ?></p>
    <?php endif; ?>

    <form method="post" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>">
      <label for="name">Name:</label>
      <input type="text" id="name" name="name" placeholder="Enter your name" >

      <label for="email">Email:</label>
      <input type="email" id="email" name="email" placeholder="Enter your email" >

      <label for="password">New Password:</label>
      <input type="password" id="password" name="password" placeholder="Enter new password" >

      <label for="theme">Theme:</label>
      <select id="theme" name="theme">
        <option value="light" <?php if ($theme === 'light') echo 'selected'; ?>>Light</option>
        <option value="dark" <?php if ($theme === 'dark') echo 'selected'; ?>>Dark</option>
      </select>

      <button type="submit">Save Changes</button>
    </form>
  </div>

</body>
</html>
