<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['logged_in'])) {
    header("Location: log.php");
    exit();
}

include('db_connect.php'); 

$teacher_email = $_SESSION['email'];

// Fetch teacher details
$stmt = $conn->prepare("SELECT teacher_code, name, email, number, dob, institute_id, class_id FROM teachers WHERE email = ?");
$stmt->bind_param("s", $teacher_email);
$stmt->execute();
$stmt->bind_result($teacher_code, $name, $email, $number, $dob, $institute_id, $class_id);
$stmt->fetch();
$stmt->close();

// Fetch class info
$class_name = $class_year = '';
if ($class_id) {
    $stmt = $conn->prepare("SELECT class, year FROM class WHERE id = ?");
    $stmt->bind_param("i", $class_id);
    $stmt->execute();
    $stmt->bind_result($class_name, $class_year);
    $stmt->fetch();
    $stmt->close();
}

// Fetch institute name
$institute_name = '';
if ($institute_id) {
    $stmt = $conn->prepare("SELECT name FROM users WHERE id = ?");
    $stmt->bind_param("i", $institute_id);
    $stmt->execute();
    $stmt->bind_result($institute_name);
    $stmt->fetch();
    $stmt->close();
}

// Fetch assigned subjects (subject names)
$subjects = [];
$stmt = $conn->prepare("
    SELECT sub.subject
    FROM assignsubjectstoteacher ast
    JOIN subjects sub ON ast.sub_id = sub.id
    WHERE ast.teacher_code = ? AND ast.institute_id = ?
");
$stmt->bind_param("si", $teacher_code, $institute_id);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $subjects[] = $row['subject'];
}
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<title>Teacher Profile - Teacher Panel</title>
<style>
 body {
  font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
  background: #f4f7fa;
  margin: 0;
  padding: 0;
  color: #444;
}

header {
  background-color: #34495e;
  color: #ecf0f1;
  text-align: center;
  padding: 18px 0;
  font-size: 1.5rem;
  font-weight: 600;
  letter-spacing: 1px;
}

.container {
  max-width: 480px; /* narrower container */
  background: #fff;
  margin: 35px auto;
  padding: 25px 30px;
  border-radius: 8px;
  box-shadow: 0 3px 10px rgba(0,0,0,0.12);
}

h2 {
  color: #2c3e50;
  margin-bottom: 20px;
  font-weight: 700;
  font-size: 1.4rem;
  text-align: center;
}

form .form-group {
  margin-bottom: 16px;
}

form label {
  font-weight: 600;
  display: block;
  margin-bottom: 5px;
  color: #555;
  font-size: 14px;
}

form input, form select, form textarea {
  width: 100%;
  padding: 9px 12px;
  border-radius: 6px;
  border: 1.5px solid #ccc;
  font-size: 14px;
  color: #444;
  box-sizing: border-box;
  transition: border-color 0.3s ease;
}

form input:focus, form select:focus, form textarea:focus {
  outline: none;
  border-color: #3498db;
  box-shadow: 0 0 6px rgba(52,152,219,0.3);
}

form input[readonly], form textarea[readonly] {
  background: #f0f3f6;
  color: #666;
  cursor: not-allowed;
  border-color: #d1d7dd;
}

.subjects-list {
  background: #f0f3f6;
  border: 1.5px solid #d1d7dd;
  border-radius: 6px;
  padding: 10px 14px;
  color: #555;
  min-height: 40px;
  font-size: 14px;
  line-height: 1.4;
}

.back-btn {
  display: inline-block;
  margin-top: 25px;
  background-color: #3498db;
  color: white;
  padding: 12px 26px;
  text-decoration: none;
  border-radius: 6px;
  font-weight: 600;
  font-size: 14px;
  transition: background-color 0.3s ease;
  text-align: center;
  user-select: none;
}

.back-btn:hover {
  background-color: #2980b9;
  cursor: pointer;
}

</style>
</head>
<body>

<header>
  <h1>Teacher Panel - Profile</h1>
</header>

<div class="container">
  <h2>My Profile</h2>
  <form>
    <div class="form-group">
      <label>Teacher Code</label>
      <input type="text" value="<?php echo htmlspecialchars($teacher_code); ?>" readonly>
    </div>

    <div class="form-group">
      <label>Name</label>
      <input type="text" value="<?php echo htmlspecialchars($name); ?>" readonly>
    </div>

    <div class="form-group">
      <label>Email</label>
      <input type="email" value="<?php echo htmlspecialchars($email); ?>" readonly>
    </div>

    <div class="form-group">
      <label>Phone Number</label>
      <input type="text" value="<?php echo htmlspecialchars($number); ?>" readonly>
    </div>

    <div class="form-group">
      <label>Date of Birth</label>
      <input type="date" value="<?php echo htmlspecialchars($dob); ?>" readonly>
    </div>

    <div class="form-group">
      <label>Class</label>
      <input type="text" value="<?php echo htmlspecialchars($class_name . ' - ' . $class_year); ?>" readonly>
    </div>

    <div class="form-group">
      <label>Institute</label>
      <input type="text" value="<?php echo htmlspecialchars($institute_name); ?>" readonly>
    </div>

    <div class="form-group">
      <label>Assigned Subjects</label>
      <div class="subjects-list">
        <?php 
          if (count($subjects) > 0) {
            echo implode(", ", array_map('htmlspecialchars', $subjects));
          } else {
            echo "No subjects assigned";
          }
        ?>
      </div>
    </div>
  </form>

  <a href="teacher_dashboard.php" class="back-btn">← Back to Dashboard</a>
</div>

</body>
</html>
