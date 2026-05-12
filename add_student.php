<?php
require_once __DIR__ . '/auth.php';
require_login();
require_once __DIR__ . '/db.php';

$error = '';

$courses_res = mysqli_query($conn, 'SELECT id, course_name FROM courses ORDER BY course_name');
$courses = [];
if ($courses_res) {
    while ($c = mysqli_fetch_assoc($courses_res)) {
        $courses[] = $c;
    }
}

if (isset($_POST['add'])) {
    $student_id = trim($_POST['student_id'] ?? '');
    $fullname = trim($_POST['fullname'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $course_id = isset($_POST['course_id']) ? (int) $_POST['course_id'] : 0;

    if ($student_id === '' || $fullname === '' || $email === '') {
        $error = 'Please fill all required fields.';
    } elseif (strlen($student_id) > 50 || strlen($fullname) > 100) {
        $error = 'Student ID or name is too long.';
    } elseif (strlen($email) > 100 || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email.';
    } elseif ($course_id <= 0) {
        $error = 'Please select a course.';
    } else {
        $check = mysqli_prepare($conn, 'SELECT id FROM courses WHERE id = ? LIMIT 1');
        mysqli_stmt_bind_param($check, 'i', $course_id);
        mysqli_stmt_execute($check);
        mysqli_stmt_store_result($check);
        $ok_course = mysqli_stmt_num_rows($check) === 1;
        mysqli_stmt_close($check);

        if (!$ok_course) {
            $error = 'Invalid course selected.';
        } else {
            $stmt = mysqli_prepare(
                $conn,
                'INSERT INTO students (student_id, fullname, email, course_id) VALUES (?, ?, ?, ?)'
            );
            mysqli_stmt_bind_param($stmt, 'sssi', $student_id, $fullname, $email, $course_id);
            if (mysqli_stmt_execute($stmt)) {
                mysqli_stmt_close($stmt);
                header('Location: dashboard.php');
                exit;
            }
            if (mysqli_errno($conn) === 1062) {
                $error = 'That Student ID is already registered.';
            } else {
                $error = 'Could not add student. Try again.';
            }
            mysqli_stmt_close($stmt);
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Add Student</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<div class="container" style="width: 400px; margin: 40px auto; text-align: left;">
    <h2>Add Student</h2>

    <?php if ($error !== ''): ?>
        <p><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></p>
    <?php endif; ?>

    <form method="POST">
        <label>Student ID</label><br>
        <input type="text" name="student_id" maxlength="50" required
               value="<?php echo htmlspecialchars($_POST['student_id'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"><br>
        <label>Full Name</label><br>
        <input type="text" name="fullname" maxlength="100" required
               value="<?php echo htmlspecialchars($_POST['fullname'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"><br>
        <label>Email</label><br>
        <input type="email" name="email" maxlength="100" required
               value="<?php echo htmlspecialchars($_POST['email'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"><br>
        <label>Course</label><br>
        <select name="course_id" required>
            <option value="">-- Select --</option>
            <?php foreach ($courses as $c): ?>
                <option value="<?php echo (int) $c['id']; ?>"
                    <?php echo (isset($_POST['course_id']) && (int) $_POST['course_id'] === (int) $c['id']) ? ' selected' : ''; ?>>
                    <?php echo htmlspecialchars($c['course_name'], ENT_QUOTES, 'UTF-8'); ?>
                </option>
            <?php endforeach; ?>
        </select><br><br>
        <button type="submit" name="add" value="1">Add</button>
    </form>
    <p><a href="dashboard.php">Back to Dashboard</a></p>
</div>

</body>
</html>
