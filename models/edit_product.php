<?php
require_once '../config/database.php';
require_once '../controllers/function.php';
session_start();

$database = new Database();     
$db = $database->getConnection();
$user = new user($db);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['edit'])) {
        $id = $_POST['id'];
        $name = $_POST['name'];
        $buy_price = $_POST['buy_price'];
        $sell_price = $_POST['sell_price'];
        $quantity = $_POST['quantity'];
        $stock = $_POST['stock'];
        $category_id = $_POST['category_id'];
        
        if ($user->editProduct($id, $name, $buy_price, $sell_price, $quantity, $stock, $category_id)) {
            echo "<script>alert('Edit successful!'); window.location.href = '../views/home.php';</script>";
        } else {
            echo "<script>alert('Edit failed!');</script>";
        }
    }
} else {
    if (isset($_GET['id'])) {
        $id = $_GET['id'];
        $product = $user->getProductById($id); // New function needed in user class
        if ($product) {
            $row = $product;
        } else {
            echo "<script>alert('Product not found!'); window.location.href = '../views/home.php';</script>";
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">    
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Product</title>
    <link rel="stylesheet" href="../assets/css/styled.css">
</head>
<body>
    <div class="edit-product">
        <h1>Edit Product</h1>
        <form method="POST" action="edit_product.php">
            <input type="hidden" name="id" value="<?php echo isset($row['id']) ? htmlspecialchars($row['id']) : ''; ?>">
            
            <label for="name">Product Name</label>
            <input type="text" name="name" id="name" placeholder="Product Name" 
                   value="<?php echo isset($row['name']) ? htmlspecialchars($row['name']) : ''; ?>" required>
            
            <label for="buy_price">Buy Price</label>
            <input type="number" name="buy_price" id="buy_price" step="0.01" placeholder="Buy Price" 
                   value="<?php echo isset($row['buy_price']) ? htmlspecialchars($row['buy_price']) : ''; ?>" required>
            
            <label for="sell_price">Sell Price</label>
            <input type="number" name="sell_price" id="sell_price" step="0.01" placeholder="Sell Price" 
                   value="<?php echo isset($row['sell_price']) ? htmlspecialchars($row['sell_price']) : ''; ?>" required>
            
            <label for="quantity">Quantity</label>
            <input type="number" name="quantity" id="quantity" placeholder="Quantity" 
                   value="<?php echo isset($row['quantity']) ? htmlspecialchars($row['quantity']) : ''; ?>" required>
            
            <label for="stock">Stock Status</label>
            <select name="stock" id="stock" required>
                <option value="In Stock" <?php echo (isset($row['stock']) && $row['stock'] == 'In Stock') ? 'selected' : ''; ?>>In Stock</option>
                <option value="Out of Stock" <?php echo (isset($row['stock']) && $row['stock'] == 'Out of Stock') ? 'selected' : ''; ?>>Out of Stock</option>
                <option value="Low Stock" <?php echo (isset($row['stock']) && $row['stock'] == 'Low Stock') ? 'selected' : ''; ?>>Low Stock</option>
            </select>
            
            <label for="category_id">Category</label>
            <select name="category_id" id="category_id" required>
                <?php
                $categories = $user->getCategory();
                while ($category = $categories->fetch(PDO::FETCH_ASSOC)) {
                    $selected = (isset($row['category_id']) && $row['category_id'] == $category['id']) ? 'selected' : '';
                    echo "<option value='" . htmlspecialchars($category['id']) . "' $selected>" . 
                         htmlspecialchars($category['name']) . "</option>";
                }
                ?>
            </select>
            
            <button type="submit" name="edit" class="btne">Update Product</button>
            <a href="../views/home.php" class="btne btne-secondary">Cancel</a>
        </form>
    </div>
</body>
</html>