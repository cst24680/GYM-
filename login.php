<!-- login.php -->
<!DOCTYPE html>
<html>
<head>
    <title>Login - Gym System</title>
    <style>
        body {
            background-color: #000;
            color: white;
            font-family: 'Poppins', sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        form {
            background: #111;
            padding: 30px;
            border-radius: 8px;
            border: 1px solid #333;
        }

        h2 {
            text-align: center;
            color: #f9ac54;
        }

        input, select, button {
            display: block;
            width: 100%;
            margin: 10px 0;
            padding: 10px;
            background: #222;
            color: white;
            border: 1px solid #444;
        }

        button {
            background-color: #28a745;
            cursor: pointer;
            border: none;
        }

        button:hover {
            background-color: #218838;
        }
    </style>
</head>
<body>

    <form action="login_handler.php" method="post">
        <h2>Login</h2>
        <label>Email:</label>
        <input type="email" name="email" required>

        <label>Password:</label>
        <input type="password" name="password" required>

        <label>User Type:</label>
        <select name="User_type" required>
            <option value="member">Member</option>
            <option value="trainer">Trainer</option>
            <option value="dietician">Dietician</option>
            <option value="admin">Admin</option>
        </select>

        <button type="submit">Login</button>
    </form>

</body>
</html>
