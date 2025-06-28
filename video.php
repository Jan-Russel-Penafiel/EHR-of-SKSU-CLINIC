<?php
session_start(); // Start the session

// Check if the user is logged in
if (!isset($_SESSION['GmailAccount'])) {
    header('Location: login.html'); // Redirect to login page if not logged in
    exit();
}

// Database connection
$conn = mysqli_connect("localhost", "root", "", "ehrdb");

// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Get the user's email from the session
$GmailAccount = $_SESSION['GmailAccount'];

// Example symptom evaluation (this would be replaced with actual logic)
$symptomSeverity = 7; // Example severity score out of 10 (1 = mild, 10 = severe)

// Determine if a consultation is needed
$consultationNeeded = $symptomSeverity >= 5; // Suggest a consultation if severity is 5 or more

if ($consultationNeeded) {
    // Query to retrieve user information from the personal_info table
    $queryPersonalInfo = "SELECT Num, FirstName, LastName FROM personal_info WHERE GmailAccount = '$GmailAccount'";
    $resultPersonalInfo = mysqli_query($conn, $queryPersonalInfo);

    if ($resultPersonalInfo && mysqli_num_rows($resultPersonalInfo) > 0) {
        $userInfo = mysqli_fetch_assoc($resultPersonalInfo);
        $Num = $userInfo['Num'];
        $FirstName = $userInfo['FirstName'];
        $LastName = $userInfo['LastName'];
    } else {
        // Handle case where no user information is found
        die("User not found.");
    }

    // Here you would typically get a Zoom meeting link or create a WebRTC session
    $zoomMeetingLink = "https://zoom.us/join?someUniqueMeetingId"; // Replace with actual meeting link or WebRTC session
} else {
    $zoomMeetingLink = null; // No consultation needed
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Telemedicine Video Consultation</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;700&display=swap" rel="stylesheet">
    <style>
        body {
           font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; /* Clean font */

    background-color: lightblue; /* Soft background color */
            display: flex;
            justify-content: center;
            align-items: center;
            flex-direction: column;
            margin: 0;
            padding: 20px;
            height: 100vh; /* Full viewport height */
        }

  
   

body {
    background-image: url(image.jpeg);
    background-repeat: no-repeat;
    background-size: 100% 100%;
    background-attachment: fixed;
    
}
        .container {
            background-color: lightblue;
            padding: 30px; /* Increased padding */
            border-radius: 10px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.2); /* Softer shadow */
            max-width: 500px; /* Slightly wider container */
            width: 88%;
            text-align: center;
            transition: transform 0.3s; /* Smooth hover effect */
            margin-bottom: 50px;
           
        }
        .container:hover {
            transform: translateY(-5px); /* Lift effect on hover */
        }
        h1 {
            color: #007bff; /* Primary color for headings */
            margin-bottom: 20px; /* Space below the heading */
        }
        p {
            color: #333; /* Darker text for better readability */
            margin-bottom: 20px; /* Space below paragraphs */
            line-height: 1.6; /* Increased line height for readability */
        }
        button {
            padding: 12px 25px; /* Larger padding for buttons */
            background-color: #007bff; /* Primary color */
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px; /* Slightly larger font */
            transition: background-color 0.3s, transform 0.3s; /* Smooth transition */
        }
        button:hover {
            background-color: #0056b3; /* Darker blue on hover */
            transform: scale(1.05); /* Slightly enlarge button on hover */
        }
        a {
            text-decoration: none; /* Remove underline from links */
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
            width: 13.5%;
        }

        #backButton:hover {
            background-color: #0056b3; /* Darker blue on hover */
            transform: translateY(-2px); /* Slight lift effect */
        }

        #backButton:active {
            transform: translateY(0); /* Reset lift effect on click */
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2); /* Reduce shadow on click */
        }
    </style>
</head>
<body>

<div class="container">
    <h1>Telemedicine Video Consultation</h1>
    <?php if ($consultationNeeded): ?>
        <p>Hello, <?php echo htmlspecialchars($FirstName . " " . $LastName); ?>. Based on your symptoms, we suggest scheduling a telemedicine consultation with our health professionals.</p>
        <a href="<?php echo $zoomMeetingLink; ?>" target="_blank">
            <button>Join Consultation</button>
        </a>
    <?php else: ?>
        <p>Your symptoms do not require immediate consultation. If you have further concerns, please contact the clinic.</p>
    <?php endif; ?>
</div>

</body>
</html>

<?php
// Close database connection
mysqli_close($conn);
?>
