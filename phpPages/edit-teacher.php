<?php


session_start();

if (!isset($_SESSION['logged_in'])) {
    header("Location: log.php");
    exit();
}

include('db_connect.php'); 
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $field = $_POST['field'];
    $code = $_POST['student_code']; // assuming this is a unique identifier
    $updateField = "";
    $updateValue = "";

    if ($field === 'email') {
        $newEmail = $_POST['value_email'];

        // Check if email already exists
        $checkEmail = $conn->prepare("SELECT * FROM teachers WHERE email = ?");
        $checkEmail->bind_param("s", $newEmail);
        $checkEmail->execute();
        $result = $checkEmail->get_result();

        if ($result->num_rows > 0) {
            echo "<script>alert('Email already exists!');</script>";
        } else {
            $update = $conn->prepare("UPDATE teachers SET email = ? WHERE teacher_code  = ?");
            $update->bind_param("si", $newEmail, $code);
            $update->execute();
            echo "<script>alert('Email updated successfully');</script>";
        }
    }

    elseif ($field === 'name') {
        $name = $_POST['value_name'];
        $update = $conn->prepare("UPDATE teachers SET name = ? WHERE teacher_code  = ?");
        $update->bind_param("si", $name, $code);
        $update->execute();
        echo "<script>alert('Name updated successfully');</script>";
    }

    elseif ($field === 'phone') {
        $number = $_POST['value_phone'];
        $update = $conn->prepare("UPDATE teachers SET number = ? WHERE teacher_code  = ?");
        $update->bind_param("si", $number, $code);
        $update->execute();
        echo "<script>alert('Phone number updated successfully');</script>";
    }

    elseif ($field === 'dob') {
        $dob = $_POST['value_dob'];
        $update = $conn->prepare("UPDATE teachers SET dob = ? WHERE teacher_code  = ?");
        $update->bind_param("si", $dob, $code);
        $update->execute();
        echo "<script>alert('Date of birth updated successfully');</script>";
    }

  elseif ($field === 'subject') {
    if (!empty($_POST["subject1"])) {
        $subjectName = trim($_POST["subject1"]);

        // Get subject ID from the subject table
        $stmt = $conn->prepare("SELECT id FROM subjects WHERE  	subject  = ?");
        $stmt->bind_param("s", $subjectName);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($row = $result->fetch_assoc()) {
            $subject_id = $row['id'];
            $update = $conn->prepare("UPDATE teachers SET subject_id = ? WHERE teacher_code = ?");
            $update->bind_param("ii", $subject_id, $code);
            $update->execute();
            echo "<script>alert('Subject updated successfully');</script>";
        } else {
            echo "<script>alert('Subject not found in database.');</script>";
        }
    } else {
        echo "<script>alert('Please select a subject.');</script>";
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
  </style>
</head>
<body>

<div class="container">
  <h2>Edit teacher Detail</h2>

  <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="POST" id="edit-form">
    <label for="field-select">Select a field to edit:</label>
    <select id="field-select" name="field-select">
      <option value="">-- Choose an option --</option>
      <option value="name">Name</option>
      <option value="phone">Phone Number</option>
      <option value="dob">Date of Birth</option>
      <option value="email">Email</option>
      <option value="subject">Subjects</option>
    </select>

    <input type="hidden" name="field" id="field-name">

    <div class="input-group" id="student-code-group">
      <label>Enter teacher Code:</label>
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
      document.getElementById('student-code-group').style.display = 'block';
      document.getElementById('input-' + selectedField).style.display = 'block';
      submitBtn.style.display = 'block';
    } else {
      submitBtn.style.display = 'none';
    }

    if (selectedField !== 'subject') {
      subjectSelectsContainer.innerHTML = '';
      subjectCountSelect.value = '';
    }
  });

  subjectCountSelect.addEventListener('change', () => {
    const count = parseInt(subjectCountSelect.value);
    subjectSelectsContainer.innerHTML = '';

    const options = `
      <option value="">-- Select Subject --</option>
      <option value="Math">Math</option>
      <option value="Science">Science</option>
      <option value="English">English</option>
      <option value="History">History</option>
      <option value="Geography">Geography</option>
      <option value="ICT">ICT</option>
      <option value="Biology">Biology</option>
      <option value="Chemistry">Chemistry</option>
      <option value="Physics">Physics</option>
    `;

    for (let i = 1; i <= count; i++) {
      const select = document.createElement('select');
      select.name = `subject${i}`;
      select.innerHTML = options;
      select.style.marginBottom = '10px';
      subjectSelectsContainer.appendChild(select);
    }
  });
</script>

</body>
</html>
