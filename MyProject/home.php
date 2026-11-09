<!-- Start Header -->
<?php include 'header.php';?>
<!-- End Header -->

<!-- Menu and Main Page -->
<div class="row min-vh-100 g-0">
    <div class="col-lg-3 bg-white border-end">
        <?php include 'sidebar.php'; ?>
    </div>

    <div class="col-lg-9 p-4 bg-light">
        <div class="mx-auto" style="max-width: 700px;">
            <form class="bg-white p-4 rounded shadow-sm w-100" method="post" action="#">
                <h2 class="mb-4">Personal Information</h2>

                <div class="mb-3">
                    <label for="topText" class="form-label">Short Text</label>
                    <input type="text" class="form-control" id="topText" name="top_text" required>
                </div>

                <div class="mb-3">
                    <label for="name" class="form-label">Name</label>
                    <input type="text" class="form-control" id="name" name="name" required>
                </div>

                <div class="mb-3">
                    <label for="sex" class="form-label">Sex</label>
                    <select class="form-select" id="sex" name="sex" required>
                        <option value="" selected disabled>Select sex</option>
                        <option value="male">Male</option>
                        <option value="female">Female</option>
                        <option value="other">Other</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label for="dateOfBirth" class="form-label">Date of Birth</label>
                    <input type="date" class="form-control" id="dateOfBirth" name="date_of_birth" required>
                </div>

                <div class="mb-3">
                    <label for="phoneNumber" class="form-label">Phone Number</label>
                    <input type="tel" class="form-control" id="phoneNumber" name="phone_number" required>
                </div>

                <div class="mb-3">
                    <label for="category" class="form-label">Location</label>
                    <select class="form-select" id="category" name="category" required>
                        <option value="" selected disabled>Select an option</option>
                        <option value="option1">Siem Reap</option>
                        <option value="option2">Phnom Penh</option>
                        <option value="option3">Other</option>
                    </select>
                </div>

                <div class="mb-4">
                    <label for="work" class="form-label">Work</label>
                    <input type="text" class="form-control" id="work" name="work" required>
                </div>

                <button type="submit" class="btn btn-primary">Submit</button>
            </form>

            <div class="mt-5">
                <h3 class="mb-3">Form Data Table</h3>
                <table class="table table-bordered table-striped mt-4 bg-white w-100">
                    <thead class="table-dark">
                        <tr>
                            <th>Short Text</th>
                            <th>Name</th>
                            <th>Sex</th>
                            <th>Date of Birth</th>
                            <th>Phone Number</th>
                            <th>Location</th>
                            <th>Work</th>
                        </tr>
                    </thead>
                    <tbody id="formDataTableBody">
                        <!-- Form data will be appended here -->
                    </tbody>   
                  
                </table>
            </div>
        </div>
    </div>
</div>
<!-- End Menu and Main Page -->

<!--Start Footer -->
<?php include 'footer.php'; ?>
<!--End Footer -->