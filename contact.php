<?php
// Include the database connection file
include "db.php";

$message = "";
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Sanitize user input for display and database storage
    // NOTE: mysqli_real_escape_string should be used if you stick with direct SQL insertion
    $name = mysqli_real_escape_string($conn, htmlspecialchars($_POST['name']));
    $email = mysqli_real_escape_string($conn, htmlspecialchars($_POST['email']));
    $msg = mysqli_real_escape_string($conn, htmlspecialchars($_POST['message']));
    
    // --- Database Insertion Logic (Direct SQL - INSECURE) ---
    // Using the 'contact' table confirmed by the user. 
    // Subject column is omitted as requested.
    $sql = "INSERT INTO contact (sender_name, sender_email, message, sent_at) 
            VALUES ('$name', '$email', '$msg', NOW())";
            
    if (mysqli_query($conn, $sql)) {
        $message = "<p class='success-message'>Thank you, $name! Your message has been received. We'll be in touch soon.</p>";
    } else {
        // Display an error message if the query fails
        $message = "<p class='error-message'>Error saving message: " . mysqli_error($conn) . "</p>";
    }
    // --- End Database Insertion Logic ---
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Us - IronFlex Gym</title>
    <!-- Importing Montserrat and Poppins fonts for theme consistency -->
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@700&family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    
    <style>
        /* --- THEME COLORS MATCHING ADMIN/TRAINER DASHBOARD --- */
        :root {
            --accent-red: #E63946;
            --accent-gold: #FFD166;
            --text-light: #F5F5F5;
            --bg-dark: #121212;
            --card-bg: rgba(26, 26, 26, 0.95);
            --input-bg: #161616;
            --input-border: #444;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: var(--bg-dark);
            /* Optional: Use a solid dark color or a simple dark pattern background */
            background-image: none; 
            background-attachment: fixed;
            margin: 0;
            padding: 0;
            color: var(--text-light);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
        }
        
        .contact-container {
            background: var(--card-bg);
            backdrop-filter: blur(10px);
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.8);
            padding: 40px;
            max-width: 550px;
            width: 90%;
            text-align: center;
            animation: fadeIn 0.8s ease-in-out;
            border: 1px solid var(--input-border);
            margin: 20px;
        }

        h1 {
            font-family: 'Montserrat', sans-serif;
            font-size: 2.5rem;
            color: var(--accent-gold); 
            margin-bottom: 15px;
            letter-spacing: 1px;
            border-bottom: 2px solid var(--accent-red);
            padding-bottom: 10px;
        }

        p {
            font-size: 1rem;
            line-height: 1.6;
            margin-bottom: 20px;
            color: var(--text-light);
        }

        .success-message {
            color: var(--accent-gold);
            font-weight: 600;
            background: rgba(255, 209, 102, 0.1); /* Light gold background */
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            border: 1px solid var(--accent-gold);
        }
        
        .error-message {
            color: var(--accent-red);
            font-weight: 600;
            background: rgba(230, 57, 70, 0.1);
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            border: 1px solid var(--accent-red);
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
            border-radius: 8px;
            background: var(--input-bg);
            color: var(--text-light);
            font-size: 1rem;
            font-family: 'Poppins', sans-serif;
            transition: border-color 0.3s ease, box-shadow 0.3s ease;
            box-sizing: border-box;
        }
        
        input:focus, textarea:focus {
            outline: none;
            border-color: var(--accent-red);
            box-shadow: 0 0 10px rgba(230, 57, 70, 0.5);
        }

        input[type="submit"] {
            background-color: var(--accent-red); 
            color: var(--text-light);
            font-weight: bold;
            cursor: pointer;
            text-transform: uppercase;
            letter-spacing: 1px;
            border: none;
            padding: 15px;
            font-size: 1.1rem;
            border-radius: 8px;
            margin-top: 10px;
            transition: background 0.3s ease, transform 0.3s ease;
        }

        input[type="submit"]:hover {
            background-color: #FF595E;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(230, 57, 70, 0.5);
        }
        
        /* Placeholder styling */
        ::placeholder {
            color: rgba(245, 245, 245, 0.5);
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
       For any inquiries or support, simply send us a message — we’ll respond as quickly as possible.
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
