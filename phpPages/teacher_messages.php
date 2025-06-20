<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['logged_in'])) {
    header("Location: log.php");
    exit();
}

include('db_connect.php'); 

// Get current logged-in teacher's email
$teacher_email = $_SESSION['email'];

// Step 1: Get the teacher's institute_id and teacher_code
$stmt = $conn->prepare("SELECT institute_id, teacher_code FROM teachers WHERE email = ?");
$stmt->bind_param("s", $teacher_email);
$stmt->execute();
$stmt->bind_result($institute_id, $teacher_code);
$stmt->fetch();
$stmt->close();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Collect and sanitize inputs
    $student_code = $_POST['student'] ?? '';
    $message = trim($_POST['message'] ?? '');

    if ($student_code === '' || $message === '') {
        $error = "Please select a student and enter a message.";
    } else {
        // Get student class and year for the selected student
        $stmt = $conn->prepare("SELECT c.class, c.year FROM student s JOIN class c ON s.class_id = c.id WHERE s.stupassword = ?");
        $stmt->bind_param("s", $student_code);
        $stmt->execute();
        $stmt->bind_result($student_class, $student_year);
        if (!$stmt->fetch()) {
            $error = "Selected student not found.";
        }
        $stmt->close();

        if (!isset($error)) {
            // Get teacher name (for teacher_name column)
            $stmt = $conn->prepare("SELECT name FROM teachers WHERE teacher_code = ?");
            $stmt->bind_param("s", $teacher_code);
            $stmt->execute();
            $stmt->bind_result($teacher_name);
            $stmt->fetch();
            $stmt->close();

            // Insert message into teac_messages table
            $stmt = $conn->prepare("
                INSERT INTO teac_messages 
                (stu_code, class, year, message, date, time, institute_id, teacher_code, teacher_name)
                VALUES (?, ?, ?, ?, CURRENT_DATE, CURRENT_TIME, ?, ?, ?)
            ");
            $stmt->bind_param(
                "ssissss",
                $student_code,
                $student_class,
                $student_year,
                $message,
                $institute_id,
                $teacher_code,
                $teacher_name
            );

            if ($stmt->execute()) {
                $success = "Message sent successfully!";
            } else {
                $error = "Failed to send message. Please try again.";
            }
            $stmt->close();
        }
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Messages - Teacher Panel</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #eef2f3;
            margin: 0;
            padding: 0;
        }

        header {
            background-color: #2c3e50;
            color: white;
            text-align: center;
            padding: 20px;
        }

        .container {
            max-width: 900px;
            margin: 30px auto;
            background-color: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }

        h2 {
            text-align: center;
            color: #2c3e50;
            margin-top: 30px;
        }

        form {
            margin-bottom: 30px;
        }

        label {
            display: block;
            margin-top: 15px;
            font-weight: bold;
        }

        input[type="text"], textarea, select {
            width: 100%;
            padding: 10px;
            margin-top: 5px;
            border-radius: 5px;
            border: 1px solid #ccc;
            font-size: 14px;
        }

        textarea {
            height: 100px;
            resize: vertical;
        }

        input[type="submit"] {
            background-color: #3498db;
            color: white;
            border: none;
            padding: 12px 20px;
            border-radius: 5px;
            cursor: pointer;
            margin-top: 20px;
        }

        input[type="submit"]:hover {
            background-color: #2980b9;
        }

        .message-box {
            border-radius: 8px;
            padding: 15px 20px;
            margin-bottom: 15px;
            box-shadow: 0 2px 6px rgba(0,0,0,0.05);
        }

        .sent {
            background-color: #e0f7fa;
            border-left: 5px solid #00acc1;
        }

        .received {
            background-color: #e8f5e9;
            border-left: 5px solid #43a047;
        }

        .message-box p {
            margin: 5px 0;
            font-size: 15px;
        }

        .meta {
            font-size: 13px;
            color: #555;
        }

        .back-btn {
            display: inline-block;
            margin-top: 30px;
            background-color: #3498db;
            color: white;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 5px;
        }

        .back-btn:hover {
            background-color: #2980b9;
        }
    </style>
</head>
<body>

<header>
    <h1>Teacher Panel - Messages</h1>
    
</header>
<?php if (!empty($error)): ?>
    <div style="background-color: #f8d7da; color: #721c24; padding: 15px; margin: 15px auto; max-width: 900px; border: 1px solid #f5c6cb; border-radius: 5px;">
        <?php echo htmlspecialchars($error); ?>
    </div>
<?php elseif (!empty($success)): ?>
    <div style="background-color: #d4edda; color: #155724; padding: 15px; margin: 15px auto; max-width: 900px; border: 1px solid #c3e6cb; border-radius: 5px;">
        <?php echo htmlspecialchars($success); ?>
    </div>
<?php endif; ?>
<div class="container">
    <h2>Send Message to Students</h2>

    <form action="#" method="post">
        <label for="student">Select Student:</label>
      <select name="student" id="student">
    <option value="">-- Choose a Student --</option>
    <?php
    // Step 1: Get teacher_code, class_id, and institute_id
    $teacher_email = $_SESSION['email'];
    $stmt = $conn->prepare("SELECT teacher_code, class_id, institute_id FROM teachers WHERE email = ?");
    $stmt->bind_param("s", $teacher_email);
    $stmt->execute();
    $stmt->bind_result($teacher_code, $teacher_class_id, $institute_id);
    $stmt->fetch();
    $stmt->close();

    // Step 2: Get class and year of teacher
    $stmt = $conn->prepare("SELECT class, year FROM class WHERE id = ?");
    $stmt->bind_param("i", $teacher_class_id);
    $stmt->execute();
    $stmt->bind_result($teacher_class, $teacher_year);
    $stmt->fetch();
    $stmt->close();

    // Step 3: Fetch students
    $stmt = $conn->prepare("
        SELECT 
            s.name AS student_name,
            s.stupassword AS student_code,
            c.class,
            c.year
        FROM student s
        JOIN assignsubjects sa ON s.stupassword = sa.student_code
        JOIN assignsubjectstoteacher ta ON sa.sub_id = ta.sub_id
        JOIN class c ON s.class_id = c.id
        WHERE s.institute_id = ? 
          AND c.class = ? 
          AND c.year = ?
          AND ta.teacher_code = ?
          AND ta.institute_id = ?
        GROUP BY s.stupassword
    ");
    $stmt->bind_param("isssi", $institute_id, $teacher_class, $teacher_year, $teacher_code, $institute_id);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        echo '<option value="' . htmlspecialchars($row['student_code']) . '">' .
             htmlspecialchars($row['student_name']) . ' (' . 
             htmlspecialchars($row['class']) . ' - ' . htmlspecialchars($row['year']) . ')' .
             '</option>';
    }

    $stmt->close();
    ?>
</select>


        <label for="message">Your Message:</label>
        <textarea name="message" id="message" placeholder="Type your message here..."></textarea>

        <input type="submit" value="Send Message">
    </form>

    <h2>Sent Messages</h2>


   <?php
$sent_stmt = $conn->prepare("
    SELECT 
        m.stu_code,
        m.class,
        m.year,
        m.message,
        m.date,
        m.time,
        s.name AS student_name
    FROM teac_messages m
    JOIN student s ON m.stu_code = s.stupassword
    WHERE m.teacher_code = ? AND m.institute_id = ?
    ORDER BY m.date DESC, m.time DESC
    LIMIT 2
");
$sent_stmt->bind_param("si", $teacher_code, $institute_id);
$sent_stmt->execute();
$sent_result = $sent_stmt->get_result();
?>
<h2>Sent Messages</h2>

<?php if ($sent_result->num_rows > 0): ?>
    <?php while ($row = $sent_result->fetch_assoc()): ?>
        <div class="message-box sent">
            <p><strong>To:</strong> <?php echo htmlspecialchars($row['student_name']); ?></p>
            <p><?php echo nl2br(htmlspecialchars($row['message'])); ?></p>
            <p class="meta">Sent on <?php echo htmlspecialchars($row['date']); ?> at <?php echo htmlspecialchars($row['time']); ?></p>
        </div>
    <?php endwhile; ?>
<?php else: ?>
    <p>No sent messages found.</p>
<?php endif; ?>

<?php $sent_stmt->close(); ?>


   <?php
$received_stmt = $conn->prepare("
    SELECT m.class, m.year, m.message, m.date, m.time, s.name AS student_name
    FROM messages m
    JOIN student s ON m.stu_code = s.stupassword
    WHERE m.teacher_code = ? AND m.institute_id = ?
    ORDER BY m.date DESC, m.time DESC
    LIMIT 2
");

$received_stmt->bind_param("si", $teacher_email_code, $institute_id);
$teacher_email_code = $teacher_code; // alias to avoid confusion
$received_stmt->execute();
$received_result = $received_stmt->get_result();
?>

<h2>Received Messages</h2>

<?php if ($received_result->num_rows > 0): ?>
    <?php while ($row = $received_result->fetch_assoc()): ?>
        <div class="message-box received">
            <p><strong>From:</strong> <?php echo htmlspecialchars($row['student_name']); ?></p>
            <p><strong>Class:</strong> <?php echo htmlspecialchars($row['class']) . " - " . htmlspecialchars($row['year']); ?></p>
            <p><?php echo htmlspecialchars($row['message']); ?></p>
            <p class="meta">Received on <?php echo htmlspecialchars($row['date']); ?> at <?php echo htmlspecialchars($row['time']); ?></p>
        </div>
    <?php endwhile; ?>
<?php else: ?>
    <p>No received messages found.</p>
<?php endif; ?>


    <a href="teacher_dashboard.php" class="back-btn">← Back to Dashboard</a>
</div>

</body>
</html>
