<?php
require_once __DIR__ . '/db.php';

$errorMessage = '';
$successMessage = isset($_GET['saved']) ? 'Product saved successfully.' : '';
$pdo = null;
$products = [];
$formData = [
    'name' => '',
    'category' => '',
    'quantity' => '',
    'price' => '',  
    'description' => '',
];
$categories = ['drink', 'food', 'other'];

function escapeProductText(?string $value): string
{
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

try {
    $pdo = getDatabaseConnection();
} catch (PDOException $exception) {
    error_log('Product database connection failed: ' . $exception->getMessage());
    $errorMessage = 'Unable to connect to the a18_afternoon database. Start MySQL in Laragon and check the credentials in db.php.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($formData as $field => $value) {
        if (isset($_POST[$field]) && is_string($_POST[$field])) {
            $formData[$field] = trim($_POST[$field]);
        }
    }

    $quantity = filter_var($formData['quantity'], FILTER_VALIDATE_INT);
    $price = filter_var($formData['price'], FILTER_VALIDATE_FLOAT);

    if ($formData['name'] === '' || strlen($formData['name']) > 50) {
        $errorMessage = 'Enter a product name of 1 to 50 characters.';
    } elseif (!in_array($formData['category'], $categories, true)) {
        $errorMessage = 'Choose a valid product category.';
    } elseif ($quantity === false || $quantity < 0) {
        $errorMessage = 'Enter a whole-number quantity of zero or more.';
    } elseif ($price === false || !is_finite((float) $price) || $price < 0 || strlen($formData['price']) > 12) {
        $errorMessage = 'Enter a valid price of zero or more.';
    } elseif (strlen($formData['description']) > 200) {
        $errorMessage = 'The description must be 200 characters or fewer.';
    } elseif ($pdo !== null) {
        try {
            $statement = $pdo->prepare(
                'INSERT INTO product (name, categary, quantity, price, description)
                 VALUES (:name, :category, :quantity, :price, :description)'
            );
            $statement->execute([
                'name' => $formData['name'],
                'category' => $formData['category'],
                'quantity' => $quantity,
                'price' => number_format((float) $price, 2, '.', ''),
                'description' => $formData['description'],
            ]);

            header('Location: product.php?saved=1');
            exit;
        } catch (PDOException $exception) {
            error_log('Product insert failed: ' . $exception->getMessage());
            $errorMessage = 'Unable to save the product. Check that the product table has the required columns.';
        }
    }
}

if ($pdo !== null) {
    try {
        $products = $pdo->query(
            'SELECT name, categary AS category, quantity, price, description
             FROM product'
        )->fetchAll();
    } catch (PDOException $exception) {
        error_log('Product list query failed: ' . $exception->getMessage());
        $errorMessage = 'Unable to load products. Check that the product table has the required columns.';
    }
}
?>

<?php include 'header.php'; ?>

<div class="row min-vh-100 g-0">
    <div class="col-lg-3 bg-white border-end">
        <?php include 'sidebar.php'; ?>
    </div>

    <main class="col-lg-9 p-4 p-lg-5 bg-light">
        <div class="mx-auto" style="max-width: 900px;">
            <h1 class="h2 mb-4">Products</h1>

            <form class="bg-white p-4 rounded shadow-sm" method="post" action="product.php">
                <h2 class="h4 mb-4">Add Product</h2>

                <?php if ($errorMessage !== ''): ?>
                    <div class="alert alert-danger" role="alert"><?= escapeProductText($errorMessage) ?></div>
                <?php elseif ($successMessage !== ''): ?>
                    <div class="alert alert-success" role="status"><?= escapeProductText($successMessage) ?></div>
                <?php endif; ?>

                <div class="mb-3">
                    <label for="name" class="form-label">Name</label>
                    <input type="text" class="form-control" id="name" name="name" maxlength="50" value="<?= escapeProductText($formData['name']) ?>" required>
                </div>

                <div class="mb-3">
                    <label for="category" class="form-label">Category</label>
                    <select class="form-select" id="category" name="category" required>
                        <option value="" disabled <?= $formData['category'] === '' ? 'selected' : '' ?>>Choose a category</option>
                        <option value="drink" <?= $formData['category'] === 'drink' ? 'selected' : '' ?>>Drink</option>
                        <option value="food" <?= $formData['category'] === 'food' ? 'selected' : '' ?>>Food</option>
                        <option value="other" <?= $formData['category'] === 'other' ? 'selected' : '' ?>>Other</option>
                    </select>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="quantity" class="form-label">Quantity</label>
                        <input type="number" class="form-control" id="quantity" name="quantity" min="0" step="1" value="<?= escapeProductText($formData['quantity']) ?>" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="price" class="form-label">Price</label>
                        <input type="number" class="form-control" id="price" name="price" min="0" step="0.01" value="<?= escapeProductText($formData['price']) ?>" required>
                    </div>
                </div>

                <div class="mb-4">
                    <label for="description" class="form-label">Description</label>
                    <textarea class="form-control" id="description" name="description" rows="4" maxlength="200"><?= escapeProductText($formData['description']) ?></textarea>
                </div>

                <button type="submit" class="btn btn-primary" <?= $pdo === null ? 'disabled' : '' ?>>Save Product</button>
            </form>

            <section class="mt-5">
                <h2 class="h4 mb-3">Saved Products</h2>
                <div class="table-responsive bg-white">
                    <table class="table table-bordered table-striped mb-0">
                        <thead class="table-dark">
                            <tr>
                                <th>Name</th>
                                <th>Category</th>
                                <th>Quantity</th>
                                <th>Price</th>
                                <th>Description</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($products as $product): ?>
                                <tr>
                                    <td><?= escapeProductText($product['name']) ?></td>
                                    <td><?= escapeProductText(ucfirst($product['category'])) ?></td>
                                    <td><?= escapeProductText((string) $product['quantity']) ?></td>
                                    <td><?= escapeProductText(number_format((float) $product['price'], 2)) ?></td>
                                </tr>
                            <?php endforeach; ?>
                            <?php if ($products === []): ?>
                                               <td><?= escapeProductText($product['description']) ?></td>
                     <tr>
                                    <td colspan="5" class="text-center text-secondary">No products have been saved yet.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </section>
        </div>
    </main>
</div>

<?php include 'footer.php'; ?>
