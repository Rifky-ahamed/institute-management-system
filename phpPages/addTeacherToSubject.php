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
$theme = isset($_SESSION['theme']) ? $_SESSION['theme'] : 'default';

// Fetch user ID
$stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
$stmt->bind_param("s", $user_email);
$stmt->execute();
$stmt->bind_result($institute_id);
$stmt->fetch();
$stmt->close();

// Fetch subjects 
$subject_result = $conn->query("SELECT id, subject FROM subjects");

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $teacher_code = trim($_POST['teacher_code']);
    $subjects = $_POST['subjects'] ?? [];

    if (empty($subjects)) {
        echo "<script>alert('Please select at least one subject.');</script>";
        exit;
    }

    $stmt = $conn->prepare("SELECT teacher_code FROM teachers WHERE teacher_code = ? AND institute_id = ?");
    $stmt->bind_param("si", $teacher_code, $institute_id);
    $stmt->execute();
    $stmt->bind_result($fetched_code);
    $stmt->fetch();
    $stmt->close();

     if (!$fetched_code) {
    echo "<script>alert('Student does not exist in your institute.');</script>";
    exit;
    }

    $inserted = 0;
    $skipped = 0;

    foreach ($subjects as $subject_id) {
        // Step 2: Check if already assigned
        $check = $conn->prepare("SELECT id FROM assignSubjectsToTeacher WHERE teacher_code = ? AND sub_id = ?");
        $check->bind_param("si", $teacher_code, $subject_id);
        $check->execute();
        $check->store_result();

        if ($check->num_rows > 0) {
            $skipped++;
        } else {
            // Insert new assignment
            $insert = $conn->prepare("INSERT INTO assignSubjectsToTeacher (teacher_code, sub_id, institute_id) VALUES (?, ?, ?)");
            $insert->bind_param("sii", $teacher_code, $subject_id, $institute_id);
            $insert->execute();
            $inserted++;
            $insert->close();
        }

        $check->close();
    }

    echo "<script>alert('Subjects Assigned: $inserted, Skipped (Already Exist): $skipped');</script>";
}

?>

<!DOCTYPE html>
<html lang="en">
<head> 
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Add Teacher to Subject - Institute Class Management System</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
  <style>
<?php if (isset($_SESSION['theme']) && $_SESSION['theme'] === 'dark'): ?> 
  body {
    font-family: Arial, sans-serif;
    background: #121212; 
    color: #e0e0e0;
    padding: 40px;
    display: flex;
    justify-content: center;
  }

  .container {
    max-width: 600px;
    width: 100%;
    background: #1e1e1e;
    padding: 30px 40px;
    border-radius: 10px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.8);
  }

  h2 {
    text-align: center;
    color: #ffffff;
    margin-bottom: 25px;
  }

  label {
    font-weight: bold;
    display: block;
    margin-bottom: 8px;
    color: #cfcfcf;
  }

  input[type="text"] {
    width: 100%;
    padding: 10px;
    font-size: 16px;
    border: 1px solid #444;
    background-color: #2c2c2c;
    color: #e0e0e0;
    border-radius: 6px;
    margin-bottom: 20px;
  }

  .checkbox-group {
    margin-bottom: 20px;
  }

  .checkbox-group label {
    display: block;
    margin-bottom: 10px;
    color: #cccccc;
  }

  .checkbox-group input[type="checkbox"] {
    margin-right: 10px;
    accent-color: #3498db;
  }

  button {
    width: 100%;
    padding: 12px;
    background-color: #3498db;
    color: white;
    border: none;
    border-radius: 6px;
    font-size: 16px;
    cursor: pointer;
  }

  button:hover {
    background-color: #2980b9;
  }

<?php else: ?>

  body {
    font-family: Arial, sans-serif;
    background: #f0f2f5;
    display: flex;
    justify-content: center;
    padding-top: 40px;
  }

  .container {
    background: #fff;
    padding: 30px 40px;
    border-radius: 10px;
    box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
    width: 600px;
    max-width: 90%;
  }

  h2 {
    text-align: center;
    margin-bottom: 25px;
    color: #333;
  }

  label {
    font-weight: bold;
    display: block;
    margin-bottom: 8px;
    color: #555;
  }

  input[type="text"] {
    width: 100%;
    padding: 10px;
    font-size: 16px;
    border: 1px solid #ccc;
    border-radius: 6px;
    margin-bottom: 20px;
  }

  .checkbox-group {
    margin-bottom: 20px;
  }

  .checkbox-group label {
    display: block;
    margin-bottom: 10px;
    color: #333;
  }

  .checkbox-group input[type="checkbox"] {
    margin-right: 10px;
  }

  button {
    width: 100%;
    padding: 12px;
    background-color: #2980b9;
    color: white;
    border: none;
    border-radius: 6px;
    font-size: 16px;
    cursor: pointer;
  }

  button:hover {
    background-color: #2471a3;
  }

<?php endif; ?>
</style>

</head>
<body>
  <div class="container">
    <h2><i class="fas fa-user-plus"></i> Add Teacher to Subject</h2>
    <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="POST">
      <label for="teacher_code">Teacher Code:</label>
      <input type="text" id="teacher_code" name="teacher_code" required />

      <label>Select Subjects:</label>
      <div class="checkbox-group">
        <?php while ($subject = $subject_result->fetch_assoc()): ?>
          <label>
            <input type="checkbox" name="subjects[]" value="<?php echo $subject['id']; ?>" />
            <?php echo htmlspecialchars($subject['subject']); ?>
          </label>
        <?php endwhile; ?>
      </div>

      <button type="submit"><i class="fas fa-save"></i> Assign Subjects</button>
    </form>
  </div>
</body>
</html>