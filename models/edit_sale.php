<?php
require_once '../config/database.php';
require_once '../controllers/function.php';
session_start();

$database = new Database();
$db = $database->getConnection();

$user = new user($db);

// Get sale ID and fetch sale data
$sale_id = isset($_GET['id']) ? $_GET['id'] : null;
$sale = null;
if ($sale_id) {
    $stmt = $db->prepare("SELECT * FROM sales WHERE id = ?");
    $stmt->execute([$sale_id]);
    $sale = $stmt->fetch(PDO::FETCH_ASSOC);
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['add_product'])) {
        // Capture form data
        $id = $_POST['id'];
        $product_id = $_POST['product_id'];
        $price = $_POST['price'];
        $quantity = $_POST['quantity'];
        $date = $_POST['date'];

        // Call the editSale method
        $addProductResult = $user->editSale($id, $product_id, $price, $quantity, $date);
        
        if ($addProductResult) {
            echo "<p style='color:green;'>Sale updated successfully!</p>";
            echo "<script>alert('Edit successful!'); window.location.href = '../views/home.php';</script>";
        } else {
            echo "<p style='color:red; text-align: center; padding: 20px;'>Failed to update sale. Please try again.</p>";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Sale</title>
    <link rel="stylesheet" href="../assets/css/styled.css">
</head>
<body> 
<div class="sales-edit">
    <h1>Edit Sale</h1>
    <form method="POST" action="edit_sale.php?id=<?php echo htmlspecialchars($sale_id); ?>" enctype="multipart/form-data">
        <input type="hidden" name="id" value="<?php echo htmlspecialchars($sale_id); ?>">
        
        <label for="product_id">Product</label>
        <select name="product_id" id="product_id" required>
            <option value="">Select Product</option>
            <?php
            $products_stmt = $user->getProducts();
            while ($product = $products_stmt->fetch(PDO::FETCH_ASSOC)) {
                $selected = ($sale && $sale['product_id'] == $product['id']) ? 'selected' : '';
            ?>
                <option value="<?php echo htmlspecialchars($product['id']); ?>" 
                        data-quantity="<?php echo htmlspecialchars($product['quantity']); ?>"
                        data-price="<?php echo htmlspecialchars($product['sell_price']); ?>"
                        <?php echo $selected; ?>>
                    <?php echo htmlspecialchars($product['name']); ?>
                </option>
            <?php } ?>
        </select>

        <label for="price">Price</label>
        <input type="number" 
               name="price" 
               id="price" 
               placeholder="Price" 
               step="0.01" 
               required 
               value="<?php echo $sale ? htmlspecialchars($sale['price']) : ''; ?>">

        <label for="quantity">Quantity</label>
        <input type="number" 
               name="quantity" 
               id="quantity" 
               required 
               value="<?php echo $sale ? htmlspecialchars($sale['quantity']) : ''; ?>">

        <label for="date">Date</label>
        <input type="date" 
               name="date" 
               id="date" 
               required 
               value="<?php echo $sale ? htmlspecialchars($sale['date']) : ''; ?>">

        <button type="submit" name="add_product">Update Sale</button>
    </form>
    <a href="../views/home.php">Back to Home</a>
</div>
</body>
</html>