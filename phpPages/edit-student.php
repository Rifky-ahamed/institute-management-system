<?php
session_start();
if (!isset($_SESSION['logged_in'])) {
    header("Location: log.php");
    exit();
}
include('db_connect.php');
// Get current logged-in user's email
$user_email = $_SESSION['email'];

$message = '';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $field = $_POST['field'] ?? '';
    $student_code = $_POST['student_code'] ?? '';

    $stmt = $conn->prepare("SELECT * FROM student WHERE stupassword = ?");
    $stmt->bind_param("s", $student_code);
    $stmt->execute();
    $result = $stmt->get_result();

    if (!$result || $result->num_rows === 0) {
        $message = "Student code does not exist.";
    } else {
        $student = $result->fetch_assoc();

        if ($field === 'class_year') {
            $class = $_POST['value_class'] ?? '';
            $year = $_POST['value_year'] ?? '';

            if (!$class || !$year) {
                $message = "Both class and year must be provided.";
            } else {
                $check_stmt = $conn->prepare("SELECT id FROM class WHERE class = ? AND year = ?");
                $check_stmt->bind_param("ss", $class, $year);
                $check_stmt->execute();
                $check_result = $check_stmt->get_result();

                if ($check_result->num_rows > 0) {
                    $class_row = $check_result->fetch_assoc();
                    $class_id = $class_row['id'];
                } else {
                    $insert_stmt = $conn->prepare("INSERT INTO class (class, year) VALUES (?, ?)");
                    $insert_stmt->bind_param("ss", $class, $year);
                    if ($insert_stmt->execute()) {
                        $class_id = $insert_stmt->insert_id;
                    } else {
                        $message = "Failed to insert new class.";
                    }
                }

                if (!$message) {
                    $update_stmt = $conn->prepare("UPDATE student SET class_id = ? WHERE stupassword = ?");
                    $update_stmt->bind_param("is", $class_id, $student_code);
                    if ($update_stmt->execute()) {
                        $message = "Class and Year updated successfully!";
                    } else {
                        $message = "Failed to update student class.";
                    }
                }
            }
        } else {
            $allowed_fields = ['name', 'phone', 'dob', 'email'];
            $new_value = $_POST["value_$field"] ?? '';

            if (!in_array($field, $allowed_fields)) {
                $message = "This field cannot be edited.";
            } else {
                if ($field === 'email') {
                    $new_value = strtolower(trim($new_value));
                    $institute_name = $student['institute_name'];

                    $check_email = $conn->prepare("SELECT * FROM student WHERE email = ? AND institute_name = ? AND stupassword != ?");
                    $check_email->bind_param("sss", $new_value, $institute_name, $student_code);
                    $check_email->execute();
                    $email_result = $check_email->get_result();

                    if ($email_result && $email_result->num_rows > 0) {
                        $message = "This email already exists under your institute.";
                    }
                }

                if (!$message) {
                    $update_query = "UPDATE student SET $field = ? WHERE stupassword = ?";
                    $update_stmt = $conn->prepare($update_query);
                    $update_stmt->bind_param("ss", $new_value, $student_code);
                    if ($update_stmt->execute()) {
                        $message = "Student $field updated successfully!";
                    } else {
                        $message = "Update failed. Try again later.";
                    }
                }
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Edit Student</title>
  <style>
    body {
      font-family: Arial, sans-serif;
      background: #f5f6f8;
      padding: 40px;
    }
    .container {
      max-width: 500px;
      margin: auto;
      background: white;
      padding: 25px;
      border-radius: 10px;
      box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    }
    h2 {
      text-align: center;
      color: #333;
      margin-bottom: 20px;
    }
    label {
      font-weight: bold;
      display: block;
      margin: 10px 0 5px;
    }
    select, input {
      width: 100%;
      padding: 10px;
      margin-bottom: 15px;
      border-radius: 6px;
      border: 1px solid #ccc;
    }
    .input-group {
      display: none;
    }
    button {
      width: 100%;
      background: #007bff;
      color: white;
      padding: 10px;
      border: none;
      border-radius: 6px;
      cursor: pointer;
      font-size: 16px;
    }
    button:hover {
      background: #0056b3;
    }
    .message {
      text-align: center;
      margin-bottom: 20px;
      font-weight: bold;
      color: green;
    }
  </style>
</head>
<body>

<div class="container">
  <h2>Edit Student Detail</h2>

  <?php if (!empty($message)) echo "<div class='message'>" . htmlspecialchars($message) . "</div>"; ?>

  <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="POST" id="edit-form">
    <label for="field-select">Select a field to edit:</label>
    <select id="field-select" name="field-select" required>
      <option value="">-- Choose an option --</option>
      <option value="name">Name</option>
      <option value="phone">Phone Number</option>
      <option value="dob">Date of Birth</option>
      <option value="email">Email</option>
      <option value="class_year">Class & Year</option>
    </select>

    <input type="hidden" name="field" id="field-name">

    <div class="input-group" id="student-code-group" style="display:block;">
      <label>Enter Student Code:</label>
      <input type="text" name="student_code" required>
    </div>

    <div class="input-group" id="input-name">
      <label>Enter New Name:</label>
      <input type="text" name="value_name">
    </div>

    <div class="input-group" id="input-phone">
      <label>Enter New Phone Number:</label>
      <input type="text" name="value_phone">
    </div>

    <div class="input-group" id="input-dob">
      <label>Select New Date of Birth:</label>
      <input type="date" name="value_dob">
    </div>

    <div class="input-group" id="input-email">
      <label>Enter New Email:</label>
      <input type="email" name="value_email">
    </div>

    <div class="input-group" id="input-class_year">
      <label>Select New Class:</label>
      <select name="value_class" id="value_class">
        <option value="">-- Select Class --</option>
        <option value="class 09">class 09</option>
        <option value="class 10">class 10</option>
        <option value="class 11">class 11</option>
        <option value="class 12">class 12</option>
        <option value="class 13">class 13</option>
      </select>

      <label>Enter New Year:</label>
      <input type="number" name="value_year" id="value_year" min="2024" max="2099" step="1">
    </div>

    <button type="submit" id="submit-btn" style="display:none;">Update Field</button>
  </form>
</div>

<script>
  const select = document.getElementById('field-select');
  const groups = document.querySelectorAll('.input-group:not(#student-code-group)');
  const fieldName = document.getElementById('field-name');
  const submitBtn = document.getElementById('submit-btn');

  select.addEventListener('change', () => {
    groups.forEach(group => group.style.display = 'none');
    const value = select.value;

    if (value) {
      fieldName.value = value;
      const selectedInputGroup = document.getElementById('input-' + value);
      if (selectedInputGroup) {
        selectedInputGroup.style.display = 'block';
      }
      submitBtn.style.display = 'block';
    } else {
      fieldName.value = '';
      submitBtn.style.display = 'none';
    }
  });

  document.getElementById('edit-form').addEventListener('submit', function (e) {
    const selectedField = select.value;
    const classValue = document.getElementById('value_class')?.value;
    const yearValue = document.getElementById('value_year')?.value;

    if (selectedField === 'class_year') {
      if (!classValue || !yearValue) {
        alert("You must select both class and year.");
        e.preventDefault();
      }
    }
  });
</script>

</body>
</html>
