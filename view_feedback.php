<?php
include "db.php";

$result = mysqli_query($conn, "
    SELECT f.feedback_id, m.Mem_name, f.target_type, f.rating, f.comments, f.created_at
    FROM feedback f
    JOIN member_registration m ON f.Mem_id = m.Mem_id
    ORDER BY f.created_at DESC
");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Feedback List</title>
    <link rel="stylesheet" href="member.css">
</head>
<body>
    <div class="main-content content-section">
        <h2>All Feedback</h2>
        <table border="1" cellpadding="10" cellspacing="0" style="width:100%; background:#222; color:white;">
            <tr>
                <th>ID</th>
                <th>Member</th>
                <th>Target</th>
                <th>Rating</th>
                <th>Comments</th>
                <th>Date</th>
            </tr>
            <?php while($row = mysqli_fetch_assoc($result)) { ?>
                <tr>
                    <td><?= $row['feedback_id'] ?></td>
                    <td><?= $row['Mem_name'] ?></td>
                    <td><?= $row['target_type'] ?></td>
                    <td><?= $row['rating'] ?></td>
                    <td><?= htmlspecialchars($row['comments']) ?></td>
                    <td><?= $row['created_at'] ?></td>
                </tr>
            <?php } ?>
        </table>
    </div>
</body>
</html>
