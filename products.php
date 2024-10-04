<?php
require('db_connection_mysqli.php');
session_start();

if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: login.php");
    exit;
}

function cleanInput($data) {
    return htmlspecialchars(trim($data));
}

// Initialize variables and error messages
$productName = $description = $quantity = $price = $category = $size = $color = "";
$productNameErr = $descriptionErr = $quantityErr = $priceErr = $categoryErr = $sizeErr = $colorErr = "";


$productAddedBy = "Admin";  

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validate Product Name
    if (empty($_POST["productName"])) {
        $productNameErr = "Product Name is required";
    } else {
        $productName = cleanInput($_POST["productName"]);
        if (!preg_match("/^[a-zA-Z0-9 ]*$/", $productName)) {
            $productNameErr = "Only letters, numbers, and white space allowed";
        }
    }

    // Validate Description
    if (empty($_POST["description"])) {
        $descriptionErr = "Description is required";
    } else {
        $description = cleanInput($_POST["description"]);
    }

    // Validate Quantity (must be a positive integer)
    if (empty($_POST["quantity"])) {
        $quantityErr = "Quantity is required";
    } else {
        $quantity = cleanInput($_POST["quantity"]);
        if (!filter_var($quantity, FILTER_VALIDATE_INT, array("options" => array("min_range" => 1)))) {
            $quantityErr = "Quantity must be a positive integer";
        }
    }

    // Validate Price (must be a positive decimal number)
    if (empty($_POST["price"])) {
        $priceErr = "Price is required";
    } else {
        $price = cleanInput($_POST["price"]);
        if (!filter_var($price, FILTER_VALIDATE_FLOAT) || $price <= 0) {
            $priceErr = "Price must be a positive number";
        }
    }

    // Validate Category
    if (empty($_POST["category"])) {
        $categoryErr = "Category is required";
    } else {
        $category = cleanInput($_POST["category"]);
    }

    // Validate Size
    if (empty($_POST["size"])) {
        $sizeErr = "Size is required";
    } else {
        $size = cleanInput($_POST["size"]);
    }

    // Validate Color (only letters allowed)
    if (empty($_POST["color"])) {
        $colorErr = "Color is required";
    } else {
        $color = cleanInput($_POST["color"]);
        if (!preg_match("/^[a-zA-Z ]*$/", $color)) {
            $colorErr = "Only letters and white space allowed for color";
        }
    }




    // If no errors, insert the data into the database
    if (empty($productNameErr) && empty($descriptionErr) && empty($quantityErr) && empty($priceErr) && empty($categoryErr) && empty($sizeErr) && empty($colorErr)) {
        $stmt = $dbc->prepare("INSERT INTO shoes (productName, description, quantity, price, category, size, color, productAddedBy) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssisssss", $productName, $description, $quantity, $price, $category, $size, $color, $productAddedBy);

        if ($stmt->execute()) {
            // Redirect to the index page after successful insert
            header("Location: index.php");
            exit;
        } else {
            echo "Error: " . $stmt->error;
        }

        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add New Product</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<nav class="navbar navbar-expand-lg" style="background-color: skyblue;">
        <div class="container-fluid">
            <a class="navbar-brand" href="index.php">Shoes Store</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link active" aria-current="page" href="index.php">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="products.php">Add Products</a>
                    </li>
                   
                    <li class="nav-item">
                        <a class="nav-link" href="logout.php">Logout</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
    <div class="container mt-5">
        <h2>Add New Shoe Product</h2>
        <form action="products.php" method="POST">
            <div class="mb-3">
                <label for="productName" class="form-label">Product Name</label>
                <input type="text" class="form-control" id="productName" name="productName" value="<?php echo $productName; ?>">
                <span class="text-danger"><?php echo $productNameErr; ?></span>
            </div>

            <div class="mb-3">
                <label for="description" class="form-label">Description</label>
                <textarea class="form-control" id="description" name="description"><?php echo $description; ?></textarea>
                <span class="text-danger"><?php echo $descriptionErr; ?></span>
            </div>

            <div class="mb-3">
                <label for="quantity" class="form-label">Quantity</label>
                <input type="number" class="form-control" id="quantity" name="quantity" value="<?php echo $quantity; ?>">
                <span class="text-danger"><?php echo $quantityErr; ?></span>
            </div>

            <div class="mb-3">
                <label for="price" class="form-label">Price</label>
                <input type="number" step="0.01" class="form-control" id="price" name="price" value="<?php echo $price; ?>">
                <span class="text-danger"><?php echo $priceErr; ?></span>
            </div>

            <div class="mb-3">
                <label for="category" class="form-label">Category</label>
                <select class="form-control" id="category" name="category">
                    <option value="">Select Category</option>
                    <option value="Sneakers" <?php if ($category == 'Sneakers') echo 'selected'; ?>>Sneakers</option>
                    <option value="Boots" <?php if ($category == 'Boots') echo 'selected'; ?>>Boots</option>
                    <option value="Formal" <?php if ($category == 'Formal') echo 'selected'; ?>>Formal</option>
                </select>
                <span class="text-danger"><?php echo $categoryErr; ?></span>
            </div>

            <div class="mb-3">
                <label for="size" class="form-label">Size</label>
                <select class="form-control" id="size" name="size">
                    <option value="">Select Size</option>
                    <option value="6" <?php if ($size == '6') echo 'selected'; ?>>6</option>
                    <option value="7" <?php if ($size == '7') echo 'selected'; ?>>7</option>
                    <option value="8" <?php if ($size == '8') echo 'selected'; ?>>8</option>
                    <option value="9" <?php if ($size == '9') echo 'selected'; ?>>9</option>
                    <option value="10" <?php if ($size == '10') echo 'selected'; ?>>10</option>
                </select>
                <span class="text-danger"><?php echo $sizeErr; ?></span>
            </div>

            <div class="mb-3">
                <label for="color" class="form-label">Color</label>
                <input type="text" class="form-control" id="color" name="color" value="<?php echo $color; ?>">
                <span class="text-danger"><?php echo $colorErr; ?></span>
            </div>

            <input type="hidden" name="productAddedBy" value="<?php echo $productAddedBy; ?>">

            <button type="submit" class="btn btn-primary">Add Product</button>
        </form>
    </div>
    <footer class="text-center text-lg-start mt-5" style="background-color: skyblue;">
    
    <div class="text-center p-3" style="background-color: skyblue;">
        © 2024 Shoes Store. All rights reserved.
    </div>
</footer>


    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
