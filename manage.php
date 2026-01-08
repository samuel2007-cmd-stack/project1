<?php
session_start();

if (!isset($_SESSION['manager_logged_in'])) {
    header("Location: login.php");
    exit();
}

require_once 'settings.php';

$conn = mysqli_connect($host, $user, $pwd, $sql_db);

if (!$conn) {
    exit("Database connection failed.");
}

$message = "";
$results = array();

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    if (isset($_POST['logout'])) {
        session_destroy();
        header("Location: login.php");
        exit();
    }

    if (isset($_POST['list_all'])) {
        $sort_field = mysqli_real_escape_string($conn, $_POST['sort_field'] ?? 'EOInumber');
        $sort_order = mysqli_real_escape_string($conn, $_POST['sort_order'] ?? 'ASC');
        
        $allowed_fields = ['EOInumber', 'first_name', 'last_name', 'job_reference', 'status'];
        if (!in_array($sort_field, $allowed_fields)) { $sort_field = 'EOInumber'; }
        if (!in_array($sort_order, ['ASC', 'DESC'])) { $sort_order = 'ASC'; }

        $query = "SELECT * FROM eoi ORDER BY $sort_field $sort_order";
        $result = mysqli_query($conn, $query);
        if ($result) {
            while ($row = mysqli_fetch_assoc($result)) { $results[] = $row; }
            $message = "Displaying all records sorted by $sort_field";
        }
    }

    if (isset($_POST['search_by_job'])) {
        $job_ref = mysqli_real_escape_string($conn, trim($_POST['job_reference']));
        $query = "SELECT * FROM eoi WHERE job_reference = '$job_ref'";
        $result = mysqli_query($conn, $query);
        while ($row = mysqli_fetch_assoc($result)) { $results[] = $row; }
        $message = count($results) > 0 ? "Found records for $job_ref" : "No records found";
    }

    if (isset($_POST['search_by_name'])) {
        $fname = mysqli_real_escape_string($conn, trim($_POST['first_name']));
        $lname = mysqli_real_escape_string($conn, trim($_POST['last_name']));
        $query = "SELECT * FROM eoi WHERE first_name LIKE '%$fname%' AND last_name LIKE '%$lname%'";
        $result = mysqli_query($conn, $query);
        while ($row = mysqli_fetch_assoc($result)) { $results[] = $row; }
        $message = "Search completed";
    }

    if (isset($_POST['update_status'])) {
        $eoi_id = mysqli_real_escape_string($conn, $_POST['eoi_number']);
        $status = mysqli_real_escape_string($conn, $_POST['new_status']);
        $query = "UPDATE eoi SET status='$status' WHERE EOInumber='$eoi_id'";
        mysqli_query($conn, $query);
        $message = mysqli_affected_rows($conn) > 0 ? "Status updated to $status" : "Update failed";
    }

    if (isset($_POST['delete_by_job'])) {
        $job_ref = mysqli_real_escape_string($conn, $_POST['delete_job_reference']);
        $query = "DELETE FROM eoi WHERE job_reference='$job_ref'";
        mysqli_query($conn, $query);
        $message = "Deleted " . mysqli_affected_rows($conn) . " records for $job_ref";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage EOI | HR Portal</title>
    <link rel="stylesheet" href="styles/styles.css">
</head>
<body>

<?php include 'header.inc'; ?>

<div class="manage-container">
    <div style="display: flex; justify-content: space-between; align-items: center;">
        <h1>Manage EOI Records</h1>
        <form method="post"><button type="submit" name="logout" class="btn btn-danger">Logout</button></form>
    </div>
    
    <?php if ($message != ""): ?>
        <div class="message-box"><p><?= htmlspecialchars($message) ?></p></div>
    <?php endif; ?>

    <div class="form-grid" style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
        
        <div class="form-section">
            <h2>List All EOIs</h2>
            <form method="post">
                <label>Sort By:</label>
                <select name="sort_field">
                    <option value="EOInumber">EOI Number</option>
                    <option value="first_name">First Name</option>
                    <option value="last_name">Last Name</option>
                    <option value="status">Status</option>
                </select>
                <select name="sort_order">
                    <option value="ASC">Ascending</option>
                    <option value="DESC">Descending</option>
                </select>
                <button type="submit" name="list_all" class="btn">Show All</button>
            </form>
        </div>

        <div class="form-section">
            <h2>Search by Job</h2>
            <form method="post">
                <input type="text" name="job_reference" placeholder="Enter Job Ref">
                <button type="submit" name="search_by_job" class="btn">Search</button>
            </form>
        </div>

        <div class="form-section">
            <h2>Search by Name</h2>
            <form method="post">
                <input type="text" name="first_name" placeholder="First Name">
                <input type="text" name="last_name" placeholder="Last Name">
                <button type="submit" name="search_by_name" class="btn">Search</button>
            </form>
        </div>

        <div class="form-section">
            <h2>Update Status</h2>
            <form method="post">
                <input type="text" name="eoi_number" placeholder="EOI ID">
                <select name="new_status">
                    <option value="New">New</option>
                    <option value="Current">Current</option>
                    <option value="Final">Final</option>
                </select>
                <button type="submit" name="update_status" class="btn">Update</button>
            </form>
        </div>

        <div class="form-section" style="grid-column: span 2;">
            <h2>Delete by Job</h2>
            <form method="post" onsubmit="return confirm('Are you sure?');">
                <input type="text" name="delete_job_reference" placeholder="Job Ref to Delete">
                <button type="submit" name="delete_by_job" class="btn btn-danger">Delete All Records</button>
            </form>
        </div>
    </div>

    <?php if (count($results) > 0): ?>
        <div class="results-section">
            <h2>Query Results (<?= count($results) ?>)</h2>
            <table class="eoi-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Ref</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($results as $row): ?>
                        <tr>
                            <td><?= htmlspecialchars($row['EOInumber']) ?></td>
                            <td><?= htmlspecialchars($row['job_reference']) ?></td>
                            <td><?= htmlspecialchars($row['first_name'] . " " . $row['last_name']) ?></td>
                            <td><?= htmlspecialchars($row['email']) ?></td>
                            <td><?= htmlspecialchars($row['phone']) ?></td>
                            <td><?= htmlspecialchars($row['status']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<?php include 'footer.inc'; ?>

</body>
</html>