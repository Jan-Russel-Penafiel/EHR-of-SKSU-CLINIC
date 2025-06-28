<?php
    $conn = mysqli_connect("localhost", "root", "", "ehrdb");

// Fetch total number of student and faculty appointments
$queryAppointments = "SELECT COUNT(*) as total_appointments FROM appointments";
$queryFacultyAppointments = "SELECT COUNT(*) as total_faculty_appointments FROM faculty_appointments";

$resultAppointments = mysqli_query($conn, $queryAppointments);
$resultFacultyAppointments = mysqli_query($conn, $queryFacultyAppointments);

$totalAppointments = (int)mysqli_fetch_assoc($resultAppointments)['total_appointments'];
$totalFacultyAppointments = (int)mysqli_fetch_assoc($resultFacultyAppointments)['total_faculty_appointments'];

// Combine the total appointments
$totalNewAppointments = $totalAppointments + $totalFacultyAppointments;

// Return the total as JSON
header('Content-Type: application/json');
echo json_encode(['new_appointment_count' => $totalNewAppointments]);

mysqli_close($conn);
?>
