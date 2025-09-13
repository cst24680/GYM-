<?php
$message = "";
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = htmlspecialchars($_POST['name']);
    $email = htmlspecialchars($_POST['email']);
    $msg = htmlspecialchars($_POST['message']);
    
    // Here you can save to DB or send email
    $message = "<p style='color:orange;'>Thank you, $name! Your message has been received.</p>";
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Contact Us - Gym System</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: black;
            color: white;
            margin: 0;
            padding: 0;
        }
        header {
            background: orange;
            padding: 15px;
            text-align: center;
            font-size: 24px;
            font-weight: bold;
        }
        .container {
            padding: 30px;
            max-width: 600px;
            margin: auto;
        }
        input, textarea {
            width: 100%;
            padding: 10px;
            margin: 8px 0;
            border: none;
            border-radius: 5px;
        }
        input[type="submit"] {
            background: orange;
            color: black;
            font-weight: bold;
            cursor: pointer;
        }
        input[type="submit"]:hover {
            background: darkorange;
        }
    </style>
</head>
<body>

<header>Contact Us</header>

<div class="container">
    <h2>Get in Touch</h2>
    <?php echo $message; ?>
    <form method="post" action="">
        <input type="text" name="name" placeholder="Your Name" required pattern="[A-Za-z\s]+">
        <input type="email" name="email" placeholder="Your Email" required>
        <textarea name="message" rows="5" placeholder="Your Message" required></textarea>
        <input type="submit" value="Send Message">
    </form>
</div>

</body>
</html>
