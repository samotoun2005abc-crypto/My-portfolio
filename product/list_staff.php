<?php
require_once __DIR__ . '/../MyProject/db.php';

$staffMembers = [];
$errorMessage = '';

try {
    $pdo = getDatabaseConnection();
    $staffMembers = $pdo->query(
        'SELECT id, name, sex, dob, pob, phone FROM staff ORDER BY id'
    )->fetchAll();
} catch (PDOException $exception) {
    error_log('Staff list query failed: ' . $exception->getMessage());
    $errorMessage = 'Unable to load the staff list. Check the database connection and staff table.';
}

function escapeStaffValue(?string $value): string
{
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

include 'header.php';
?>
<!-- Manu and Main Page -->
<div class="row min-vh-100 product-page">
    <aside class="col-lg-3 product-sidebar">
        <?php include 'sidebar.php'; ?>
    </aside>

    <main class="col-lg-9 product-content">
        <section class="card product-card">
            <header class="card-header product-card-header">
                <h2 class="h3 mb-0">Staff List</h2>
            </header>

            <div class="card-body">
                <?php if ($errorMessage !== ''): ?>
                    <div class="alert alert-danger" role="alert">
                        <?= escapeStaffValue($errorMessage) ?>
                    </div>
                <?php endif; ?>

                <div class="table-responsive">
                    <table class="table table-hover align-middle product-table">
                        <thead>
                            <tr>
                                <th scope="col">ល.រ</th>
                                <th scope="col">ឈ្មោះ</th>
                                <th scope="col">ភេទ</th>
                                <th scope="col">ថ្ងៃ-ខែ-ឆ្នាំកំណើត</th>
                                <th scope="col">ទីកន្លែងកំណើត</th>
                                <th scope="col">លេខទូរស័ព្ទ</th>
                                <th scope="col">សកម្មភាព</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($staffMembers as $index => $staff): ?>
                                <tr>
                                    <td><?= $index + 1 ?></td>
                                    <td><?= escapeStaffValue($staff['name']) ?></td>
                                    <td><?= escapeStaffValue($staff['sex']) ?></td>
                                    <td><?= escapeStaffValue($staff['dob']) ?></td>
                                    <td><?= escapeStaffValue($staff['pob']) ?></td>
                                    <td><?= escapeStaffValue($staff['phone']) ?></td>
                                    <td class="text-nowrap">
                                        <a href="edit_staff.php?id=<?= (int) $staff['id'] ?>" class="btn btn-warning btn-sm">Edit</a>
                                        <a href="delete_staff.php?id=<?= (int) $staff['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this staff?');">Delete</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            <?php if ($staffMembers === [] && $errorMessage === ''): ?>
                                <tr>
                                    <td colspan="7" class="text-center text-secondary">No staff records found.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </section>
    </main>
</div>

<!--End Footer -->



<?php include 'footer.php'; ?>