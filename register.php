<?php
include('db.php');

// Get form data
$Mem_name   = $_POST['name'];
$Mem_age    = $_POST['age'];
$Mem_email  = $_POST['email'];
$Height     = $_POST['height'];
$Weight     = $_POST['weight'];
$Mem_dob    = $_POST['dob'];
$Mem_phno   = $_POST['phone'];
$password   = $_POST['password'];
$Mem_gender = $_POST['gender'];
$Plan_name  = $_POST['Goal'];

// Calculate BMI
$height_m = $Height / 100;
$BMI = $Weight / ($height_m * $height_m);

// Insert into Member_registration table
$sql = "INSERT INTO `member_registration`(  `Mem_name`, `Mem_age`, `Mem_email`, `Height`, `Weight`, `Mem_dob`, `Mem_phno`, `BMI`, `Plan_name`, `Mem_pass`) VALUES 
        ('$Mem_name', '$Mem_age', '$Mem_email', '$Height', '$Weight', '$Mem_dob', '$Mem_phno', '$BMI', '$Plan_name','$password')";

if (mysqli_query($conn, $sql)) {

    $login_sql = "INSERT INTO login (Email, Password, User_type) 
                  VALUES ('$Mem_email', '$password', 'member')";

    if (mysqli_query($conn, $login_sql)) {
        echo "<script>alert('Registration Successful!'); window.location.href='login.html';</script>";
    } else {
        echo "Login table error: " . mysqli_error($conn);
    }
} else {
    echo "Registration Error: " . mysqli_error($conn);
}

mysqli_close($conn);
?>
