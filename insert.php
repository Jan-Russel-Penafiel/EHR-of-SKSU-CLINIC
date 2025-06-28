<html>
<title>Insert Product</title>

<head></head>

<body>
    <div align="center">
        <h1>Insert Products Page</h1>
    </div>
    <div align="center">
        <form action="save_product.php" method="POST">
            <div><label>ID:</label><input type="text" name="id" /></div>
            <div><label>Name:</label><input type="text" name="name" /></div>
            <div><label>Code:</label><input type="text" name="code" /></div>
            <div><label>Quantity:</label><input type="text" name="quantity" /></div>
            <div><label>Buying Price:</label><input type="text" name="buying_price" /></div>
            <div><label>Selling Price:</label><input type="text" name="selling_price" /></div>
            <div><label>Quantity Alert:</label><input type="text" name="quantity_alert" /></div>
            <div><label>Tax:</label><input type="text" name="tax" /></div>
            <div><label>Category ID:</label><input type="text" name="category_id" /></div>
            <div><label>Unit Status:</label><input type="text" name="unit_status" /></div>
           value="Save Product Details" />
        </form>
    </div>
</body>

</html>