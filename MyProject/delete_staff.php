<?php
require_once __DIR__ . '/db.php';

$errorMessage = '';
$successMessage = isset($_GET['saved']) ? 'Staff record saved successfully.' : '';
$pdo = null;
$staff = [];
$sexOptions = [
    'male' => 'ប្រុស',
    'female' => 'ស្រី',
];
$pobOptions = [
    'phnom penh' => 'ភ្នំពេញ',
    'kandal' => 'កណ្តាល',
    'kampong cham' => 'កំពង់ចាម',
    'kampong speu' => 'កំពង់ស្ពឺ',
    'kampong thom' => 'កំពង់ធំ',
    'kampot' => 'កំពត',
    'koh kong' => 'កោះកុង',
    'kratie' => 'ក្រចេះ',
    'mondulkiri' => 'មណ្ឌលគិរី',
    'oddar meanchey' => 'ឧត្តរមានជ័យ',
    'pursat' => 'ពោធិ៍សាត់',
    'preah vihear' => 'ព្រះវិហារ',
    'prey veng' => 'ព្រៃវែង',
    'ratanakiri' => 'រតនគិរី',
    'siem reap' => 'សៀមរាប',
    'stung treng' => 'ស្ទឹងត្រែង',
    'svay rieng' => 'ស្វាយរៀង',
    'takeo' => 'តាកែវ',
    'banteay meanchey' => 'បន្ទាយមានជ័យ',
    'batdambang' => 'បាត់ដំបង',
    'kampong chhnang' => 'កំពង់ឆ្នាំង',
    'other' => 'ផ្សេងៗ',
];
$countryOptions = [
    'cambodia' => 'កម្ពុជា',
    'vietnam' => 'វៀតណាម',
    'laos' => 'ឡាវ',
    'china' => 'ចិន',
    'japan' => 'ជប៉ុន',
    'south korea' => 'កូរ៉េ',
    'united states' => 'សហរដ្ឋអាមេរិក',
    'france' => 'បារាំង',
    'australia' => 'អូស្ត្រាលី',
    'other' => 'ផ្សេងៗ',
];

try {
    $pdo = getDatabaseConnection();
} catch (PDOException $exception) {
    error_log('Staff database connection failed: ' . $exception->getMessage());
    $errorMessage = 'Unable to connect to the database. Check the database settings and import database.sql.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $pdo !== null) {
    $name = isset($_POST['name']) && is_string($_POST['name']) ? trim($_POST['name']) : '';
    $sex = isset($_POST['sex']) && is_string($_POST['sex']) ? $_POST['sex'] : '';
    $dob = isset($_POST['dob']) && is_string($_POST['dob']) ? $_POST['dob'] : '';
    $pob = isset($_POST['pob']) && is_string($_POST['pob']) ? $_POST['pob'] : '';
    $country = isset($_POST['country']) && is_string($_POST['country']) ? $_POST['country'] : '';
    $phone = isset($_POST['phone']) && is_string($_POST['phone']) ? trim($_POST['phone']) : '';
    $date = preg_match('/^\d{4}-\d{2}-\d{2}$/D', $dob)
        ? DateTime::createFromFormat('!Y-m-d', $dob)
        : false;

    if ($name === '' || strlen($name) > 50 || $phone === '') {
        $errorMessage = 'Enter a name (up to 50 characters) and phone number.';
    } elseif (!isset($sexOptions[$sex]) || !isset($pobOptions[$pob]) || !isset($countryOptions[$country])) {
        $errorMessage = 'Choose valid values for sex, place of birth, and country.';
    } elseif (!$date || $date->format('Y-m-d') !== $dob) {
        $errorMessage = 'Enter a valid date of birth.';
    } else {
        try {
            $statement = $pdo->prepare(
                'INSERT INTO staff (sex, dob, pob, country, phone, name)
                 VALUES (:sex, :dob, :pob, :country, :phone, :name)'
            );
            $statement->execute([
                'sex' => $sex,
                'dob' => $dob,
                'pob' => $pob,
                'country' => $country,
                'phone' => $phone,
                'name' => $name,
            ]);

            header('Location: staff.php?saved=1');
            exit;
        } catch (PDOException $exception) {
            error_log('Staff record insert failed: ' . $exception->getMessage());
            $errorMessage = 'Unable to save the staff record. Please try again.';
        }
    }
}

if ($pdo !== null) {
    try {
        $staff = $pdo->query(
            'SELECT id, name, sex, dob, pob, country, phone FROM staff ORDER BY id'
        )->fetchAll();
    } catch (PDOException $exception) {
        error_log('Staff list query failed: ' . $exception->getMessage());
        $errorMessage = 'Unable to load staff records. Check that database.sql has been imported.';
    }
}

function escapeHtml(?string $value): string
{
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}
?>

<!-- Start Header -->
<?php include 'header.php'; ?>
<!-- End Header -->

<div class="row min-vh-100 g-0">
    <div class="col-lg-3 bg-white border-end">
        <?php include 'sidebar.php'; ?>
    </div>

    <div class="col-lg-9 p-4 bg-light">
        <div class="card mb-4">
            <div class="card-header"><h3>Staff List</h3></div>
            <form action="staff.php" method="post">
                <div class="card-body">
                    <?php if ($errorMessage !== ''): ?>
                        <div class="alert alert-danger" role="alert"><?= escapeHtml($errorMessage) ?></div>
                    <?php elseif ($successMessage !== ''): ?>
                        <div class="alert alert-success" role="status"><?= escapeHtml($successMessage) ?></div>
                    <?php endif; ?>

                    <label for="name">ឈ្មោះ</label>
                    <input type="text" name="name" id="name" maxlength="50" required class="form-control mb-2">

                    <label for="sex">ភេទ</label>
                    <select name="sex" id="sex" required class="form-control mb-2">
                        <?php foreach ($sexOptions as $value => $label): ?>
                            <option value="<?= escapeHtml($value) ?>"><?= escapeHtml($label) ?></option>
                        <?php endforeach; ?>
                    </select>

                    <label for="dob">ថ្ងៃ ខែ ឆ្នាំកំណើត</label>
                    <input type="date" name="dob" id="dob" required class="form-control mb-2">

                    <label for="pob">ទីកន្លែងកំណើត</label>
                    <select name="pob" id="pob" required class="form-control mb-2">
                        <?php foreach ($pobOptions as $value => $label): ?>
                            <option value="<?= escapeHtml($value) ?>"><?= escapeHtml($label) ?></option>
                        <?php endforeach; ?>
                    </select>

                    <label for="country">ប្រទេស</label>
                    <select name="country" id="country" required class="form-control mb-2">
                        <?php foreach ($countryOptions as $value => $label): ?>
                            <option value="<?= escapeHtml($value) ?>"><?= escapeHtml($label) ?></option>
                        <?php endforeach; ?>
                    </select>

                    <label for="phone">លេខទូរស័ព្ទ</label>
                    <input type="tel" name="phone" id="phone" required class="form-control mb-2">
                </div>
                <div class="card-footer text-center">
                    <button type="submit" class="btn btn-primary" <?= $pdo === null ? 'disabled' : '' ?>>Save</button>
                </div>
            </form>
        </div>

       
    </div>
</div>

<!--Start Footer -->
<?php include 'footer.php'; ?>
<!--End Footer -->
