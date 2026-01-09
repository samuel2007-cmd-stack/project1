<?php
session_start();

// Check if manager is logged in
if (!isset($_SESSION['manager_logged_in'])) {
    header("Location: login.php");
    exit();
}

require_once 'settings.php';

// Initialize variables
$message = "";
$message_type = "";
$results = array();
$search_performed = "";

// Pagination variables
$records_per_page = 50;
$current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$current_page = max(1, $current_page);
$offset = ($current_page - 1) * $records_per_page;
$total_records = 0;
$total_pages = 0;

// ============================================================================
// PROCESS FORM SUBMISSIONS
// ============================================================================

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // LIST ALL EOIs WITH SORTING
    if (isset($_POST['list_all'])) {
        $sort_field = isset($_POST['sort_field']) && !empty($_POST['sort_field']) ? $_POST['sort_field'] : 'EOInumber';
        $sort_order = isset($_POST['sort_order']) ? $_POST['sort_order'] : 'ASC';
        
        // Whitelist allowed fields to prevent SQL injection
        $allowed_fields = ['EOInumber', 'first_name', 'last_name', 'job_reference', 'status', 'created_at'];
        $sort_field = in_array($sort_field, $allowed_fields) ? $sort_field : 'EOInumber';
        $sort_order = ($sort_order == 'DESC') ? 'DESC' : 'ASC';
        
        // Get total count for pagination
        $count_query = "SELECT COUNT(*) as total FROM eoi";
        $count_result = mysqli_query($conn, $count_query);
        $count_row = mysqli_fetch_assoc($count_result);
        $total_records = $count_row['total'];
        $total_pages = ceil($total_records / $records_per_page);
        
        // Properly escape the field name (even though whitelisted, extra security)
        $safe_sort_field = mysqli_real_escape_string($conn, $sort_field);
        $query = "SELECT * FROM eoi ORDER BY $safe_sort_field $sort_order LIMIT $records_per_page OFFSET $offset";
        $result = mysqli_query($conn, $query);
        
        if ($result) {
            if (mysqli_num_rows($result) > 0) {
                while ($row = mysqli_fetch_assoc($result)) {
                    $results[] = $row;
                }
                $search_performed = "All EOI Records (sorted by " . ucfirst(str_replace('_', ' ', $sort_field)) . " - $sort_order)";
                $message = "Showing " . count($results) . " of $total_records total records";
                $message_type = "success";
            } else {
                $message = "No records found in database";
                $message_type = "error";
            }
        } else {
            error_log("Database query error: " . mysqli_error($conn));
            $message = "Error retrieving records";
            $message_type = "error";
        }
    }

    // SEARCH BY JOB REFERENCE
    if (isset($_POST['search_by_job'])) {
        $job_ref = trim($_POST['job_reference']);
        
        if (!empty($job_ref)) {
            $stmt = mysqli_prepare($conn, "SELECT * FROM eoi WHERE job_reference=? ORDER BY EOInumber DESC");
            mysqli_stmt_bind_param($stmt, "s", $job_ref);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            
            if (mysqli_num_rows($result) > 0) {
                while ($row = mysqli_fetch_assoc($result)) {
                    $results[] = $row;
                }
                $search_performed = "Job Reference: " . htmlspecialchars($job_ref);
                $message = "Found " . count($results) . " record(s) for job reference: " . htmlspecialchars($job_ref);
                $message_type = "success";
            } else {
                $message = "No records found for job reference: " . htmlspecialchars($job_ref);
                $message_type = "error";
            }
            mysqli_stmt_close($stmt);
        } else {
            $message = "Please enter a job reference number";
            $message_type = "error";
        }
    }

    // SEARCH BY NAME (FIRST, LAST, OR BOTH)
    if (isset($_POST['search_by_name'])) {
        $firstname = trim($_POST['first_name']);
        $lastname = trim($_POST['last_name']);
        
        if (!empty($firstname) || !empty($lastname)) {
            $conditions = array();
            $types = "";
            $params = array();
            $search_terms = array();
            
            if (!empty($firstname)) {
                $conditions[] = "first_name LIKE ?";
                $types .= "s";
                $params[] = "%" . $firstname . "%";
                $search_terms[] = "First Name: " . htmlspecialchars($firstname);
            }
            
            if (!empty($lastname)) {
                $conditions[] = "last_name LIKE ?";
                $types .= "s";
                $params[] = "%" . $lastname . "%";
                $search_terms[] = "Last Name: " . htmlspecialchars($lastname);
            }
            
            $query = "SELECT * FROM eoi WHERE " . implode(" AND ", $conditions) . " ORDER BY last_name, first_name";
            $stmt = mysqli_prepare($conn, $query);
            
            if ($stmt) {
                mysqli_stmt_bind_param($stmt, $types, ...$params);
                mysqli_stmt_execute($stmt);
                $result = mysqli_stmt_get_result($stmt);
                
                if (mysqli_num_rows($result) > 0) {
                    while ($row = mysqli_fetch_assoc($result)) {
                        $results[] = $row;
                    }
                    $search_performed = implode(", ", $search_terms);
                    $message = "Found " . count($results) . " matching applicant(s)";
                    $message_type = "success";
                } else {
                    $message = "No matching applicants found";
                    $message_type = "error";
                }
                mysqli_stmt_close($stmt);
            } else {
                error_log("Database prepare error: " . mysqli_error($conn));
                $message = "Error performing search";
                $message_type = "error";
            }
        } else {
            $message = "Please enter at least first name, last name, or both";
            $message_type = "error";
        }
    }

    // UPDATE EOI STATUS
    if (isset($_POST['update_status'])) {
        $eoi_num = trim($_POST['eoi_number']);
        $new_status = $_POST['new_status'];
        
        if (!empty($eoi_num) && !empty($new_status)) {
            // Validate EOI number is numeric
            if (!is_numeric($eoi_num)) {
                $message = "EOI number must be a valid number";
                $message_type = "error";
            } else {
                $stmt = mysqli_prepare($conn, "UPDATE eoi SET status=? WHERE EOInumber=?");
                mysqli_stmt_bind_param($stmt, "si", $new_status, $eoi_num);
                
                if (mysqli_stmt_execute($stmt)) {
                    if (mysqli_stmt_affected_rows($stmt) > 0) {
                        $message = "Status updated successfully for EOI #" . htmlspecialchars($eoi_num) . " to '" . htmlspecialchars($new_status) . "'";
                        $message_type = "success";
                        
                        // Fetch and display the updated record
                        $fetch_stmt = mysqli_prepare($conn, "SELECT * FROM eoi WHERE EOInumber=?");
                        mysqli_stmt_bind_param($fetch_stmt, "i", $eoi_num);
                        mysqli_stmt_execute($fetch_stmt);
                        $fetch_result = mysqli_stmt_get_result($fetch_stmt);
                        
                        if ($row = mysqli_fetch_assoc($fetch_result)) {
                            $results[] = $row;
                            $search_performed = "Updated Record - EOI #" . htmlspecialchars($eoi_num);
                        }
                        mysqli_stmt_close($fetch_stmt);
                    } else {
                        $message = "EOI number #" . htmlspecialchars($eoi_num) . " not found";
                        $message_type = "error";
                    }
                } else {
                    error_log("Status update error: " . mysqli_stmt_error($stmt));
                    $message = "Error updating status";
                    $message_type = "error";
                }
                mysqli_stmt_close($stmt);
            }
        } else {
            $message = "Please enter both EOI number and select a status";
            $message_type = "error";
        }
    }

    // DELETE EOIs BY JOB REFERENCE
    if (isset($_POST['delete_by_job'])) {
        $job_ref = trim($_POST['delete_job_reference']);
        
        if (!empty($job_ref)) {
            // First, get count of records to be deleted
            $count_stmt = mysqli_prepare($conn, "SELECT COUNT(*) as count FROM eoi WHERE job_reference=?");
            mysqli_stmt_bind_param($count_stmt, "s", $job_ref);
            mysqli_stmt_execute($count_stmt);
            $count_result = mysqli_stmt_get_result($count_stmt);
            $count_row = mysqli_fetch_assoc($count_result);
            $records_to_delete = $count_row['count'];
            mysqli_stmt_close($count_stmt);
            
            if ($records_to_delete > 0) {
                // Proceed with deletion
                $stmt = mysqli_prepare($conn, "DELETE FROM eoi WHERE job_reference=?");
                mysqli_stmt_bind_param($stmt, "s", $job_ref);
                
                if (mysqli_stmt_execute($stmt)) {
                    $message = "Successfully deleted $records_to_delete record(s) for job reference: " . htmlspecialchars($job_ref);
                    $message_type = "success";
                } else {
                    error_log("Delete operation error: " . mysqli_stmt_error($stmt));
                    $message = "Error occurred during deletion";
                    $message_type = "error";
                }
                mysqli_stmt_close($stmt);
            } else {
                $message = "No records found to delete for job reference: " . htmlspecialchars($job_ref);
                $message_type = "error";
            }
        } else {
            $message = "Please enter a job reference number to delete";
            $message_type = "error";
        }
    }

    // EXPORT TO CSV
    if (isset($_POST['export_csv'])) {
        if (count($results) > 0) {
            header('Content-Type: text/csv');
            header('Content-Disposition: attachment; filename="eoi_records_' . date('Y-m-d_His') . '.csv"');
            
            $output = fopen('php://output', 'w');
            
            // CSV Headers - REMOVED dob and gender
            fputcsv($output, array('EOI Number', 'Job Reference', 'First Name', 'Last Name', 
                                   'Street Address', 'Suburb/Town', 'State', 'Postcode', 'Email', 'Phone', 
                                   'Skill 1', 'Skill 2', 'Skill 3', 'Skill 4', 'Other Skills', 'Status', 'Created At'));
            
            // CSV Data - REMOVED dob and gender
            foreach ($results as $row) {
                fputcsv($output, array(
                    $row['EOInumber'],
                    $row['job_reference'],
                    $row['first_name'],
                    $row['last_name'],
                    $row['street_address'],
                    $row['suburb_town'],
                    $row['state'],
                    $row['postcode'],
                    $row['email'],
                    $row['phone'],
                    $row['skill1'] ?? '',
                    $row['skill2'] ?? '',
                    $row['skill3'] ?? '',
                    $row['skill4'] ?? '',
                    $row['other_skills'] ?? '',
                    $row['status'],
                    $row['created_at']
                ));
            }
            
            fclose($output);
            exit();
        }
    }
}

