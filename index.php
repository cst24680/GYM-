<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Gym Management System</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        @import url("https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap");

        :root {
            --bg: #000;
            --accent: #f9ac54;
            --text: #fff;
            --button: #28a745;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background-color: var(--bg);
            color: var(--text);
            line-height: 1.6;
        }

        header {
            background-color: #111;
            padding: 20px 40px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 2px solid var(--accent);
        }

        header h1 {
            color: var(--accent);
            font-size: 28px;
        }

        nav a {
            margin-left: 20px;
            color: var(--text);
            text-decoration: none;
            font-weight: 600;
            transition: color 0.3s ease;
        }

        nav a:hover {
            color: var(--accent);
        }

        .hero {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 80vh;
            background: url('gym-bg.jpg') no-repeat center center/cover;
            text-align: center;
            padding: 20px;
            position: relative;
        }

        .hero::before {
            content: "";
            position: absolute;
            top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(0, 0, 0, 0.6);
            z-index: 0;
        }

        .hero-content {
            position: relative;
            z-index: 1;
        }

        .hero h2 {
            font-size: 48px;
            color: var(--accent);
            margin-bottom: 20px;
        }

        .hero p {
            font-size: 20px;
            max-width: 700px;
            margin: 0 auto 30px;
        }

        .buttons {
            display: flex;
            gap: 20px;
            flex-wrap: wrap;
            justify-content: center;
        }

        .buttons a {
            padding: 12px 24px;
            background-color: var(--button);
            color: white;
            border: none;
            border-radius: 8px;
            text-decoration: none;
            font-weight: bold;
            transition: background 0.3s;
        }

        .buttons a:hover {
            background-color: #218838;
        }

        footer {
            background-color: #111;
            color: #aaa;
            text-align: center;
            padding: 20px;
            font-size: 14px;
        }

        @media (max-width: 768px) {
            .hero h2 {
                font-size: 32px;
            }
            .hero p {
                font-size: 16px;
            }
        }
    </style>
</head>
<body>

    <header>
        <h1>GYM SYSTEM</h1>
        <nav>
            <a href="index.php">Home</a>
            <a href="login.php">Login</a>
            <a href="register.php">Register</a>
            <a href="#about">About</a>
            <a href="#contact">Contact</a>
        </nav>
    </header>

    <section class="hero">
        <div class="hero-content">
            <h2>Transform Your Body, Empower Your Life</h2>
            <p>Track your workouts, manage your diet, connect with trainers, and crush your fitness goals — all in one smart system.</p>
            <div class="buttons">
                <a href="login.php">Login</a>
                <a href="register.php">Join Now</a>
            </div>
        </div>
    </section>

    <footer>
        &copy; 2025 Gym Management System. All rights reserved.
    </footer>

</body>
</html>
