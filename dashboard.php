<?php
require_once 'db_config.php';

// Query to get the count of students
$student_count_query = "SELECT COUNT(*) as total_students FROM personal_info";
$student_count_result = mysqli_query($conn, $student_count_query);
$student_count = (int) mysqli_fetch_assoc($student_count_result)['total_students'];

// Query to get the count of appointments
$appointment_count_query = "SELECT COUNT(*) as total_consultations FROM intv";
$appointment_count_result = mysqli_query($conn, $appointment_count_query);
$appointment_count = (int) mysqli_fetch_assoc($appointment_count_result)['total_consultations'];

// Query to get the count of medical records
$medical_count_query = "SELECT COUNT(*) as total_treatments FROM illmed";
$medical_count_result = mysqli_query($conn, $medical_count_query);
$medical_count = (int) mysqli_fetch_assoc($medical_count_result)['total_treatments'];

// Query to get the count of faculty members
$faculty_count_query = "SELECT COUNT(*) as total_faculty FROM faculty";
$faculty_count_result = mysqli_query($conn, $faculty_count_query);
$faculty_count = (int) mysqli_fetch_assoc($faculty_count_result)['total_faculty'];

// Close the database connection
mysqli_close($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - School Clinic DBMS</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        /* Styles from the previous code (unchanged) */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        html, body {
            height: 100%;
            font-size: 16px;
            line-height: 1.5;
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
            font-size: 20px;
        }
        header h1 {
    margin-left: 20px; /* Add margin above the heading */
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

.container {
    margin-left: 200px; /* Adjusted for new sidebar width */
    padding: 15px; /* Reduced padding */
    transition: margin-left 0.3s;
}

.main-content {
    background-color: rgba(173, 216, 230, 0.50);
    border-radius: 5px;
    padding: 15px; /* Reduced padding */
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    font-size: 14px; /* Reduced font size */
    text-align: center;
}

        .chart-header-container {
            background-color: rgba(0, 123, 255, 0.1); /* Light blue background */
            border-radius: 10px; /* Rounded corners */
            padding: 20px; /* Padding around the text */
            margin-bottom: 40px; /* Space below the header */
            text-align: center; /* Center the text */
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1); /* Subtle shadow */
        }

        .chart-header-container h2 {
            color: #007bff; /* Blue text color */
            margin: 0; /* Remove default margin */
        }

        #myPieChart {
            max-width: 800px; /* Increased max width for better view */
            margin: auto;
            margin-bottom: 40px;
        }

        footer {
            text-align: center;
            padding: 10px 0;
            background-color: green;
            color: white;
            width: 100%;
            border-radius: 10px;
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

        .btn:hover {
            background-color: #0056b3; /* Darker shade on hover */
        }
    
        a.btn1 {
    color: white;
    background-color: red;
    padding: 10px 10px; /* Reduced padding */
    margin-bottom: 5px;
  
    border-radius: 5px;
    text-decoration: none;
    font-size: 1em; /* Reduced font size */
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    transition: background-color 0.3s ease;
}

a.btn1:hover {
    background-color: darkred;
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

        /* Additional styling for responsiveness */
        @media (max-width: 768px) {
            .dashboard-card {
                width: calc(100% - 20px);
                margin: 10px 0;
            }
        }
    </style>
</head>
<body>
<div>
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

<div class="container">
    
    <header>
   
        <h1>SCHOOL CLINIC DASHBOARD</h1>
        
    </header>

    <div class="main-content">
        <!-- Clinic Metrics Overview -->
        <div class="chart-header-container">
            <h2>CLINIC METRICS OVERVIEW</h2>
        </div>

        <!-- Bar Chart -->
        <canvas id="myPieChart" width="400" height="400"></canvas>

        <!-- Button for navigating to detailed charts -->
        <div style="text-align: center; margin-top: 20px;">
        <a href="sched_calendar.php" class="btn">Schedule Calendar</a>
            <a href="charts.php" class="btn">View Detailed Charts</a>
           
            
        </div>
    </div>

    <footer>
        <p>&copy; 2024 School Clinic Electronic Health Record System. All rights reserved.</p>
    </footer>
</div>

<script>
    // Define color constants for maintainability
    const colors = {
        blue: 'rgba(54, 162, 235, 1.00)',
        green: 'rgba(75, 192, 192, 1.00)',
        red: 'rgba(255, 99, 132, 1.00)',
        yellow: 'rgba(255, 206, 86, 1.00)',
    };

    // Sample Chart.js code for Pie Chart
    var ctx = document.getElementById('myPieChart').getContext('2d');

    // Validate data before creating the chart
    var studentCount = <?php echo (int)$student_count; ?>;
    var appointmentCount = <?php echo (int)$appointment_count; ?>;
    var medicalCount = <?php echo (int)$medical_count; ?>;
    var facultyCount = <?php echo (int)$faculty_count; ?>;

    if (studentCount < 0 || appointmentCount < 0 || medicalCount < 0 || facultyCount < 0) {
        console.error('Invalid data counts');
    } else {
        var myPieChart = new Chart(ctx, {
            type: 'pie',
            data: {
                labels: ['Total Students', 'Total Consultations', 'Total Treatments', 'Total Faculty'],
                datasets: [{
                    label: 'Metrics',
                    data: [studentCount, appointmentCount, medicalCount, facultyCount],
                    backgroundColor: [
                        colors.blue,
                        colors.green,
                        colors.red,
                        colors.yellow
                    ],
                    borderColor: [
                        colors.blue,
                        colors.green,
                        colors.red,
                        colors.yellow
                    ],
                    borderWidth: 4
                }]
            },
            options: {
                responsive: true,
                animation: {
                    animateScale: true,
                    animateRotate: true
                },
                plugins: {
                    legend: {
                        position: 'top',
                    },
                    tooltip: {
                        callbacks: {
                            label: function(tooltipItem) {
                                return `${tooltipItem.label}: ${tooltipItem.raw.toLocaleString()}`; // Formats number with commas
                            }
                        }
                    }
                }
            }
        });
    }
</script>



</body>
</html>
