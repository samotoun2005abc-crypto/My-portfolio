<?php
// Start the session to access the CSRF token used by the delete form.
session_start();
// Load the database connection helper.
require_once __DIR__ . '/../MyProject/db.php';

// Only allow product deletion through a POST request.
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: product.php?delete_error=1');
    exit;
}

// Validate the product ID and read the submitted and session CSRF tokens.
$productId = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT, [
    'options' => ['min_range' => 1],
]);
$submittedToken = isset($_POST['csrf_token']) && is_string($_POST['csrf_token'])
    ? $_POST['csrf_token']
    : '';
$sessionToken = isset($_SESSION['csrf_token']) && is_string($_SESSION['csrf_token'])
    ? $_SESSION['csrf_token']
    : '';

// Reject invalid IDs or requests with a missing or incorrect CSRF token.
if (
    $productId === false
    || $productId === null
    || $sessionToken === ''
    || !hash_equals($sessionToken, $submittedToken)
) {
    header('Location: product.php?delete_error=1');
    exit;
}

try {
    $pdo = getDatabaseConnection();
    // Delete the matching product using a prepared statement.
    $statement = $pdo->prepare('DELETE FROM products WHERE id = ?');
    $statement->execute([$productId]);
    $wasDeleted = $statement->rowCount() === 1;

    // Show success only when a product was actually deleted.
    header('Location: product.php?' . ($wasDeleted ? 'deleted=1' : 'delete_error=1'));
    exit;
} catch (PDOException $exception) {
    // Log the database error and return to the product list with an error status.
    error_log('Product delete failed: ' . $exception->getMessage());
    header('Location: product.php?delete_error=1');
    exit;
}
