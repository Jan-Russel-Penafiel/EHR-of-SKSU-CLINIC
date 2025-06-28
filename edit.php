<html>
<title>Insert Product</title>

<head></head>

<body>
  <div align="center">
    <h1>Insert Products Page</h1>
  </div>
  <div align="center">

  <?php
$dsn = 'mysql:host=' . 'localhost' . ';dbname=' . 'peñafiel_sales_ordering' . ';charset=utf8';
$username = '';
$password = '0922';

try {
  $dbh = new PDO($dsn, $username, $password);

  // Your existing code using $dbh...
  $id = isset($_GET["id"]) ? $_GET["id"] : "";  
  $query = "SELECT * FROM Products WHERE id=:id";
  $stmt = $dbh->prepare($query);
  $stmt->bindParam(':id', $id, PDO::PARAM_INT);
  $stmt->execute();

  if ($stmt->rowCount() > 0) {
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
      // Process the retrieved data here
      // Example: $productID = $row['id'];
    }
  } else {
    echo 'No records found.';
  }
} catch (PDOException $e) {
  echo 'Error: ' . $e->getMessage();
}

?>

    <form method="POST">
      <div><label>ID:</label><input type="text" name="id" value="<?php echo isset($row['id']) ? $row['id'] : ""; ?>" /></div>
      <div><label>Name:</label><input type="text" name="name" value="<?php echo isset($row['name']) ? $row['name'] : ""; ?>" /></div>
      <div><label>Code:</label><input type="text" name="code" value="<?php echo isset($row['code']) ? $row['code'] : ""; ?>" /></div>
      <div><label>Quantity:</label><input type="text" name="quantity" value="<?php echo isset($row['quantity']) ? $row['quantity'] : ""; ?>" /></div>
      <div><label>Buying Price:</label><input type="text" name="buying_price" value="<?php echo isset($row['buying_price']) ? $row['buying_price'] : ""; ?>" /></div>
      <div><label>Selling Price:</label><input type="text" name="selling_price" value="<?php echo isset($row['selling_price']) ? $row['selling_price'] : ""; ?>" /></div>
      <div><label>Quantity Alert:</label><input type="text" name="quantity_alert" value="<?php echo isset($row['quantity_alert']) ? $row['quantity_alert'] : ""; ?>" /></div>
      <div><label>Tax:</label><input type="text" name="tax" value="<?php echo isset($row['tax']) ? $row['tax'] : ""; ?>" /></div>
      <div><label>Category ID:</label><input type="text" name="category_id" value="<?php echo isset($row['category_id']) ? $row['category_id'] : ""; ?>" /></div>
      <div><label>Unit Status:</label><input type="text" name="unit_status" value="<?php echo isset($row['unit_status']) ? $row['unit_status'] : ""; ?>" /></div>
      <input type="submit" name="submit" value="Update Product Details" />
    </form>
  </div>

  <?php
  if (isset($_POST["submit"])) {
    include ("database_connection.php");

    $a = $_POST['id'];
    $b = $_POST['name'];
    $c = $_POST['code'];
    $d = $_POST['quantity'];
    $e = $_POST['buying_price'];
    $f = $_POST['selling_price'];
    $g = $_POST['quantity_alert'];
    $h = $_POST['tax'];
    $i = $_POST['category_id'];
    $j = $_POST['unit_status'];

    $update_qry = "UPDATE Products SET id='$a', name='$b', code='$c', quantity='$d', buying_price='$e', selling_price='$f', quantity_alert='$g', tax='$h', category_id='$i', unit_status='$j' WHERE id = $a"; // Use prepared statements for security

    $update_result = mysqli_query($conn, $update_qry);
}
    
