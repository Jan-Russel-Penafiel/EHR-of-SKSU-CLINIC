 

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>School Clinic Events</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box; /* Ensures padding and border are included in element's total width and height */
}

/* Additional Improvements */
html, body {
    height: 100%; /* Ensures the body takes the full height of the viewport */
    font-size: 16px; /* Base font size for better scaling */
    line-height: 1.5; /* Improved line height for readability */
}

/* A smoother scroll experience */
html {
    scroll-behavior: smooth; /* Smooth scrolling for anchor links */
}

/* Prevent overflow on small screens */
body {
    overflow-x: hidden; /* Prevents horizontal overflow */
}

/* Ensure all elements have consistent transition properties */
*,
*:before,
*:after {
    transition: all 0.3s ease; /* Smooth transition for all properties */
}

/* Target specific elements for consistent styling */
h1, h2, h3, h4, h5, h6 {
    margin-bottom: 10px; /* Consistent margin for headings */
}

p {
    margin-bottom: 15px; /* Consistent margin for paragraphs */
}

a {
    text-decoration: none; /* Remove underline from links */
    color: inherit; /* Inherit color for links */
    transition: color 0.3s; /* Smooth color transition for links */
}

a:hover {
    color: #1abc9c; /* Change link color on hover */
}
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
    height: 100px; /* Define a fixed height for better vertical alignment */
    border-radius: 8px;
    margin-top: -20px;
    margin-left: -20px;
    margin-right: -20px;
    font-size: 20px;
}



.sidebar {
    background-color: #2c3e50;
    width: 200px; /* Reduced width */
    height: 100vh;
    position: fixed;
    top: 0;
    left: 0;
    padding-top: 10px;
    overflow-y: auto;
    z-index: 1000;
    transition: width 0.3s;
}

.sidebar a {
    display: flex;
    align-items: center;
    padding: 8px; /* Reduced padding */
    text-decoration: none;
    color: white;
    font-size: 12px; /* Reduced font size */
}

.sidebar a:hover {
    background-color: green;
    border-radius: 5px;
}



.icon-container {
    width: 30px; /* Smaller width */
    height: 21.3px; /* Smaller height */
    display: flex;
    justify-content: center;
    align-items: center;
    background-color: lightblue;
    border-radius: 50%;
    margin-right: 3px; /* Smaller margin */
    font-size: 16px; /* Smaller font size */
}


