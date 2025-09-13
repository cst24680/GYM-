<?php
session_start();
include "db.php";

// Ensure member is logged in
if (!isset($_SESSION['Mem_id'])) {
    header("Location: login.php");
    exit();
}

$member_id = $_SESSION['Mem_id'];
$message = "";

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $target_id = $_POST['target_id'];
    $target_type = $_POST['target_type'];
    $rating = $_POST['rating'];
    $comments = $_POST['comments'];

    $sql = "INSERT INTO feedback (Mem_id, target_id, target_type, rating, comments) 
            VALUES ($member_id, $target_id, '$target_type', $rating, '$comments')";

    if (mysqli_query($conn, $sql)) {
        $message = "✅ Feedback submitted successfully!";
    } else {
        $message = "❌ Error: " . mysqli_error($conn);
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Give Feedback</title>
    <link rel="stylesheet" href="member.css">
</head>
<body>
    <div class="main-content content-section">
        <h2>Submit Feedback</h2>
        <?php if ($message) echo "<p>$message</p>"; ?>

        <form method="post">
            <label>Target Type:</label>
            <select name="target_type" required>
                <option value="">-- Select --</option>
                <option value="Trainer">Trainer</option>
                <option value="Dietician">Dietician</option>
                <option value="Gym">Gym</option>
            </select><br><br>

            <label>Target ID:</label>
            <input type="number" name="target_id" placeholder="Enter ID of Trainer/Dietician" required><br><br>

            <label>Rating (1-5):</label>
            <input type="number" name="rating" min="1" max="5" required><br><br>

            <label>Comments:</label><br>
            <textarea name="comments" rows="4" cols="50" placeholder="Write your feedback..."></textarea><br><br>

            <button type="submit">Submit Feedback</button>
        </form>
    </div>
</body>
</html>
  
