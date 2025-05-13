<?php
include 'db.php';

// Handle search, sort, club filter, and order direction
$search = $_GET['search'] ?? '';
$sort = $_GET['sort'] ?? '';
$clubFilter = $_GET['club_filter'] ?? '';
$order = $_GET['order'] ?? 'asc'; // default to ascending
$filter = "";

if ($search) {
    $searchEsc = $conn->real_escape_string($search);
    $filter .= " AND (firstName LIKE '%$searchEsc%' OR lastName LIKE '%$searchEsc%' OR studID LIKE '%$searchEsc%')";
}
if ($clubFilter) {
    $clubFilterEsc = $conn->real_escape_string($clubFilter);
    $filter .= " AND students.club = '$clubFilterEsc'";
}

// Set order direction
$orderDir = ($order === 'desc') ? 'DESC' : 'ASC';

$orderBy = "ORDER BY id $orderDir";
if ($sort == "year") $orderBy = "ORDER BY studYear $orderDir";
if ($sort == "course") $orderBy = "ORDER BY course $orderDir";
if ($sort == "club") $orderBy = "ORDER BY club $orderDir";

// LEFT JOIN to get club name
$sql = "
SELECT students.*, clubs.club_name
FROM students
LEFT JOIN clubs ON students.club = clubs.club_code
WHERE 1 $filter $orderBy
";
$result = $conn->query($sql);

// GROUP BY: Students per club
$groupSql = "
SELECT clubs.club_name, COUNT(students.id) as total
FROM clubs
LEFT JOIN students ON students.club = clubs.club_code
GROUP BY clubs.club_name
";
$groupResult = $conn->query($groupSql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Admin - School Club</title>
  <link rel="stylesheet" href="styles.css">
  
</head>
<body>
  <div class="admin-container">
    <h1>Admin Panel - Registered Students</h1>
    <!-- GROUP BY summary -->
    <?php if ($groupResult && $groupResult->num_rows > 0): ?>
      <div class="group-summary">
        <strong>Students per Club:</strong>
        <ul style="margin:0; padding-left:18px;">
          <?php while($g = $groupResult->fetch_assoc()): ?>
            <li><?php echo htmlspecialchars($g['club_name']); ?>: <?php echo $g['total']; ?></li>
          <?php endwhile; ?>
        </ul>
      </div>
    <?php endif; ?>
    <div class="admin-actions">
      <form class="search-group" method="get" action="admin.php">
        <input type="text" name="search" placeholder="🔍 Search by Name or Stud Id" value="<?php echo htmlspecialchars($search); ?>">
        <select name="club_filter">
          <option value="">All Clubs</option>
          <?php
            $clubs = $conn->query("SELECT club_code, club_name FROM clubs");
            while($c = $clubs->fetch_assoc()) {
              $selected = ($clubFilter == $c['club_code']) ? 'selected' : '';
              echo '<option value="'.htmlspecialchars($c['club_code']).'" '.$selected.'>'.htmlspecialchars($c['club_name']).'</option>';
            }
          ?>
        </select>
        <select name="sort">
          <option value="">Sort By</option>
          <option value="year" <?php if($sort=="year") echo "selected"; ?>>Year</option>
          <option value="course" <?php if($sort=="course") echo "selected"; ?>>Course</option>
          <option value="club" <?php if($sort=="club") echo "selected"; ?>>Club</option>
        </select>
        <span class="order-btns">
          <button type="submit" name="order" value="asc" <?php if($order=='asc') echo 'class="active"'; ?>>↑</button>
          <button type="submit" name="order" value="desc" <?php if($order=='desc') echo 'class="active"'; ?>>↓</button>
        </span>
        <button type="submit">Apply</button>
      </form>
    </div>
    <table>
      <tr>
        <th>ID</th>
        <th>First Name</th>
        <th>Last Name</th>
        <th>Student ID</th>
        <th>Course</th>
        <th>Year</th>
        <th>Club</th>
        <th>Actions</th>
      </tr>
      <?php if ($result && $result->num_rows > 0): ?>
        <?php while($row = $result->fetch_assoc()): ?>
          <tr>
            <td><?php echo htmlspecialchars($row['id']); ?></td>
            <td><?php echo htmlspecialchars($row['firstName']); ?></td>
            <td><?php echo htmlspecialchars($row['lastName']); ?></td>
            <td><?php echo htmlspecialchars($row['studID']); ?></td>
            <td><?php echo htmlspecialchars($row['course']); ?></td>
            <td><?php echo htmlspecialchars($row['studYear']); ?></td>
            <td><?php echo htmlspecialchars($row['club_name'] ?? 'No Club'); ?></td>
            <td>
              <a href="edit_student.php?id=<?php echo $row['id']; ?>" class="edit-btn">✏️ Edit</a>
              <a href="delete_student.php?id=<?php echo $row['id']; ?>" class="delete-btn" onclick="return confirm('Are you sure you want to delete this student?');">❌ Delete</a>
            </td>
          </tr>
        <?php endwhile; ?>
      <?php else: ?>
        <tr><td colspan="8" style="text-align:center;">No students found.</td></tr>
      <?php endif; ?>
    </table>
    <div class="bottom-actions">
      <a href="user.php" class="back-btn">← Back to Registration</a>
    </div>
  </div>
</body>
</html>