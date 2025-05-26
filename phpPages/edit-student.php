<?php

session_start();

if (!isset($_SESSION['logged_in'])) {
    header("Location: log.php");
    exit();
}

include('db_connect.php'); 

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $field = $_POST['field'] ?? '';
    $student_code = $_POST['student_code'] ?? '';
    $new_value = $_POST["value_$field"] ?? '';

    // Only allow specific fields
    $allowed_fields = ['name', 'phone', 'class', 'year', 'dob', 'email'];
    if (!in_array($field, $allowed_fields)) {
        echo "<script>alert('This field cannot be edited.');</script>";
    } else {
        // Check if student exists
        $stmt = $conn->prepare("SELECT * FROM student WHERE stupassword = ?");
        $stmt->bind_param("s", $student_code);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result && $result->num_rows > 0) {
            // Student exists
            if ($field === 'email') {
                // Get institute_name for this student
                $student_data = $result->fetch_assoc();
                $institute_name = $student_data['institute_name'];

                // Check if the new email already exists in same institute
                $check_email = $conn->prepare("SELECT * FROM student WHERE email = ? AND institute_name = ? AND stupassword != ?");
                $check_email->bind_param("sss", $new_value, $institute_name, $student_code);
                $check_email->execute();
                $email_result = $check_email->get_result();

                if ($email_result && $email_result->num_rows > 0) {
                    echo "<script>alert('This email already exists under your institute. Please use a different email.'); window.location.href='edit-student.php';</script>";
                    exit();
                }
            }

            // Safe to update
            $update_query = "UPDATE student SET $field = ? WHERE stupassword = ?";
            $update_stmt = $conn->prepare($update_query);
            $update_stmt->bind_param("ss", $new_value, $student_code);
            if ($update_stmt->execute()) {
                echo "<script>alert('Student $field updated successfully!'); window.location.href='edit-student.php';</script>";
                exit();
            } else {
                echo "<script>alert('Update failed. Try again later.');</script>";
            }
        } else {
            echo "<script>alert('Student code does not exist.');</script>";
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
  </style>
</head>
<body>

<div class="container">
  <h2>Edit Student Detail</h2>

  <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="POST" id="edit-form">
    <label for="field-select">Select a field to edit:</label>
    <select id="field-select" name="field-select">
      <option value="">-- Choose an option --</option>
      <option value="name">Name</option>
      <option value="phone">Phone Number</option>
      <option value="class">Class</option>
      <option value="year">Year</option>
      <option value="dob">Date of Birth</option>
      <option value="email">Email</option>
    </select>

    <input type="hidden" name="field" id="field-name">

    <div class="input-group" id="student-code-group">
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

    <div class="input-group" id="input-class">
      <label for="student_class">Class</label>
      <select id="student_class" name="value_class">
        <option value="">-- Select Class --</option>
        <option value="class 09">class 09</option>
        <option value="class 10">class 10</option>
        <option value="class 11">class 11</option>
        <option value="class 12">class 12</option>
        <option value="class 13">class 13</option>
      </select>
    </div>

    <div class="input-group" id="input-year">
      <label for="student_year">Year</label>
      <input type="number" id="student_year" name="value_year" min="2020" max="2099" step="1">
    </div>

    <div class="input-group" id="input-dob">
      <label>Select New Date of Birth:</label>
      <input type="date" name="value_dob">
    </div>

    <div class="input-group" id="input-email">
      <label>Enter New Email:</label>
      <input type="email" name="value_email">
    </div>

    <button type="submit" id="submit-btn" style="display:none;">Update Field</button>
  </form>
</div>

<script>
  const select = document.getElementById('field-select');
  const groups = document.querySelectorAll('.input-group');
  const fieldName = document.getElementById('field-name');
  const studentCodeGroup = document.getElementById('student-code-group');
  const submitBtn = document.getElementById('submit-btn');

  select.addEventListener('change', () => {
    groups.forEach(group => group.style.display = 'none');
    const value = select.value;

    if (value) {  // Show input for any selected field
      fieldName.value = value;
      studentCodeGroup.style.display = 'block';

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
</script>

</body>
</html>
