<?php
session_start();

if (!isset($_SESSION['logged_in'])) {
    header("Location: log.php");
    exit();
}

include('db_connect.php'); 

$user_email = $_SESSION['email'];

$stmt_user = $conn->prepare("SELECT id FROM users WHERE email = ?");
$stmt_user->bind_param("s", $user_email);
$stmt_user->execute();
$result_user = $stmt_user->get_result();
$row_user = $result_user->fetch_assoc();

if (!$row_user) {
    die("Error: User not found.");
}

$institute_id = $row_user['id'];

function generateTeacherCode($length = 8) {
    $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    $charactersLength = strlen($characters);
    $randomString = '';
    
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[random_int(0, $charactersLength - 1)];
    }

    return $randomString;
}



$teacher_code = generateTeacherCode();

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $dob = $_POST['dob'];
    $phone = $_POST['phone'];
    $subject_name = $_POST['subject'];
    $class_name = $_POST['class'];
    $year = $_POST['student_year'];

    // Get subject ID
    $stmt_subject = $conn->prepare("SELECT id FROM subjects WHERE subject = ?");
    $stmt_subject->bind_param("s", $subject_name);
    $stmt_subject->execute();
    $result_subject = $stmt_subject->get_result();
    $subject_row = $result_subject->fetch_assoc();

    if (!$subject_row) {
        die("Error: Subject not found.");
    }

    $subject_id = $subject_row['id'];

    // Check or insert class & year
    $stmt_class = $conn->prepare("SELECT id FROM class WHERE class = ? AND year = ?");
    $stmt_class->bind_param("ss", $class_name, $year);
    $stmt_class->execute();
    $result_class = $stmt_class->get_result();
    $class_row = $result_class->fetch_assoc();

    if ($class_row) {
        $class_id = $class_row['id'];
    } else {
        $stmt_insert_class = $conn->prepare("INSERT INTO class (class, year) VALUES (?, ?)");
        $stmt_insert_class->bind_param("ss", $class_name, $year);
        $stmt_insert_class->execute();
        $class_id = $stmt_insert_class->insert_id;
    }

  // Check for duplicate email in same institute
$stmt_check_email = $conn->prepare("SELECT teacher_code FROM teachers WHERE email = ? AND institute_id = ?");
$stmt_check_email->bind_param("si", $email, $institute_id);
$stmt_check_email->execute();
$result_email = $stmt_check_email->get_result();

if ($result_email->num_rows > 0) {
    die("Error: Email already exists for this institute.");
}


    

    $stmt_insert_teacher = $conn->prepare("INSERT INTO teachers (institute_id, teacher_code, name, email, dob, number, subject_id, class_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt_insert_teacher->bind_param("isssssii", $institute_id, $teacher_code, $name, $email, $dob, $phone, $subject_id, $class_id);

    if ($stmt_insert_teacher->execute()) {
        echo "<script>alert('Teacher added successfully!'); window.location.href='addteacher.php';</script>";
    } else {
        echo "Error: " . $stmt_insert_teacher->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Add New Teacher</title>
  <style>
  body {
    font-family: Arial, sans-serif;
    background-color: #f4f6f8;
    padding: 20px;
  }

  .form-container {
    max-width: 500px;
    margin: auto;
    background: white;
    padding: 30px;
    border-radius: 10px;
    box-shadow: 0 0 10px rgba(0,0,0,0.1);
  }

  h2 {
    text-align: center;
    margin-bottom: 20px;
  }

  label {
    display: block;
    margin-bottom: 5px;
    font-weight: bold;
  }

  input[type="text"],
  input[type="email"],
  input[type="date"],
  input[type="tel"],
  input[type="number"],
  select {
    width: 100%;
    padding: 10px;
    margin-bottom: 15px;
    border: 1px solid #ccc;
    border-radius: 5px;
    box-sizing: border-box;
  }

  button {
    width: 100%;
    background-color: #007BFF;
    color: white;
    padding: 12px;
    border: none;
    border-radius: 5px;
    font-size: 16px;
    cursor: pointer;
  }

  button:hover {
    background-color: #0056b3;
  }
</style>
</head>
<body>

  <div class="form-container">
    <h2>Add New Teacher</h2>
    <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="POST">
      <label for="name">Name:</label>
      <input type="text" id="name" name="name" required>

      <label for="email">Email:</label>
      <input type="email" id="email" name="email" required>

      <label for="dob">Date of Birth:</label>
      <input type="date" id="dob" name="dob" required>

      <label for="phone">Phone Number:</label>
      <input type="tel" id="phone" name="phone" required>

      <label for="subject">Subject:</label>
      <select id="subject" name="subject" required>
        <option value="">Select Subject</option>
        <option value="Mathematics">Mathematics</option>
        <option value="Science">Science</option>
        <option value="English">English</option>
        <option value="Sinhala">Sinhala</option>
        <option value="Tamil">Tamil</option>
        <option value="Islam">Islam</option>
        <option value="GEO">GEO</option>
        <option value="Civices">Civices</option>
        <option value="History">History</option>
      </select>

      <label for="class">Class:</label>
      <select id="class" name="class" required>
        <option value="">-- Select Class --</option>
            <option value="class 09">class 09</option>
            <option value="class 10">class 10</option>
            <option value="class 11">class 11</option>
            <option value="class 12">class 12</option>
            <option value="class 13">class 13</option>
      </select>

      <label for="year">Year:</label>
       <input type="number" id="student_year" name="student_year" min="2024" max="2099" step="1" required>
      <button type="submit">Add Teacher</button>
    </form>
  </div>

</body>
</html>
