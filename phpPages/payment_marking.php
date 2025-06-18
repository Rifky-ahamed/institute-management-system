
<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['logged_in'])) {
    header("Location: log.php");
    exit();
}

include('db_connect.php');
$conn->set_charset("utf8mb4");

// Get current user's email
$user_email = $_SESSION['email'];
$theme = isset($_SESSION['theme']) ? $_SESSION['theme'] : 'default';

// Get institute_id from user
$stmt_user = $conn->prepare("SELECT id FROM users WHERE email = ?");
$stmt_user->bind_param("s", $user_email);
$stmt_user->execute();
$result_user = $stmt_user->get_result();
if ($user = $result_user->fetch_assoc()) {
    $institute_id = $user['id'];
} else {
    echo "User not found.";
    exit;
}
$stmt_user->close();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $class_id = isset($_POST['class_id']) ? intval($_POST['class_id']) : 0;
    $subject_id = isset($_POST['subject_id']) ? intval($_POST['subject_id']) : 0;
} else {
    $class_id = isset($_GET['class_id']) ? intval($_GET['class_id']) : 0;
    $subject_id = isset($_GET['subject_id']) ? intval($_GET['subject_id']) : 0;
}


// Get class name and year
$stmt = $conn->prepare("SELECT class, year FROM class WHERE id = ? AND institute_id = ?");
$stmt->bind_param("ii", $class_id, $institute_id);
$stmt->execute();
$result = $stmt->get_result();

if (!$row = $result->fetch_assoc()) {
    echo "Invalid class or unauthorized access.";
    exit;
}

$class_name = htmlspecialchars($row['class']);
$class_year = htmlspecialchars($row['year']);
$stmt->close();

