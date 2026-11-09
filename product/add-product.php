<?php
// Load the database connection helper.
require_once __DIR__ . '/../MyProject/db.php';

// Keep form values so they can be redisplayed if validation fails.
$errorMessage = '';
$name = '';
$category = '';
$quantity = '';
$price = '';
$description = '';
$pdo = null;

try {
    // Connect to the database before processing the form.
    $pdo = getDatabaseConnection();
} catch (PDOException $exception) {
    // Log the technical details and show a helpful message in the form.
    error_log('Product database connection failed: ' . $exception->getMessage());
    $errorMessage = 'Unable to connect to the a18_afternoon database. Check that MySQL is running in Laragon.';
}

// Process and validate the submitted form.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim((string) ($_POST['name'] ?? ''));
    $category = (string) ($_POST['category'] ?? '');
    $quantityInput = $_POST['quantity'] ?? '';
    $priceInput = $_POST['price'] ?? '';
    $description = trim((string) ($_POST['description'] ?? ''));
    $categories = ['food', 'drink', 'other'];
    $quantity = filter_var($quantityInput, FILTER_VALIDATE_INT);

    // Validate each field before attempting to save the product.
    if ($name === '') {
        $errorMessage = 'Please enter a product name.';
    } elseif (!in_array($category, $categories, true)) {
        $errorMessage = 'Please select a valid category.';
    } elseif ($quantity === false || $quantity < 0) {
        $errorMessage = 'Please enter a valid quantity.';
    } elseif (!is_numeric($priceInput) || (float) $priceInput < 0) {
        $errorMessage = 'Please enter a valid price.';
    } elseif ($pdo !== null) {
        // Convert the validated price input to a numeric value for storage.
        $price = (float) $priceInput;

        try {
            // Use a prepared statement to safely insert the new product.
            $statement = $pdo->prepare(
                'INSERT INTO products (name, category, quantity, price, description) VALUES (?, ?, ?, ?, ?)'
            );
            $statement->execute([$name, $category, $quantity, $price, $description]);

            // Return to the product list after the product is saved.
            header('Location: product.php');
            exit;
        } catch (PDOException $exception) {
            // Log the database error and keep the form available for correction.
            error_log($exception->getMessage());
            $errorMessage = 'Could not save the product. Check the products table columns.';
        }
    }
}

// Include the shared page header.
include 'header.php';
?>

<div class="row min-vh-100 product-page">
    <aside class="col-lg-3 product-sidebar">
        <!-- Display the shared sidebar navigation. -->
        <?php include 'sidebar.php'; ?>
    </aside>

    <main class="col-lg-9 product-content">
        <section class="card product-card">
            <header class="card-header product-card-header">
                <h2 class="h3 mb-0">Add Product</h2>
               
            </header>

            <div class="card-body">
                <!-- Display validation or database errors above the form. -->
                <?php if ($errorMessage !== ''): ?>
                    <div class="alert alert-danger" role="alert">
                        <?= htmlspecialchars($errorMessage, ENT_QUOTES, 'UTF-8') ?>
                    </div>
                <?php endif; ?>

                <!-- Collect the details needed to create a product. -->
                <form method="POST">
                    <div class="mb-3">
                        <label for="pName" class="form-label">Product Name</label>
                        <!-- Escape saved input before putting it back into the page. -->
                        <input type="text" id="pName" name="name" class="form-control" placeholder="Enter Product Name" value="<?= htmlspecialchars($name, ENT_QUOTES, 'UTF-8') ?>" required>
                    </div>

                    <div class="mb-3">
                        <label for="category" class="form-label">Category</label>
                        <!-- Limit category selection to the supported options. -->
                        <select id="category" name="category" class="form-select" required>
                            <option value="">Please Select</option>
                            <option value="food" <?= $category === 'food' ? 'selected' : '' ?>>Food</option>
                            <option value="drink" <?= $category === 'drink' ? 'selected' : '' ?>>Drink</option>
                            <option value="other" <?= $category === 'other' ? 'selected' : '' ?>>Other</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="qty" class="form-label">Quantity</label>
                        <!-- Accept zero or greater in whole-number increments. -->
                        <input type="number" id="qty" name="quantity" class="form-control" placeholder="Enter Quantity" min="0" step="1" value="<?= htmlspecialchars((string) $quantity, ENT_QUOTES, 'UTF-8') ?>" required>
                    </div>

                    <div class="mb-3">
                        <label for="price" class="form-label">Price</label>
                        <!-- Accept zero or greater with up to two decimal places. -->
                        <input type="number" id="price" name="price" class="form-control" placeholder="Enter Price" min="0" step="0.01" value="<?= htmlspecialchars((string) $price, ENT_QUOTES, 'UTF-8') ?>" required>
                    </div>

                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <!-- Description is optional. -->
                        <textarea id="description" name="description" class="form-control" rows="3"><?= htmlspecialchars($description, ENT_QUOTES, 'UTF-8') ?></textarea>
                    </div>

                    <div class="text-center">
                        <!-- Disable saving when the database connection is unavailable. -->
                        <button type="submit" class="btn btn-primary" <?= $pdo === null ? 'disabled' : '' ?>>Save</button>
                    </div>
                </form>
            </div>
        </section>
    </main>
</div>

<!-- Include the shared page footer. -->
<?php include 'footer.php'; ?>