.logout { background-color: #ff4c4c; } /* Red */
.dashboard { background-color: #4caf50; } /* Green */
.students { background-color: #2196f3; } /* Blue */
.faculty { background-color: #9c27b0; } /* Purple */
.consultations { background-color: #ff9800; } /* Orange */
.treatments { background-color: #f44336; } /* Dark Red */
.medical { background-color: #009688; } /* Teal */
.inventory { background-color: #673ab7; } /* Deep Purple */
.online-appointment { background-color: #3f51b5; } /* Indigo */
.search { background-color: #00bcd4; } /* Cyan */
.alert { background-color: #ffeb3b; } /* Yellow */
.events { background-color: #ff5722; } /* Deep Orange */
.account { background-color: #607d8b; } /* Blue Grey */
.reports { background-color: #795548; } /* Brown */

.icon-container i {
    color: #fff; /* White icon color */
}

.logo-container {
    text-align: center; /* Center the logo */
    margin-bottom: 20px; /* Space below the logo */
    margin-left: 55px; /* Automatically adds space on the left */
    margin-right: 0; /* Optional: Ensure no right margin */
    width: 80px; /* Reduced width for a more compact look */
    height: 80px; /* Reduced height for a more compact look */
    border-radius: 50%; /* Makes the container circular */
    overflow: hidden; /* Hides overflow */
    display: flex; /* Center the image within the circle */
    justify-content: center; /* Center horizontally */
    align-items: center; /* Center vertically */
    padding-left: 0; /* Remove left padding for perfect centering */
    background-color: #fff; /* Optional: background color for visibility */
    border: 2px solid green; /* Optional: border for better visibility */
}

.sksu-logo {
    width: 100%; /* Makes the logo responsive to container size */
    height: auto; /* Maintain aspect ratio */
}
 
        .resizer {
            width: 10px; /* Width of the resizer */
            cursor: ew-resize; /* Cursor style */
            position: fixed; /* Fixed position */
            top: 0;
            left: 220px; /* Initially set to match the sidebar width */
            height: 100vh; /* Full height */
            background-color: transparent; /* Invisible, can change for visibility */
            z-index: 1100; /* Above sidebar */
        }

        .main-content {
            margin: 0 auto;
            padding: 20px;
            max-width: 1200px;
            margin-left: 195px; /* Adjusted to fit sidebar */
            padding: 20px;
            margin-top: -5px;
        }
        
        .container {
            background-color: rgba(173, 216, 230, 0.50); /* Light blue background with 50% transparency */
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            padding: 20px;
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
      /* Input and textarea styling */
input[type="text"],
textarea {
    width: 100%;
    padding: 10px; /* Add padding for space */
    margin: 5px 0; /* Margin for spacing */
    border: 2px solid #ccc; /* Light border */
    border-radius: 4px; /* Rounded corners */
    box-sizing: border-box; /* Ensure padding is included in width */
    resize: none; /* Prevent resizing of textareas */
    overflow: auto; /* Allow scrolling if content exceeds height */
    font-size: 16px; /* Base font size for readability */
    color: #333; /* Text color for contrast */
}

/* Specific styles for text areas */
textarea {
    height: auto; /* Auto height to allow expansion based on content */
    height: 200px; /* Minimum height */
    vertical-align: top; /* Align text to the top */
}

/* Add focus state for inputs and text areas */
input[type="text"]:focus,
textarea:focus {
    border-color: #007bff; /* Change border color on focus */
    box-shadow: 0 0 5px rgba(0, 123, 255, 0.5); /* Subtle shadow effect */
    outline: none; /* Remove default outline */
}

/* Input date styling */
input[type="datetime-local"] {
    width: 100%;
    padding: 10px; /* Consistent padding */
    margin: 10px 0; /* Margin for spacing */
    border: 2px solid #ccc; /* Light border */
    border-radius: 4px; /* Rounded corners */
    height: auto; /* Set to auto for better responsiveness */
    font-size: 16px; /* Base font size for readability */
    color: #333; /* Text color for contrast */
}

/* Add focus state for date input */
input[type="datetime-local"]:focus {
    border-color: #007bff; /* Change border color on focus */
    box-shadow: 0 0 5px rgba(0, 123, 255, 0.5); /* Subtle shadow effect */
    outline: none; /* Remove default outline */
}

label {
            font-weight: bold;
            display: block;
            margin-top: 8px;
        }
    </style>
</head>
<body>

<div class="sidebar">
<div class="logo-container">
        <img src="sksu-logo.png" alt="SKSU Logo" class="sksu-logo">
        </div> 
        <a href="logout_admin.php"><span class="icon-container logout"><i class="fas fa-sign-out-alt"></i></span> Logout</a>
<a href="dashboard.php"><span class="icon-container dashboard"><i class="fas fa-tachometer-alt"></i></span> Dashboard</a>
<a href="display_faculty.php"><span class="icon-container faculty"><i class="fas fa-chalkboard-teacher"></i></span> Personnel Records</a>
<a href="student_information.php"><span class="icon-container students"><i class="fas fa-user-graduate"></i></span> Students Records</a>
<a href="display_intv.php"><span class="icon-container consultations"><i class="fas fa-comments"></i></span> Consultations Records</a>
<a href="display_illmed.php"><span class="icon-container treatments"><i class="fas fa-stethoscope"></i></span> Treatments Records</a>
<a href="display_medical.php"><span class="icon-container medical"><i class="fas fa-file-medical"></i></span> Medical History</a>
<a href="display_inventory.php"><span class="icon-container inventory"><i class="fas fa-capsules"></i></span> Medicine Inventory</a>
<a href="display_online_appointment.php"><span class="icon-container online-appointment"><i class="fas fa-laptop"></i></span> Online Appointments</a>
<a href="search1.php"><span class="icon-container search"><i class="fas fa-search"></i></span> Search Engine</a>
<a href="events.php"><span class="icon-container events"><i class="fas fa-calendar-alt"></i></span> Create Events</a>
<a href="alert.php"><span class="icon-container alert"><i class="fas fa-exclamation-triangle"></i></span> View Alerts</a>
<a href="account.php"><span class="icon-container account"><i class="fas fa-user-circle"></i></span> View Accounts</a>
<a href="reports.php"><span class="icon-container reports"><i class="fas fa-file-alt"></i></span> View Reports</a>

</div>

<div class="main-content">
    <div class="container">
        <header>
            <h1>CREATE EVENT</h1>
        </header>
        <form method="POST" action="">
            <label for="event_name">Event Name:</label>
            <input type="text" id="event_name" name="event_name" required placeholder="Enter the event name"></input>

            <label for="event_date">Event Date:</label>
            <input type="datetime-local" id="event_date" name="event_date" required placeholder="MM/DD/YYYY HH:MM AM/PM">

            <label for="event_location">Event Location:</label>
            <input type="text" id="event_location" name="event_location" placeholder="Enter the event location">

            <label for="event_details">Event Details:</label>
            <textarea id="event_details" name="event_details" placeholder="Enter additional details about the event"></textarea>

            <input type="submit" class="btn" value="Send Email Notification">
        </form>
    </div>
</div>

</body>
</html>