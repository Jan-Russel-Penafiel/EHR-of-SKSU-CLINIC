<?php
    $conn = mysqli_connect("localhost", "root", "", "ehrdb");

$searchTerm = '';
$results = []; // Initialize an array to store search results

// Check if a search term is provided
if (isset($_GET['search'])) {
    $searchTerm = $_GET['search'];

    // Combined query to search across all three tables
    $combinedQuery = "
        SELECT 
            pi.Num, 
            pi.FirstName, 
            pi.LastName, 
            pi.IDNumber, 
            pi.GmailAccount, 
            pi.Gender, 
            pi.Course, 
            pi.Yr, 
            pi.Section, 
            pi.BirthDate,
            i.what_you_do, 
            i.what_is_your_existing_desease, 
            i.have_you_a_family_history_desease, 
            i.have_you_a_allergy,
            im.IllName, 
            im.MedName, 
            im.Temperature, 
            im.BloodPressure,
            im.Appointment_Date
        FROM personal_info pi
        LEFT JOIN intv i ON pi.Num = i.Num
        LEFT JOIN illmed im ON pi.Num = im.Num
        WHERE 
            (pi.FirstName LIKE ? 
            OR pi.LastName LIKE ?
            OR pi.IDNumber LIKE ? 
            OR pi.GmailAccount LIKE ?
            OR i.what_you_do LIKE ?
            OR i.what_is_your_existing_desease LIKE ?
            OR i.have_you_a_family_history_desease LIKE ?
            OR im.IllName LIKE ? 
            OR im.MedName LIKE ?
            OR im.Temperature LIKE ?
            OR im.BloodPressure LIKE ?)
        ORDER BY pi.FirstName ASC, pi.LastName ASC"; // Order by FirstName and LastName

    // Prepare statement
    if ($stmt = mysqli_prepare($conn, $combinedQuery)) {
        // Bind parameters
        $param = '%' . $searchTerm . '%';
        mysqli_stmt_bind_param($stmt, 'sssssssssss', $param, $param, $param, $param, $param, $param, $param, $param, $param, $param, $param);

        // Execute the statement
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
// Inside the result fetching section after fetching results
if ($result) {
    if (mysqli_num_rows($result) > 0) {
        $results = mysqli_fetch_all($result, MYSQLI_ASSOC);
        
        // Calculate age for each result
        foreach ($results as &$result) {
            // Format Appointment Date
            if (!empty($result['Appointment_Date'])) {
                $appointmentDate = new DateTime($result['Appointment_Date']);
                $result['Appointment_Date'] = $appointmentDate->format('F j, Y g:i A'); // Format to Month Day, Year Hour:Minute AM/PM
            } else {
                $result['Appointment_Date'] = 'N/A'; // Set appointment date as 'N/A' if not available
            }

            if (!empty($result['BirthDate'])) {
                $birthDate = new DateTime($result['BirthDate']);
                $today = new DateTime();
                $age = $today->diff($birthDate)->y; // Calculate age
                $result['Age'] = $age; // Add age to the result array
            } else {
                $result['Age'] = 'N/A'; // Set age as 'N/A' if BirthDate is not available
            }
        }
    }
}


        // Close statement
        mysqli_stmt_close($stmt);
    } else {
        // Handle error if prepare fails
        echo json_encode(['error' => 'Database query failed.']);
        exit;
    }

    mysqli_close($conn);

    // Return results as JSON
    echo json_encode($results);
    exit; // Ensure no further output is sent after this
}
?>