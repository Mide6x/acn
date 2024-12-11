<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
require_once $_SERVER['DOCUMENT_ROOT'] . '/acnnew/include/config.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/acnnew/class/rev.php';

include("../includes/header.html");
include("../includes/sidebar.html");

$revenue = new Revenue($con);
$departments = $revenue->getAllDepartments();

// Get current hour for greeting
$hour = date('H');
$greeting = '';
if ($hour < 12) {
    $greeting = 'Good Morning';
} else if ($hour < 17) {
    $greeting = 'Good Afternoon'; 
} else {
    $greeting = 'Good Evening';
}

// Get HR name from email
$hrName = explode('@', CURRENT_USER['email'])[0];
$hrName = ucwords(str_replace('.', ' ', $hrName));
?>

<main id="main" class="main">
    <section class="section">
        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title"><?php echo $greeting . ', ' . $hrName; ?></h5>
                        <h6 class="card-subtitle mb-3">Departments Management</h6>
                        
                        <!-- Add Department Button -->
                        <button type="button" class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#addDepartmentModal">
                            <i class="bi bi-plus-circle"></i> Add Department
                        </button>

                        <!-- Departments Table -->
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>Department Code</th>
                                        <th>Department Name</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($departments as $dept): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($dept['departmentcode']); ?></td>
                                        <td><?php echo htmlspecialchars($dept['departmentname']); ?></td>
                                        <td>
                                            <div class="form-check form-switch">
                                                <input class="form-check-input status-toggle" type="checkbox" 
                                                       data-deptcode="<?php echo htmlspecialchars($dept['departmentcode']); ?>"
                                                       <?php echo $dept['status'] === 'Active' ? 'checked' : ''; ?>>
                                                <label class="form-check-label"><?php echo $dept['status']; ?></label>
                                            </div>
                                        </td>
                                        <td>
                                            <button class="btn btn-sm btn-info edit-dept" 
                                                    data-deptcode="<?php echo htmlspecialchars($dept['departmentcode']); ?>"
                                                    data-deptname="<?php echo htmlspecialchars($dept['departmentname']); ?>">
                                                <i class="bi bi-pencil"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</main>

<!-- Add Department Modal -->
<div class="modal fade" id="addDepartmentModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add New Department</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="addDepartmentForm">
                    <div class="mb-3">
                        <label for="departmentName" class="form-label">Department Name</label>
                        <input type="text" class="form-control" id="departmentName" required>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="saveDepartment">Save</button>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Handle status toggle
    $('.status-toggle').change(function() {
        const deptCode = $(this).data('deptcode');
        $.ajax({
            url: '../class/parameters.php',
            type: 'POST',
            data: {
                action: 'toggle_department_status',
                departmentcode: deptCode
            },
            success: function(response) {
                if (response === 'success') {
                    location.reload();
                } else {
                    alert('Error updating status: ' + response);
                }
            },
            error: function() {
                alert('Error updating status');
            }
        });
    });

    // Handle add department
    $('#saveDepartment').click(function() {
        const deptName = $('#departmentName').val();
        if (!deptName) {
            alert('Please enter department name');
            return;
        }

        $.ajax({
            url: '../class/parameters.php',
            type: 'POST',
            data: {
                action: 'create_department',
                departmentname: deptName
            },
            success: function(response) {
                if (response === 'success') {
                    location.reload();
                } else {
                    alert('Error creating department: ' + response);
                }
            },
            error: function() {
                alert('Error creating department');
            }
        });
    });
});
</script>

<?php include("../includes/footer.html"); ?>