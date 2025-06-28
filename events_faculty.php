<?php
session_start();
$conn = mysqli_connect("localhost", "root", "", "ehrdb");
require 'vendor/autoload.php'; // Ensure you have installed PHPMailer via Composer

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Retrieve event details from the form
    $event_name = $_POST['event_name'];
    $event_date = $_POST['event_date']; // Expecting the user to provide the date in the desired format
    $event_location = $_POST['event_location'] ?? 'Not specified'; // Optional location
    $event_details = $_POST['event_details'] ?? 'No additional details provided.'; // Optional details

    // Convert the event date to the desired format
    $formatted_event_date = date("F j, Y g:i A", strtotime($event_date)); // month, day, year and 12-hour format

    // Fetch faculty email addresses
    $query = "SELECT GmailAccount, FirstName, LastName FROM faculty"; 
    $result = mysqli_query($conn, $query);

    if (mysqli_num_rows($result) > 0) {
        // Create a new PHPMailer instance
        $mail = new PHPMailer(true);

        try {
            // SMTP configuration for Gmail
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com'; // Use Gmail SMTP
            $mail->SMTPAuth = true;
            $mail->Username = 'sksuisulanschoolclinic@gmail.com'; // Your Gmail address
            $mail->Password = 'ukti coep ddhn tzhh'; // Your Gmail password or app password
            $mail->SMTPSecure = 'tls';
            $mail->Port = 587;

            // Email sender details
            $mail->setFrom('sksuisulanschoolclinic@gmail.com', 'Jan Russel E. Peñafiel');

            // Prepare the email subject and body
            $mail->Subject = "Upcoming Event: $event_name";
            $mail->isHTML(true); // Set email format to HTML

            // Send the event to each faculty member
            while ($row = mysqli_fetch_assoc($result)) {
                $recipient_email = $row['GmailAccount'];
                $first_name = $row['FirstName'];
                $last_name = $row['LastName'];

                // Validate email address
                if (filter_var($recipient_email, FILTER_VALIDATE_EMAIL)) {
                    // Set email body content
                    $mail->Body = "
                        <html>
                        <body>
                          <h2>Dear $first_name $last_name,</h2>
<p>We are pleased to inform you about an upcoming event at the school clinic. Your participation is greatly valued, and we believe this event will contribute to the well-being of our school community.</p>
<p><strong>Event Name:</strong> $event_name</p>
<p><strong>Date:</strong> $formatted_event_date</p>
<p><strong>Location:</strong> $event_location</p>
<p><strong>Details:</strong> $event_details</p>
<p>We sincerely hope you will be able to attend. Thank you for your continued support.</p>
<p>Best regards,<br>Your School Clinic Team</p>

                            <br>
                            <p>Best regards,<br>Jan Russel E. Peñafiel<br>School Clinic</p>
                        </body>
                        </html>
                    ";

                    // Add recipient email and send the message
                    $mail->addAddress($recipient_email);

                    if ($mail->send()) {
                        echo "Message sent to $recipient_email<br>";
                    } else {
                        echo "Failed to send message to $recipient_email: {$mail->ErrorInfo}<br>";
                    }

                    // Clear addresses for the next iteration
                    $mail->clearAddresses();
                } else {
                    echo "Invalid email address: $recipient_email<br>";
                }
            }
        } catch (Exception $e) {
            echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
        }
    } else {
        echo "No faculty members found.";
    }

    // Close the database connection
    mysqli_close($conn);
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Event for Personnel</title>
    <style>
 body {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; /* Clean font */

    font-size: 18px; /* Adjust to suit the design */
    background-color: lightblue; /* Soft background color */
    color: black; /* Darker text for better readability */
  
   
}
body {
    background-image: url(image.jpeg);
    background-repeat: no-repeat;
    background-size: 100% 100%;
    background-attachment: fixed;
    
}
header {
    background-color:green; /* Green for a fresh look */
    color: white;
    display: flex;
    justify-content: center; /* Center horizontally */
    align-items: center; /* Center vertically */
    text-align: center;
    padding: 20px 0;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1); /* Slight shadow for depth */
    height: 60px; /* Define a fixed height for better vertical alignment */
    border-radius: 8px;
    margin-top: -20px;
    margin-left: -20px;
    margin-right: -20px;
    font-size: 20px;
}

        .container {
            width: 150%;
            max-width: 1000px;
            margin: 0px auto;
            background-color: rgba(173, 216, 230, 0.50); /* Light blue background with 50% transparency */
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            margin-top: 20px;
        }

      

      
        label {
            font-weight: bold;
            display: block;
            margin-top: 8px;
        }

        input[type="text"], input[type="datetime-local"], textarea {
            width: 97%;
            padding: 10px;
            margin-top: 10px;
            border-radius: 4px;
            border: 1px solid #ccc;
            font-size: 16px;
        }

        

        .btn {
            display: inline-block;
            background-color: #28a745;
            color: white;
            padding: 10px 20px;
            text-align: center;
            border-radius: 4px;
            margin-top: 20px;
            cursor: pointer;
            font-size: 16px;
        }

        .btn:hover {
            background-color: #218838;
        }
        #backButton {
            display: inline-block; /* Allows padding and margin */
            background-color: #007bff; /* Blue background */
            color: white; /* White text */
            padding: 10px 20px; /* Padding around the text */
            font-size: 16px; /* Font size */
            border-radius: 5px; /* Rounded corners */
            text-decoration: none; /* Remove underline */
            transition: background-color 0.3s, transform 0.2s; /* Transition effects */
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2); /* Subtle shadow effect */
            margin: 10px; /* Margin for spacing */
            width: 3%;
            margin-left: -20px
            

        }

        #backButton:hover {
            background-color: green; /* Darker blue on hover */
            transform: translateY(-2px); /* Slight lift effect */
        }

        #backButton:active {
            transform: translateY(0); /* Reset lift effect on click */
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.5); /* Reduce shadow on click */
        }
    </style>
</head>
<body>

<div class="container">
    
    <header>
    <a id="backButton" href="display_faculty.php">Back</a>
    <h1>CREATE EVENT </h1>
  
    
    </header>
    <form method="POST" action="">
        <label for="event_name">Event Name:</label>
        <input type="text" id="event_name" name="event_name" required placeholder="Enter event name">

        <label for="event_date">Event Date:</label>
        <input type="datetime-local" id="event_date" name="event_date" required placeholder="MM/DD/YYYY HH:MM AM/PM">

        <label for="event_location">Event Location:</label>
        <input type="text" id="event_location" name="event_location" placeholder="Enter event location ">

        <label for="event_details">Event Details:</label>
        <textarea id="event_details" name="event_details" placeholder="Enter event details" rows="5"></textarea>

        <input type="submit" class="btn" value="Send Event Notification">
    </form>
</div>

</body>
</html>
