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

// Fetch health resources (could be articles, videos, quizzes, etc.)
// This is a placeholder array. Replace it with actual database queries if needed.
$healthResources = [
    [
        'title' => 'Understanding Hypertension',
        'type' => 'Article',
        'link' => 'https://www.heart.org/en/health-topics/high-blood-pressure/understanding-blood-pressure-readings',
        'description' => 'Learn about what hypertension is, its causes, symptoms, and how to manage it.'
    ],
    [
        'title' => 'Healthy Eating Tips',
        'type' => 'Video',
        'link' => 'https://www.youtube.com/watch?v=ZD3aeGgchmg',
        'description' => 'Watch this video for practical tips on healthy eating habits.'
    ],
    [
        'title' => 'Quiz: Test Your Nutrition Knowledge',
        'type' => 'Quiz',
        'link' => 'https://www.healthshots.com/quiz/nutrition-quiz/',
        'description' => 'Take this quiz to see how much you know about nutrition and healthy eating!'
    ],
    [
        'title' => 'Stress Management Techniques',
        'type' => 'Article',
        'link' => 'https://www.helpguide.org/mental-health/stress/stress-management',
        'description' => 'Explore various techniques for managing stress effectively.'
    ],
    [
        'title' => 'The Importance of Regular Exercise',
        'type' => 'Video',
        'link' => 'https://www.youtube.com/watch?v=-lxg-35Xo_o',
        'description' => 'Find out why regular exercise is crucial for your overall health and well-being.'
    ],
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Health Resources</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;700&display=swap" rel="stylesheet">
    <style>

header {
            background-color: green;
            color: white;
            display: flex;
            justify-content: center;
            align-items: center;
            text-align: center;
            padding: 20px 0;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            height: 100px;
            border-radius: 10px;
        }

        body { 
           
            margin: 0; 
            padding: 20px; 
            display: flex; 
            justify-content: center; 
            align-items: center; 
            flex-direction: column; 
        }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; /* Clean font */
            font-size: 18px;
            background-image: url(image.jpeg);
            background-repeat: no-repeat;
            background-size: cover;
            background-attachment: fixed;
            color: black;
        }
        .resources-container {
            background-color: lightblue; 
            padding: 30px; 
            border-radius: 10px; 
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1); 
            max-width: 700px; 
            width: 88%; 
        }
        h1 {
            text-align: center;
            color: white; 
            font-size: 36px; 
            margin-bottom: 20px; 
        }
        .resource-item {
            border: 1px solid #007bff; 
            border-radius: 5px; 
            padding: 15px; 
            margin-bottom: 15px; 
            transition: background 0.3s ease; 
        }
        .resource-item:hover {
            background-color: #f1f1f1; 
        }
        .resource-title {
            font-size: 20px; 
            color: #343a40; 
        }
        .resource-description {
            font-size: 16px; 
            color: #666; 
        }
        .resource-link {
            display: inline-block; 
            margin-top: 10px; 
            padding: 8px 12px; 
            background: linear-gradient(90deg, #28a745, #218838); 
            color: white; 
            border-radius: 5px; 
            text-decoration: none; 
            transition: background 0.3s ease; 
        }
        .resource-link:hover {
            background: linear-gradient(90deg, #218838, #1e7e34); 
        }

        .btn {
    display: inline-block;
    background-color: #007bff; /* Button color */
    color: white; /* Text color */
    padding: 10px 20px; /* Padding around the text */
    border-radius: 5px; /* Rounded corners */
    text-decoration: none; /* Remove underline */
    transition: background-color 0.3s; /* Transition for hover effect */
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
            width: 13%;
            margin-left: -0.5px;
            margin-top: -20px;
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
    <div class="resources-container">
        <header>
        <h1>HEALTH RESOURCES</h1>
        </header>
        <?php foreach ($healthResources as $resource): ?>
            <div class="resource-item">
                <div class="resource-title"><?php echo htmlspecialchars($resource['title']); ?> (<?php echo htmlspecialchars($resource['type']); ?>)</div>
                <div class="resource-description"><?php echo htmlspecialchars($resource['description']); ?></div>
                <a href="<?php echo htmlspecialchars($resource['link']); ?>" class="resource-link" target="_blank">Read More</a>
            </div>
        <?php endforeach; ?>
       
    </div>
</body>
</html>

<?php
// Close database connection
mysqli_close($conn);
?>
