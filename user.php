<?php
// Include database connection
include 'db.php';

$msg = '';
if ($_SERVER["REQUEST_METHOD"] == "POST") {
  $firstName = $_POST['firstName'] ?? '';
  $lastName = $_POST['lastName'] ?? '';
  $studID = $_POST['studID'] ?? '';
  $course = $_POST['course'] ?? '';
  $studYear = $_POST['studYear'] ?? '';
  $club = $_POST['club'] ?? '';

  // Simple validation (optional)
  if ($firstName && $lastName && $studID && $course && $studYear) {
    $stmt = $conn->prepare("INSERT INTO students (firstName, lastName, studID, course, studYear, club) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssss", $firstName, $lastName, $studID, $course, $studYear, $club);
    if ($stmt->execute()) {
      // Store message in session and redirect (Post/Redirect/Get)
      session_start();
      $_SESSION['msg'] = "Student registered successfully!";
      header("Location: " . $_SERVER['PHP_SELF']);
      exit();
    } else {
      $msg = "Error: " . $stmt->error;
    }
    $stmt->close();
  } else {
    $msg = "Please fill in all required fields.";
  }
}

// Show message from session if available
session_start();
if (!empty($_SESSION['msg'])) {
  $msg = $_SESSION['msg'];
  unset($_SESSION['msg']);
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>SCHOOL CLUB</title>
  <link rel="stylesheet" href="styles.css">
</head>

<body>
  <div class="container">
    <h1>SCHOOL CLUB</h1>
    <?php if (!empty($msg)) {
      echo "<div id='msg' style='margin-bottom:15px;color:green;'>$msg</div>";
    } ?>
    <form method="post" autocomplete="off">
      <label for="firstName">FirstName:</label>
      <input type="text" id="firstName" name="firstName" required><br>
      <label for="lastName">LastName:</label>
      <input type="text" id="lastName" name="lastName" required><br>
      <label for="studID">Student Id:</label>
      <input type="text" id="studID" name="studID" required><br>
      <label for="course">Course:</label>
      <input type="text" id="course" name="course" required><br>
      <label for="studYear">Year:</label>
      <input type="number" id="studYear" name="studYear" min="1" max="4" required><br>
      <label for="club">Club:</label>
      <select id="club" name="club" required>
        <option value="" disabled selected>Select a club</option>
        <?php
        // Fetch all clubs from the database
        $clubResult = $conn->query("SELECT club_code, club_name FROM clubs");
        if ($clubResult && $clubResult->num_rows > 0) {
          while ($clubRow = $clubResult->fetch_assoc()) {
            echo '<option value="' . htmlspecialchars($clubRow['club_code']) . '">' . htmlspecialchars($clubRow['club_name']) . '</option>';
          }
        }
        ?>
        <option value="none">No Club</option>
      </select><br>
      <button class="submit" type="submit">Submit</button><br>
    </form>
    <button class="admin" type="button" onclick="window.open('admin.php', '_self')">Admin</button><br>
    <button id="darkModeToggle" style="position:fixed;top:20px;left:20px;z-index:1000;">🌙 Dark Mode</button>
  </div>
</body>

</html>

<script>
  // Apply dark mode on page load if set
  if (localStorage.getItem('darkMode') === 'true') {
    document.body.classList.add('dark-mode');
    document.getElementById('darkModeToggle').textContent = '☀️ Light Mode';
  }

  document.getElementById('darkModeToggle').onclick = function() {
    document.body.classList.toggle('dark-mode');
    const isDark = document.body.classList.contains('dark-mode');
    this.textContent = isDark ? '☀️ Light Mode' : '🌙 Dark Mode';
    localStorage.setItem('darkMode', isDark);
  };

  // Hide the message after 3 seconds
  window.onload = function() {
    var msgDiv = document.getElementById('msg');
    if (msgDiv) {
      setTimeout(function() {
        msgDiv.style.display = 'none';
      }, 3000);
    }
  };
</script>