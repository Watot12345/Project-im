<?php
class user {
    private $conn;
    private $business_id;
    private $table_users = "users"; 
    private $table_products = "products";   // Assuming you have a products table       
    private $table_categories = "categories"; // Assuming you have a categories table   
    private $table_images = "images"; // Assuming you have an images table
    private $table_sales = "sales"; // Assuming you have a sales table

    
    
    public function __construct($db) {
        $this->conn = $db;
         $this->business_id = isset($_SESSION['business_id']) ? $_SESSION['business_id'] : null;
    }

    //register function
public function register($name, $password, $email, $bussname, $confirm) {
    try {
        // Validate database connection
        if (!$this->conn) {
            return ["Database error: No connection established"];
        }

        // Start transaction
        $this->conn->beginTransaction();
        
        // Initialize errors array
        $errors = [];
        
        // Validate the fields first
        if (empty($name)) {
            $errors[] = "Name is required.";
        }
        if (empty($password)) {
            $errors[] = "Password is required.";
        } elseif (strlen($password) < 8) {
            $errors[] = "Password must be at least 8 characters long.";
        }
        if ($password !== $confirm) {
            $errors[] = "Passwords do not match.";
        }
        if (!preg_match("/[A-Z]/", $password)) {
            $errors[] = "Password must contain at least one uppercase letter.";
        }
        if (!preg_match("/[a-z]/", $password)) {
            $errors[] = "Password must contain at least one lowercase letter.";
        }
        if (!preg_match("/[0-9]/", $password)) {
            $errors[] = "Password must contain at least one number.";
        }
        if (!preg_match("/[^a-zA-Z0-9]/", $password)) {
            $errors[] = "Password must contain at least one special character.";
        }
        if (empty($email)) {
            $errors[] = "Please enter your email.";
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = "Invalid email format.";
        }
        if (empty($bussname)) {
            $errors[] = "Business name is required.";
        }

        // Only proceed with database checks if basic validation passed
        if (empty($errors)) {
            // Check if email already exists
            $checkEmailQuery = "SELECT id FROM " . $this->table_users . " WHERE email = :email LIMIT 1";
            $checkEmailStmt = $this->conn->prepare($checkEmailQuery);
            $checkEmailStmt->bindParam(':email', $email);
            if (!$checkEmailStmt->execute()) {
                $errors[] = "Database error checking email";
            } elseif ($checkEmailStmt->rowCount() > 0) {
                $errors[] = "Email already exists.";
            }

            // Check if business name already exists
            $checkBussNameQuery = "SELECT id FROM " . $this->table_users . " WHERE business_name = :business_name LIMIT 1";
            $checkBussNameStmt = $this->conn->prepare($checkBussNameQuery);
            $checkBussNameStmt->bindParam(':business_name', $bussname);
            if (!$checkBussNameStmt->execute()) {
                $errors[] = "Database error checking business name";
            } elseif ($checkBussNameStmt->rowCount() > 0) {
                $errors[] = "Business name already exists.";
            }
        }

        // If there are errors, return them
        if (!empty($errors)) {
            $this->conn->rollBack();
            return $errors;
        }

        // Hash the password
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);

        // Insert new user with temporary business_id (0)
        $query = "INSERT INTO " . $this->table_users . " 
                 (name, password, email, business_name, business_id) 
                 VALUES 
                 (:name, :password, :email, :business_name, 0)";  // Temporary value
        $stmt = $this->conn->prepare($query);
        
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':password', $passwordHash);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':business_name', $bussname);
        
        if ($stmt->execute()) {
            $user_id = $this->conn->lastInsertId();
            $business_id = $user_id; // Set business_id same as user_id
            
            // Update business_id with actual value
            $updateQuery = "UPDATE " . $this->table_users . " 
                          SET business_id = :business_id 
                          WHERE id = :id";
            $updateStmt = $this->conn->prepare($updateQuery);
            $updateStmt->bindParam(':business_id', $business_id);
            $updateStmt->bindParam(':id', $user_id);
            
            if (!$updateStmt->execute()) {
                $this->conn->rollBack();
                return ["Database error updating business ID"];
            }

            // Create initial login log
            $logQuery = "INSERT INTO login_logs (user_id, business_id, login_time) 
                        VALUES (:user_id, :business_id, NOW())";
            $logStmt = $this->conn->prepare($logQuery);
            $logStmt->bindParam(':user_id', $user_id);
            $logStmt->bindParam(':business_id', $business_id);
            
            if (!$logStmt->execute()) {
                $this->conn->rollBack();
                return ["Database error creating login log"];
            }

            $this->conn->commit();
            return true;
        } else {
            $this->conn->rollBack();
            return ["Registration failed. Please try again."];
        }

    } catch (PDOException $e) {
        if ($this->conn->inTransaction()) {
            $this->conn->rollBack();
        }
        return ["Database error: " . $e->getMessage()];
    }
}
    
   //login function
