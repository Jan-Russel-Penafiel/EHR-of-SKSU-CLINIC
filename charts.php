<?php
session_start();



// Database connection
$conn = mysqli_connect("localhost", "root", "", "ehrdb");

// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Query to retrieve data from personal_info
$query = "SELECT Gender, Course, Yr, Section FROM personal_info";
$result = mysqli_query($conn, $query);

// Initialize arrays to hold the counts
$genderCounts = [];
$courseCounts = [];
$yearCounts = [];
$sectionCounts = [];

// Count occurrences for each category
while ($row = mysqli_fetch_assoc($result)) {
    $gender = $row['Gender'];
    $genderCounts[$gender] = isset($genderCounts[$gender]) ? $genderCounts[$gender] + 1 : 1;

    $course = $row['Course'];
    $courseCounts[$course] = isset($courseCounts[$course]) ? $courseCounts[$course] + 1 : 1;

    $year = $row['Yr'];
    $yearCounts[$year] = isset($yearCounts[$year]) ? $yearCounts[$year] + 1 : 1;

    $section = $row['Section'];
    $sectionCounts[$section] = isset($sectionCounts[$section]) ? $sectionCounts[$section] + 1 : 1;
}

// Query to retrieve illness data from illmed table
$illQuery = "SELECT illname FROM illmed";
$illResult = mysqli_query($conn, $illQuery);

// Initialize an array to hold illness counts
$illnessCounts = [];

// Count occurrences of each illness
while ($row = mysqli_fetch_assoc($illResult)) {
    $illname = $row['illname'];
    $illnessCounts[$illname] = isset($illnessCounts[$illname]) ? $illnessCounts[$illname] + 1 : 1;
}

// Prepare data for charts
$genders = array_keys($genderCounts);
$genderValues = array_values($genderCounts);

$courses = array_keys($courseCounts);
$courseValues = array_values($courseCounts);

$years = array_keys($yearCounts);
$yearValues = array_values($yearCounts);

$sections = array_keys($sectionCounts);
$sectionValues = array_values($sectionCounts);

// Prepare data for illness chart
$illnesses = array_keys($illnessCounts);
$illnessValues = array_values($illnessCounts);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Charts Dashboard</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2"></script>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; /* Clean font */
            font-size: 18px;
            background-color: lightblue;
            color: green;
            margin: 0;
            padding: 0;
            background-image: url(image.jpeg);
            background-repeat: no-repeat;
            background-size: cover;
            background-attachment: fixed;
          
        }

    

        .container {
            max-width: 1000px;
            margin: 20px auto;
            background-color: rgba(173, 216, 230, 0.50);
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.2);
            text-align: center;
        }

        canvas {
            width: 400px;
            margin: 10px auto;
        }

        .back-button {
            position: absolute;
            top: 20px;
            left: 20px;
            padding: 10px 20px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
        }

        .back-button:hover {
            background-color: #0056b3;
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
        }
    </style>
