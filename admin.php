<?php
// Filename: admin.php
// Purpose: This file serves as the administration page for managing unassigned cab bookings.
// Author: Shashank Chauhan
// Student ID: 104168546

session_start();

// Check if the password is already set in the session
if (!isset($_SESSION['isAdminAuthenticated'])) {
    // Check if the form was submitted
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['admin_password'])) {
        if ($_POST['admin_password'] === 'root') {
            // Set a session variable to indicate authentication
            $_SESSION['isAdminAuthenticated'] = true;
        } else {
            $errorMsg = "Incorrect password!";
        }
    }
}

// If not authenticated, display the login form
if (!isset($_SESSION['isAdminAuthenticated'])) {
?>

<!-- Authentication Form -->
<!DOCTYPE html>
<html>
<head>
    <title>Admin Authentication</title>
</head>
<body>
    <h2>Admin Authentication</h2>
    <?php
    // Display an error message if the password is incorrect
    if (isset($errorMsg)) {
        echo "<p style='color: red;'>$errorMsg</p>";
    }
    ?>
    <form method="post">
        Password: <input type="password" name="admin_password">
        <input type="submit" value="Login">
    </form>
</body>
</html>

<?php
exit; // Stop the further execution of the page until password is correct
}

// Setting the default timezone
date_default_timezone_set('Australia/Melbourne');

// Database connection details
$host = 'localhost';
$user = 'root';
$pass = '';
$db = 'booking_system';
$conn = new mysqli($host, $user, $pass, $db);
// Check for connection errors
if ($conn->connect_error) {
	die("Connection failed: " . $conn->connect_error);
}

// List unassigned bookings
if (isset($_POST['list_all'])) {
    // Getting all bookings within the next 3 hours and that are unassigned
    $current_time = new DateTime();
    $three_hours_later = $current_time->modify('+3 hours');
    $formatted_time = $three_hours_later->format('Y-m-d H:i:s');
    $stmt = $conn->prepare("SELECT bookings.*, customers.name AS customer_name FROM bookings JOIN customers ON bookings.email = customers.email WHERE pickup_datetime <= ? AND status = 'unassigned'");
    $stmt->bind_param("s", $formatted_time);
    $stmt->execute();
    $result = $stmt->get_result();
    $bookings = $result->fetch_all(MYSQLI_ASSOC);
}

// Assign taxi
if (isset($_POST['assign_taxi'])) {
    // Update the status of a booking to 'assigned'
    $booking_reference = $_POST['booking_reference'];
    $stmt = $conn->prepare("UPDATE bookings SET status = 'assigned' WHERE booking_number = ? AND status = 'unassigned'");
    $stmt->bind_param("i", $booking_reference);
    if ($stmt->execute() && $stmt->affected_rows > 0) {
        echo "The booking request $booking_reference has been properly assigned.";
    } else {
        echo "Error: Unable to assign the booking or the booking might already be assigned.";
    }
}
?>

<!-- List all unassigned bookings within 3 hours -->
<!DOCTYPE html>
<html>
<head>
    <title>Admin Page of CabsOnline</title>
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

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        th, td {
            padding: 8px 12px;
            border: 1px solid #ddd;
        }

        th {
            background-color: #f2f2f2;
        }

        p {
            text-align: center;
            margin-top: 20px;
            margin-bottom: 20px;
        }

        input[type="text"], input[type="submit"] {
            width: 100%;
            padding: 10px;
            margin-bottom: 20px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }

        input[type="submit"] {
            background-color: #333;
            color: #fff;
            border: none;
            cursor: pointer;
            font-weight: bold;
        }

        input[type="submit"]:hover {
            background-color: #555;
        }

        label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
        }

    </style>
</head>
<body>
    <h1>Admin Page of CabsOnline</h1>
    <p>Click the button below to search for all unassigned booking requests with a pickup time within 3 hours:</p>
    <form method="post">
        <input type="submit" name="list_all" value="List All">
    </form>
    <?php
    if (isset($bookings) && count($bookings) > 0) {
        echo "<table>";
        echo "<tr><th>Booking Number</th><th>Customer Name</th><th>Passenger Name</th><th>Contact Phone</th><th>Pick-up Address</th><th>Destination Suburb</th><th>Pick-up Date/Time</th></tr>";
        foreach ($bookings as $booking) {
            $address = ($booking['unit_number'] ? $booking['unit_number'] . '/' : '') . $booking['street_number'] . ' ' . $booking['street_name'] . ', ' . $booking['suburb'];
            echo "<tr>
                <td>{$booking['booking_number']}</td>
                <td>{$booking['customer_name']}</td>
                <td>{$booking['passenger_name']}</td>
                <td>{$booking['contact_phone']}</td>
                <td>{$address}</td>
                <td>{$booking['destination_suburb']}</td>
                <td>{$booking['pickup_datetime']}</td>
            </tr>";
        }
        echo "</table>";
    } else if (isset($_POST['list_all'])) {
        echo "<p>No unassigned bookings found within the next 3 hours.</p>";
    }
    ?>
    <p>Input a reference and click "Assign Taxi" button to assign a taxi to that request:</p>
    <form method="post">
        <label for="booking_reference">Booking Reference:</label>
        <input type="text" name="booking_reference" id="booking_reference">
        <input type="submit" name="assign_taxi" value="Assign Taxi">
    </form>
</body>
</html>