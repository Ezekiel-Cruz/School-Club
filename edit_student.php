<?php
include 'db.php';

$id = $_GET['id'] ?? '';
if (!$id) {
  header("Location: admin.php");
  exit();
}

// Fetch student data
$stmt = $conn->prepare("SELECT * FROM students WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$student = $result->fetch_assoc();
$stmt->close();

if (!$student) {
  echo "Student not found.";
  exit();
}

// Handle update
$msg = '';
if ($_SERVER["REQUEST_METHOD"] == "POST") {
  $firstName = $_POST['firstName'] ?? '';
  $lastName = $_POST['lastName'] ?? '';
  $studID = $_POST['studID'] ?? '';
  $course = $_POST['course'] ?? '';
  $studYear = $_POST['studYear'] ?? '';
  $club = $_POST['club'] ?? '';

  if ($firstName && $lastName && $studID && $course && $studYear) {
    $stmt = $conn->prepare("UPDATE students SET firstName=?, lastName=?, studID=?, course=?, studYear=?, club=? WHERE id=?");
    $stmt->bind_param("ssssssi", $firstName, $lastName, $studID, $course, $studYear, $club, $id);
    if ($stmt->execute()) {
      header("Location: admin.php");
      exit();
    } else {
      $msg = "Error: " . $stmt->error;
    }
    $stmt->close();
  } else {
    $msg = "Please fill in all required fields.";
  }
}

// Fetch clubs for dropdown
$clubs = $conn->query("SELECT club_code, club_name FROM clubs");
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <title>Edit Student</title>
  <link rel="stylesheet" href="styles.css">
</head>

<body>
  <div class="container" style="max-width:500px;margin:40px auto;">
    <h2>Edit Student</h2>
    <?php if ($msg) echo "<div style='color:red;margin-bottom:10px;'>$msg</div>"; ?>
    <form method="post" autocomplete="off">
      <label for="firstName">First Name:</label>
      <input type="text" name="firstName" id="firstName" value="<?php echo htmlspecialchars($student['firstName']); ?>" required><br>
      <label for="lastName">Last Name:</label>
      <input type="text" name="lastName" id="lastName" value="<?php echo htmlspecialchars($student['lastName']); ?>" required><br>
      <label for="studID">Student ID:</label>
      <input type="text" name="studID" id="studID" value="<?php echo htmlspecialchars($student['studID']); ?>" required><br>
      <label for="course">Course:</label>
      <input type="text" name="course" id="course" value="<?php echo htmlspecialchars($student['course']); ?>" required><br>
      <label for="studYear">Year:</label>
      <input type="number" name="studYear" id="studYear" min="1" max="4" value="<?php echo htmlspecialchars($student['studYear']); ?>" required><br>
      <label for="club">Club:</label>
      <select name="club" id="club" required>
        <option value="" disabled>Select a club</option>
        <?php
        if ($clubs && $clubs->num_rows > 0) {
          while ($clubRow = $clubs->fetch_assoc()) {
            $selected = ($student['club'] == $clubRow['club_code']) ? 'selected' : '';
            echo '<option value="' . htmlspecialchars($clubRow['club_code']) . '" ' . $selected . '>' . htmlspecialchars($clubRow['club_name']) . '</option>';
          }
        }
        ?>
        <option value="none" <?php if ($student['club'] == 'none') echo 'selected'; ?>>No Club</option>
      </select><br>
      <div style="text-align:center; margin-top:18px;">
        <button class="submit" type="submit" style="margin-right:18px; margin-bottom:18px;">Update</button>
        <a href="admin.php" class="back-btn">Cancel</a>
      </div>
</body>

</html>