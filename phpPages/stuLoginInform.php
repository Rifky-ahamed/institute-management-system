<?php
session_start();

if (!isset($_SESSION['logged_in'])) {
    header("Location: log.php");
    exit();
}

include 'db_connect.php';

// Get current logged-in institute's email
$institute_email = $_SESSION['email'];

// Step 1: Get institute name from `users` table
$query_institute = "SELECT name FROM users WHERE email = ?";
$stmt = $conn->prepare($query_institute);
$stmt->bind_param("s", $institute_email);
$stmt->execute();
$result_institute = $stmt->get_result();

if ($result_institute->num_rows > 0) {
    $row = $result_institute->fetch_assoc();
    $institute_name = $row['name'];

    // Step 2: Fetch students of that institute
    $query_students = "SELECT email, stupassword FROM student WHERE institute_name = ?";
    $stmt_students = $conn->prepare($query_students);
    $stmt_students->bind_param("s", $institute_name);
    $stmt_students->execute();
    $result_students = $stmt_students->get_result();
} else {
    echo "Institute not found.";
    exit();
}
?>

<!-- Step 3: Show in HTML Table with CSS -->
<!DOCTYPE html>
<html>
<head>
    <title>Students of <?php echo htmlspecialchars($institute_name); ?></title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f2f5f7;
            padding: 30px;
        }
        h2 {
            color: #333;
        }
        table {
            border-collapse: collapse;
            width: 80%;
            margin-top: 20px;
            background-color: #fff;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        th, td {
            border: 1px solid #ccc;
            padding: 12px 15px;
            text-align: left;
        }
        th {
            background-color: #007bff;
            color: white;
        }
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        tr:hover {
            background-color: #eef;
        }
    </style>
</head>
<body>

<h2>Students of <?php echo htmlspecialchars($institute_name); ?></h2>

<table>
    <thead>
        <tr>
            <th>NO</th>
            <th>Email</th>
            <th>Password</th>
        </tr>
    </thead>
    <tbody>
        <?php if ($result_students->num_rows > 0): 
            $counter = 1; ?>
            <?php while ($student = $result_students->fetch_assoc()): ?>
                <tr>
                    <td><?php echo $counter++; ?></td> 
                    <td><?php echo htmlspecialchars($student['email']); ?></td>
                    <td><?php echo htmlspecialchars($student['stupassword']); ?></td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr><td colspan="2">No students found for this institute.</td></tr>
        <?php endif; ?>
    </tbody>
</table>

</body>
</html>
