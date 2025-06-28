<?php
session_start();
require_once 'db_config.php';

// Array of background images
$background_images = [
   
    'image5.jpg',
    'image7.jpeg',
    'image8.jpeg',
    'image9.jpeg',
    'image10.jpeg',
    // Add more images as needed
];

// Select a random background image for each page load
$background_image = $background_images[array_rand($background_images)];


// Array of inspirational quotes
$quotes = [
    "The best way to predict the future is to create it.",
    "Success is not the key to happiness. Happiness is the key to success.",
    "Believe you can and you're halfway there.",
    "Your limitation—it's only your imagination.",
    "Push yourself, because no one else is going to do it for you.",
    "Dream big and dare to fail.",
    "You miss 100% of the shots you don't take.",
    "Strive for progress, not perfection.",
    "Act as if what you do makes a difference. It does.",
    "The future belongs to those who believe in the beauty of their dreams.",
    "Don't watch the clock; do what it does. Keep going.",
    "Your only limit is you.",
    "Great things never come from comfort zones.",
    "Believe in yourself and all that you are."
];

// Array of dynamic messages
$messages = [
    "Make a difference!",
    "Inspire others!",
    "Lead with vision!",
    "Build success together!",
    "Innovation starts with you!",
    "Shape the future!",
    "Drive our progress!",
    "Inspire excellence!",
    "Lead by example!"
];

// Randomly select a quote and a message
$random_quote = $quotes[array_rand($quotes)];
$random_message = $messages[array_rand($messages)];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet">
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; /* Clean font */
            background: url('<?php echo $background_image; ?>') no-repeat center center fixed;
            background-size: cover;
            transition: background-color 0.5s ease;
            color: <?php echo $_SESSION['dark_mode'] === 'on' ? '#fff' : '#000'; ?>;
            background-color: <?php echo $_SESSION['dark_mode'] === 'on' ? '#121212' : '#f4f4f4'; ?>;
            
        }

        
        .container {
    max-width: 1500px; /* Set maximum width for the container */
    height: 93.8vh; /* Full height of the viewport */
    margin: 0 auto; /* Center the container */
    background: rgba(173, 216, 230, 0.17); /* Optional: semi-transparent background */
    border-radius: 0px; /* Rounded corners */  
    padding: 20px; /* Padding inside the container */
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3); /* Shadow for depth */
    overflow: hidden; /* Prevent scrolling */
    overflow-y: hidden; /* Prevent vertical scrolling */
}



        .logo {
            font-size: 1.5em;
            font-weight: bold;
            color: #fff;
            text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.7);
        }

        /* Main Content */
        .main-content {
            margin-top: 0px; /* Leave space for the nav */
            display: flex;
            flex-direction: column; /* Stack items vertically */
            align-items: center;
            flex-grow: 1;
            padding: 20px;
            font-size: 25px;
            overflow: hidden; /* Prevent scrolling */
        }
      h1 {
    font-size: 3.4em; /* Increased font size for more impact */
    margin: 0 0 0.5em; /* Maintain existing margins */
    line-height: 1.2; /* Keep line height for readability */
    text-align: center; /* Center-aligned for balance */
    text-shadow: 4px 4px 10px rgba(0, 0, 0, 0.8), 0 0 20px rgba(0, 255, 255, 0.5); /* Enhanced text shadow */
    
    color: black; /* Set text color to black */
    padding: 10px; /* Add some padding */
    border-radius: 5px; /* Rounded corners for the background */
    animation: glow 2s infinite alternate; /* Glow animation */
    font-weight: bolder; /* Make the font bold */
    text-transform: uppercase;
}

/* Keyframes for glow animation */
@keyframes glow {
    0% {
        text-shadow: 0 0 5px rgba(0, 255, 255, 0.3), 0 0 10px rgba(0, 255, 255, 0.3); /* Softer glow */
    }
    100% {
        text-shadow: 0 0 15px rgba(0, 255, 255, 0.6), 0 0 25px rgba(0, 255, 255, 0.6); /* Brighter glow */
    }
}




        p {
            font-size: 1.3em;
            margin-bottom: 20px;
            text-shadow: 1px 1px 3px rgba(0, 0, 0, 0.5);
        }

        a.button {
    color: #fff;
    background-color: #007bff;
    padding: 5px 10px; /* Reduced padding */
    margin-bottom: 5px;
    border-radius: 5px;
    text-decoration: none;
    font-size: 1em; /* Reduced font size */
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    transition: background-color 0.3s ease;
}

a.button:hover {
    background-color: #0056b3;
}

a.btn {
    color: white;
    background-color: red;
    padding: 5px 10px; /* Reduced padding */
    margin-bottom: 5px;
    border-radius: 5px;
    text-decoration: none;
    font-size: 1em; /* Reduced font size */
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    transition: background-color 0.3s ease;
}

a.btn:hover {
    background-color: darkred;
}

        /* User Information Section */
        .user-info {
            margin-bottom: 0px;
            text-align: left;
        }

        .user-info p {
            margin: 5px 0;
        }

        .user-info i {
            margin-right: 10px;
        }

        /* Quote Section */
        .quote {
            font-size: 1.4em;
            font-style: italic;
            margin: 20px 0;
            text-align: center;
            max-width: 1250px; /* Limit width of quote */
            padding: 20px;
            border: 1px solid #fff;
            border-radius: 10px;
            background-color: rgba(0, 0, 0, 0.7);
            color: #fff;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.5);
        }
    </style>
</head>
<body>

    <div class="container"> <!-- Container for main content -->
        <div class="main-content">
            <header>
            <h1><?php echo htmlspecialchars($random_message); ?> Admin!</h1>
            </header>
            <!-- User Profile Information -->
            <div class="user-info">
                <p><i class="fas fa-user"></i> Username: <strong><?php echo htmlspecialchars($_SESSION['username'] ?? 'admin'); ?></strong></p>
                <p><i class="fas fa-envelope"></i> Email: <strong><?php echo htmlspecialchars($_SESSION['email'] ?? 'admin@sksu.edu.ph'); ?></strong></p>
                <p><i class="fas fa-user-shield"></i> Role: <strong><?php echo htmlspecialchars($_SESSION['role'] ?? 'Admin'); ?></strong></p>
            </div>

            <!-- Dynamic Inspirational Quote -->
            <div class="quote">
                "<?php echo htmlspecialchars($random_quote); ?>"
            </div>
            <a class="button" href="dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a>


        </div>
    </div> <!-- End of container -->
</body>
</html>
