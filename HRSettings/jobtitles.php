<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
require_once $_SERVER['DOCUMENT_ROOT'] . '/acnnew/include/config.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/acnnew/class/rev.php';

include("../includes/header.html");
include("../includes/sidebar.html");

$revenue = new Revenue($con);

// Pagination settings
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10; // Items per page
$search = isset($_GET['search']) ? $_GET['search'] : '';

// Get paginated and filtered job titles
$result = $revenue->getAllJobTitles($page, $limit, $search);
$jobtitles = $result['data'];
$total_pages = $result['total_pages'];

$departments = $revenue->getDepartmentUnits();

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
                        <h6 class="card-subtitle mb-3">Job Titles Management</h6>
                        
                        <!-- Search and Add Button Row -->
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <div class="input-group">
                                    <input type="text" class="form-control" id="searchInput" 
                                           placeholder="Search job titles..." 
                                           value="<?php echo htmlspecialchars($search); ?>">
                                    <button class="btn btn-outline-secondary" type="button" id="searchBtn">
                                        <i class="bi bi-search"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="col-md-6 text-end">
                                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addJobTitleModal">
                                    <i class="bi bi-plus-circle"></i> Add Job Title
                                </button>
                            </div>
                        </div>

                        <!-- Job Titles Table -->
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>Job Title</th>
                                        <th>Department</th>
                                        <th>Description</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($jobtitles as $job): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($job['jdtitle']); ?></td>
                                        <td><?php echo htmlspecialchars($job['deptunitname']); ?></td>
                                        <td><?php echo htmlspecialchars($job['jddescription'] ?? 'No description available'); ?></td>
                                        <td>
                                            <div class="form-check form-switch">
                                                <input class="form-check-input status-toggle" type="checkbox" 
                                                       data-jobtitle="<?php echo htmlspecialchars($job['jdtitle']); ?>"
                                                       data-deptcode="<?php echo htmlspecialchars($job['deptunitcode']); ?>"
                                                       <?php echo $job['jdstatus'] === 'Active' ? 'checked' : ''; ?>>
                                                <label class="form-check-label"><?php echo $job['jdstatus']; ?></label>
                                            </div>
                                        </td>
                                        <td>
                                            <button class="btn btn-sm btn-info edit-jobtitle" 
                                                    data-jobtitle="<?php echo htmlspecialchars($job['jdtitle']); ?>"
                                                    data-deptcode="<?php echo htmlspecialchars($job['deptunitcode']); ?>"
                                                    data-description="<?php echo htmlspecialchars($job['jddescription'] ?? ''); ?>">
                                                <i class="bi bi-pencil"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <nav aria-label="Page navigation" class="mt-4">
                            <ul class="pagination justify-content-center">
                                <?php if ($page > 1): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="?page=<?php echo ($page-1); ?>&search=<?php echo urlencode($search); ?>">Previous</a>
                                    </li>
                                <?php endif; ?>
                                
                                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                    <li class="page-item <?php echo ($i == $page) ? 'active' : ''; ?>">
                                        <a class="page-link" href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>"><?php echo $i; ?></a>
                                    </li>
                                <?php endfor; ?>
                                
                                <?php if ($page < $total_pages): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="?page=<?php echo ($page+1); ?>&search=<?php echo urlencode($search); ?>">Next</a>
                                    </li>
                                <?php endif; ?>
                            </ul>
                        </nav>
                    </div>
                </div>
            </div>
        </div>
    </section>
</main>

<!-- Add Job Title Modal -->
<div class="modal fade" id="addJobTitleModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add New Job Title</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="addJobTitleForm">
                    <div class="mb-3">
                        <label for="jobTitle" class="form-label">Job Title</label>
                        <input type="text" class="form-control" id="jobTitle" required>
                    </div>
                    <div class="mb-3">
                        <label for="department" class="form-label">Department</label>
                        <select class="form-control" id="department" required>
                            <option value="">Select Department</option>
                            <?php echo $departments; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control" id="description" rows="3" required></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="saveJobTitle">Save</button>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Handle search button click
    $('#searchBtn').click(function() {
        const searchTerm = $('#searchInput').val();
        window.location.href = '?page=1&search=' + encodeURIComponent(searchTerm);
    });

    // Handle enter key in search input
    $('#searchInput').keypress(function(e) {
        if (e.which == 13) {
            $('#searchBtn').click();
        }
    });

    // Handle status toggle
    $('.status-toggle').change(function() {
        const jobTitle = $(this).data('jobtitle');
        const deptCode = $(this).data('deptcode');
        $.ajax({
            url: '../class/parameters.php',
            type: 'POST',
            data: {
                action: 'toggle_jobtitle_status',
                jobtitle: jobTitle,
                deptunitcode: deptCode
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

    // Handle add job title
    $('#saveJobTitle').click(function() {
        const jobTitle = $('#jobTitle').val();
        const department = $('#department').val();
        const description = $('#description').val();

        if (!jobTitle || !department || !description) {
            alert('Please fill in all fields');
            return;
        }

        $.ajax({
            url: '../class/parameters.php',
            type: 'POST',
            data: {
                action: 'create_jobtitle',
                jobtitle: jobTitle,
                deptunitcode: department,
                description: description
            },
            success: function(response) {
                if (response === 'success') {
                    location.reload();
                } else {
                    alert('Error creating job title: ' + response);
                }
            },
            error: function() {
                alert('Error creating job title');
            }
        });
    });
});
</script>

<?php include("../includes/footer.html"); ?>
