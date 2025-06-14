<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['logged_in'])) {
    header("Location: log.php");
    exit();
}

include 'db_connect.php';


// Get current logged-in institute's email
$institute_email = $_SESSION['email'];
$theme = isset($_SESSION['theme']) ? $_SESSION['theme'] : 'default';


// Step 1: Get user id and name from users table
$query_user = "SELECT id, name FROM users WHERE email = ?";
$stmt = $conn->prepare($query_user);
$stmt->bind_param("s", $institute_email);
$stmt->execute();
$result_user = $stmt->get_result();

if ($result_user->num_rows > 0) {
    $user = $result_user->fetch_assoc();
    $institute_id = $user['id'];
    $institute_name = $user['name'];

    // Step 2: Fetch students using institute_id
    $query_students = "SELECT email, teacher_code FROM teachers WHERE institute_id = ?";
    $stmt_students = $conn->prepare($query_students);
    $stmt_students->bind_param("i", $institute_id);
    $stmt_students->execute();
    $result_students = $stmt_students->get_result();
}  else {
    echo "Institute not found.";
    exit();
}
?>

<!-- Step 3: Show in HTML Table with CSS -->
<!DOCTYPE html>
<html>
<head>
    <title>teacher of <?php echo htmlspecialchars($institute_name); ?></title>
     <style>
        <?php if (isset($theme) && $theme === 'dark'): ?>
            body {
                font-family: Arial, sans-serif;
                background-color: #121212; /* very dark background */
                padding: 30px;
                color: #e0e0e0; /* light text */
            }
            h2 {
                color: #f0f0f0; /* lighter heading */
            }
            table {
                border-collapse: collapse;
                width: 80%;
                margin-top: 20px;
                background-color: #1e1e1e; /* dark table background */
                box-shadow: 0 2px 8px rgba(0,0,0,0.8);
            }
            th, td {
                border: 1px solid #333;
                padding: 12px 15px;
                text-align: left;
                color: #e0e0e0; /* light text */
            }
            th {
                background-color: #0056b3; /* darkish blue */
                color: #fff;
            }
            tr:nth-child(even) {
                background-color: #2a2a2a; /* slightly lighter dark */
            }
            tr:hover {
                background-color: #003a75; /* dark blue hover */
            }
        <?php else: ?>
            body {
                font-family: Arial, sans-serif;
                background-color: #f2f5f7;
                padding: 30px;
                color: #000;
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
                color: #000;
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
        <?php endif; ?>
    </style>
</head>
<body>

<h2>teacher of <?php echo htmlspecialchars($institute_name); ?></h2>

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
                    <td><?php echo htmlspecialchars($student['teacher_code']); ?></td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr><td colspan="2">No teachers found for this institute.</td></tr>
        <?php endif; ?>
    </tbody>
</table>

</body>
</html>
