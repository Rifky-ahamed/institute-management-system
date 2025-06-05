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
// Step 1: Get institute_id using email from session
$institute_stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
$institute_stmt->bind_param("s", $user_email);
$institute_stmt->execute();
$institute_result = $institute_stmt->get_result();

$message = '';

if ($institute_result->num_rows > 0) {
    $institute = $institute_result->fetch_assoc();
    $institute_id = $institute['id'];


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $field = $_POST['field'];
    $code = $_POST['teacher_code'];

    if ($field === 'email') {
        $newEmail = $_POST['value_email'];

        $checkEmail = $conn->prepare("SELECT * FROM teachers WHERE email = ?");
        $checkEmail->bind_param("s", $newEmail);
        $checkEmail->execute();
        $result = $checkEmail->get_result();

        if ($result->num_rows > 0) {
            $message = "Email already exists! ";
        } else {
            $update = $conn->prepare("UPDATE teachers SET email = ? WHERE teacher_code = ?");
            $update->bind_param("si", $newEmail, $code);
            $update->execute();
            $message = " Email updated successfully ";
        }
    }

    elseif ($field === 'name') {
        $name = $_POST['value_name'];
        $update = $conn->prepare("UPDATE teachers SET name = ? WHERE teacher_code = ?");
        $update->bind_param("si", $name, $code);
        $update->execute();
        $message = "Name updated successfully ";
    }

    elseif ($field === 'phone') {
        $number = $_POST['value_phone'];
        $update = $conn->prepare("UPDATE teachers SET number = ? WHERE teacher_code = ?");
        $update->bind_param("si", $number, $code);
        $update->execute();
        $message = " Phone number updated successfully ";
    }

    elseif ($field === 'dob') {
        $dob = $_POST['value_dob'];
        $update = $conn->prepare("UPDATE teachers SET dob = ? WHERE teacher_code = ?");
        $update->bind_param("si", $dob, $code);
        $update->execute();
        $message = "Date of birth updated successfully";
    }

    elseif ($field === 'subject') {
        if (!empty($_POST["subject1"])) {
            $subjectName = trim($_POST["subject1"]);
            $stmt = $conn->prepare("SELECT id FROM subjects WHERE subject = ?");
            $stmt->bind_param("s", $subjectName);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($row = $result->fetch_assoc()) {
                $subject_id = $row['id'];
                $update = $conn->prepare("UPDATE teachers SET subject_id = ? WHERE teacher_code = ?");
                $update->bind_param("ii", $subject_id, $code);
                $update->execute();
                $message =  "Subject updated successfully";
            } else {
                $message = "Subject not found in database.";
            }
        } else {
            $message = "Please select a subject.";
        }
    }

    elseif ($field === 'class_year') {
        $newClass = $_POST['value_class'];
        $newYear = $_POST['value_year'];

        if (!empty($newClass) && !empty($newYear)) {
            // Step 1: Check if class-year pair exists
            $stmt = $conn->prepare("SELECT id FROM class WHERE class = ? AND year = ? AND institute_id = ?");
            $stmt->bind_param("ssi", $newClass, $newYear, $institute_id);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($row = $result->fetch_assoc()) {
                // Found existing class
                $class_id = $row['id'];
            } else {
                // Insert new class-year
                $insert = $conn->prepare("INSERT INTO class (class, year, institute_id) VALUES (?, ?, ?)");
                $insert->bind_param("ssi", $newClass, $newYear, $institute_id);
                $insert->execute();
                $class_id = $insert->insert_id;
            }

            // Step 2: Update teacher's class_id
            $update = $conn->prepare("UPDATE teachers SET class_id = ? WHERE teacher_code = ?");
            $update->bind_param("ii", $class_id, $code);
            $update->execute();
            $message =  "Class & Year updated successfully.";
        } else {
            $message = "Please select both Class and Year.";
        }
    }
}
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Edit teacher</title>
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
  <h2>Edit Teacher Detail</h2>

  <?php if (!empty($message)) echo "<div class='message'>" . htmlspecialchars($message) . "</div>"; ?>

  <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="POST" id="edit-form">
    <label for="field-select">Select a field to edit:</label>
    <select id="field-select" name="field-select">
      <option value="">-- Choose an option --</option>
      <option value="name">Name</option>
      <option value="phone">Phone Number</option>
      <option value="dob">Date of Birth</option>
      <option value="email">Email</option>
      <option value="subject">Subjects</option>
      <option value="class_year">Class & Year</option>
    </select>

    <input type="hidden" name="field" id="field-name">

    <div class="input-group" id="teacher-code-group">
      <label>Enter Teacher Code:</label>
      <input type="text" name="teacher_code" required>
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

    <div class="input-group" id="input-subject">
      <label for="subject1">Select Subject:</label>
      <select name="subject1">
        <option value="">-- Select Subject --</option>
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
  const fieldSelect = document.getElementById('field-select');
  const inputGroups = document.querySelectorAll('.input-group');
  const submitBtn = document.getElementById('submit-btn');
  const fieldName = document.getElementById('field-name');

  fieldSelect.addEventListener('change', () => {
    inputGroups.forEach(group => group.style.display = 'none');
    const selectedField = fieldSelect.value;
    fieldName.value = selectedField;

    if (selectedField) {
      document.getElementById('teacher-code-group').style.display = 'block';
      const group = document.getElementById('input-' + selectedField);
      if (group) group.style.display = 'block';
      submitBtn.style.display = 'block';
    } else {
      submitBtn.style.display = 'none';
    }
  });

  document.getElementById('edit-form').addEventListener('submit', function (e) {
    const selectedField = fieldSelect.value;

    if (selectedField === 'class_year') {
      const classValue = document.getElementById('value_class').value;
      const yearValue = document.getElementById('value_year').value;

      if (!classValue || !yearValue) {
        alert("You must select both Class and Year.");
        e.preventDefault();
      }
    }
  });
</script>

</body>
</html>
