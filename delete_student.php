<?php
include 'db.php';

$id = $_GET['id'] ?? '';
if ($id) {
    // Prepare and execute delete
    $stmt = $conn->prepare("DELETE FROM students WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
}

// Redirect back to admin panel
header("Location: admin.php");
exit();
?>