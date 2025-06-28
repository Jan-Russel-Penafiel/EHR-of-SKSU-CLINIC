<?php
// Database connection
$conn = mysqli_connect("localhost", "root", "", "ehrdb");

// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Get the IDNumber from the URL parameter for auto-fill
$searchIDNumber = isset($_GET['IDNumber']) ? htmlspecialchars($_GET['IDNumber']) : '';

// Initialize variables
$userInfo = null;
$facultyRecords = []; // Updated to store multiple faculty records
$selectedYear = date('Y'); // Default to current year

// Get the IDNumber from the URL parameter if available, or use POST if form is submitted
if (isset($_GET['IDNumber']) && !empty($_GET['IDNumber'])) {
    $searchIDNumber = htmlspecialchars($_GET['IDNumber']);
} elseif (isset($_POST['IDNumber']) && !empty($_POST['IDNumber'])) {
    $searchIDNumber = htmlspecialchars($_POST['IDNumber']);
}

// Get selected year from POST (if available)
if (isset($_POST['selectedYear'])) {
    $selectedYear = mysqli_real_escape_string($conn, $_POST['selectedYear']);
}

// Query to retrieve user information from the personal_info table based on IDNumber and selected year
if ($searchIDNumber) {
    $queryPersonalInfo = "
        SELECT Num, FirstName, LastName, IDNumber, BirthDate, Age, Gender, Course, Yr, Section, years, ProfilePicture 
        FROM personal_info 
        WHERE IDNumber = '$searchIDNumber' 
        AND years = '$selectedYear'";

    $resultPersonalInfo = mysqli_query($conn, $queryPersonalInfo);

    if ($resultPersonalInfo && mysqli_num_rows($resultPersonalInfo) > 0) {
        $userInfo = mysqli_fetch_assoc($resultPersonalInfo);
    } else {
        $userInfo = false;
    }
}

// Fetch intervention records if user info is found
$resultIntv = null;
if ($userInfo && $userInfo['IDNumber'] != "N/A") {
    $idNumber = $userInfo['IDNumber'];
    $queryIntv = "
        SELECT IDNumber, what_you_do, what_is_your_existing_desease, have_you_a_family_history_desease, have_you_a_allergy, years 
        FROM intv 
        WHERE IDNumber = '$idNumber' 
        AND years = '$selectedYear'";

    $resultIntv = mysqli_query($conn, $queryIntv);
}

// Fetch illness medication records if user info is found
$resultIllmed = null;
if ($userInfo && $userInfo['IDNumber'] != "N/A") {
    $idNumber = $userInfo['IDNumber'];
    $queryIllmed = "
        SELECT IDNumber, IllName, MedName, Prescription, Temperature, BloodPressure, Appointment_Date, years 
        FROM illmed 
        WHERE IDNumber = '$idNumber' 
        AND years = '$selectedYear'";

    $resultIllmed = mysqli_query($conn, $queryIllmed);
}

