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
$show_results = false;

// Pagination variables
$records_per_page = 50;
$current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$current_page = max(1, $current_page);
$offset = ($current_page - 1) * $records_per_page;
$total_records = 0;
$total_pages = 0;

// Check if we should show details for a specific EOI
$show_details_for = isset($_GET['details']) ? (int)$_GET['details'] : null;

// Store search parameters to persist across details view
$search_params = '';
if (isset($_GET['search_type'])) {
    $search_params = '&search_type=' . urlencode($_GET['search_type']);
    if (isset($_GET['search_value'])) {
        $search_params .= '&search_value=' . urlencode($_GET['search_value']);
    }
}

// ============================================================================
// PROCESS FORM SUBMISSIONS
// ============================================================================

// Store search context in session for back navigation
if (!isset($_SESSION['last_search'])) {
    $_SESSION['last_search'] = array();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // QUICK ACCEPT EOI (NEW FEATURE - HD QUALITY)
    if (isset($_POST['quick_accept'])) {
        $eoi_num = trim($_POST['accept_eoi_number']);
        
        if (!empty($eoi_num) && is_numeric($eoi_num)) {
            $stmt = mysqli_prepare($conn, "UPDATE eoi SET status='Accepted' WHERE EOInumber=?");
            mysqli_stmt_bind_param($stmt, "i", $eoi_num);
            
            if (mysqli_stmt_execute($stmt)) {
                if (mysqli_stmt_affected_rows($stmt) > 0) {
                    $message = "✅ EOI #" . htmlspecialchars($eoi_num) . " has been ACCEPTED successfully!";
                    $message_type = "success";
                    
                    // Fetch and display the updated record
                    $fetch_stmt = mysqli_prepare($conn, "SELECT * FROM eoi WHERE EOInumber=?");
                    mysqli_stmt_bind_param($fetch_stmt, "i", $eoi_num);
                    mysqli_stmt_execute($fetch_stmt);
                    $fetch_result = mysqli_stmt_get_result($fetch_stmt);
                    
                    if ($row = mysqli_fetch_assoc($fetch_result)) {
                        $results[] = $row;
                        $search_performed = "Accepted Application - EOI #" . htmlspecialchars($eoi_num);
                        $show_results = true;
                    }
                    mysqli_stmt_close($fetch_stmt);
                } else {
                    $message = "EOI #" . htmlspecialchars($eoi_num) . " not found";
                    $message_type = "error";
                }
            } else {
                error_log("Quick accept error: " . mysqli_stmt_error($stmt));
                $message = "Error accepting EOI";
                $message_type = "error";
            }
            mysqli_stmt_close($stmt);
        } else {
            $message = "Please enter a valid EOI number";
            $message_type = "error";
        }
    }

    // BULK ACCEPT BY JOB REFERENCE (NEW FEATURE - HD QUALITY)
    if (isset($_POST['bulk_accept'])) {
        $job_ref = trim($_POST['bulk_accept_job_ref']);
        
        if (!empty($job_ref)) {
            // First, get count of records to be accepted (includes blank, NULL, New, Current)
            $count_stmt = mysqli_prepare($conn, "SELECT COUNT(*) as count FROM eoi WHERE job_reference=? AND (status IS NULL OR status = '' OR status NOT IN ('Accepted', 'Final'))");
            mysqli_stmt_bind_param($count_stmt, "s", $job_ref);
            mysqli_stmt_execute($count_stmt);
            $count_result = mysqli_stmt_get_result($count_stmt);
            $count_row = mysqli_fetch_assoc($count_result);
            $records_to_accept = $count_row['count'];
            mysqli_stmt_close($count_stmt);
            
            if ($records_to_accept > 0) {
                // Proceed with bulk acceptance - update ALL records including blank/NULL statuses
                $stmt = mysqli_prepare($conn, "UPDATE eoi SET status='Accepted' WHERE job_reference=? AND (status IS NULL OR status = '' OR status NOT IN ('Accepted', 'Final'))");
                mysqli_stmt_bind_param($stmt, "s", $job_ref);
                
                if (mysqli_stmt_execute($stmt)) {
                    $affected = mysqli_stmt_affected_rows($stmt);
                    if ($affected > 0) {
                        $message = "✅ Successfully accepted $affected EOI(s) for job reference: " . htmlspecialchars($job_ref);
                        $message_type = "success";
                        
                        // Fetch and display the accepted records
                        $fetch_stmt = mysqli_prepare($conn, "SELECT * FROM eoi WHERE job_reference=? ORDER BY EOInumber DESC");
                        mysqli_stmt_bind_param($fetch_stmt, "s", $job_ref);
                        mysqli_stmt_execute($fetch_stmt);
                        $fetch_result = mysqli_stmt_get_result($fetch_stmt);
                        
                        while ($row = mysqli_fetch_assoc($fetch_result)) {
                            $results[] = $row;
                        }
                        $search_performed = "Bulk Accepted - Job Reference: " . htmlspecialchars($job_ref);
                        $show_results = true;
                        
                        mysqli_stmt_close($fetch_stmt);
                    } else {
                        $message = "No records were updated. They may already be accepted.";
                        $message_type = "error";
                    }
                } else {
                    error_log("Bulk accept error: " . mysqli_stmt_error($stmt));
                    $message = "Error occurred during bulk acceptance: " . mysqli_error($conn);
                    $message_type = "error";
                }
                mysqli_stmt_close($stmt);
            } else {
                $message = "No pending records found to accept for job reference: " . htmlspecialchars($job_ref);
                $message_type = "error";
            }
        } else {
            $message = "Please enter a job reference number";
            $message_type = "error";
        }
    }
    
    // LIST ALL EOIs WITH SORTING
    if (isset($_POST['list_all'])) {
        $sort_field = isset($_POST['sort_field']) && !empty($_POST['sort_field']) ? $_POST['sort_field'] : 'EOInumber';
        $sort_order = isset($_POST['sort_order']) ? $_POST['sort_order'] : 'ASC';
        
        // Store in session
        $_SESSION['last_search'] = array(
            'type' => 'list_all',
            'sort_field' => $sort_field,
            'sort_order' => $sort_order
        );
        
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
                $show_results = true;
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
        
        // Store in session
        $_SESSION['last_search'] = array(
            'type' => 'job_ref',
            'job_ref' => $job_ref
        );
        
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
                $show_results = true;
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
        
        // Store in session
        $_SESSION['last_search'] = array(
            'type' => 'name',
            'first_name' => $firstname,
            'last_name' => $lastname
        );
        
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
                    $show_results = true;
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
                            $show_results = true;
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
        // Need to re-fetch results based on current session or re-run last query
        // For now, we'll handle this separately
    }
}