if (isset($_POST['submit_attendance']) && isset($_POST['attendance'])) {
    $subject_id = intval($_POST['subject_id']);
    $class_id = intval($_POST['class_id']);

    // Get class name and year
    $class_query = $conn->prepare("SELECT class, year FROM class WHERE id = ? AND institute_id = ?");
    $class_query->bind_param("ii", $class_id, $institute_id);
    $class_query->execute();
    $class_result = $class_query->get_result();
    $class_data = $class_result->fetch_assoc();
    $class_name = $class_data['class'];
    $class_year = $class_data['year'];
    $class_query->close();

    // Get subject name
    $sub_stmt = $conn->prepare("SELECT subject FROM subjects WHERE id = ?");
    $sub_stmt->bind_param("i", $subject_id);
    $sub_stmt->execute();
    $sub_result = $sub_stmt->get_result();
    $subject_row = $sub_result->fetch_assoc();
    $subject_name = $subject_row['subject'] ?? '';
    $sub_stmt->close();

    $monthName = date('F');

    // Prepare statements for checking and updating/inserting
    $check_stmt = $conn->prepare("SELECT id FROM payment WHERE student_code = ? AND institute_id = ? AND class = ? AND year = ? AND month = ? AND subject = ?");
    $update_stmt = $conn->prepare("UPDATE payment SET status = ? WHERE id = ?");
    $insert_stmt = $conn->prepare("INSERT INTO payment (status, institute_id, student_code, class, year, month, subject) VALUES (?, ?, ?, ?, ?, ?, ?)");

    foreach ($_POST['attendance'] as $student_code => $status) {
        // Check if record exists
        $check_stmt->bind_param("sissss", $student_code, $institute_id, $class_name, $class_year, $monthName, $subject_name);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();

        if ($check_result->num_rows > 0) {
            // Record exists, update it
            $row = $check_result->fetch_assoc();
            $payment_id = $row['id'];
            $update_stmt->bind_param("si", $status, $payment_id);
            if (!$update_stmt->execute()) {
                echo "Error updating payment for student_code $student_code: " . $update_stmt->error;
            }
        } else {
            // Insert new record
            $insert_stmt->bind_param("sisssss", $status, $institute_id, $student_code, $class_name, $class_year, $monthName, $subject_name);
            if (!$insert_stmt->execute()) {
                echo "Error inserting payment for student_code $student_code: " . $insert_stmt->error;
            }
        }
    }

    $check_stmt->close();
    $update_stmt->close();
    $insert_stmt->close();

    echo "<script>alert('Payment records successfully submitted.'); window.location.href='payment_marking.php?class_id={$class_id}&subject_id={$subject_id}';</script>";
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Payment Marking <?php echo $class_name . " " . $class_year; ?></title>
  <style>
<?php if (isset($_SESSION['theme']) && $_SESSION['theme'] === 'dark'): ?>
    body {
        font-family: Arial, sans-serif;
        background: #121212;
        color: #e0e0e0;
        padding: 40px;
    }

    h2 {
        color: #ffffff;
    }

    form {
        background: #1e1e1e;
        padding: 25px;
        border-radius: 10px;
        box-shadow: 0 0 10px rgba(255, 255, 255, 0.05);
    }

    table {
        width: 100%;
        margin-top: 20px;
        border-collapse: collapse;
    }

    th, td {
        padding: 12px;
        border: 1px solid #444;
        text-align: left;
        background-color: #2a2a2a;
        color: #e0e0e0;
    }

    th {
        background-color: #0d6efd;
        color: #ffffff;
    }

    button {
        margin-top: 20px;
        padding: 12px 20px;
        background: #28a745;
        color: white;
        border: none;
        font-size: 16px;
        border-radius: 5px;
        cursor: pointer;
    }

    button:hover {
        background: #218838;
    }

    select, option {
        padding: 8px;
        background-color: #2c2c2c;
        color: #ffffff;
        border: 1px solid #555;
    }

    label {
        font-weight: bold;
        display: block;
        margin-bottom: 8px;
        color: #cccccc;
    }

<?php else: ?>

    body {
        font-family: Arial, sans-serif;
        background: #f9f9f9;
        padding: 40px;
        color: #333;
    }

    h2 {
        color: #333;
    }

    form {
        background: white;
        padding: 25px;
        border-radius: 10px;
        box-shadow: 0 0 10px rgba(0,0,0,0.1);
    }

    table {
        width: 100%;
        margin-top: 20px;
        border-collapse: collapse;
    }

    th, td {
        padding: 12px;
        border: 1px solid #ccc;
        text-align: left;
    }

    th {
        background-color: #e8f0fe;
    }

    button {
        margin-top: 20px;
        padding: 12px 20px;
        background: #28a745;
        color: white;
        border: none;
        font-size: 16px;
        border-radius: 5px;
        cursor: pointer;
    }

    button:hover {
        background: #218838;
    }

    select, option {
        padding: 8px;
        background-color: #ffffff;
        color: #333333;
        border: 1px solid #ccc;
    }

    label {
        font-weight: bold;
        display: block;
        margin-bottom: 8px;
        color: #555;
    }

<?php endif; ?>
</style>
</head>
<body>

<h2>Mark payment for Class: <?php echo "$class_name ($class_year)"; ?></h2>

<!-- Subject selection form -->
<form method="GET" action="">
    <input type="hidden" name="class_id" value="<?php echo $class_id; ?>">
    <label for="subject_select">Select Subject:</label>
    <select id="subject_select" name="subject_id" onchange="this.form.submit()" required>
        <option value="">-- Select Subject --</option>
        <?php
        $subject_result = $conn->query("SELECT id, subject FROM subjects ORDER BY subject");
        while ($subject = $subject_result->fetch_assoc()) {
            $selected = (isset($_GET['subject_id']) && $_GET['subject_id'] == $subject['id']) ? 'selected' : '';
            echo '<option value="' . $subject['id'] . '" ' . $selected . '>' . htmlspecialchars($subject['subject']) . '</option>';
        }
        ?>
    </select>
</form>

<?php
if (isset($_GET['subject_id']) && !empty($_GET['subject_id'])):
    $subject_id = intval($_GET['subject_id']);

    // Step 1: Get class and year
    $class_query = $conn->prepare("SELECT class, year FROM class WHERE id = ? AND institute_id = ?");
    $class_query->bind_param("ii", $class_id, $institute_id);
    $class_query->execute();
    $class_result = $class_query->get_result();
    $class_data = $class_result->fetch_assoc();
    $class_name = $class_data['class'];
    $class_year = $class_data['year'];
    $class_query->close();

    // Step 2: Get subject name
    $sub_stmt = $conn->prepare("SELECT subject FROM subjects WHERE id = ?");
    $sub_stmt->bind_param("i", $subject_id);
    $sub_stmt->execute();
    $sub_result = $sub_stmt->get_result();
    $subject_row = $sub_result->fetch_assoc();
    $subject_name = $subject_row['subject'] ?? '';
    $sub_stmt->close();

    // Step 3: Find students with 4+ attendance records for this month
    $current_month = date('Y-m');

    $query = "
        SELECT a.student_code, s.name, COUNT(*) as attend_count
        FROM attendance a
        JOIN student s ON s.stupassword = a.student_code
        WHERE a.class = ? AND a.year = ? AND a.subject = ? 
            AND DATE_FORMAT(a.attendance_date, '%Y-%m') = ?
            AND s.class_id = ?
            AND a.institute_id = ?
        GROUP BY a.student_code
        HAVING attend_count >= 4
    ";

    $stmt = $conn->prepare($query);
    $stmt->bind_param("ssssii", $class_name, $class_year, $subject_name, $current_month, $class_id, $institute_id);
    $stmt->execute();
    $students = $stmt->get_result();

    if ($students->num_rows > 0):
?>

<!-- Attendance form -->
<form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="POST">
    <input type="hidden" name="class_id" value="<?php echo $class_id; ?>">
    <input type="hidden" name="subject_id" value="<?php echo $subject_id; ?>">

    <table>
        <thead>
            <tr>
                <th>Student Name</th>
                <th>Payment Status</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($student = $students->fetch_assoc()): ?>
                <tr>
                    <td><?php echo htmlspecialchars($student['name']); ?></td>
                    <td>
                        <select name="attendance[<?php echo htmlspecialchars($student['student_code']); ?>]" required>
                            <option value="">-- Select Status --</option>
                            <option value="done">done</option>
                            <option value="not-done">not-done</option>
                        </select>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>

    <button type="submit" name="submit_attendance">Submit Payment</button>
</form>

<?php
    else:
        echo "<p><strong>No students are assigned to this subject in this class.</strong></p>";
    endif;
endif;
?>

</body>
</html>
