<?php
// Start the session if it is not already active so session data is available below.
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

// Load the functions used to connect to the database.
require_once __DIR__ . '/../MyProject/db.php';

$errorMessage = '';
$products = [];
// Create a CSRF token to protect product deletion requests from other sites.
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

try {
    // Retrieve all products and sort them by ID.
    $pdo = getDatabaseConnection();
    $products = $pdo->query(
        'SELECT id, name, category, quantity, price, description FROM products ORDER BY id'
    )->fetchAll();
} catch (PDOException $exception) {
    // Log the technical error and show a user-friendly message.
    error_log('Product list database query failed: ' . $exception->getMessage());
    $errorMessage = 'Unable to load products. Check the database connection and products table.';
}

// Include the page header before displaying the product list.
include 'header.php';
?>

<div class="row min-vh-100 product-page">
    <aside class="col-lg-3 product-sidebar">
        <!-- Display the page's sidebar menu. -->
        <?php include 'sidebar.php'; ?>
    </aside>

    <main class="col-lg-9 product-content">
        <section class="card product-card">
            <header class="card-header product-card-header">
                <h2 class="h3 mb-0">Product List</h2>
                <!-- Link to the page for adding a new product. -->
                <a href="add-product.php" class="btn btn-primary">ផលិតផលថ្មី</a>
            </header>

            <div class="card-body">
                <!-- Show an error if the products could not be loaded from the database. -->
                <?php if ($errorMessage !== ''): ?>
                    <div class="alert alert-danger" role="alert">
                        <?= htmlspecialchars($errorMessage, ENT_QUOTES, 'UTF-8') ?>
                    </div>
                <?php endif; ?>
                <!-- Show the result of an action indicated by the URL. -->
                <?php if (isset($_GET['updated'])): ?>
                    <div class="alert alert-success" role="status">Product updated successfully.</div>
                <?php elseif (isset($_GET['deleted'])): ?>
                    <div class="alert alert-success" role="status">Product deleted successfully.</div>
                <?php elseif (isset($_GET['delete_error'])): ?>
                    <div class="alert alert-danger" role="alert">Could not delete the product.</div>
                <?php endif; ?>

                <div class="table-responsive">
                    <table class="table table-hover align-middle product-table">
                        <thead>
                            <tr>
                                <th scope="col">No.</th>
                                <th scope="col">Name</th>
                                <th scope="col">Category</th>
                                <th scope="col">Quantity</th>
                                <th scope="col">Price</th>
                                <th scope="col">Description</th>
                                <th scope="col">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Render one table row for each product. -->
                            <?php foreach ($products as $index => $product): ?>
                                <tr>
                                    <td><?= $index + 1 ?></td>
                                    <!-- Escape values before displaying them to prevent XSS. -->
                                    <td><?= htmlspecialchars((string) $product['name'], ENT_QUOTES, 'UTF-8') ?></td>
                                    <td><?= htmlspecialchars((string) $product['category'], ENT_QUOTES, 'UTF-8') ?></td>
                                    <td><?= htmlspecialchars((string) $product['quantity'], ENT_QUOTES, 'UTF-8') ?></td>
                                    <td><?= htmlspecialchars(number_format((float) $product['price'], 2), ENT_QUOTES, 'UTF-8') ?></td>
                                    <td><?= htmlspecialchars((string) ($product['description'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                                    <td class="text-nowrap">
                                        <!-- Edit the product or submit a POST request to delete it. -->
                                        <a href="edit_product.php?id=<?= (int) $product['id'] ?>" class="btn btn-warning btn-sm">Edit</a>
                                        <form method="post" action="delete_Product.php" class="d-inline" onsubmit="return confirm('Delete this product?');">
                                            <input type="hidden" name="id" value="<?= (int) $product['id'] ?>">
                                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8') ?>">
                                            <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            <!-- Show a message when the product list is empty. -->
                            <?php if ($products === []): ?>
                                <tr>
                                    <td colspan="7" class="text-secondary">No products found.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </section>
    </main>
</div>

<!-- Include the page footer. -->
<?php include 'footer.php'; ?>
