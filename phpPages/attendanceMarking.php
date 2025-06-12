<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['logged_in'])) {
    header("Location: log.php");
    exit();
}

include('db_connect.php');

// Get current user's email
$user_email = $_SESSION['email'];

// Get class_id from URL
if (!isset($_GET['class_id']) || empty($_GET['class_id'])) {
    echo "No class selected.";
    exit;
}

$class_id = intval($_GET['class_id']);

// Get the institute_id of current user
$stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
$stmt->bind_param("s", $user_email);
$stmt->execute();
$stmt->bind_result($institute_id);
$stmt->fetch();
$stmt->close();

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
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Attendance Marking - <?php echo $class_name . " " . $class_year; ?></title>
    <style>
        body {
            font-family: Arial;
            background: #f9f9f9;
            padding: 40px;
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
        select, option {
            padding: 8px;
        }
        label {
            font-weight: bold;
            display: block;
            margin-bottom: 8px;
            color: #555;
        }
    </style>
</head>
<body>

<h2>Mark Attendance for Class: <?php echo "$class_name ($class_year)"; ?></h2>

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


    // Get students in the selected class and subject 
    $stmt = $conn->prepare("
        SELECT s.stupassword, s.name
        FROM student s
        INNER JOIN class c ON s.class_id = c.id
        INNER JOIN assignsubjects a ON s.stupassword = a.student_code
        WHERE s.class_id = ? AND a.sub_id = ? AND c.institute_id = ?
    ");
    $stmt->bind_param("iii", $class_id, $subject_id, $institute_id);
    $stmt->execute();
    $students = $stmt->get_result();

    if ($students->num_rows > 0):
?>

<!-- Attendance form -->
<form action="submit_attendance.php" method="POST">
    <input type="hidden" name="class_id" value="<?php echo $class_id; ?>">
    <input type="hidden" name="subject_id" value="<?php echo $subject_id; ?>">

    <table>
        <thead>
            <tr>
                <th>Student Name</th>
                <th>Attendance Status</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($student = $students->fetch_assoc()): ?>
                <tr>
                    <td><?php echo htmlspecialchars($student['name']); ?></td>
                    <td>
                        <select name="attendance[<?php echo $student['stupassword']; ?>]" required>
                            <option value="present">Present</option>
                            <option value="absent">Absent</option>
                        </select>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>

    <button type="submit" name="submit_attendance">Submit Attendance</button>
</form>

<?php
    else:
        echo "<p><strong>No students are assigned to this subject in this class.</strong></p>";
    endif;
endif;

?>

</body>
</html>