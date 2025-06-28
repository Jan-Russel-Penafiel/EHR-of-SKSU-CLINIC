<?php
    $conn = mysqli_connect("localhost", "root", "", "ehrdb");
    session_start();

// Function to handle the deletion of appointments
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['delete_selected']) && !empty($_POST['selected_appointments'])) {
        // Handle student appointments deletion
        $appointmentsToDelete = implode(',', $_POST['selected_appointments']);
        $deleteQuery = "DELETE FROM appointments WHERE Num IN ($appointmentsToDelete)";
        if (mysqli_query($conn, $deleteQuery)) {
            $_SESSION['successMessage'] = "Selected student appointments deleted successfully.";
        } else {
            $_SESSION['errorMessage'] = "Error: " . mysqli_error($conn);
        }
    }
    
    if (isset($_POST['delete_faculty_selected']) && !empty($_POST['selected_faculty_appointments'])) {
        // Handle faculty appointments deletion
        $facultyAppointmentsToDelete = implode(',', $_POST['selected_faculty_appointments']);
        $deleteQuery = "DELETE FROM faculty_appointments WHERE Num IN ($facultyAppointmentsToDelete)";
        if (mysqli_query($conn, $deleteQuery)) {
            $_SESSION['successMessage'] = "Selected faculty appointments deleted successfully.";
        } else {
            $_SESSION['errorMessage'] = "Error: " . mysqli_error($conn);
        }
    }

    // Redirect after deletion
    header("Location: display_online_appointment.php");
    exit();
}

mysqli_close($conn);
?>
