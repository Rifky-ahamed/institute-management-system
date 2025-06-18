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
        echo "Error preparing statement: " . $conn->error;
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

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Login</title>
  <link rel="stylesheet" href="../cssPages/style.css"/>
  <style>
    #myForm-login label[for="role"] {
      display: block;
      margin-top: 12px;
      margin-bottom: 6px;
      font-size: 16px;
      font-weight: 600;
      color: #2c3e50;
    }

    #myForm-login select {
      width: 100%;
      padding: 10px 14px;
      font-size: 15px;
      color: #2c3e50;
      background-color: #f0f4f8;
      border: 2px solid #d1d9e6;
      border-radius: 8px;
      appearance: none;
      cursor: pointer;
    }

    #myForm-login select:focus {
      border-color: #4a90e2;
      background-color: #ffffff;
      outline: none;
      box-shadow: 0 0 5px rgba(74, 144, 226, 0.5);
    }
  </style>
</head>
<body>
<?php if (isset($_GET['error'])): ?>
  <script>
    <?php
    switch ($_GET['error']) {
        case 'invalid_password':
            echo 'alert("Incorrect password.");';
            break;
        case 'user_not_found':
            echo 'alert("User not found.");';
            break;
        case 'unknown_role':
            echo 'alert("Unknown user role.");';
            break;
        default:
            echo 'alert("Unknown error occurred.");';
    }
    ?>
  </script>
<?php endif; ?>

<div id="container">
  <div id="left-second">
    <div id="sub-left-second">
      <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="post" id="myForm-login" class="myForm">
        <h2>Log in to your account</h2>

        <label for="email">Email</label>
        <input type="email" id="email" name="email" placeholder="example@gmail.com" required autocomplete="off">

        <label for="password">Password</label>
        <input type="password" id="password" name="password" placeholder="We@3ty" required autocomplete="off">

        <label for="role">Select Role</label>
        <select id="role" name="role" required>
          <option value="" disabled selected>Select your role</option>
          <option value="admin">Admin</option>
          <option value="teacher">Teacher</option>
          <option value="student">Student</option>
        </select>

        <button type="submit">Log In</button>
        <p>Don't have an account yet? <a href="sign.php">SignUp</a></p>
      </form>
    </div>
  </div>
  <div id="right-second">
    <div id="sub-right-second">
      <h1 id="text">Log in to<br><small>simplify how you manage</small><br><small>students and staff.</small></h1>
    </div>
  </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function () {
  const form = document.getElementById("myForm-login");

  form.addEventListener("submit", function (event) {
    const email = document.getElementById("email").value.trim();
    const password = document.getElementById("password").value.trim();
    const role = document.getElementById("role").value;

    if (!email || !password || !role) {
      event.preventDefault();
      alert("All fields are required.");
    }
  });
});
</script>

</body>
</html>