closeDatabaseConnection($conn);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Manage EOI records for Control Alt Elite">
    <title>Manage EOI - Control Alt Elite</title>
    <link rel="stylesheet" href="styles/styles.css">
    <style>
        .manage-card h2 {
            word-wrap: break-word;
            overflow-wrap: break-word;
            hyphens: auto;
        }
        
        .view-details-btn {
            background: #3b82f6;
            color: white;
            border: none;
            padding: 6px 12px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 0.875rem;
        }
        .view-details-btn:hover {
            background: #2563eb;
        }
        .details-row {
            display: none;
            background: #f8fafc;
        }
        .details-row.show {
            display: table-row;
        }
        .details-content {
            padding: 20px;
            border-top: 2px solid #3b82f6;
        }
        .details-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 16px;
        }
        .detail-item {
            background: white;
            padding: 12px;
            border-radius: 8px;
            border: 1px solid #e2e8f0;
        }
        .detail-label {
            font-weight: 600;
            color: #64748b;
            font-size: 0.875rem;
            margin-bottom: 4px;
        }
        .detail-value {
            color: #1e293b;
            font-size: 1rem;
        }
        .pagination {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 10px;
            margin-top: 20px;
        }
        .pagination a, .pagination span {
            padding: 8px 12px;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            text-decoration: none;
            color: #1e293b;
        }
        .pagination a:hover {
            background: #f1f5f9;
        }
        .pagination .current {
            background: #3b82f6;
            color: white;
            border-color: #3b82f6;
        }
    </style>
