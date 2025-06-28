<?php
session_start();
$conn = mysqli_connect("localhost", "root", "", "ehrdb");

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $medicineId = $_POST['Num'];
    $operation = $_POST['operation'];

    // Fetch current StockQuantity
    $query = "SELECT StockQuantity FROM inventory WHERE Num = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "i", $medicineId);
    mysqli_stmt_execute($stmt);
    $fetchResult = mysqli_stmt_get_result($stmt);
    
    if ($fetchResult && mysqli_num_rows($fetchResult) > 0) {
        $row = mysqli_fetch_assoc($fetchResult);
        $currentQuantity = $row['StockQuantity'];

        // Update the quantity based on the operation
        if ($operation == 'add') {
            $newQuantity = $currentQuantity + 1;
        } elseif ($operation == 'remove') {
            $newQuantity = max(0, $currentQuantity - 1); // Ensure quantity doesn't go below 0
        }

        // Update StockQuantity in the database
        $updateQuery = "UPDATE inventory SET StockQuantity = ? WHERE Num = ?";
        $updateStmt = mysqli_prepare($conn, $updateQuery);
        mysqli_stmt_bind_param($updateStmt, "ii", $newQuantity, $medicineId);
        mysqli_stmt_execute($updateStmt);
        
        // Call renumbering function after updating the quantity
        renumberInventory($conn);
        
        // Fetch updated row to return as response
        $updatedQuery = "SELECT Num, MedName, SupplierName, StockQuantity FROM inventory WHERE Num = ?";
        $updatedStmt = mysqli_prepare($conn, $updatedQuery);
        mysqli_stmt_bind_param($updatedStmt, "i", $medicineId);
        mysqli_stmt_execute($updatedStmt);
        $updatedResult = mysqli_stmt_get_result($updatedStmt);
        
        if ($updatedRow = mysqli_fetch_assoc($updatedResult)) {
            echo implode(',', $updatedRow); // Send back updated data as CSV
        }

        mysqli_stmt_close($updateStmt);
    } else {
        echo "Medicine not found.";
    }
}

function renumberInventory($conn) {
    $query = "SELECT Num FROM inventory ORDER BY Num ASC";
    $result = mysqli_query($conn, $query);
    if ($result) {
        $num = 1; // Start numbering from 1
        while ($row = mysqli_fetch_assoc($result)) {
            // Use prepared statement for the update
            $updateQuery = "UPDATE inventory SET Num = ? WHERE Num = ?";
            $stmt = mysqli_prepare($conn, $updateQuery);
            mysqli_stmt_bind_param($stmt, "ii", $num, $row['Num']);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
            $num++;
        }
    }
}
?>