public function login($email, $password) {
    $query = "SELECT id, email, password, business_id, name, business_name 
              FROM " . $this->table_users . " 
              WHERE email = :email";
    
    $stmt = $this->conn->prepare($query);
    $stmt->bindParam(':email', $email);
    $stmt->execute();
    
    if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        if (password_verify($password, $row['password'])) {
            // Create login log entry
            $logQuery = "INSERT INTO login_logs (user_id, business_id, login_time) 
                        VALUES (:user_id, :business_id, NOW())";
            $logStmt = $this->conn->prepare($logQuery);
            $logStmt->bindParam(':user_id', $row['id']);
            $logStmt->bindParam(':business_id', $row['business_id']);
            $logStmt->execute();
            
            // Store log_id in session for logout tracking
            $_SESSION['log_id'] = $this->conn->lastInsertId();
            $_SESSION['user_id'] = $row['id'];
            $_SESSION['email'] = $row['email'];
            $_SESSION['business_id'] = $row['business_id'];
            $_SESSION['name'] = $row['name'];
            $_SESSION['business_name'] = $row['business_name'];
            
            return [
                'success' => true,
                'user_id' => $row['id'],
                'user_email' => $row['email'],
                'user_name' => $row['name'],
                'business_id' => $row['business_id'],
                'business_name' => $row['business_name']
            ];
        }
    }
    return [
        'success' => false,
        'message' => 'Invalid email or password'
    ];
}
public function addMember($name, $email, $password, $confirm_password) {
    try {
        // Validation
        if (empty($name) || empty($email) || empty($password)) {
            throw new Exception("All fields are required");
        }

        if ($password !== $confirm_password) {
            throw new Exception("Passwords do not match");
        }

        // Check if email already exists
        $checkQuery = "SELECT id FROM " . $this->table_users . " 
                      WHERE email = :email AND business_id = :business_id";
        $checkStmt = $this->conn->prepare($checkQuery);
        $checkStmt->bindParam(':email', $email);
        $checkStmt->bindParam(':business_id', $this->business_id);
        $checkStmt->execute();

        if ($checkStmt->rowCount() > 0) {
            throw new Exception("Email already exists");
        }

        // Hash password
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);

        // Insert new member
         $query = "INSERT INTO " . $this->table_users . " 
                 (name, email, password, business_id, business_name, added_by) 
                 VALUES 
                 (:name, :email, :password, :business_id, :business_name, :added_by)";
        
       $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':password', $passwordHash);
        $stmt->bindParam(':business_id', $this->business_id);
        $stmt->bindParam(':business_name', $_SESSION['business_name']);
        $stmt->bindParam(':added_by', $_SESSION['user_id']); 

        if ($stmt->execute()) {
            // Create initial login log entry
            $user_id = $this->conn->lastInsertId();
            $logQuery = "INSERT INTO login_logs (user_id, business_id, login_time) 
                        VALUES (:user_id, :business_id, NOW())";
            $logStmt = $this->conn->prepare($logQuery);
            $logStmt->bindParam(':user_id', $user_id);
            $logStmt->bindParam(':business_id', $this->business_id);
            $logStmt->execute();
            
            return true;
        }
        throw new Exception("Failed to add member");

    } catch (Exception $e) {
        return ['error' => $e->getMessage()];
    }
}
public function getUserLogs($user_id) {
    $query = "SELECT 
                login_time,
                CASE 
                    WHEN login_time >= DATE_SUB(NOW(), INTERVAL 5 MINUTE) THEN 'Online'
                    ELSE 'Offline'
                END as status
              FROM login_logs 
              WHERE user_id = :user_id 
              AND business_id = :business_id
              ORDER BY login_time DESC 
              LIMIT 1";
              
    $stmt = $this->conn->prepare($query);
    $stmt->bindParam(':user_id', $user_id);
    $stmt->bindParam(':business_id', $this->business_id);
    $stmt->execute();
    
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
    
    //check if user is logged in
    public function isLoggedIn() {
        return isset($_SESSION['user_id']);
    }

    //logout function
    public function logout() {
        session_destroy();
        unset($_SESSION['user_id']);
        unset($_SESSION['email']);
    }
     //members function
public function members() {
    $query = "SELECT 
                id, 
                name,
                SUBSTRING_INDEX(name, ' ', 1) AS first_name, 
                SUBSTRING_INDEX(name, ' ', -1) AS last_name,
                CONCAT('EMP', LPAD(id, 2, '0')) AS employee_id
              FROM " . $this->table_users . "
              WHERE business_id = :business_id 
              AND id != :current_user_id
              AND added_by = :business_owner_id"; // Only show members added by the business owner

    $stmt = $this->conn->prepare($query);
    $stmt->bindParam(':business_id', $this->business_id);
    $stmt->bindParam(':current_user_id', $_SESSION['user_id']);
    $stmt->bindParam(':business_owner_id', $_SESSION['user_id']);
    $stmt->execute();
    return $stmt;
}
    //edit function
   public function edit($id, $name) {
    $query = "UPDATE " . $this->table_users . " 
              SET name = :name 
              WHERE id = :id AND business_id = :business_id";
    $stmt = $this->conn->prepare($query);
    $stmt->bindParam(':name', $name);
    $stmt->bindParam(':id', $id);
    $stmt->bindParam(':business_id', $this->business_id);
    return $stmt->execute();
} 
    //edit category function
    public function editCategory($id, $name) {
    $query = "UPDATE " . $this->table_categories . " 
              SET name = :name 
              WHERE id = :id AND business_id = :business_id";
    $stmt = $this->conn->prepare($query);
    $stmt->bindParam(':name', $name);
    $stmt->bindParam(':id', $id);
    $stmt->bindParam(':business_id', $this->business_id);
    return $stmt->execute();
}
    //editSale function
public function editSale($id, $product_id, $price, $quantity, $date) {
    try {
        $query = "UPDATE sales 
                 SET product_id = ?, 
                     price = ?, 
                     quantity = ?, 
                     date = ? 
                 WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([$product_id, $price, $quantity, $date, $id]);
    } catch(PDOException $e) {
        return false;
    }
}
    //edit product function
   public function editProduct($id, $name, $buy_price, $sell_price, $quantity, $stock, $category_id) {
    $query = "UPDATE " . $this->table_products . " 
              SET name = :name, 
                  buy_price = :buy_price, 
                  sell_price = :sell_price, 
                  quantity = :quantity, 
                  stock = :stock, 
                  category_id = :category_id 
              WHERE id = :id AND business_id = :business_id";
    
    $stmt = $this->conn->prepare($query);
    $stmt->bindParam(':id', $id);
    $stmt->bindParam(':name', $name);
    $stmt->bindParam(':buy_price', $buy_price);
    $stmt->bindParam(':sell_price', $sell_price);
    $stmt->bindParam(':quantity', $quantity);
    $stmt->bindParam(':stock', $stock);
    $stmt->bindParam(':category_id', $category_id);
    $stmt->bindParam(':business_id', $this->business_id);
    return $stmt->execute();
}
    //delete sale function
    public function deleteSale($id) {
        try {
            $this->conn->beginTransaction();
    
            // Get the sale details first
          $saleQuery = "SELECT product_id, quantity FROM " . $this->table_sales . " 
                     WHERE id = :id AND business_id = :business_id";
        $saleStmt = $this->conn->prepare($saleQuery);
        $saleStmt->bindParam(':id', $id);
        $saleStmt->bindParam(':business_id', $this->business_id);
        $saleStmt->execute();
            if ($saleStmt->rowCount() === 0) {
                throw new Exception("Sale not found");
            }
    
            $sale = $saleStmt->fetch(PDO::FETCH_ASSOC);
    
            // Return quantity to product stock
            $updateQuery = "UPDATE " . $this->table_products . " 
                           SET quantity = quantity + :return_quantity 
                           WHERE id = :product_id";
            $updateStmt = $this->conn->prepare($updateQuery);
            $updateStmt->bindParam(':return_quantity', $sale['quantity']);
            $updateStmt->bindParam(':product_id', $sale['product_id']);
            $updateStmt->execute();
    
            // Delete the sale record
            $deleteQuery = "DELETE FROM " . $this->table_sales . " WHERE id = :id";
            $deleteStmt = $this->conn->prepare($deleteQuery);
            $deleteStmt->bindParam(':id', $id);
            $result = $deleteStmt->execute();
    
            $this->conn->commit();
            return true;
    
        } catch (Exception $e) {
            if ($this->conn->inTransaction()) {
                $this->conn->rollBack();
            }
            error_log($e->getMessage());
            throw $e;
        }
    }
    //delete members function
    public function delete($id) {
        try {
            // Start transaction
            $this->conn->beginTransaction();
    
            // Check if user exists first
            $checkQuery = "SELECT id FROM " . $this->table_users . " WHERE id = :id AND business_id = :business_id";
            $checkStmt = $this->conn->prepare($checkQuery);
            $checkStmt->bindParam(':id', $id);
            $checkStmt->bindParam(':business_id', $this->business_id);
            $checkStmt->execute();
    
            if ($checkStmt->rowCount() === 0) {
                throw new Exception("User not found");
            }
    
            // Perform deletion
            $query = "DELETE FROM " . $this->table_users . " WHERE id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id);
            $result = $stmt->execute();
    
            if (!$result) {
                throw new Exception("Failed to delete user");
            }
    
            $this->conn->commit();
            return true;
    
        } catch (Exception $e) {
            if ($this->conn->inTransaction()) {
                $this->conn->rollBack();
            }
            error_log($e->getMessage());
            return false;
        }
    }
     //delete category function
    public function deleteCategory($id) {
        try {
            // Start transaction
            $this->conn->beginTransaction();
    
            // Check if category exists first
            $checkQuery = "SELECT id FROM " . $this->table_categories . " WHERE id = :id AND business_id = :business_id";
            $checkStmt = $this->conn->prepare($checkQuery);
            $checkStmt->bindParam(':id', $id);
            $checkStmt->bindParam(':business_id', $this->business_id);
            $checkStmt->execute();
    
            if ($checkStmt->rowCount() === 0) {
                throw new Exception("Category not found");
            }
    
            // Perform deletion
            $query = "DELETE FROM " . $this->table_categories . " WHERE id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id);
            $result = $stmt->execute();
    
            if (!$result) {
                throw new Exception("Failed to delete category");
            }
    
            $this->conn->commit();
            return true;
    
        } catch (Exception $e) {
            if ($this->conn->inTransaction()) {
                $this->conn->rollBack();
            }
            error_log($e->getMessage());
            return false;
        }
    }
    //get category function
     public function getCategory() {
        $query = "SELECT id, name FROM " . $this->table_categories . 
                 " WHERE business_id = :business_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':business_id', $this->business_id);
        $stmt->execute();
        return $stmt;
    }   
    public function getCategoryById($id) {
        $query = "SELECT * FROM " . $this->table_categories . 
                 " WHERE id = :id AND business_id = :business_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':business_id', $this->business_id);
        $stmt->execute();
        return $stmt;
    }
    //add category function
  public function addCategory($name) {
        $query = "INSERT INTO categories (name, business_id) VALUES (:name, :business_id)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':business_id', $this->business_id);
        return $stmt->execute();
    }
   //add product function
    public function add_product($name, $photo, $quantity, $buy_price, $sell_price, $stock, $category_name) {
        try {
            // Validation
            if (empty($name) || empty($quantity) || empty($buy_price) || empty($sell_price) || empty($stock)) {
                throw new Exception("Name, quantity, prices and stock are required");
            }
    
            // Start transaction
            $this->conn->beginTransaction();
    
            // Check if category exists first
            $checkCategoryQuery = "SELECT id FROM " . $this->table_categories . " WHERE name = :category_name";
            $checkCategoryStmt = $this->conn->prepare($checkCategoryQuery);
            $checkCategoryStmt->bindParam(':category_name', $category_name);
            $checkCategoryStmt->execute();
            
            if ($checkCategoryStmt->rowCount() === 0) {
                // Category doesn't exist, create it
                $categoryQuery = "INSERT INTO " . $this->table_categories . " (name) VALUES (:category_name)";
                $categoryStmt = $this->conn->prepare($categoryQuery);
                $categoryStmt->bindParam(':category_name', $category_name);
                $categoryStmt->execute();
                $category_id = $this->conn->lastInsertId();
            } else {
                // Category exists, get its ID
                $category_id = $checkCategoryStmt->fetchColumn();
               
            }
            if ($checkCategoryStmt->rowCount() === 0) {
                echo("Category '{$category_name}' does not exist. Please create the category first.");
            }
            // First insert into categories table
            $categoryQuery = "INSERT INTO " . $this->table_categories . " (name) VALUES (:category_name)";
            $categoryStmt = $this->conn->prepare($categoryQuery);
            $categoryStmt->bindParam(':category_name', $category_name);
            
            // Try to insert the category
            try {
                $categoryStmt->execute();
                $category_id = $this->conn->lastInsertId();
            } catch (PDOException $e) {
                // If category already exists, get its ID
                if ($e->getCode() == '23000') { // Duplicate entry error
                    $getCategoryQuery = "SELECT id FROM " . $this->table_categories . " WHERE name = :category_name";
                    $getCategoryStmt = $this->conn->prepare($getCategoryQuery);
                    $getCategoryStmt->bindParam(':category_name', $category_name);
                    $getCategoryStmt->execute();
                    $category_id = $getCategoryStmt->fetchColumn();
                } else {
                echo"ERROR"; // Re-throw if it's a different error
                }
            }
         
            $current_date = date('Y-m-d');
            // Insert product
           $query = "INSERT INTO " . $this->table_products . " 
                     (name, buy_price, sell_price, quantity, stock, date, category_id, business_id) 
                     VALUES 
                     (:name, :buy_price, :sell_price, :quantity, :stock, :date, :category_id, :business_id)";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':business_id', $this->business_id);
            $stmt->bindParam(':name', $name);
            $stmt->bindParam(':buy_price', $buy_price);
            $stmt->bindParam(':sell_price', $sell_price);
            $stmt->bindParam(':quantity', $quantity);
            $stmt->bindParam(':date', $current_date);
            $stmt->bindParam(':stock', $stock);
            $stmt->bindParam(':category_id', $category_id);
            $stmt->execute();
            
            $product_id = $this->conn->lastInsertId();
            
            // Handle image upload if photo is provided
            if (!empty($_FILES['product_photo']['name'])) {
                $file_name = $_FILES['product_photo']['name'];
                $file_tmp = $_FILES['product_photo']['tmp_name'];
                $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
                
                // Validate file extension
                $allowed_extensions = array('jpg', 'jpeg', 'png', 'gif');
                if (!in_array($file_ext, $allowed_extensions)) {
                    throw new Exception("Only JPG, JPEG, PNG & GIF files are allowed.");
                }
                
                // Generate unique filename
                $new_file_name = md5(uniqid() . time()) . '.' . $file_ext;
                
                // Upload directory
                $upload_dir = dirname(__DIR__) . '/uploads/';
                if (!file_exists($upload_dir)) {
                    mkdir($upload_dir, 0777, true);
                }
                
                // Move uploaded file
                if (move_uploaded_file($file_tmp, $upload_dir . $new_file_name)) {
                    // Insert image record
                    $imageQuery = "INSERT INTO images (product_id, image_path) VALUES (:product_id, :image_path)";
                    $imageStmt = $this->conn->prepare($imageQuery);
                    $imageStmt->bindParam(':product_id', $product_id);
                    $imageStmt->bindParam(':image_path', $new_file_name);
                    $imageStmt->execute();
                } else {
                    throw new Exception("Failed to upload image.");
                }
            }
            
            $this->conn->commit();
            return true;
            
        } catch (Exception $e) {
            if ($this->conn->inTransaction()) {
                $this->conn->rollBack();
            }
            throw $e;
        }
    }
     // Update getProducts() function
    public function getProducts() {
    $query = "SELECT p.id, p.name, p.buy_price, p.sell_price, 
                     p.quantity, p.stock, p.date, p.category_id,
                     MIN(i.image_path) AS image_path, 
                     c.name AS category_name
              FROM " . $this->table_products . " p 
              LEFT JOIN " . $this->table_categories . " c ON p.category_id = c.id
              LEFT JOIN images i ON p.id = i.product_id 
              WHERE p.business_id = :business_id
              GROUP BY p.id, p.name, p.buy_price, p.sell_price, 
                       p.quantity, p.stock, p.date, p.category_id, c.name
              ORDER BY p.id ASC";

    $stmt = $this->conn->prepare($query);
    $stmt->bindParam(':business_id', $this->business_id);
    $stmt->execute();
    return $stmt;
}
    //delete product function
    public function deleteProduct($id) {
        try {
            // Start transaction
            $this->conn->beginTransaction();
    
            // Check if product exists first
            $checkQuery = "SELECT id FROM " . $this->table_products . " WHERE id = :id  AND business_id = :business_id";
            $checkStmt = $this->conn->prepare($checkQuery);
            $checkStmt->bindParam(':id', $id);
            $checkStmt->bindParam(':business_id', $this->business_id);
            $checkStmt->execute();
    
            if ($checkStmt->rowCount() === 0) {
                throw new Exception("Product not found");
            }
    
            // Delete associated image if exists
            $imageQuery = "DELETE FROM images WHERE product_id = :product_id";
            $imageStmt = $this->conn->prepare($imageQuery);
            $imageStmt->bindParam(':product_id', $id);
            $imageStmt->execute();
    
            // Then delete the product
            $query = "DELETE FROM " . $this->table_products . " WHERE id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id);
            $result = $stmt->execute();
    
            if (!$result) {
                throw new Exception("Failed to delete product");
            }
    
            $this->conn->commit();
            return true;
    
        } catch (Exception $e) {
            if ($this->conn->inTransaction()) {
                $this->conn->rollBack();
            }
            error_log($e->getMessage());
            return false;
        }
    }

    //sell information function
    public function addSale($product_id, $price, $quantity, $date) {
        try {
            $this->conn->beginTransaction();
    
            // Check if product exists and has enough quantity
            $checkQuery = "SELECT quantity FROM " . $this->table_products . " WHERE id = :product_id";
            $checkStmt = $this->conn->prepare($checkQuery);
            $checkStmt->bindParam(':product_id', $product_id);
            $checkStmt->execute();
    
            if ($checkStmt->rowCount() === 0) {
                throw new Exception("Product not found");
            }
    
            $product = $checkStmt->fetch(PDO::FETCH_ASSOC);
            // Add debug output
            error_log("Available stock: " . $product['quantity'] . ", Requested: " . $quantity);
            
           
    
            // Calculate total sales
            $total_sales = $price * $quantity;
    
            // Insert sale record
            $query = "INSERT INTO " . $this->table_sales . " 
                     (product_id, price, quantity, total_sales, date, business_id) 
                     VALUES (:product_id, :price, :quantity, :total_sales, :date, :business_id)";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':business_id', $this->business_id);
            $stmt->bindParam(':product_id', $product_id);
            $stmt->bindParam(':price', $price);
            $stmt->bindParam(':quantity', $quantity);
            $stmt->bindParam(':total_sales', $total_sales);
            $stmt->bindParam(':date', $date);
            $stmt->execute();
    
            // Update product quantity in PRODUCTS table (not sales table)
            $updateQuery = "UPDATE " . $this->table_products . " 
                           SET quantity = quantity - :sold_quantity 
                           WHERE id = :product_id";
            $updateStmt = $this->conn->prepare($updateQuery);
            $updateStmt->bindParam(':sold_quantity', $quantity);
            $updateStmt->bindParam(':product_id', $product_id);
            $updateStmt->execute();
    
            $this->conn->commit();
            return true;
    
        } catch (Exception $e) {
            if ($this->conn->inTransaction()) {
                $this->conn->rollBack();
            }
            error_log($e->getMessage());
            throw $e;
        }
    }
    //get report function
    public function getSales() {
        $query = "SELECT 
            s.id, s.date, s.quantity, s.price,
            p.name as product_name, p.stock, p.buy_price, p.sell_price,
            (s.price * s.quantity) as total,
            SUM(s.price * s.quantity) as grand_total,
            ((s.price * s.quantity) - (p.buy_price * s.quantity)) as profit,
            i.image_path
            FROM " . $this->table_sales . " s
            LEFT JOIN " . $this->table_products . " p ON s.product_id = p.id
            LEFT JOIN " . $this->table_images . " i ON p.id = i.product_id
            WHERE s.business_id = :business_id
            GROUP BY s.id
            ORDER BY s.date DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':business_id', $this->business_id);
        $stmt->execute();
        return $stmt;
    }
    public function getProductById($id) {
    $query = "SELECT p.*, c.name as category_name 
              FROM " . $this->table_products . " p 
              LEFT JOIN " . $this->table_categories . " c ON p.category_id = c.id 
              WHERE p.id = :id 
              AND p.business_id = :business_id";
    $stmt = $this->conn->prepare($query);
    $stmt->bindParam(':id', $id);
    $stmt->bindParam(':business_id', $this->business_id);
    $stmt->execute();
    return $stmt->fetch(PDO::FETCH_ASSOC);
}
 } 