</head>
<body class="manage-page">

<?php include 'header.inc'; ?>

<div class="manage-hero">
    <div class="manage-hero-content">
        <h1>Manage EOI Records</h1>
        <p>Welcome, <?php echo htmlspecialchars($_SESSION['manager_username']); ?> | <a href="logout.php" style="color: white; text-decoration: underline;">Logout</a></p>
    </div>
</div>

<div class="manage-container">
    
    <?php if ($message != ""): ?>
        <div class="message-box <?php echo $message_type; ?>">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                <?php if ($message_type == "success"): ?>
                    <polyline points="20 6 9 17 4 12"></polyline>
                <?php else: ?>
                    <circle cx="12" cy="12" r="10"></circle>
                    <line x1="12" y1="8" x2="12" y2="12"></line>
                    <line x1="12" y1="16" x2="12.01" y2="16"></line>
                <?php endif; ?>
            </svg>
            <p><?php echo htmlspecialchars($message); ?></p>
        </div>
    <?php endif; ?>

    <?php if ($search_performed != ""): ?>
        <div style="background: #f1f5f9; padding: 12px 16px; border-radius: 8px; margin-bottom: 20px; border-left: 4px solid #3b82f6;">
            <strong>Current Search:</strong> <?php echo $search_performed; ?>
        </div>
    <?php endif; ?>

    <div class="manage-grid">
        
        <!-- List All EOIs -->
        <div class="manage-card">
            <h2>📋 List All EOIs</h2>
            <form method="post">
                <div class="form-row">
                    <div class="form-group">
                        <label for="sort_field">Sort by:</label>
                        <select name="sort_field" id="sort_field">
                            <option value="EOInumber">EOI Number</option>
                            <option value="first_name">First Name</option>
                            <option value="last_name">Last Name</option>
                            <option value="job_reference">Job Reference</option>
                            <option value="status">Status</option>
                            <option value="created_at">Date Created</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="sort_order">Order:</label>
                        <select name="sort_order" id="sort_order">
                            <option value="ASC">Ascending</option>
                            <option value="DESC">Descending</option>
                        </select>
                    </div>
                </div>
                <button type="submit" name="list_all" class="manage-btn">Show All EOIs</button>
            </form>
        </div>

        <!-- Search by Job Reference -->
        <div class="manage-card">
            <h2>🔍 Search by Job Reference</h2>
            <form method="post">
                <div class="form-group">
                    <label for="job_reference">Job Reference:</label>
                    <input type="text" name="job_reference" id="job_reference" placeholder="e.g. SWD93" required>
                </div>
                <button type="submit" name="search_by_job" class="manage-btn">Search</button>
            </form>
        </div>

        <!-- Search by Name -->
        <div class="manage-card">
            <h2>👤 Search by Applicant Name</h2>
            <form method="post">
                <div class="form-group">
                    <label for="first_name">First Name:</label>
                    <input type="text" name="first_name" id="first_name" placeholder="Enter first name">
                </div>
                <div class="form-group">
                    <label for="last_name">Last Name:</label>
                    <input type="text" name="last_name" id="last_name" placeholder="Enter last name">
                </div>
                <p style="font-size: 0.875rem; color: #64748b; margin-top: 8px;">Enter first name, last name, or both</p>
                <button type="submit" name="search_by_name" class="manage-btn">Search</button>
            </form>
        </div>

        <!-- Change Status -->
        <div class="manage-card">
            <h2>✏️ Change EOI Status</h2>
            <form method="post">
                <div class="form-group">
                    <label for="eoi_number">EOI Number:</label>
                    <input type="number" name="eoi_number" id="eoi_number" placeholder="Enter EOI number" required>
                </div>
                <div class="form-group">
                    <label for="new_status">New Status:</label>
                    <select name="new_status" id="new_status" required>
                        <option value="">Select Status...</option>
                        <option value="New">New</option>
                        <option value="Current">Current</option>
                        <option value="Final">Final</option>
                    </select>
                </div>
                <button type="submit" name="update_status" class="manage-btn">Update Status</button>
            </form>
        </div>

        <!-- Delete Records -->
        <div class="manage-card danger-card">
            <h2>🗑️ Delete EOI Records</h2>
            <form method="post" onsubmit="return confirm('⚠️ WARNING: Are you sure you want to delete ALL EOIs for this job reference?\n\nThis action CANNOT be undone!');">
                <div class="form-group">
                    <label for="delete_job_reference">Job Reference:</label>
                    <input type="text" name="delete_job_reference" id="delete_job_reference" placeholder="e.g. SWD93" required>
                </div>
                <p style="font-size: 0.875rem; color: #dc2626; margin-top: 8px; font-weight: 600;">⚠️ This will delete ALL applications for this job</p>
                <button type="submit" name="delete_by_job" class="manage-btn danger-btn">Delete All EOIs</button>
            </form>
        </div>

    </div>

    <!-- RESULTS TABLE -->
    <?php if (count($results) > 0): ?>
        <div class="results-section">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                <h2>Results (<?php echo count($results); ?> record<?php echo count($results) > 1 ? 's' : ''; ?>)</h2>
                <form method="post" style="margin: 0;">
                    <button type="submit" name="export_csv" class="manage-btn" style="background: #10b981;">
                        📥 Export to CSV
                    </button>
                </form>
            </div>
            
            <div class="table-container">
                <table class="eoi-table">
                    <thead>
                        <tr>
                            <th>EOI #</th>
                            <th>Job Ref</th>
                            <th>First Name</th>
                            <th>Last Name</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($results as $index => $row): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['EOInumber']); ?></td>
                                <td><?php echo htmlspecialchars($row['job_reference']); ?></td>
                                <td><?php echo htmlspecialchars($row['first_name']); ?></td>
                                <td><?php echo htmlspecialchars($row['last_name']); ?></td>
                                <td><?php echo htmlspecialchars($row['email']); ?></td>
                                <td><?php echo htmlspecialchars($row['phone']); ?></td>
                                <td><span class="status-badge status-<?php echo strtolower($row['status']); ?>"><?php echo htmlspecialchars($row['status']); ?></span></td>
                                <td>
                                    <button class="view-details-btn" onclick="toggleDetails(<?php echo $index; ?>)">View Details</button>
                                </td>
                            </tr>
                            <tr class="details-row" id="details-<?php echo $index; ?>">
                                <td colspan="8">
                                    <div class="details-content">
                                        <h3 style="margin-bottom: 16px; color: #1e293b;">Complete EOI Details - #<?php echo htmlspecialchars($row['EOInumber']); ?></h3>
                                        <div class="details-grid">
                                            <div class="detail-item">
                                                <div class="detail-label">Street Address</div>
                                                <div class="detail-value"><?php echo htmlspecialchars($row['street_address']); ?></div>
                                            </div>
                                            <div class="detail-item">
                                                <div class="detail-label">Suburb/Town</div>
                                                <div class="detail-value"><?php echo htmlspecialchars($row['suburb_town']); ?></div>
                                            </div>
                                            <div class="detail-item">
                                                <div class="detail-label">State</div>
                                                <div class="detail-value"><?php echo htmlspecialchars($row['state']); ?></div>
                                            </div>
                                            <div class="detail-item">
                                                <div class="detail-label">Postcode</div>
                                                <div class="detail-value"><?php echo htmlspecialchars($row['postcode']); ?></div>
                                            </div>
                                            <div class="detail-item">
                                                <div class="detail-label">Skill 1</div>
                                                <div class="detail-value"><?php echo isset($row['skill1']) && $row['skill1'] ? htmlspecialchars($row['skill1']) : 'N/A'; ?></div>
                                            </div>
                                            <div class="detail-item">
                                                <div class="detail-label">Skill 2</div>
                                                <div class="detail-value"><?php echo isset($row['skill2']) && $row['skill2'] ? htmlspecialchars($row['skill2']) : 'N/A'; ?></div>
                                            </div>
                                            <div class="detail-item">
                                                <div class="detail-label">Skill 3</div>
                                                <div class="detail-value"><?php echo isset($row['skill3']) && $row['skill3'] ? htmlspecialchars($row['skill3']) : 'N/A'; ?></div>
                                            </div>
                                            <div class="detail-item">
                                                <div class="detail-label">Skill 4</div>
                                                <div class="detail-value"><?php echo isset($row['skill4']) && $row['skill4'] ? htmlspecialchars($row['skill4']) : 'N/A'; ?></div>
                                            </div>
                                            <div class="detail-item" style="grid-column: 1 / -1;">
                                                <div class="detail-label">Other Skills</div>
                                                <div class="detail-value"><?php echo isset($row['other_skills']) && $row['other_skills'] ? htmlspecialchars($row['other_skills']) : 'None specified'; ?></div>
                                            </div>
                                            <div class="detail-item">
                                                <div class="detail-label">Application Date</div>
                                                <div class="detail-value"><?php echo htmlspecialchars($row['created_at']); ?></div>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <?php if ($total_pages > 1): ?>
                <div class="pagination">
                    <?php if ($current_page > 1): ?>
                        <a href="?page=<?php echo $current_page - 1; ?>">← Previous</a>
                    <?php endif; ?>
                    
                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                        <?php if ($i == $current_page): ?>
                            <span class="current"><?php echo $i; ?></span>
                        <?php else: ?>
                            <a href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                        <?php endif; ?>
                    <?php endfor; ?>
                    
                    <?php if ($current_page < $total_pages): ?>
                        <a href="?page=<?php echo $current_page + 1; ?>">Next →</a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    <?php endif; ?>

</div>

<?php include 'footer.inc'; ?>

<script>
function toggleDetails(index) {
    const detailsRow = document.getElementById('details-' + index);
    detailsRow.classList.toggle('show');
}
</script>

</body>
</html>