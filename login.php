<?php
/**
 * File: login.php
 * Author: Shashank Chauhan
 * Student ID: 104168546
 *
 * Description: This file provides the login interface for CabsOnline. 
 * Users can log in by providing their email and password, which will then be verified with the database.
 */

// Start the session
session_start();

// Check if the form has been submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Database connection details
$host = 'localhost';
$user = 'root';
$pass = '';
$db = 'booking_system';
$conn = new mysqli($host, $user, $pass, $db);

    // Fetch user input
    $email = $_POST["email"];
    $password = $_POST["password"];

    // Check if email exists in the database
    $stmt = $conn->prepare("SELECT password FROM customers WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();
    
    if ($stmt->num_rows > 0) {
        // Fetch the hashed password for the provided email
        $stmt->bind_result($hashed_password);
        $stmt->fetch();
        
        // Verify if the provided password matches the hashed password
        if(crypt($password, $hashed_password) == $hashed_password) {
            // Set session and redirect to booking page
            $_SESSION["email"] = $email;
            header("Location: booking.php");
            exit;
        } else {
            echo "Incorrect password.";
        }
    } else {
        echo "Email not found.";
    }
}

?>

<!DOCTYPE html>
<html>
<head>
    <title>Login to CabsOnline</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            padding: 50px;
        }

        h1 {
            color: #333;
            text-align: center;
            margin-bottom: 40px;
        }

        form {
            background-color: #fff;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0px 0px 10px rgba(0,0,0,0.1);
            max-width: 400px;
            margin: 0 auto;
        }

        label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
        }

        input[type="email"], input[type="password"] {
            width: 100%;
            padding: 10px;
            margin-bottom: 20px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }

        input[type="submit"] {
            background-color: #333;
            color: #fff;
            padding: 10px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-weight: bold;
        }

        input[type="submit"]:hover {
            background-color: #555;
        }

        p {
            text-align: center;
            margin-top: 20px;
        }

        a {
            color: #333;
        }

        a:hover {
            text-decoration: underline;
        }

    </style>
</head>
<body>
    <h1>Login to CabsOnline</h1>
    <form method="post">
        <label for="email">Email:</label>
        <input type="email" name="email" id="email">

        <label for="password">Password:</label>
        <input type="password" name="password" id="password">

        <input type="submit" value="Login">
    </form>
    <p>New Member? <a href="register.php">Register now</a></p>
</body>
</html>