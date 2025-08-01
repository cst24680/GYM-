<?php
include('db.php');

// List of trainers with plain passwords
$trainers = [
    'trainer1@gmail.com' => 'trainer',
    'trainer2@gmail.com' => 'trainer',
    'trainer3@gmail.com' => 'trainer',
];

foreach ($trainers as $email => $plain_password) {
    $hashed_password = password_hash($plain_password, PASSWORD_DEFAULT);
    $sql = "UPDATE login SET Password = '$hashed_password' WHERE Email = '$email' AND User_type = 'trainer'";
    
    if (mysqli_query($conn, $sql)) {
        echo "Password updated for $email<br>";
    } else {
        echo "Error updating $email: " . mysqli_error($conn) . "<br>";
    }
}

mysqli_close($conn);
?>