// Fetch faculty data based on IDNumber and selected year (allow duplicates)
if ($searchIDNumber) {
    $queryFaculty = "
        SELECT Num, IDNumber, Rank, FirstName, LastName, GmailAccount, Department, Position, Complains, Temperature, BloodPressure, 
               HeartRate, RespiratoryRate, Height, Weight, AppointmentDate, ProfilePicture, years
        FROM faculty
        WHERE IDNumber = '$searchIDNumber'
          AND years = '$selectedYear'";

    $resultFaculty = mysqli_query($conn, $queryFaculty);

    if ($resultFaculty && mysqli_num_rows($resultFaculty) > 0) {
        // Fetch all rows as an associative array
        while ($row = mysqli_fetch_assoc($resultFaculty)) {
            $facultyRecords[] = $row; // Store each record in the array
        }
    } else {
        // Handle case where no faculty information is found
        $facultyRecords = []; // No data found for faculty
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search Student</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        :root {
            --primary-color: #2ecc71;
            --secondary-color: #27ae60;
            --accent-color: #3498db;
            --text-color: #2c3e50;
            --light-gray: #f5f6fa;
            --white: #ffffff;
            --shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            --transition: all 0.3s ease;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }

        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            color: var(--text-color);
            line-height: 1.6;
        }

        header {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: var(--white);
            padding: 1.5rem;
            border-radius: 0 0 20px 20px;
            margin-bottom: 2rem;
            box-shadow: var(--shadow);
            text-align: center;
        }

        header h1 {
            font-size: 2rem;
            font-weight: 600;
            margin: 0;
        }

        .main-content {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 1rem;
        }

        .search-form {
            background: var(--white);
            padding: 2rem;
            border-radius: 15px;
            box-shadow: var(--shadow);
            margin-bottom: 2rem;
            transition: var(--transition);
        }

        .search-form:hover {
            transform: translateY(-5px);
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        input[type="text"],
        select {
            width: 100%;
            padding: 1rem;
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            font-size: 1rem;
            transition: var(--transition);
            background: var(--light-gray);
        }

        input[type="text"]:focus,
        select:focus {
            border-color: var(--accent-color);
            outline: none;
            box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.2);
        }

        button {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: var(--white);
            padding: 1rem 2rem;
            border: none;
            border-radius: 10px;
            font-size: 1rem;
            font-weight: 500;
            cursor: pointer;
            width: 100%;
            transition: var(--transition);
        }

        button:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }

        .info-card {
            background: var(--white);
            padding: 2rem;
            border-radius: 15px;
            box-shadow: var(--shadow);
            margin-bottom: 2rem;
            transition: var(--transition);
        }

        .info-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
        }

        .info-card h2 {
            color: var(--primary-color);
            margin-bottom: 1.5rem;
            font-weight: 600;
            border-bottom: 2px solid var(--light-gray);
            padding-bottom: 0.5rem;
        }

        .profile-image {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            object-fit: cover;
            margin: 0 auto 1.5rem;
            display: block;
            border: 4px solid var(--primary-color);
            box-shadow: var(--shadow);
        }

        .info-card p {
            margin-bottom: 1rem;
            display: flex;
            justify-content: space-between;
            padding: 0.5rem 0;
            border-bottom: 1px solid var(--light-gray);
        }

        .info-card p strong {
            color: var(--text-color);
            font-weight: 500;
        }

        .nav-buttons {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1rem;
            margin-top: 2rem;
        }

        .nav-button {
            background: linear-gradient(135deg, var(--accent-color), #2980b9);
            color: var(--white);
            padding: 1rem;
            border-radius: 10px;
            text-decoration: none;
            text-align: center;
            transition: var(--transition);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            font-weight: 500;
        }

        .nav-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }

        .nav-button i {
            font-size: 1.2rem;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            header {
                border-radius: 0;
                margin-bottom: 1rem;
            }

            header h1 {
                font-size: 1.5rem;
            }

            .main-content {
                padding: 0.5rem;
            }

            .search-form,
            .info-card {
                padding: 1.5rem;
            }

            .profile-image {
                width: 120px;
                height: 120px;
            }

            .nav-buttons {
                grid-template-columns: 1fr;
            }
        }

        /* Animation */
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .search-form,
        .info-card {
            animation: fadeIn 0.5s ease-out;
        }

        /* Custom Scrollbar */
        ::-webkit-scrollbar {
            width: 8px;
        }

        ::-webkit-scrollbar-track {
            background: var(--light-gray);
        }

        ::-webkit-scrollbar-thumb {
            background: var(--primary-color);
            border-radius: 4px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: var(--secondary-color);
        }
    </style>
