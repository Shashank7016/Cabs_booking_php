<?php
/**
 * File: register.php.
 * Author: Shashank Chauhan
 * Student ID: 104168546
 *
 * Description: This file provides the registration interface for CabsOnline. 
 * Users can register by providing their details, which will then be stored in the database.
 */

// Check if the form has been submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Database connection details
$host = 'localhost';
$user = 'root';
$pass = '';
$db = 'booking_system';
$conn = new mysqli($host, $user, $pass, $db);
    // Establish the database connection
    $conn = new mysqli($host, $user, $pass,$db);

    // Fetch data from POST request
    $name = $_POST["name"];
    $email = $_POST["email"];
    $password = $_POST["password"];
    $confirm_password = $_POST["confirm_password"];
    $phone_number = $_POST["phone_number"];

    // Check for any empty fields
    if (empty($name) || empty($email) || empty($password) || empty($confirm_password) || empty($phone_number)) {
        echo "All fields are required!";
    } elseif ($password != $confirm_password) { // Check if passwords match
        echo "Passwords do not match!";
    } else {
        // Check if the email is already registered
        $check = $conn->query("SELECT email FROM customers WHERE email = '$email'");
        if ($check->num_rows > 0) {
            echo "Email already exists!";
        } else {
            // Insert the new user's details into the database
            $stmt = $conn->prepare("INSERT INTO customers (email, name, password, phone_number) VALUES (?, ?, ?, ?)");
            $salt = uniqid(mt_rand(), true);
            $hashed_password = crypt($password, $salt);
            $stmt->bind_param("ssss", $email, $name, $hashed_password, $phone_number);
            if ($stmt->execute()) {
                // Redirect to booking page if registration is successful
                header("Location: booking.php?email=$email");
            } else {
                echo "Error registering!";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Register to CabsOnline</title>
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

        input[type="text"], input[type="email"], input[type="password"] {
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
    <h1>Register to CabsOnline</h1>
    <form method="post">
        <label for="name">Name:</label>
        <input type="text" name="name" id="name">

        <label for="email">Email:</label>
        <input type="email" name="email" id="email">

        <label for="password">Password:</label>
        <input type="password" name="password" id="password">

        <label for="confirm_password">Confirm Password:</label>
        <input type="password" name="confirm_password" id="confirm_password">

        <label for="phone_number">Phone Number:</label>
        <input type="text" name="phone_number" id="phone_number">

        <input type="submit" value="Register">
    </form>
    <p>Already Registered? <a href="login.php">Login here</a></p>
</body>
</html>