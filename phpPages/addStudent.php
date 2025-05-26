<?php
session_start();

if (!isset($_SESSION['logged_in'])) {
    header("Location: log.php");
    exit();
}

include('db_connect.php'); 

// Get current user's email from session
$current_user_email = $_SESSION['email'];

// Use prepared statement to safely fetch user's name from `users` table
$stmt_user = $conn->prepare("SELECT name FROM users WHERE email = ?");
$stmt_user->bind_param("s", $current_user_email);
$stmt_user->execute();
$result_user = $stmt_user->get_result();
$row = $result_user->fetch_assoc();

$institute = $row ? $row['name'] : "Unknown"; // fallback if not found

$success_message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit'])) {
    // Sanitize form data
    $name = mysqli_real_escape_string($conn, $_POST['student_name']);
    $email = mysqli_real_escape_string($conn, $_POST['student_email']);
    $phone = mysqli_real_escape_string($conn, $_POST['student_phone']);
    $class = mysqli_real_escape_string($conn, $_POST['student_class']);
    $year = mysqli_real_escape_string($conn, $_POST['student_year']);
    $dob = mysqli_real_escape_string($conn, $_POST['student_dob']);
    
   function generateStudentCode() {
    $unique = substr(md5(microtime(true) . rand()), 0, 8);
    return strtoupper($unique);
}

    $plain_code = generateStudentCode($name, $email); // Raw password
    $student_code = md5($plain_code); // Encrypted code

    // Check if the email already exists in the same institute
    $stmt_check = $conn->prepare("SELECT student_code FROM student WHERE email = ? AND institute_name = ?");
    $stmt_check->bind_param("ss", $email, $institute);
    $stmt_check->execute();
    $stmt_check->store_result();

    if ($stmt_check->num_rows > 0) {
        echo "<script>alert('Email already in use!');</script>";
    } else {
        // Insert new student using prepared statement
        $stmt_insert = $conn->prepare("INSERT INTO student (student_code, name, email, phone, class, year, dob, institute_name, stupassword)
                                       VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt_insert->bind_param("sssssssss", $student_code, $name, $email, $phone, $class, $year, $dob, $institute, $plain_code);

        if ($stmt_insert->execute()) {
            echo "<script>alert('Student added successfully!');</script>";
            $success_message = '
                <div style="margin-top:20px; padding:15px; border:1px solid #ccc; background:#e9f7ef;">
                    <h3>Submitted Student Email & Password:</h3>
                    <p><strong>Email:</strong> ' . htmlspecialchars($email) . '</p>
                    <p><strong>Password:</strong> ' . htmlspecialchars($plain_code) . '</p>
                </div>';

                 // ✅ Log activity
          $activity = "Student $name registered for  $class";
          $stmt_log = $conn->prepare("INSERT INTO activity_log (activity) VALUES (?)");
          $stmt_log->bind_param("s", $activity);
          $stmt_log->execute();
        } else {
            echo "<script>alert('Error: " . $stmt_insert->error . "');</script>";
        }
    }
}



?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Add New Student</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>
  <style>
    body {
      font-family: Arial, sans-serif;
      background: #f2f2f2;
      margin: 0;
      padding: 0;
    }

    .container {
      width: 90%;
      max-width: 600px;
      margin: 50px auto;
      background: #fff;
      padding: 30px;
      border-radius: 10px;
      box-shadow: 0 4px 10px rgba(0,0,0,0.1);
    }

    h2 {
      text-align: center;
      margin-bottom: 25px;
      color: #2c3e50;
    }

    .form-group {
      margin-bottom: 20px;
    }

    label {
      display: block;
      margin-bottom: 8px;
      color: #333;
    }

    input, select {
      width: 100%;
      padding: 10px;
      font-size: 16px;
      border: 1px solid #ccc;
      border-radius: 6px;
    }

    button {
      width: 100%;
      padding: 12px;
      font-size: 16px;
      background: #2980b9;
      color: #fff;
      border: none;
      border-radius: 6px;
      cursor: pointer;
    }

    button:hover {
      background: #3498db;
    }

    .back-link {
      display: block;
      text-align: center;
      margin-top: 15px;
      text-decoration: none;
      color: #2980b9;
    }

    .back-link:hover {
      text-decoration: underline;
    }

    .form-group-row {
      display: flex;
      gap: 20px;
      align-items: flex-end;
    }

    .form-group {
      flex: 1;
      display: flex;
      flex-direction: column;
    }
  </style>
</head>
<body>
  <div class="container">
    <h2>Add New Student</h2>
    <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="POST" id="add-student">
      <div class="form-group">
        <label for="student_name">Full Name</label>
        <input type="text" id="student_name" name="student_name" required />
      </div>

      <div class="form-group">
        <label for="student_email">Email</label>
        <input type="email" id="student_email" name="student_email" required />
      </div>

      <div class="form-group">
        <label for="student_phone">Phone Number</label>
        <input type="text" id="student_phone" name="student_phone" required />
      </div>

      <div class="form-group-row">
        <div class="form-group">
          <label for="student_class">Class</label>
          <select id="student_class" name="student_class" required>
            <option value="">-- Select Class --</option>
            <option value="class 09">class 09</option>
            <option value="class 10">class 10</option>
            <option value="class 11">class 11</option>
            <option value="class 12">class 12</option>
            <option value="class 13">class 13</option>
          </select>
        </div>

        <div class="form-group">
          <label for="student_year">Year</label>
          <input type="number" id="student_year" name="student_year" min="2020" max="2099" step="1" required>
        </div>
      </div>

      <div class="form-group">
        <label for="student_dob">Date of Birth</label>
        <input type="date" id="student_dob" name="student_dob" required />
      </div>

      <button type="submit" name="submit"><i class="fa fa-user-plus"></i> Add Student</button>
    </form>

    <?php if (!empty($success_message)) echo $success_message; ?>

    <a href="manageStudent.php" class="back-link">← Back to Manage Students</a>
  </div>
</body>
</html>


