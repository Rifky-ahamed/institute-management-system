<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
 
if (!isset($_SESSION['logged_in'])) {
    header("Location: log.php");
    exit();
}

include('db_connect.php'); 

// Get current logged-in user's email
$user_email = $_SESSION['email'];
$theme = $_SESSION['theme'] ;

// Fetch the user's id (institute_id) from users table using email
$stmt_user = $conn->prepare("SELECT id FROM users WHERE email = ?");
$stmt_user->bind_param("s", $user_email);
$stmt_user->execute();
$result_user = $stmt_user->get_result();
$row_user = $result_user->fetch_assoc();

if (!$row_user) {
    die("Error: User not found.");
}

$institute_id = $row_user['id'];

$success_message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit'])) {
    // Sanitize inputs
    $name = trim($_POST['student_name']);
    $email = trim($_POST['student_email']);
    $phone = trim($_POST['student_phone']);
    $class = trim($_POST['student_class']);
    $year = intval($_POST['student_year']);
    $dob = trim($_POST['student_dob']);
    
    function generateStudentCode() {
        return strtoupper(substr(md5(microtime(true) . rand()), 0, 8));
    }

    $plain_code = generateStudentCode();
    $student_code = md5($plain_code);

    // Check if email already exists for this institute
    $stmt_check = $conn->prepare("SELECT student_code FROM student WHERE email = ? AND institute_id = ?");
    $stmt_check->bind_param("si", $email, $institute_id);
    $stmt_check->execute();
    $stmt_check->store_result();

    if ($stmt_check->num_rows > 0) {
        echo "<script>alert('Email already in use for this institute!');</script>";
    } else {
    // Step 1: Check if the class with the given class and year already exists
    $stmt_check_class = $conn->prepare("SELECT id FROM class WHERE class = ? AND year = ? AND institute_id = ?");
    $stmt_check_class->bind_param("sii", $class, $year, $institute_id);
    $stmt_check_class->execute();
    $result_class = $stmt_check_class->get_result();

    if ($result_class->num_rows > 0) {
        // Class already exists, fetch the ID
        $row_class = $result_class->fetch_assoc();
        $class_id = $row_class['id'];
    } else {
        // Class doesn't exist, insert it
        $stmt_insert_class = $conn->prepare("INSERT INTO class (class, year, institute_id) VALUES (?, ?, ?)");
        $stmt_insert_class->bind_param("sii", $class, $year, $institute_id);
        $stmt_insert_class->execute();
        $class_id = $stmt_insert_class->insert_id;
    }

    // Step 2: Insert student with the obtained class_id
    $stmt_insert = $conn->prepare("INSERT INTO student (student_code, name, email, phone, dob, institute_id, class_id, stupassword) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt_insert->bind_param("sssssiis", $student_code, $name, $email, $phone, $dob, $institute_id, $class_id, $plain_code);

    if ($stmt_insert->execute()) {
        echo "<script>alert('Student added successfully!');</script>";
        $success_message = '
            <div style="margin-top:20px; padding:15px; border:1px solid #ccc; background:#e9f7ef;">
                <h3>Submitted Student Email & Password:</h3>
                <p><strong>Email:</strong> ' . htmlspecialchars($email) . '</p>
                <p><strong>Password:</strong> ' . htmlspecialchars($plain_code) . '</p>
            </div>';

        // Log activity
        $activity = "Student $name registered for $class";
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
<?php if (isset($_SESSION['theme']) && $_SESSION['theme'] === 'dark'): ?>
  body {
    font-family: Arial, sans-serif;
    background: #121212;
    margin: 0;
    padding: 0;
    color: #e0e0e0;
  }

  .container {
    width: 90%;
    max-width: 600px;
    margin: 50px auto;
    background: #1e1e1e;
    padding: 30px;
    border-radius: 10px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.4);
  }

  h2 {
    text-align: center;
    margin-bottom: 25px;
    color: #ffffff;
  }

  .form-group {
    margin-bottom: 20px;
  }

  label {
    display: block;
    margin-bottom: 8px;
    color: #cccccc;
  }

  input, select {
    width: 100%;
    padding: 10px;
    font-size: 16px;
    background: #2c2c2c;
    color: #ffffff;
    border: 1px solid #444;
    border-radius: 6px;
  }

  input:focus, select:focus {
    outline: none;
    border-color: #5c9ded;
    box-shadow: 0 0 5px #5c9ded;
  }

  button {
    width: 100%;
    padding: 12px;
    font-size: 16px;
    background: #1e88e5;
    color: #fff;
    border: none;
    border-radius: 6px;
    cursor: pointer;
  }

  button:hover {
    background: #42a5f5;
  }

  .back-link {
    display: block;
    text-align: center;
    margin-top: 15px;
    text-decoration: none;
    color: #90caf9;
  }

  .back-link:hover {
    text-decoration: underline;
    color: #bbdefb;
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

  /* Success message box */
  div[style*="background:#e9f7ef"] {
    background: #2e7d32 !important;
    color: #ffffff;
    border: 1px solid #66bb6a;
  }

<?php else: ?>
  body {
    font-family: Arial, sans-serif;
    background: #f2f2f2;
    margin: 0;
    padding: 0;
    color: #333;
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
<?php endif; ?>
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
          <input type="number" id="student_year" name="student_year" min="2024" max="2099" step="1" required>
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


