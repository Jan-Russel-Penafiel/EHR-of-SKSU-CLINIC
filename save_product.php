<?php
// Include the database connection file (assuming it's in the same directory)
$conn = mysqli_connect("localhost", "root", "", "ehrdb");

// Check if the form was submitted
if (isset($_POST["submit"])) {

  // Get form data (check if keys exist before accessing)
  $a = isset($_POST['id']) ? $_POST['id'] : "";
  $b = isset($_POST['name']) ? $_POST['name'] : "";
  $c = isset($_POST['code']) ? $_POST['code'] : "";
  $d = isset($_POST['quantity']) ? $_POST['quantity'] : "";
  $e = isset($_POST['buying_price']) ? $_POST['buying_price'] : "";
  $f = isset($_POST['selling_price']) ? $_POST['selling_price'] : "";
  $g = isset($_POST['quantity_alert']) ? $_POST['quantity_alert'] : ""; // Corrected typo
  $h = isset($_POST['tax']) ? $_POST['tax'] : "";
  $i = isset($_POST['category_id']) ? $_POST['category_id'] : "";
  $j = isset($_POST['unit_status']) ? $_POST['unit_status'] : "";

  // Construct the insert query
  $insert_qry = "INSERT INTO products (id, name, code, quantity, buying_price, selling_price, quantity_alert, tax, category_id, unit_status) 
                 VALUES ('$a','$b','$c','$d','$e','$f','$g','$h','$i','$j')";

  // Execute the query using the connection from the included file
  $insert_result = mysqli_query($conn, $insert_qry);

  if ($insert_result) {
    echo '<script type="text/javascript">alert("Product ' . $c . ' successfully saved!");document.location.href="index.php";</script>';
  } else {
    echo '<script type="text/javascript">alert("Product ' . $c . ' failed to be inserted in the database!");document.location.href="insert.php";</script>';
  }
}

?>
