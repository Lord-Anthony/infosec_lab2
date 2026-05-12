<?php
require_once __DIR__ . '/auth.php';
require_login();
require_once __DIR__ . '/db.php';

$sql = 'SELECT s.id, s.student_id, s.fullname, s.email, c.course_name
        FROM students s
        INNER JOIN courses c ON c.id = s.course_id
        ORDER BY s.id';
$result = mysqli_query($conn, $sql);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Dashboard</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<h2>Welcome <?php echo htmlspecialchars($_SESSION['user'], ENT_QUOTES, 'UTF-8'); ?></h2>

<a href="add_student.php">Add Student</a> |
<a href="logout.php">Logout</a>

<h3>Student List</h3>

<table border="1">
<tr>
    <th>ID</th>
    <th>Student ID</th>
    <th>Full Name</th>
    <th>Email</th>
    <th>Course</th>
    <th>Action</th>
</tr>

<?php
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $id = (int) $row['id'];
        ?>
<tr>
    <td><?php echo $id; ?></td>
    <td><?php echo htmlspecialchars($row['student_id'], ENT_QUOTES, 'UTF-8'); ?></td>
    <td><?php echo htmlspecialchars($row['fullname'], ENT_QUOTES, 'UTF-8'); ?></td>
    <td><?php echo htmlspecialchars($row['email'], ENT_QUOTES, 'UTF-8'); ?></td>
    <td><?php echo htmlspecialchars($row['course_name'], ENT_QUOTES, 'UTF-8'); ?></td>
    <td>
        <a href="delete_student.php?id=<?php echo $id; ?>"
           onclick="return confirm('Delete this student?');">
            Delete
        </a>
    </td>
</tr>
        <?php
    }
}
?>

</table>

</body>
</html>