// Handle back to results request
if (isset($_GET['back_to_results']) && isset($_SESSION['last_search'])) {
    $last_search = $_SESSION['last_search'];
    
    if ($last_search['type'] == 'list_all') {
        $sort_field = $last_search['sort_field'];
        $sort_order = $last_search['sort_order'];
        
        $allowed_fields = ['EOInumber', 'first_name', 'last_name', 'job_reference', 'status', 'created_at'];
        $sort_field = in_array($sort_field, $allowed_fields) ? $sort_field : 'EOInumber';
        $sort_order = ($sort_order == 'DESC') ? 'DESC' : 'ASC';
        
        $count_query = "SELECT COUNT(*) as total FROM eoi";
        $count_result = mysqli_query($conn, $count_query);
        $count_row = mysqli_fetch_assoc($count_result);
        $total_records = $count_row['total'];
        $total_pages = ceil($total_records / $records_per_page);
        
        $safe_sort_field = mysqli_real_escape_string($conn, $sort_field);
        $query = "SELECT * FROM eoi ORDER BY $safe_sort_field $sort_order LIMIT $records_per_page OFFSET $offset";
        $result = mysqli_query($conn, $query);
        
        if ($result && mysqli_num_rows($result) > 0) {
            while ($row = mysqli_fetch_assoc($result)) {
                $results[] = $row;
            }
            $search_performed = "All EOI Records (sorted by " . ucfirst(str_replace('_', ' ', $sort_field)) . " - $sort_order)";
            $show_results = true;
        }
    } elseif ($last_search['type'] == 'job_ref') {
        $job_ref = $last_search['job_ref'];
        $stmt = mysqli_prepare($conn, "SELECT * FROM eoi WHERE job_reference=? ORDER BY EOInumber DESC");
        mysqli_stmt_bind_param($stmt, "s", $job_ref);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        if (mysqli_num_rows($result) > 0) {
            while ($row = mysqli_fetch_assoc($result)) {
                $results[] = $row;
            }
            $search_performed = "Job Reference: " . htmlspecialchars($job_ref);
            $show_results = true;
        }
        mysqli_stmt_close($stmt);
    } elseif ($last_search['type'] == 'name') {
        $firstname = $last_search['first_name'];
        $lastname = $last_search['last_name'];
        
        $conditions = array();
        $types = "";
        $params = array();
        
        if (!empty($firstname)) {
            $conditions[] = "first_name LIKE ?";
            $types .= "s";
            $params[] = "%" . $firstname . "%";
        }
        
        if (!empty($lastname)) {
            $conditions[] = "last_name LIKE ?";
            $types .= "s";
            $params[] = "%" . $lastname . "%";
        }
        
        if (count($conditions) > 0) {
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
                    $search_performed = "Name Search";
                    $show_results = true;
                }
                mysqli_stmt_close($stmt);
            }
        }
    }
}

