<?php
$message = "";
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = htmlspecialchars($_POST['name']);
    $email = htmlspecialchars($_POST['email']);
    $msg = htmlspecialchars($_POST['message']);
    
    // Here you can add logic to send an email or save to a database.
    // Example: mail($to, $subject, $msg, $headers);
    
    $message = "<p class='success-message'>Thank you, $name! Your message has been received.</p>";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Us - IronFlex Gym</title>
    <link href="https://fonts.googleapis.com/css2?family=Lato:wght@300;400;700&family=Racing+Sans+One&display=swap" rel="stylesheet">
    <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
    <style>
        :root {
            --primary-color: #ffcc00;
            --secondary-color: #ff6600;
            --text-color: #f1f1f1;
            --background-dark: #121212;
            --form-bg: rgba(255, 255, 255, 0.08);
            --input-border: rgba(255, 255, 255, 0.2);
        }

        body {
            font-family: 'Lato', sans-serif;
            background: var(--background-dark) url('https://images.unsplash.com/photo-1571019613454-1cb2f99b231b?q=80&w=2070&auto=format&fit=crop') no-repeat center center/cover;
            background-attachment: fixed;
            margin: 0;
            padding: 0;
            color: var(--text-color);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
        }
        
        .contact-container {
            background: rgba(0, 0, 0, 0.75);
            backdrop-filter: blur(15px);
            border-radius: 20px;
            box-shadow: 0px 15px 40px rgba(0, 0, 0, 0.7);
            padding: 40px 60px;
            max-width: 650px;
            width: 90%;
            text-align: center;
            animation: fadeIn 1s ease-in-out;
            border: 1px solid rgba(255, 255, 255, 0.1);
            margin: 20px;
        }

        h1 {
            font-family: 'Racing Sans One', sans-serif;
            font-size: clamp(2.5rem, 6vw, 4rem);
            background: linear-gradient(45deg, var(--primary-color), var(--secondary-color));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 20px;
            letter-spacing: 2px;
            text-shadow: 2px 2px 5px rgba(0,0,0,0.5);
        }

        h2 {
            font-family: 'Racing Sans One', sans-serif;
            font-size: 1.8rem;
            color: var(--primary-color);
            margin-bottom: 30px;
            position: relative;
        }

        h2::after {
            content: "";
            width: 80px;
            height: 4px;
            background: linear-gradient(90deg, var(--primary-color), var(--secondary-color));
            display: block;
            margin: 10px auto 0;
            border-radius: 2px;
        }

        p {
            font-size: 1.1rem;
            line-height: 1.6;
            margin-bottom: 25px;
            color: var(--text-color);
        }

        .success-message {
            color: var(--primary-color);
            font-weight: bold;
            background: rgba(255, 204, 0, 0.1);
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
            border: 1px solid var(--primary-color);
        }

        form {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        input, textarea {
            width: 100%;
            padding: 15px;
            border: 1px solid var(--input-border);
            border-radius: 10px;
            background: var(--form-bg);
            color: var(--text-color);
            font-size: 1rem;
            transition: border-color 0.3s ease, box-shadow 0.3s ease;
            box-sizing: border-box; /* Ensures padding doesn't affect width */
        }
        
        input:focus, textarea:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 10px rgba(255, 204, 0, 0.5);
        }

        input[type="submit"] {
            background: linear-gradient(45deg, var(--secondary-color), var(--primary-color));
            color: var(--background-dark);
            font-weight: bold;
            cursor: pointer;
            text-transform: uppercase;
            letter-spacing: 1px;
            border: none;
            padding: 15px;
            font-size: 1.1rem;
            border-radius: 30px;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        input[type="submit"]:hover {
            transform: translateY(-3px) scale(1.02);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.5);
        }
        
        /* Placeholder styling for a modern look */
        ::placeholder {
            color: rgba(241, 241, 241, 0.5);
        }
        ::-webkit-input-placeholder {
            color: rgba(241, 241, 241, 0.5);
        }
        :-moz-placeholder {
            color: rgba(241, 241, 241, 0.5);
            opacity: 1; /* override Firefox's opacity */
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

    </style>
</head>
<body>

<div class="contact-container">
    <h1>Contact Us</h1>
    <p>
        Ready to start your fitness journey? Have a question about our classes or membership plans? 
        Fill out the form below, and we'll get back to you as soon as possible.
    </p>

    <?php echo $message; ?>

    <form method="post" action="">
        <input type="text" name="name" placeholder="Your Full Name" required pattern="[A-Za-z\s]+" title="Name should only contain letters and spaces">
        <input type="email" name="email" placeholder="Your Email Address" required>
        <textarea name="message" rows="6" placeholder="Your Message" required></textarea>
        <input type="submit" value="Send Message">
    </form>
</div>

</body>
</html>