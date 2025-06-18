<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['logged_in'])) {
    header("Location: log.php");
    exit();
}

include('db_connect.php');

$user_email = $_SESSION['email'];
$theme = $_SESSION['theme'] ?? 'default';

// Get current user's institute_id
$stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
$stmt->bind_param("s", $user_email);
$stmt->execute();
$result_user = $stmt->get_result();
if ($user = $result_user->fetch_assoc()) {
    $institute_id = $user['id'];
} else {
    echo "User not found.";
    exit;
}
$stmt->close();

// Get class list for filter
$class_stmt = $conn->prepare("SELECT id, class FROM class WHERE institute_id = ?");
$class_stmt->bind_param("i", $institute_id);
$class_stmt->execute();
$class_result = $class_stmt->get_result();

// Get selected class ID from filter
$selected_class_id = $_GET['class_id'] ?? '';

// Build SQL query
$query = "
    SELECT t.name, t.email, c.class AS class_name, t.number
    FROM teachers t
    JOIN class c ON t.class_id = c.id
    WHERE t.institute_id = ?
";

if (!empty($selected_class_id)) {
    $query .= " AND t.class_id = ?";
}


$stmt = $conn->prepare($query);

if (!empty($selected_class_id)) {
    $stmt->bind_param("ii", $institute_id, $selected_class_id);
} else {
    $stmt->bind_param("i", $institute_id);
}

$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Teacher Details</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }

        .header {
            background-color: #2c3e50;
            color: white;
            padding: 20px;
            text-align: center;
        }

        .container {
            padding: 30px;
        }

        h2 {
            color: #2c3e50;
            margin-bottom: 20px;
        }

        form {
            margin-bottom: 20px;
        }

        select {
            padding: 8px;
            font-size: 14px;
        }

        button.filter-btn {
            padding: 8px 14px;
            background-color: #2980b9;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            background-color: white;
            border-radius: 8px;
            overflow: hidden;
        }

        th, td {
            padding: 12px;
            border: 1px solid #ddd;
            text-align: left;
        }

        th {
            background-color: #2980b9;
            color: white;
        }

        tr:hover {
            background-color: #f1f1f1;
        }

        .btn {
            margin-top: 20px;
            padding: 12px 20px;
            background-color: #27ae60;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
        }

        .btn:hover {
            background-color: #1e8449;
        }
    </style>
</head>
<body>

<div class="header">
    <h1>Teacher Details</h1>
</div>

<div class="container">
    <h2>LIST OF TEACHERS</h2>

    <!-- Filter Form -->
    <form method="GET" action="">
        <label for="class_id">Filter by Class:</label>
        <select name="class_id" id="class_id">
            <option value="">-- All Classes --</option>
            <?php while ($class = $class_result->fetch_assoc()): ?>
                <option value="<?php echo $class['id']; ?>" <?php if ($selected_class_id == $class['id']) echo 'selected'; ?>>
                    <?php echo htmlspecialchars($class['class']); ?>
                </option>
            <?php endwhile; ?>
        </select>
        <button type="submit" class="filter-btn">Apply</button>
    </form>

    <table>
        <thead>
        <tr>
            <th>NO</th>
            <th>Name</th>
            <th>Email</th>
            <th>Class</th>
            <th>Phone</th>
        </tr>
        </thead>
        <tbody>
        <?php
        $counter = 1;
        if ($result && $result->num_rows > 0):
            while ($row = $result->fetch_assoc()):
                ?>
                <tr>
                    <td><?php echo $counter++; ?></td>
                    <td><?php echo htmlspecialchars($row['name']); ?></td>
                    <td><?php echo htmlspecialchars($row['email']); ?></td>
                    <td><?php echo htmlspecialchars($row['class_name']); ?></td>
                    <td><?php echo htmlspecialchars($row['number']); ?></td>
                </tr>
            <?php
            endwhile;
        else:
            ?>
            <tr><td colspan="5">No teachers found.</td></tr>
        <?php endif; ?>
        </tbody>
    </table>

    <form method="get" action="generate_report_tech.php">
    <input type="hidden" name="class_id" value="<?php echo htmlspecialchars($selected_class_id); ?>">
    <button type="submit" class="btn">Generate Report</button>
</form>

</div>

</body>
</html>
