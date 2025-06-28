<?php
// check_low_stock.php
$conn = mysqli_connect("localhost", "root", "", "ehrdb");

// Threshold for low stock
$threshold = 10;

// Query to get medicines with stock below the threshold
$sql = "SELECT MedName FROM inventory WHERE StockQuantity <= $threshold";
$result = mysqli_query($conn, $sql);

$lowStockMedicines = [];

// Fetch all medicines that are low on stock
while ($row = mysqli_fetch_assoc($result)) {
    $lowStockMedicines[] = $row['MedName'];
}

// Return the list of low stock medicines as a JSON response
echo json_encode($lowStockMedicines);

mysqli_close($conn);
?>
