<?php
session_start();

if (!isset($_SESSION['manager_logged_in'])) {
    header("Location: login.php");
    exit();
}

require_once 'settings.php';

$conn = mysqli_connect($host, $user, $pwd, $sql_db);

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

$message = "";
$results = array();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    if (isset($_POST['list_all'])) {
        $query = "SELECT * FROM eoi";
        $result = mysqli_query($conn, $query);
        
        if ($result) {
            if (mysqli_num_rows($result) > 0) {
                while ($row = mysqli_fetch_assoc($result)) {
                    $results[] = $row;
                }
                $message = "Showing all EOI records";
            } 
            else {
                $message = "No records found";
            }
        }
    }

    if (isset($_POST['search_by_job'])) {
        $job_ref = trim($_POST['job_reference']);
        
        if (!empty($job_ref)) {
            $job_ref = mysqli_real_escape_string($conn, $job_ref);
            $query = "SELECT * FROM eoi WHERE job_reference='$job_ref'";
            $result = mysqli_query($conn, $query);
            
            if (mysqli_num_rows($result) > 0) {
                while ($row = mysqli_fetch_assoc($result)) {
                    $results[] = $row;
                }
                $message = "Found records for job: " . $job_ref;
            } 
            else {
                $message = "No records for this job reference";
            }
        } 
        else {
            $message = "Please enter a job reference";
        }
    }

    if (isset($_POST['search_by_name'])) {
        $firstname = trim($_POST['first_name']);
        $lastname = trim($_POST['last_name']);
        
        if (!empty($firstname) || !empty($lastname)) {
            $conditions = array();
            
            if (!empty($firstname)) {
                $firstname = mysqli_real_escape_string($conn, $firstname);
                $conditions[] = "first_name LIKE '%$firstname%'";
            }
            
            if (!empty($lastname)) {
                $lastname = mysqli_real_escape_string($conn, $lastname);
                $conditions[] = "last_name LIKE '%$lastname%'";
            }
            
            $query = "SELECT * FROM eoi WHERE " . implode(" AND ", $conditions);
            $result = mysqli_query($conn, $query);
            
            if (mysqli_num_rows($result) > 0) {
                while ($row = mysqli_fetch_assoc($result)) {
                    $results[] = $row;
                }
                $message = "Found matching applicants";
            } 
            else {
                $message = "No matching applicants";
            }
        } 
        else {
            $message = "Please enter a name to search";
        }
    }

    if (isset($_POST['update_status'])) {
        $eoi_num = trim($_POST['eoi_number']);
        $new_status = $_POST['new_status'];
        
        if (!empty($eoi_num) && !empty($new_status)) {
            $eoi_num = mysqli_real_escape_string($conn, $eoi_num);
            $new_status = mysqli_real_escape_string($conn, $new_status);
            
            $query = "UPDATE eoi SET status='$new_status' WHERE EOInumber='$eoi_num'";
            
            if (mysqli_query($conn, $query)) {
                if (mysqli_affected_rows($conn) > 0) {
                    $message = "Status updated successfully";
                } 
                else {
                    $message = "EOI number not found";
                }
            } 
            else {
                $message = "Error: " . mysqli_error($conn);
            }
        } 
        else {
            $message = "Please fill all fields";
        }
    }

    if (isset($_POST['delete_by_job'])) {
        $job_ref = trim($_POST['delete_job_reference']);
        
        if (!empty($job_ref)) {
            $job_ref = mysqli_real_escape_string($conn, $job_ref);
            $query = "DELETE FROM eoi WHERE job_reference='$job_ref'";
            
            if (mysqli_query($conn, $query)) {
                $count = mysqli_affected_rows($conn);
                $message = "Deleted $count record(s)";
            } 
            else {
                $message = "Delete failed";
            }
        } 
        else {
            $message = "Enter job reference to delete";
        }
    }
}

mysqli_close($conn);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage EOI</title>
    <link rel="stylesheet" href="dynamic/dynamic.css">
</head>
<body>

<?php include 'header.inc'; ?>

<div class="manage-container">
    <h1>Manage EOI Records</h1>
    
    <?php if ($message != ""): ?>
        <div class="message-box">
            <p><?php echo htmlspecialchars($message); ?></p>
        </div>
    <?php endif; ?>

    <div class="form-section">
        <h2>List All EOIs</h2>
        <form method="post">
            <label>Sort by:</label>
        <select name="sort_field">
            <option value="">No sorting</option>
            <option value="EOInumber">EOI Number</option>
            <option value="first_name">First Name</option>
            <option value="last_name">Last Name</option>
            <option value="job_reference">Job Reference</option>
            <option value="status">Status</option>
        </select>
        
        <label>Order:</label>
        <select name="sort_order">
            <option value="ASC">Ascending</option>
            <option value="DESC">Descending</option>
        </select>
            <button type="submit" name="list_all" class="btn">Show All</button>
        </form>
    </div>

    <div class="form-section">
        <h2>Search by Job Reference</h2>
        <form method="post">
            <label>Job Reference:</label>
            <input type="text" name="job_reference">
            <button type="submit" name="search_by_job" class="btn">Search</button>
        </form>
    </div>

    <div class="form-section">
        <h2>Search by Applicant Name</h2>
        <form method="post">
            <label>First Name:</label>
            <input type="text" name="first_name">
            <label>Last Name:</label>
            <input type="text" name="last_name">
            <button type="submit" name="search_by_name" class="btn">Search</button>
        </form>
    </div>

    <div class="form-section">
        <h2>Change EOI Status</h2>
        <form method="post">
            <label>EOI Number:</label>
            <input type="text" name="eoi_number">
            <label>New Status:</label>
            <select name="new_status">
                <option value="">Select...</option>
                <option>New</option>
                <option>Current</option>
                <option>Final</option>
            </select>
            <button type="submit" name="update_status" class="btn">Update</button>
        </form>
    </div>

    <div class="form-section">
        <h2>Delete EOIs by Job Reference</h2>
        <form method="post" onsubmit="return confirm('Delete all for this job?');">
            <label>Job Reference:</label>
            <input type="text" name="delete_job_reference">
            <button type="submit" name="delete_by_job" class="btn btn-danger">Delete</button>
        </form>
    </div>

    <?php if (count($results) > 0): ?>
        <div class="results-section">
            <h2>Results (<?php echo count($results); ?>)</h2>
            <table class="eoi-table">
                <tr>
                    <th>EOI#</th>
                    <th>Job Ref</th>
                    <th>First Name</th>
                    <th>Last Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Status</th>
                </tr>
                <?php foreach ($results as $row): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['EOInumber']); ?></td>
                        <td><?php echo htmlspecialchars($row['job_reference']); ?></td>
                        <td><?php echo htmlspecialchars($row['first_name']); ?></td>
                        <td><?php echo htmlspecialchars($row['last_name']); ?></td>
                        <td><?php echo htmlspecialchars($row['email']); ?></td>
                        <td><?php echo htmlspecialchars($row['phone']); ?></td>
                        <td><?php echo htmlspecialchars($row['status']); ?></td>
                    </tr>
                <?php endforeach; ?>
            </table>
        </div>
    <?php endif; ?>

</div>

<?php include 'footer.inc'; ?>

</body>
</html>