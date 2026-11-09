<?php
// Start a session to access the CSRF token used by the edit form.
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

// Load the database connection helper.
require_once __DIR__ . '/../MyProject/db.php';

// Define the categories that users are allowed to assign to products.
$categories = ['food', 'drink', 'other'];
$errorMessage = '';
$pdo = null;
$product = [
    'name' => '',
    'category' => '',
    'quantity' => '',
    'price' => '',
    'description' => '',
];

if (!isset($_SESSION['csrf_token'])) {
    // Generate a token to verify that form submissions come from this site.
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Read the product ID from the appropriate source for this request.
$rawId = $_SERVER['REQUEST_METHOD'] === 'POST'
    ? ($_POST['id'] ?? '')
    : ($_GET['id'] ?? '');
$productId = filter_var($rawId, FILTER_VALIDATE_INT, [
    'options' => ['min_range' => 1],
]);

if ($productId === false) {
    // Return to the product list if the ID is missing or invalid.
    header('Location: product.php');
    exit;
}

try {
    $pdo = getDatabaseConnection();
    // Load the current product so its values can fill the edit form.
    $statement = $pdo->prepare(
        'SELECT id, name, category, quantity, price, description FROM products WHERE id = ?'
    );
    $statement->execute([$productId]);
    $existingProduct = $statement->fetch();

    if ($existingProduct === false) {
        // Return to the list if no product exists for this ID.
        header('Location: product.php');
        exit;
    }

    $product = $existingProduct;

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Check the submitted CSRF token before accepting changes.
        $submittedToken = isset($_POST['csrf_token']) && is_string($_POST['csrf_token'])
            ? $_POST['csrf_token']
            : '';

        if (!hash_equals($_SESSION['csrf_token'], $submittedToken)) {
            $errorMessage = 'Invalid form submission. Refresh the page and try again.';
        } else {
            // Read and trim the submitted values before validating them.
            $product['name'] = trim((string) ($_POST['name'] ?? ''));
            $product['category'] = (string) ($_POST['category'] ?? '');
            $product['quantity'] = trim((string) ($_POST['quantity'] ?? ''));
            $product['price'] = trim((string) ($_POST['price'] ?? ''));
            $product['description'] = trim((string) ($_POST['description'] ?? ''));
            $quantity = filter_var($product['quantity'], FILTER_VALIDATE_INT);
            $price = filter_var($product['price'], FILTER_VALIDATE_FLOAT);

            // Validate every field before updating the database.
            if ($product['name'] === '' || strlen($product['name']) > 100) {
                $errorMessage = 'Enter a product name of 1 to 100 characters.';
            } elseif (!in_array($product['category'], $categories, true)) {
                $errorMessage = 'Choose a valid product category.';
            } elseif ($quantity === false || $quantity < 0) {
                $errorMessage = 'Enter a whole-number quantity of zero or more.';
            } elseif ($price === false || !is_finite((float) $price) || $price < 0 || $price > 99999999.99) {
                $errorMessage = 'Enter a valid price between 0 and 99999999.99.';
            } else {
                // Update the product using a prepared statement.
                $updateStatement = $pdo->prepare(
                    'UPDATE products
                     SET name = ?, category = ?, quantity = ?, price = ?, description = ?
                     WHERE id = ?'
                );
                $updateStatement->execute([
                    $product['name'],
                    $product['category'],
                    $quantity,
                    number_format((float) $price, 2, '.', ''),
                    $product['description'],
                    $productId,
                ]);

                // Redirect to the list with a success indicator after saving.
                header('Location: product.php?updated=1');
                exit;
            }
        }
    }
} catch (PDOException $exception) {
    // Log database details for troubleshooting and show a safe message to the user.
    error_log('Product edit failed: ' . $exception->getMessage());
    $errorMessage = 'Unable to load or update this product.';
}

function escapeProductValue(?string $value): string
{
    // Escape values before displaying them in HTML.
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
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
                <h2 class="h3 mb-0">Edit Product</h2>
                <a href="product.php" class="btn btn-secondary">Back</a>
            </header>

            <div class="card-body">
                <!-- Show validation or database errors above the form. -->
                <?php if ($errorMessage !== ''): ?>
                    <div class="alert alert-danger" role="alert">
                        <?= escapeProductValue($errorMessage) ?>
                    </div>
                <?php endif; ?>

                <!-- Submit the edited product details to this page. -->
                <form method="post">
                    <!-- Keep the product ID and CSRF token with the form submission. -->
                    <input type="hidden" name="id" value="<?= (int) $productId ?>">
                    <input type="hidden" name="csrf_token" value="<?= escapeProductValue($_SESSION['csrf_token']) ?>">

                    <div class="mb-3">
                        <label for="name" class="form-label">Product Name</label>
                        <input type="text" id="name" name="name" class="form-control" maxlength="100" value="<?= escapeProductValue($product['name']) ?>" required>
                    </div>

                    <div class="mb-3">
                        <label for="category" class="form-label">Category</label>
                        <select id="category" name="category" class="form-select" required>
                            <option value="">Please Select</option>
                            <!-- Build the options from the allowed category list. -->
                            <?php foreach ($categories as $category): ?>
                                <option value="<?= escapeProductValue($category) ?>" <?= $product['category'] === $category ? 'selected' : '' ?>><?= escapeProductValue(ucfirst($category)) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="quantity" class="form-label">Quantity</label>
                        <input type="number" id="quantity" name="quantity" class="form-control" min="0" step="1" value="<?= escapeProductValue((string) $product['quantity']) ?>" required>
                    </div>

                    <div class="mb-3">
                        <label for="price" class="form-label">Price</label>
                        <input type="number" id="price" name="price" class="form-control" min="0" max="99999999.99" step="0.01" value="<?= escapeProductValue((string) $product['price']) ?>" required>
                    </div>

                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea id="description" name="description" class="form-control" rows="3"><?= escapeProductValue($product['description']) ?></textarea>
                    </div>

                    <div class="text-center">
                        <button type="submit" class="btn btn-primary" <?= $pdo === null ? 'disabled' : '' ?>>Save Changes</button>
                    </div>
                </form>
            </div>
        </section>
    </main>
</div>

<!-- Include the shared page footer. -->
<?php include 'footer.php'; ?>