// Handle GET request for viewing details
if (isset($_GET['details']) && is_numeric($_GET['details']) && !isset($_GET['back_to_results'])) {
    $eoi_id = (int)$_GET['details'];
    
    // Re-run last search to get all results
    if (isset($_SESSION['last_search'])) {
        $last_search = $_SESSION['last_search'];
        
        if ($last_search['type'] == 'list_all') {
            $sort_field = $last_search['sort_field'];
            $sort_order = $last_search['sort_order'];
            
            $allowed_fields = ['EOInumber', 'first_name', 'last_name', 'job_reference', 'status', 'created_at'];
            $sort_field = in_array($sort_field, $allowed_fields) ? $sort_field : 'EOInumber';
            $sort_order = ($sort_order == 'DESC') ? 'DESC' : 'ASC';
            
            $safe_sort_field = mysqli_real_escape_string($conn, $sort_field);
            $query = "SELECT * FROM eoi ORDER BY $safe_sort_field $sort_order";
            $result = mysqli_query($conn, $query);
            
            if ($result && mysqli_num_rows($result) > 0) {
                while ($row = mysqli_fetch_assoc($result)) {
                    $results[] = $row;
                }
                $search_performed = "All EOI Records (sorted by " . ucfirst(str_replace('_', ' ', $sort_field)) . " - $sort_order)";
                $show_results = true;
                $show_details_for = $eoi_id;
            }
        } elseif ($last_search['type'] == 'job_ref') {
            $job_ref = $last_search['job_ref'];
            $stmt = mysqli_prepare($conn, "SELECT * FROM eoi WHERE job_reference=? ORDER BY EOInumber DESC");
            mysqli_stmt_bind_param($stmt, "s", $job_ref);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            
            if (mysqli_num_rows($result) > 0) {
                while ($row = mysqli_fetch_assoc($result)) {
                    $results[] = $row;
                }
                $search_performed = "Job Reference: " . htmlspecialchars($job_ref);
                $show_results = true;
                $show_details_for = $eoi_id;
            }
            mysqli_stmt_close($stmt);
        } elseif ($last_search['type'] == 'name') {
            $firstname = $last_search['first_name'];
            $lastname = $last_search['last_name'];
            
            $conditions = array();
            $types = "";
            $params = array();
            
            if (!empty($firstname)) {
                $conditions[] = "first_name LIKE ?";
                $types .= "s";
                $params[] = "%" . $firstname . "%";
            }
            
            if (!empty($lastname)) {
                $conditions[] = "last_name LIKE ?";
                $types .= "s";
                $params[] = "%" . $lastname . "%";
            }
            
            if (count($conditions) > 0) {
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
                        $search_performed = "Name Search";
                        $show_results = true;
                        $show_details_for = $eoi_id;
                    }
                    mysqli_stmt_close($stmt);
                }
            }
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
</head>
<body class="manage-page">

<?php include 'header.inc'; ?>

<div class="manage-hero">
    <div class="manage-hero-content">
        <h1>Manage EOI Records</h1>
        <p>Welcome, <?php echo htmlspecialchars($_SESSION['manager_username']); ?> | <a href="logout.php" class="logout-link">Logout</a></p>
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
            <p><?php echo $message; ?></p>
        </div>
    <?php endif; ?>

    <?php if ($show_results): ?>
        
        <div class="back-button-container">
            <a href="manage.php" class="back-button">← Back to Search Options</a>
        </div>

        <?php if ($search_performed != ""): ?>
            <div class="current-search-box">
                <strong>Current Search:</strong> <?php echo $search_performed; ?>
            </div>
        <?php endif; ?>

        <div class="results-section">
            <div class="results-header">
                <h2>Results (<?php echo count($results); ?> record<?php echo count($results) > 1 ? 's' : ''; ?>)</h2>
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
                        <?php foreach ($results as $row): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['EOInumber']); ?></td>
                                <td><?php echo htmlspecialchars($row['job_reference']); ?></td>
                                <td><?php echo htmlspecialchars($row['first_name']); ?></td>
                                <td><?php echo htmlspecialchars($row['last_name']); ?></td>
                                <td><?php echo htmlspecialchars($row['email']); ?></td>
                                <td><?php echo htmlspecialchars($row['phone']); ?></td>
                                <td>
                                    <span class="status-badge status-<?php echo strtolower($row['status']); ?>">
                                        <?php if (strtolower($row['status']) == 'accepted'): ?>
                                            <svg class="accept-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3">
                                                <polyline points="20 6 9 17 4 12"></polyline>
                                            </svg>
                                        <?php endif; ?>
                                        <?php echo htmlspecialchars($row['status']); ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="quick-actions-row">
                                        <?php if ($show_details_for == $row['EOInumber']): ?>
                                            <a href="?back_to_results=1" class="back-to-table-btn">← Back</a>
                                        <?php else: ?>
                                            <a href="?details=<?php echo $row['EOInumber']; ?>" class="view-details-btn">View Details</a>
                                            <?php if (strtolower($row['status']) != 'accepted'): ?>
                                                <form method="post" style="display: inline; margin: 0;">
                                                    <input type="hidden" name="accept_eoi_number" value="<?php echo $row['EOInumber']; ?>">
                                                    <button type="submit" name="quick_accept" class="accept-btn" onclick="return confirm('Accept EOI #<?php echo $row['EOInumber']; ?> for <?php echo htmlspecialchars($row['first_name'] . ' ' . $row['last_name']); ?>?');">
                                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3">
                                                            <polyline points="20 6 9 17 4 12"></polyline>
                                                        </svg>
                                                        Accept
                                                    </button>
                                                </form>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                            <?php if ($show_details_for == $row['EOInumber']): ?>
                                <tr class="details-row">
                                    <td colspan="8">
                                        <div class="details-content">
                                            <div class="details-header-section">
                                                <h3 class="details-title">Complete EOI Details</h3>
                                                <div class="details-eoi-number">EOI #<?php echo htmlspecialchars($row['EOInumber']); ?></div>
                                            </div>
                                            
                                            <!-- QUICK ACCEPT BUTTON IN DETAILS VIEW -->
                                            <?php if (strtolower($row['status']) != 'accepted'): ?>
                                                <div style="margin: 20px 0; text-align: center; padding: 20px; background: #f0fdf4; border-radius: 8px; border: 2px dashed #10b981;">
                                                    <form method="post" style="display: inline;">
                                                        <input type="hidden" name="accept_eoi_number" value="<?php echo $row['EOInumber']; ?>">
                                                        <button type="submit" name="quick_accept" class="accept-btn accept-btn-large" onclick="return confirm('✅ Accept this EOI application?\n\nApplicant: <?php echo htmlspecialchars($row['first_name'] . ' ' . $row['last_name']); ?>\nJob Reference: <?php echo htmlspecialchars($row['job_reference']); ?>\nEOI #<?php echo $row['EOInumber']; ?>');">
                                                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3">
                                                                <polyline points="20 6 9 17 4 12"></polyline>
                                                            </svg>
                                                            Accept This Application
                                                        </button>
                                                    </form>
                                                    <p style="margin-top: 10px; font-size: 14px; color: #059669;">Click to approve this EOI and change status to "Accepted"</p>
                                                </div>
                                            <?php endif; ?>
                                            
                                            <div class="details-section">
                                                <h4 class="section-heading">
                                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                                                        <circle cx="12" cy="7" r="4"></circle>
                                                    </svg>
                                                    Personal Information
                                                </h4>
                                                <div class="details-grid">
                                                    <div class="detail-item">
                                                        <div class="detail-label">Full Name</div>
                                                        <div class="detail-value"><?php echo htmlspecialchars($row['first_name'] . ' ' . $row['last_name']); ?></div>
                                                    </div>
                                                    <div class="detail-item">
                                                        <div class="detail-label">Email Address</div>
                                                        <div class="detail-value"><?php echo htmlspecialchars($row['email']); ?></div>
                                                    </div>
                                                    <div class="detail-item">
                                                        <div class="detail-label">Phone Number</div>
                                                        <div class="detail-value"><?php echo htmlspecialchars($row['phone']); ?></div>
                                                    </div>
                                                    <div class="detail-item">
                                                        <div class="detail-label">Job Reference</div>
                                                        <div class="detail-value detail-highlight"><?php echo htmlspecialchars($row['job_reference']); ?></div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="details-section">
                                                <h4 class="section-heading">
                                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                        <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path>
                                                        <polyline points="9 22 9 12 15 12 15 22"></polyline>
                                                    </svg>
                                                    Address Details
                                                </h4>
                                                <div class="details-grid">
                                                    <div class="detail-item detail-item-full">
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
                                                </div>
                                            </div>

                                            <div class="details-section">
                                                <h4 class="section-heading">
                                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                        <polyline points="16 18 22 12 16 6"></polyline>
                                                        <polyline points="8 6 2 12 8 18"></polyline>
                                                    </svg>
                                                    Skills & Expertise
                                                </h4>
                                                <div class="skills-grid">
                                                    <?php 
                                                    $skills = [
                                                        'skill1' => 'Skill 1',
                                                        'skill2' => 'Skill 2',
                                                        'skill3' => 'Skill 3',
                                                        'skill4' => 'Skill 4'
                                                    ];
                                                    foreach ($skills as $key => $label): 
                                                        if (isset($row[$key]) && !empty($row[$key])):
                                                    ?>
                                                        <div class="skill-badge skill-active">
                                                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                                <polyline points="20 6 9 17 4 12"></polyline>
                                                            </svg>
                                                            <?php echo htmlspecialchars($row[$key]); ?>
                                                        </div>
                                                    <?php 
                                                        else:
                                                    ?>
                                                        <div class="skill-badge skill-inactive">
                                                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                                <line x1="18" y1="6" x2="6" y2="18"></line>
                                                                <line x1="6" y1="6" x2="18" y2="18"></line>
                                                            </svg>
                                                            Not specified
                                                        </div>
                                                    <?php 
                                                        endif;
                                                    endforeach; 
                                                    ?>
                                                </div>
                                                <?php if (isset($row['other_skills']) && !empty($row['other_skills'])): ?>
                                                    <div class="other-skills-box">
                                                        <div class="detail-label">Additional Skills</div>
                                                        <div class="detail-value"><?php echo nl2br(htmlspecialchars($row['other_skills'])); ?></div>
                                                    </div>
                                                <?php endif; ?>
                                            </div>

                                            <div class="details-section">
                                                <h4 class="section-heading">
                                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                        <circle cx="12" cy="12" r="10"></circle>
                                                        <polyline points="12 6 12 12 16 14"></polyline>
                                                    </svg>
                                                    Application Information
                                                </h4>
                                                <div class="details-grid">
                                                    <div class="detail-item">
                                                        <div class="detail-label">Application Status</div>
                                                        <div class="detail-value">
                                                            <span class="status-badge-large status-<?php echo strtolower($row['status']); ?>">
                                                                <?php if (strtolower($row['status']) == 'accepted'): ?>
                                                                    <svg class="accept-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3">
                                                                        <polyline points="20 6 9 17 4 12"></polyline>
                                                                    </svg>
                                                                <?php endif; ?>
                                                                <?php echo htmlspecialchars($row['status']); ?>
                                                            </span>
                                                        </div>
                                                    </div>
                                                    <div class="detail-item">
                                                        <div class="detail-label">Submission Date</div>
                                                        <div class="detail-value"><?php echo date('F j, Y \a\t g:i A', strtotime($row['created_at'])); ?></div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="details-footer">
                                                <a href="?back_to_results=1" class="back-to-table-btn-large">
                                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                        <line x1="19" y1="12" x2="5" y2="12"></line>
                                                        <polyline points="12 19 5 12 12 5"></polyline>
                                                    </svg>
                                                    Back to Table
                                                </a>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <?php if ($total_pages > 1): ?>
                <div class="pagination">
                    <?php if ($current_page > 1): ?>
                        <a href="?page=<?php echo $current_page - 1; ?>" class="pagination-link">← Previous</a>
                    <?php endif; ?>
                    
                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                        <?php if ($i == $current_page): ?>
                            <span class="pagination-current"><?php echo $i; ?></span>
                        <?php else: ?>
                            <a href="?page=<?php echo $i; ?>" class="pagination-link"><?php echo $i; ?></a>
                        <?php endif; ?>
                    <?php endfor; ?>
                    
                    <?php if ($current_page < $total_pages): ?>
                        <a href="?page=<?php echo $current_page + 1; ?>" class="pagination-link">Next →</a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>

    <?php else: ?>

        <div class="manage-grid">
            
            <!-- NEW HD QUALITY FEATURE: Quick Accept Card -->
            <div class="manage-card accept-card">
                <h2>✅ Quick Accept EOI</h2>
                <p style="font-size: 14px; color: #059669; margin-bottom: 15px;">Instantly approve an application by EOI number</p>
                <form method="post">
                    <div class="form-group">
                        <label for="accept_eoi_number">EOI Number:</label>
                        <input type="number" name="accept_eoi_number" id="accept_eoi_number" placeholder="Enter EOI number" required>
                    </div>
                    <button type="submit" name="quick_accept" class="manage-btn accept-btn-large" onclick="return confirm('✅ Are you sure you want to ACCEPT this EOI application?');">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3">
                            <polyline points="20 6 9 17 4 12"></polyline>
                        </svg>
                        Accept Application
                    </button>
                </form>
            </div>

            <!-- NEW HD QUALITY FEATURE: Bulk Accept Card -->
            <div class="manage-card accept-card">
                <h2>✅ Bulk Accept by Job Reference</h2>
                <p style="font-size: 14px; color: #059669; margin-bottom: 15px;">Accept all pending applications for a specific job</p>
                <form method="post" onsubmit="return confirm('⚠️ BULK ACCEPT CONFIRMATION\n\nThis will accept ALL pending EOIs for this job reference.\n\nAre you sure you want to continue?');">
                    <div class="form-group">
                        <label for="bulk_accept_job_ref">Job Reference:</label>
                        <input type="text" name="bulk_accept_job_ref" id="bulk_accept_job_ref" placeholder="e.g. SWD93" required>
                    </div>
                    <div class="bulk-accept-warning">
                        ⚠️ This will accept ALL non-accepted applications for this job
                    </div>
                    <button type="submit" name="bulk_accept" class="manage-btn accept-btn-large">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3">
                            <polyline points="20 6 9 17 4 12"></polyline>
                        </svg>
                        Bulk Accept All
                    </button>
                </form>
            </div>
            
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
                    <p class="form-hint">Enter first name, last name, or both</p>
                    <button type="submit" name="search_by_name" class="manage-btn">Search</button>
                </form>
            </div>

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
                            <option value="Accepted">Accepted</option>
                        </select>
                    </div>
                    <button type="submit" name="update_status" class="manage-btn">Update Status</button>
                </form>
            </div>

            <div class="manage-card danger-card">
                <h2>🗑️ Delete EOI Records</h2>
                <form method="post" onsubmit="return confirm('⚠️ WARNING: Are you sure you want to delete ALL EOIs for this job reference?\n\nThis action CANNOT be undone!');">
                    <div class="form-group">
                        <label for="delete_job_reference">Job Reference:</label>
                        <input type="text" name="delete_job_reference" id="delete_job_reference" placeholder="e.g. SWD93" required>
                    </div>
                    <p class="delete-warning">⚠️ This will delete ALL applications for this job</p>
                    <button type="submit" name="delete_by_job" class="manage-btn danger-btn">Delete All EOIs</button>
                </form>
            </div>

        </div>

    <?php endif; ?>

</div>

<?php include 'footer.inc'; ?>

</body>
</html>
// make this look good
// ensure all information is set at proper places
