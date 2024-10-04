<?php
require('db_connection_mysqli.php');


// Initialize variables to hold form values and error messages
$productId = $productName = $description = $quantity = $price = $category = $size = $color = "";
$productNameErr = $descriptionErr = $quantityErr = $priceErr = $categoryErr = $sizeErr = $colorErr = "";

// Check if the product ID is provided
if (isset($_GET['productId'])) {
    $productId = $_GET['productId'];

    // Fetch existing product details from the database
    $query = "SELECT * FROM shoes WHERE productId = ?";
    $stmt = mysqli_prepare($dbc, $query);
    mysqli_stmt_bind_param($stmt, 'i', $productId);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    // If the product is found, populate the form fields
    if ($row = mysqli_fetch_assoc($result)) {
        $productName = $row['productName'];
        $description = $row['description'];
        $quantity = $row['quantity'];
        $price = $row['price'];
        $category = $row['category'];
        $size = $row['size'];
        $color = $row['color'];
    } else {
        echo "Product not found!";
        exit;
    }
}

// Function to sanitize form inputs
function cleanInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Validate form inputs after submission
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

    // If all validations pass, proceed with form submission
    if (empty($productNameErr) && empty($descriptionErr) && empty($quantityErr) && empty($priceErr) &&
        empty($categoryErr) && empty($sizeErr) && empty($colorErr)) {

        // Clean inputs
        $productName_clean = prepare_string($dbc, $productName);
        $description_clean = prepare_string($dbc, $description);
        $quantity_clean = prepare_string($dbc, $quantity);
        $price_clean = prepare_string($dbc, $price);
        $category_clean = prepare_string($dbc, $category);
        $size_clean = prepare_string($dbc, $size);
        $color_clean = prepare_string($dbc, $color);

        // Update data in the database
        $updateQuery = "UPDATE shoes SET productName=?, description=?, quantity=?, price=?, category=?, size=?, color=? WHERE productId=?";
        $updateStmt = mysqli_prepare($dbc, $updateQuery);

        // Bind parameters
        mysqli_stmt_bind_param($updateStmt, 'sssssssi', $productName_clean, $description_clean, $quantity_clean, 
                               $price_clean, $category_clean, $size_clean, $color_clean, $productId);

        // Execute the statement
        $result = mysqli_stmt_execute($updateStmt);

        if ($result) {
            header("Location: index.php"); // Redirect on success to refresh the page
            exit;
        } else {
            echo "<br>Some error in updating the data.";
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
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h2>Edit Shoe Product</h2>
        <form action="edit.php?productId=<?php echo $productId; ?>" method="POST">
            <div class="mb-3">
                <label for="productName" class="form-label">Product Name</label>
                <input type="text" class="form-control" id="productName" name="productName" value="<?php echo $productName; ?>">
                <span class="text-danger"><?php echo $productNameErr; ?></span>
            </div>
            <div class="mb-3">
                <label for="description" class="form-label">Description</label>
                <input type="text" class="form-control" id="description" name="description" value="<?php echo $description; ?>">
                <span class="text-danger"><?php echo $descriptionErr; ?></span>
            </div>
            <div class="mb-3">
                <label for="quantity" class="form-label">Quantity</label>
                <input type="number" class="form-control" id="quantity" name="quantity" value="<?php echo $quantity; ?>">
                <span class="text-danger"><?php echo $quantityErr; ?></span>
            </div>
            <div class="mb-3">
                <label for="price" class="form-label">Price</label>
                <input type="number" class="form-control" id="price" name="price" step="0.01" value="<?php echo $price; ?>">
                <span class="text-danger"><?php echo $priceErr; ?></span>
            </div>
            <div class="mb-3">
                <label for="category" class="form-label">Category</label>
                <select class="form-select" id="category" name="category">
                    <option value="" selected disabled>Select Category</option>
                    <option value="Athletic" <?php echo ($category == "Athletic") ? 'selected' : ''; ?>>Athletic</option>
                    <option value="Casual" <?php echo ($category == "Casual") ? 'selected' : ''; ?>>Casual</option>
                    <option value="Formal" <?php echo ($category == "Formal") ? 'selected' : ''; ?>>Formal</option>
                    <option value="Sandals" <?php echo ($category == "Sandals") ? 'selected' : ''; ?>>Sandals</option>
                    <option value="Boots" <?php echo ($category == "Boots") ? 'selected' : ''; ?>>Boots</option>
                </select>
                <span class="text-danger"><?php echo $categoryErr; ?></span>
            </div>
            <div class="mb-3">
                <label for="size" class="form-label">Size</label>
                <select class="form-select" id="size" name="size">
                    <option value="" selected disabled>Select Size</option>
                    <option value="5" <?php echo ($size == "5") ? 'selected' : ''; ?>>5</option>
                    <option value="6" <?php echo ($size == "6") ? 'selected' : ''; ?>>6</option>
                    <option value="7" <?php echo ($size == "7") ? 'selected' : ''; ?>>7</option>
                    <option value="8" <?php echo ($size == "8") ? 'selected' : ''; ?>>8</option>
                </select>
                <span class="text-danger"><?php echo $sizeErr; ?></span>
            </div>
            <div class="mb-3">
                <label for="color" class="form-label">Color</label>
                <input type="text" class="form-control" id="color" name="color" value="<?php echo $color; ?>">
                <span class="text-danger"><?php echo $colorErr; ?></span>
            </div>
            <button type="submit" class="btn btn-primary">Update Product</button>
            <a href="index.php" class="btn btn-secondary">Cancel</a>
        </form>
    </div>
</body>
</html>