</head>
<body>
    <header>
        <h1><i class="fas fa-search"></i> Health Records Search</h1>
    </header>

    <div class="main-content">
        <div class="search-form">
            <form method="POST" action="search.php">
                <div class="form-group">
                    <input type="text" name="IDNumber" id="IDNumber" value="<?php echo $searchIDNumber; ?>" placeholder="Enter ID Number" required>
                </div>
                <div class="form-group">
                    <select id="selectedYear" name="selectedYear">
                        <?php for ($year = 2024; $year <= 2100; $year++): ?>
                            <option value="<?php echo $year; ?>" <?php echo ($selectedYear == $year) ? 'selected' : ''; ?>>
                                <?php echo $year; ?>
                            </option>
                        <?php endfor; ?>
                    </select>
                </div>
                <button type="submit">Search</button>
                <style>
                .qr-scanner-btn {
                    display: block;
                    width: 100%;
                    background: linear-gradient(135deg, var(--accent-color), #2980b9);
                    color: var(--white);
                    padding: 1rem;
                    text-align: center;
                    text-decoration: none;
                    font-size: 1rem;
                    margin-top: 1rem;
                    border-radius: 10px;
                    transition: var(--transition);
                    font-weight: 500;
                }

                .qr-scanner-btn:hover {
                    transform: translateY(-2px);
                    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
                }

                .qr-scanner-btn i {
                    margin-right: 0.5rem;
                }

                @media (max-width: 768px) {
                    .search-form {
                        padding: 1.5rem;
                        margin-bottom: 1rem;
                    }

                    .form-group {
                        margin-bottom: 1rem;
                    }

                    input[type="text"],
                    select,
                    button,
                    .qr-scanner-btn {
                        padding: 0.8rem;
                        font-size: 1rem;
                    }

                    .info-card {
                        padding: 1.5rem;
                        margin-bottom: 1rem;
                    }

                    .info-card p {
                        flex-direction: column;
                        gap: 0.3rem;
                    }

                    .info-card p strong {
                        display: block;
                        margin-bottom: 0.2rem;
                    }
                }
                </style>
                <a href="qr_scanner.php" class="qr-scanner-btn"><i class="fas fa-qrcode"></i> Scan QR Code</a>
            </form>
        </div>

        <?php if ($userInfo && is_array($userInfo)): ?>
            <div class="info-card">
                <h2>Student Information</h2>
                    <img src="<?php echo $userInfo['ProfilePicture'] != "N/A" ? htmlspecialchars($userInfo['ProfilePicture']) : 'default-profile.png'; ?>" alt="Profile Picture" class="profile-image">
                <p><strong>First Name:</strong> <?php echo htmlspecialchars($userInfo['FirstName']); ?></p>
                <p><strong>Last Name:</strong> <?php echo htmlspecialchars($userInfo['LastName']); ?></p>
                <p><strong>ID Number:</strong> <?php echo htmlspecialchars($userInfo['IDNumber']); ?></p>
                <p><strong>Birth Date:</strong> <?php echo ($userInfo['BirthDate'] != "N/A") ? date("F d, Y", strtotime($userInfo['BirthDate'])) : "N/A"; ?></p>
                <p><strong>Age:</strong> <?php echo htmlspecialchars($userInfo['Age']); ?></p>
                <p><strong>Gender:</strong> <?php echo htmlspecialchars($userInfo['Gender']); ?></p>
                <p><strong>Course:</strong> <?php echo htmlspecialchars($userInfo['Course']); ?></p>
                <p><strong>Year:</strong> <?php echo htmlspecialchars($userInfo['Yr']); ?></p>
                <p><strong>Section:</strong> <?php echo htmlspecialchars($userInfo['Section']); ?></p>
            </div>
        <?php else: ?>
            <p>No student information found.</p>
        <?php endif; ?>

        <?php if ($resultIntv && mysqli_num_rows($resultIntv) > 0): ?>
            <div class="info-card">
                <h2>Consultation Records</h2>
                <ul>
                    <?php while ($rowIntv = mysqli_fetch_assoc($resultIntv)): ?>
                        <hr>
                        <li><strong>Activities:</strong> <?php echo htmlspecialchars($rowIntv['what_you_do']); ?></li>
                        <li><strong>Existing Disease:</strong> <?php echo htmlspecialchars($rowIntv['what_is_your_existing_desease']); ?></li>
                        <li><strong>Family History:</strong> <?php echo htmlspecialchars($rowIntv['have_you_a_family_history_desease']); ?></li>
                        <li><strong>Allergies:</strong> <?php echo htmlspecialchars($rowIntv['have_you_a_allergy']); ?></li>
                    <?php endwhile; ?>
                </ul>
            </div>
        <?php endif; ?>

        <?php if ($resultIllmed && mysqli_num_rows($resultIllmed) > 0): ?>
            <div class="info-card">
                <h2>Treatment Records</h2>
                <ul>
                    
                    <?php while ($rowIllmed = mysqli_fetch_assoc($resultIllmed)): ?>
                        <hr>

                        <li><strong>Illness Name:</strong> <?php echo htmlspecialchars($rowIllmed['IllName']); ?></li>
                        <li><strong>Medication Name:</strong> <?php echo htmlspecialchars($rowIllmed['MedName']); ?></li>
                        <li><strong>Prescription:</strong> <?php echo htmlspecialchars($rowIllmed['Prescription']); ?></li>
                        <li><strong>Temperature:</strong> <?php echo htmlspecialchars($rowIllmed['Temperature']); ?> °C</li>
                        <li><strong>Blood Pressure:</strong> <?php echo htmlspecialchars($rowIllmed['BloodPressure']); ?> mmHg</li>
                        <li><strong>Appointment Date:</strong> <?php echo date("F d, Y g:i A", strtotime($rowIllmed['Appointment_Date'])); ?></li>
                    <?php endwhile; ?>
                    
                </ul>
              
            </div>
        <?php endif; ?>

        <?php if (!empty($facultyRecords)): ?>
            <?php $firstRecord = true; ?>
            <?php foreach ($facultyRecords as $facultyInfo): ?>
                <div class="info-card">
                    <h2>Personnel Information</h2>
                    <?php if ($firstRecord && !empty($facultyInfo['ProfilePicture'])): ?>
                        <img src="<?php echo htmlspecialchars($facultyInfo['ProfilePicture']); ?>" alt="Faculty Profile Picture" class="profile-image">
                        <?php $firstRecord = false; ?>
                    <?php endif; ?>
                    <p><strong>Full Name:</strong> <?php echo htmlspecialchars($facultyInfo['FirstName']) . " " . htmlspecialchars($facultyInfo['LastName']); ?></p>
                    <p><strong>ID Number:</strong> <?php echo htmlspecialchars($facultyInfo['IDNumber']); ?></p>
                    <p><strong>Academic Rank:</strong> <?php echo htmlspecialchars($facultyInfo['Rank']); ?></p>
                    <p><strong>Gmail Account:</strong> <?php echo htmlspecialchars($facultyInfo['GmailAccount']); ?></p>
                    <p><strong>Department:</strong> <?php echo htmlspecialchars($facultyInfo['Department']); ?></p>
                    <p><strong>Position:</strong> <?php echo htmlspecialchars($facultyInfo['Position']); ?></p>
                    <p><strong>Complaints:</strong> <?php echo htmlspecialchars($facultyInfo['Complains']); ?></p>
                    <p><strong>Temperature:</strong> <?php echo htmlspecialchars($facultyInfo['Temperature']); ?> °C</p>
                    <p><strong>Blood Pressure:</strong> <?php echo htmlspecialchars($facultyInfo['BloodPressure']); ?> mmHg</p>
                    <p><strong>Heart Rate:</strong> <?php echo htmlspecialchars($facultyInfo['HeartRate']); ?> bpm</p>
                    <p><strong>Respiratory Rate:</strong> <?php echo htmlspecialchars($facultyInfo['RespiratoryRate']); ?> breaths/min</p>
                    <p><strong>Height:</strong> <?php echo htmlspecialchars($facultyInfo['Height']); ?> cm</p>
                    <p><strong>Weight:</strong> <?php echo htmlspecialchars($facultyInfo['Weight']); ?> kg</p>
                    <p><strong>Appointment Date:</strong> <?php echo date("F d, Y g:i A", strtotime($facultyInfo['AppointmentDate'])); ?></p>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>No personnel information found.</p>
        <?php endif; ?>

        <div class="nav-buttons">
            <a href="insert_intv.php?IDNumber=<?php echo htmlspecialchars($userInfo['IDNumber']); ?>" class="nav-button">
                <i class="fas fa-plus-circle"></i>Student Consultation
            </a>

            <a href="insert_illmed.php?IDNumber=<?php echo htmlspecialchars($userInfo['IDNumber']); ?>" class="nav-button">
                <i class="fas fa-prescription-bottle-alt"></i>Student Treatment
            </a>
            <a href="insert_faculty1.php?IDNumber=<?php echo htmlspecialchars($facultyInfo['IDNumber']); ?>" class="nav-button">
                <i class="fas fa-edit"></i> Personnel Records
            </a>

            <a href="insert_faculty.php?IDNumber=<?php echo htmlspecialchars($facultyInfo['IDNumber']); ?>" class="nav-button">
                <i class="fas fa-users"></i>Personnel Records
            </a>
        </div>
    </div>
</body>
</html>