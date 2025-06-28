<?php
    $conn = mysqli_connect("localhost", "root", "", "ehrdb");

// Check if the id is present in the GET request
if (isset($_GET['id'])) {

  $id = $_GET['id'];

  // Query to get the product details (optional)
  $query = "SELECT * FROM products WHERE id = $id";
  $result = mysqli_query($conn, $query);

  // Check if product exists before deleting
  if (mysqli_num_rows($result) > 0) {
    $qry = "DELETE FROM products WHERE id ='$id'";
    $delete_result = mysqli_query($conn, $qry);

    if ($delete_result) {
      // Use the retrieved id for the success message (if applicable)
      $product_id = mysqli_fetch_assoc($result)['id']; // Assuming you want to retrieve the id
      echo '<script type="text/javascript">alert("Product ' . $product_id . ' successfully deleted!");document.location.href="index.php";</script>';
    } else {
      echo '<script type="text/javascript">alert("Product deletion failed!");document.location.href="index.php";</script>';
    }
  } else {
    echo '<script type="text/javascript">alert("Product with ID ' . $id . ' not found!");document.location.href="index.php";</script>';
  }
} else {
  // Handle case where no id is provided in the GET request (optional)
  echo '<script type="text/javascript">alert("Missing product ID!");document.location.href="index.php";</script>';
}

?>
