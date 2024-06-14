<?php
/**
 * File: booking.php
 * Author: Shashank Chauhan
 * Student ID: 104168546
 *
 * Description: This file provides the interface for users to book a cab. 
 * It requires users to be logged in before they can make a booking.
 */

// Configure session parameters
ini_set('session.gc_maxlifetime', 600); // Set session timeout to 10 minutes
ini_set('session.cookie_lifetime', 0); // Session cookie will be deleted when browser closes
session_start();
date_default_timezone_set('Australia/Melbourne'); // Set timezone

// Redirect to login if the user isn't authenticated or if email is null
if (!isset($_SESSION["email"]) || $_SESSION["email"] == null) {
    session_destroy(); // Terminate the session
    header("Location: login.php");
    exit;
}

// Retrieve user's email from the session
$email = $_SESSION["email"];

// Process booking form
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Database connection details
$host = 'localhost';
$user = 'root';
$pass = '';
$db = 'booking_system';
$conn = new mysqli($host, $user, $pass, $db);$passenger_name = $_POST["passenger_name"];
    $contact_phone = $_POST["contact_phone"];
    $unit_number = isset($_POST["unit_number"]) ? $_POST["unit_number"] : null;
    $street_number = $_POST["street_number"];
    $street_name = $_POST["street_name"];
    $suburb = $_POST["suburb"];
    $destination_suburb = $_POST["destination_suburb"];
    $pickup_datetime = new DateTime($_POST["pickup_datetime"]);
    $current_time = new DateTime();
    $interval = $pickup_datetime->diff($current_time);
    $minutes_diff = $interval->days * 24 * 60 + $interval->h * 60 + $interval->i;

    if (empty($passenger_name) || empty($contact_phone) || empty($street_number) || empty($street_name) || empty($suburb) || empty($destination_suburb) || empty($pickup_datetime)) {
        echo "All fields are required except unit number!";
    } elseif ($minutes_diff < 40) {
        echo "Pick-up time must be at least 40 minutes after the current time!";
    } else {
        $stmt = $conn->prepare("INSERT INTO bookings (email, passenger_name, contact_phone, unit_number, street_number, street_name, suburb, destination_suburb, pickup_datetime) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $formatted_pickup_datetime = $pickup_datetime->format('Y-m-d H:i:s');
        $stmt->bind_param("sssssssss", $email, $passenger_name, $contact_phone, $unit_number, $street_number, $street_name, $suburb, $destination_suburb, $formatted_pickup_datetime);

        if ($stmt->execute()) {
            $booking_reference_number = $conn->insert_id;
            echo "Thank you! Your booking reference number is $booking_reference_number. We will pick up the passengers in front of your provided address at " . $pickup_datetime->format('H:i') . " on " . $pickup_datetime->format('Y-m-d') . ".";
            // Send the confirmation email
            /*$To = $email;
            $subject = "Your booking request with CabsOnline!";
            $message = "Dear $passenger_name, Thanks for booking with CabsOnline! Your booking reference number is $booking_reference_number. We will pick up the passengers in front of your provided address at " . $pickup_datetime->format('H:i') . " on " . $pickup_datetime->format('Y-m-d') . ".";
            $headers = "From: booking@cabsonline.com.au";
            mail($To, $subject, $message, $headers, "-r 104168546@student.swin.edu.au");
            // If using a local development environment, the mail function might not work. Consider using an SMTP server or mail service like SendGrid or PHPMailer for a more robust solution.
            if (!mail($To, $subject, $message, $headers, "-r 104168546@student.swin.edu.au")) {
            echo "Error sending confirmation email!";*/
		
            } 
        else {
            echo "Error booking!";
        }
    }
}
if (isset($_POST['logout'])) {
    session_destroy(); // End the session
    header("Location: login.php");
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Booking a Cab</title>
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
            margin-bottom: 20px;
        }

        label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
        }

        input[type="text"], input[type="datetime-local"], input[type="email"], input[type="password"] {
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
    <h1>Booking a Cab</h1>
    <form method="post">
        <label for="passenger_name">Passenger Name:</label>
        <input type="text" name="passenger_name" id="passenger_name">

        <label for="contact_phone">Contact Phone:</label>
        <input type="text" name="contact_phone" id="contact_phone">

        <label for="unit_number">Unit Number (Optional):</label>
        <input type="text" name="unit_number" id="unit_number">

        <label for="street_number">Street Number:</label>
        <input type="text" name="street_number" id="street_number">

        <label for="street_name">Street Name:</label>
        <input type="text" name="street_name" id="street_name">

        <label for="suburb">Suburb:</label>
        <input type="text" name="suburb" id="suburb">

        <label for="destination_suburb">Destination Suburb:</label>
        <input type="text" name="destination_suburb" id="destination_suburb">

        <label for="pickup_datetime">Pickup Date/Time:</label>
        <input type="datetime-local" name="pickup_datetime" id="pickup_datetime">

        <input type="submit" value="Book">
    </form>
    <form method="post">
        <input type="submit" name="logout" value="Logout">
    </form>
</body>
</html>