</head>
<body>

    <button class="back-button" onclick="window.location.href='dashboard.php';">Back</button>

    <div class="container">
        <header>
            <h1>STUDENT METRICS DASHBOARD</h1>
        </header>

        <h2>Gender Distribution</h2>
        <canvas id="genderChart" height="400"></canvas>

        <h2>Course Distribution</h2>
        <canvas id="courseChart" height="400"></canvas>

        <h2>Year Distribution</h2>
        <canvas id="yearChart" height="400"></canvas>

        <h2>Section Distribution</h2>
        <canvas id="sectionChart" height="400"></canvas>

        <h2>Treatment Distribution</h2>
        <canvas id="illnessChart" height="400"></canvas>
    </div>

    <script>
         const genderColors = [
        'rgba(75, 192, 192, 0.6)', // Male
        'rgba(255, 99, 132, 0.6)'  // Female
    ];
    const courseColors = [
    'rgba(153, 102, 255, 0.6)', // Course 1
    'rgba(255, 159, 64, 0.6)',  // Course 2
    'rgba(54, 162, 235, 0.6)',   // Course 3
    'rgba(255, 99, 132, 0.6)',   // Course 4
    'rgba(75, 192, 192, 0.6)',   // Course 5
    'rgba(255, 206, 86, 0.6)',   // Course 6
    'rgba(201, 203, 207, 0.6)',  // Course 7
    'rgba(255, 165, 0, 0.6)',    // Course 8
    'rgba(100, 149, 237, 0.6)'   // Course 9
];
const yearColors = [
    'rgba(255, 206, 86, 0.6)',  // Year 1
    'rgba(75, 192, 192, 0.6)',   // Year 2
    'rgba(255, 99, 132, 0.6)',   // Year 3
    'rgba(54, 162, 235, 0.6)'    // Year 4
];
const sectionColors = [
    'rgba(255, 159, 64, 0.6)',   // Section A
    'rgba(54, 162, 235, 0.6)',    // Section B
    'rgba(75, 192, 192, 0.6)',    // Section C
    'rgba(255, 206, 86, 0.6)',     // Section D
    'rgba(201, 203, 207, 0.6)',    // Section E
    'rgba(255, 99, 132, 0.6)',     // Section F
    'rgba(153, 102, 255, 0.6)'     // Section G
];
const illnessColors = [
    'rgba(255, 99, 132, 0.6)',   // Illness 1
    'rgba(54, 162, 235, 0.6)',    // Illness 2
    'rgba(75, 192, 192, 0.6)',    // Illness 3
    'rgba(255, 206, 86, 0.6)',    // Illness 4
    'rgba(153, 102, 255, 0.6)',   // Illness 5
    'rgba(255, 159, 64, 0.6)',    // Illness 6
    'rgba(201, 203, 207, 0.6)',   // Illness 7
    'rgba(255, 99, 71, 0.6)'      // Illness 8 (Tomato color)
];
        const genderCtx = document.getElementById('genderChart').getContext('2d');
        const courseCtx = document.getElementById('courseChart').getContext('2d');
        const yearCtx = document.getElementById('yearChart').getContext('2d');
        const sectionCtx = document.getElementById('sectionChart').getContext('2d');
        const illnessCtx = document.getElementById('illnessChart').getContext('2d');

        function createChart(ctx, labels, data, label, colors) {
            return new Chart(ctx, {
                type: 'pie', // Specify the pie chart type
                data: {
                    labels: labels,
                    datasets: [{
                        label: label,
                        data: data,
                        backgroundColor: colors,
                        borderColor: colors,
                        borderWidth: 3
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            position: 'top',
                        },
                        tooltip: {
                            callbacks: {
                                label: function(tooltipItem) {
                                    let dataset = tooltipItem.dataset;
                                    let total = dataset.data.reduce((accumulator, currentValue) => accumulator + currentValue, 0);
                                    let currentValue = dataset.data[tooltipItem.dataIndex];
                                    let percentage = ((currentValue / total) * 100).toFixed(2);
                                    return `${tooltipItem.label}: ${currentValue} (${percentage}%)`; // Display count and percentage
                                }
                            }
                        }
                    }
                }
            });
        }

        // Gender Chart
        createChart(genderCtx, 
            <?php echo json_encode($genders); ?>, 
            <?php echo json_encode($genderValues); ?>, 
            'Number of Individuals by Gender', 
            genderColors
        );

        // Course Chart
        createChart(courseCtx, 
            <?php echo json_encode($courses); ?>, 
            <?php echo json_encode($courseValues); ?>, 
            'Student Enrollment by Course', 
            courseColors
        );

        // Year Chart
        createChart(yearCtx, 
            <?php echo json_encode($years); ?>, 
            <?php echo json_encode($yearValues); ?>, 
            'Number of Students by Year', 
            yearColors
        );

        // Section Chart
        createChart(sectionCtx, 
            <?php echo json_encode($sections); ?>, 
            <?php echo json_encode($sectionValues); ?>, 
            'Number of Students by Section', 
            sectionColors
        );

        // Illness Chart
        createChart(illnessCtx, 
            <?php echo json_encode($illnesses); ?>, 
            <?php echo json_encode($illnessValues); ?>, 
            'Illnesses Count', 
            illnessColors
        );
    </script>
</body>
</html>
