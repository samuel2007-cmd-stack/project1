<?php
/**
 * Enhanced Statistics Dashboard with Pure CSS Charts
 * Displays comprehensive analytics with bar charts and pie charts
 * No JavaScript required for charts
 */

require_once 'settings.php';

// Check authentication
if (!isset($_SESSION['manager_logged_in']) || $_SESSION['manager_logged_in'] !== true) {
    header("Location: login.php");
    exit();
}

$conn = getDatabaseConnection();

if (!$conn) {
    error_log("Database connection failed in statistics.php");
    die("Database connection failed. Please try again later.");
}

// Initialize all variables with default values
$total_apps = 0;
$male_apps = 0;
$female_apps = 0;
$new_apps = 0;
$current_apps = 0;
$final_apps = 0;
$gender_stats = array();
$gender_percentages = array();
$status_stats = array();
$status_percentages = array();
$state_stats = array();
$job_stats = array();
$recent_apps = array();

// ===== TOTAL APPLICATIONS =====
$total_query = "SELECT COUNT(*) as total FROM eoi";
$total_result = mysqli_query($conn, $total_query);
if ($total_result) {
    $row = mysqli_fetch_assoc($total_result);
    $total_apps = $row ? (int)$row['total'] : 0;
} else {
    error_log("Total apps query error: " . mysqli_error($conn));
}

// ===== MALE APPLICANTS =====
$male_query = "SELECT COUNT(*) as count FROM eoi WHERE gender='male'";
$male_result = mysqli_query($conn, $male_query);
if ($male_result) {
    $row = mysqli_fetch_assoc($male_result);
    $male_apps = $row ? (int)$row['count'] : 0;
} else {
    error_log("Male apps query error: " . mysqli_error($conn));
}

// ===== FEMALE APPLICANTS =====
$female_query = "SELECT COUNT(*) as count FROM eoi WHERE gender='female'";
$female_result = mysqli_query($conn, $female_query);
if ($female_result) {
    $row = mysqli_fetch_assoc($female_result);
    $female_apps = $row ? (int)$row['count'] : 0;
} else {
    error_log("Female apps query error: " . mysqli_error($conn));
}

// ===== NEW APPLICATIONS =====
$new_query = "SELECT COUNT(*) as count FROM eoi WHERE status='New'";
$new_result = mysqli_query($conn, $new_query);
if ($new_result) {
    $row = mysqli_fetch_assoc($new_result);
    $new_apps = $row ? (int)$row['count'] : 0;
} else {
    error_log("New apps query error: " . mysqli_error($conn));
}

// ===== CURRENT APPLICATIONS =====
$current_query = "SELECT COUNT(*) as count FROM eoi WHERE status='Current'";
$current_result = mysqli_query($conn, $current_query);
if ($current_result) {
    $row = mysqli_fetch_assoc($current_result);
    $current_apps = $row ? (int)$row['count'] : 0;
} else {
    error_log("Current apps query error: " . mysqli_error($conn));
}

// ===== FINAL APPLICATIONS =====
$final_query = "SELECT COUNT(*) as count FROM eoi WHERE status='Final'";
$final_result = mysqli_query($conn, $final_query);
if ($final_result) {
    $row = mysqli_fetch_assoc($final_result);
    $final_apps = $row ? (int)$row['count'] : 0;
} else {
    error_log("Final apps query error: " . mysqli_error($conn));
}

// ===== APPLICATIONS BY GENDER =====
$gender_query = "SELECT gender, COUNT(*) as count FROM eoi WHERE gender IS NOT NULL GROUP BY gender";
$gender_result = mysqli_query($conn, $gender_query);
if ($gender_result) {
    while ($row = mysqli_fetch_assoc($gender_result)) {
        $gender_stats[$row['gender']] = (int)$row['count'];
        if ($total_apps > 0) {
            $gender_percentages[$row['gender']] = ($row['count'] / $total_apps) * 100;
        }
    }
} else {
    error_log("Gender stats query error: " . mysqli_error($conn));
}

// Calculate cumulative for gender pie chart
$cumulative_gender = 0;
$gender_pie_data = array();
$gender_colors = array('male' => '#3b82f6', 'female' => '#ec4899', 'other' => '#8b5cf6');

foreach ($gender_percentages as $gender => $percentage) {
    $gender_pie_data[$gender] = array(
        'percentage' => $percentage,
        'count' => $gender_stats[$gender],
        'start' => $cumulative_gender,
        'color' => isset($gender_colors[strtolower($gender)]) ? $gender_colors[strtolower($gender)] : '#6366f1'
    );
    $cumulative_gender += $percentage;
}

// ===== STATUS DISTRIBUTION =====
$status_query = "SELECT status, COUNT(*) as count FROM eoi WHERE status IS NOT NULL AND status != '' GROUP BY status";
$status_result = mysqli_query($conn, $status_query);
if ($status_result) {
    while ($row = mysqli_fetch_assoc($status_result)) {
        $status_stats[$row['status']] = (int)$row['count'];
        if ($total_apps > 0) {
            $status_percentages[$row['status']] = ($row['count'] / $total_apps) * 100;
        }
    }
} else {
    error_log("Status stats query error: " . mysqli_error($conn));
}

// Calculate cumulative for status pie chart
$cumulative_status = 0;
$status_pie_data = array();
$status_colors = array('New' => '#3b82f6', 'Current' => '#f59e0b', 'Final' => '#10b981', 'Accepted' => '#059669');

foreach ($status_percentages as $status => $percentage) {
    $status_pie_data[$status] = array(
        'percentage' => $percentage,
        'count' => $status_stats[$status],
        'start' => $cumulative_status,
        'color' => isset($status_colors[$status]) ? $status_colors[$status] : '#6366f1'
    );
    $cumulative_status += $percentage;
}

// ===== APPLICATIONS BY STATE/LOCATION =====
$state_query = "SELECT state, COUNT(*) as count FROM eoi WHERE state IS NOT NULL GROUP BY state ORDER BY count DESC LIMIT 10";
$state_result = mysqli_query($conn, $state_query);
$max_state_count = 1;
if ($state_result) {
    while ($row = mysqli_fetch_assoc($state_result)) {
        $state_stats[] = $row;
    }
    $max_state_count = !empty($state_stats) ? (int)$state_stats[0]['count'] : 1;
} else {
    error_log("State stats query error: " . mysqli_error($conn));
}

// ===== APPLICATIONS BY JOB POSITION/COURSE =====
$job_query = "SELECT job_reference, COUNT(*) as count FROM eoi WHERE job_reference IS NOT NULL GROUP BY job_reference ORDER BY count DESC";
$job_result = mysqli_query($conn, $job_query);
$max_job_count = 1;
if ($job_result) {
    while ($row = mysqli_fetch_assoc($job_result)) {
        $job_stats[] = $row;
    }
    $max_job_count = !empty($job_stats) ? (int)$job_stats[0]['count'] : 1;
} else {
    error_log("Job stats query error: " . mysqli_error($conn));
}

// ===== JOB POSITION NAMES =====
$job_names = array(
    'SWD93' => 'Software Developer',
    'NAD88' => 'Network Administrator',
    'CSA71' => 'Cybersecurity Analyst',
    'CEN54' => 'Cloud Engineer'
);

// ===== RECENT APPLICATIONS =====
$recent_query = "SELECT job_reference, first_name, last_name, status, EOInumber, email 
                 FROM eoi 
                 ORDER BY EOInumber DESC 
                 LIMIT 5";
$recent_result = mysqli_query($conn, $recent_query);
if ($recent_result) {
    while ($row = mysqli_fetch_assoc($recent_result)) {
        $recent_apps[] = $row;
    }
} else {
    error_log("Recent apps query error: " . mysqli_error($conn));
}

closeDatabaseConnection($conn);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="EOI Statistics Dashboard - Control Alt Elite">
    <title>Statistics Dashboard - Control Alt Elite</title>
    <link rel="stylesheet" href="styles/styles.css">
</head>
<body class="statistics-page">

<?php include 'header.inc'; ?>

<div class="stats-banner">
    <div class="stats-banner-content">
        <div class="stats-banner-icon">
            <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <line x1="18" y1="20" x2="18" y2="10"></line>
                <line x1="12" y1="20" x2="12" y2="4"></line>
                <line x1="6" y1="20" x2="6" y2="14"></line>
            </svg>
        </div>
        <h1 class="stats-banner-title">Analytics Dashboard</h1>
        <p class="stats-banner-subtitle">Real-time insights with visual charts</p>
    </div>
</div>

<div class="stats-wrapper">

    <!-- Metrics Overview Cards -->
    <div class="metrics-grid">
        <div class="metric-card metric-blue">
            <div class="metric-header">
                <span class="metric-icon">📊</span>
                <span class="metric-trend">Total</span>
            </div>
            <h3 class="metric-title">Total Applications</h3>
            <p class="metric-value"><?php echo $total_apps; ?></p>
            <p class="metric-label">All Time Submissions</p>
        </div>

        <div class="metric-card metric-purple">
            <div class="metric-header">
                <span class="metric-icon">👨</span>
                <span class="metric-trend">Male</span>
            </div>
            <h3 class="metric-title">Male Applicants</h3>
            <p class="metric-value"><?php echo $male_apps; ?></p>
            <p class="metric-label">
                <?php 
                $male_percentage = $total_apps > 0 ? round(($male_apps / $total_apps) * 100, 1) : 0;
                echo $male_percentage . "% of Total";
                ?>
            </p>
        </div>

        <div class="metric-card metric-orange">
            <div class="metric-header">
                <span class="metric-icon">👩</span>
                <span class="metric-trend">Female</span>
            </div>
            <h3 class="metric-title">Female Applicants</h3>
            <p class="metric-value"><?php echo $female_apps; ?></p>
            <p class="metric-label">
                <?php 
                $female_percentage = $total_apps > 0 ? round(($female_apps / $total_apps) * 100, 1) : 0;
                echo $female_percentage . "% of Total";
                ?>
            </p>
        </div>

        <div class="metric-card metric-green">
            <div class="metric-header">
                <span class="metric-icon">✨</span>
                <span class="metric-trend">New</span>
            </div>
            <h3 class="metric-title">New Applications</h3>
            <p class="metric-value"><?php echo $new_apps; ?></p>
            <p class="metric-label">
                <?php 
                $new_percentage = $total_apps > 0 ? round(($new_apps / $total_apps) * 100, 1) : 0;
                echo $new_percentage . "% of Total";
                ?>
            </p>
        </div>

        <div class="metric-card metric-yellow">
            <div class="metric-header">
                <span class="metric-icon">⏳</span>
                <span class="metric-trend">Current</span>
            </div>
            <h3 class="metric-title">Current Applications</h3>
            <p class="metric-value"><?php echo $current_apps; ?></p>
            <p class="metric-label">
                <?php 
                $current_percentage = $total_apps > 0 ? round(($current_apps / $total_apps) * 100, 1) : 0;
                echo $current_percentage . "% of Total";
                ?>
            </p>
        </div>

        <div class="metric-card metric-success">
            <div class="metric-header">
                <span class="metric-icon">🎯</span>
                <span class="metric-trend">Final</span>
            </div>
            <h3 class="metric-title">Final Applications</h3>
            <p class="metric-value"><?php echo $final_apps; ?></p>
            <p class="metric-label">
                <?php 
                $final_percentage = $total_apps > 0 ? round(($final_apps / $total_apps) * 100, 1) : 0;
                echo $final_percentage . "% of Total";
                ?>
            </p>
        </div>
    </div>

    <!-- STATUS DISTRIBUTION PIE CHART -->
    <div class="chart-card">
        <div class="chart-header">
            <h2 class="chart-title">
                <span class="title-icon">📊</span>
                Application Status Distribution
            </h2>
        </div>
        <div class="pie-chart-container">
            <?php if (!empty($status_pie_data)): ?>
            <div class="pie-chart" aria-label="Status distribution pie chart" style="background: conic-gradient(
                <?php
                $angle = 0;
                $gradients = array();
                foreach ($status_pie_data as $status => $data) {
                    $end_angle = $angle + ($data['percentage'] * 3.6);
                    $gradients[] = "{$data['color']} {$angle}deg {$end_angle}deg";
                    $angle = $end_angle;
                }
                echo implode(', ', $gradients);
                ?>
            );">
                <div class="pie-center">
                    <span class="pie-center-value"><?php echo array_sum($status_stats); ?></span>
                    <span class="pie-center-label">Total</span>
                </div>
            </div>
            <?php endif; ?>
            <div class="pie-legend">
                <?php 
                if (!empty($status_pie_data)) {
                    foreach ($status_pie_data as $status => $data): 
                ?>
                    <div class="legend-item">
                        <span class="legend-color" style="background-color: <?php echo $data['color']; ?>"></span>
                        <span class="legend-label"><?php echo htmlspecialchars($status); ?></span>
                        <span class="legend-value"><?php echo $data['count']; ?> (<?php echo round($data['percentage'], 1); ?>%)</span>
                    </div>
                <?php 
                    endforeach;
                } else {
                    echo '<p style="color: #94a3b8; text-align: center;">No status data available</p>';
                }
                ?>
            </div>
        </div>
    </div>

    <!-- GENDER COMPARISON PIE CHART -->
    <div class="chart-card">
        <div class="chart-header">
            <h2 class="chart-title">
                <span class="title-icon">👥</span>
                Gender Distribution
            </h2>
        </div>
        <div class="pie-chart-container">
            <?php if (!empty($gender_pie_data)): ?>
            <div class="pie-chart" aria-label="Gender distribution pie chart" style="background: conic-gradient(
                <?php
                $angle = 0;
                $gradients = array();
                foreach ($gender_pie_data as $gender => $data) {
                    $end_angle = $angle + ($data['percentage'] * 3.6);
                    $gradients[] = "{$data['color']} {$angle}deg {$end_angle}deg";
                    $angle = $end_angle;
                }
                echo implode(', ', $gradients);
                ?>
            );">
                <div class="pie-center">
                    <span class="pie-center-value"><?php echo array_sum($gender_stats); ?></span>
                    <span class="pie-center-label">People</span>
                </div>
            </div>
            <?php endif; ?>
            <div class="pie-legend">
                <?php 
                if (!empty($gender_pie_data)) {
                    foreach ($gender_pie_data as $gender => $data): 
                ?>
                    <div class="legend-item">
                        <span class="legend-color" style="background-color: <?php echo $data['color']; ?>"></span>
                        <span class="legend-label"><?php echo ucfirst(htmlspecialchars($gender)); ?></span>
                        <span class="legend-value"><?php echo $data['count']; ?> (<?php echo round($data['percentage'], 1); ?>%)</span>
                    </div>
                <?php 
                    endforeach;
                } else {
                    echo '<p style="color: #94a3b8; text-align: center;">No gender data available</p>';
                }
                ?>
            </div>
        </div>
    </div>

    <!-- BAR CHART: Location/State Distribution -->
    <div class="chart-card">
        <div class="chart-header">
            <h2 class="chart-title">
                <span class="title-icon">📍</span>
                Geographic Distribution - Top Locations
            </h2>
        </div>
        <div class="bar-chart-container">
            <?php 
            if (!empty($state_stats)) {
                foreach ($state_stats as $state): 
                    $percentage = ($state['count'] / $max_state_count) * 100;
            ?>
                <div class="bar-item">
                    <div class="bar-label-section">
                        <span class="bar-label"><?php echo htmlspecialchars($state['state']); ?></span>
                        <span class="bar-sublabel"><?php echo (int)$state['count']; ?> applications</span>
                    </div>
                    <div class="bar-visual-section">
                        <div class="bar-track">
                            <div class="bar-fill bar-blue" style="width: <?php echo $percentage; ?>%">
                                <span class="bar-value"><?php echo (int)$state['count']; ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            <?php 
                endforeach;
            } else {
                echo '<p style="color: #94a3b8; text-align: center; padding: 20px;">No location data available</p>';
            }
            ?>
        </div>
    </div>

    <!-- BAR CHART: Job Positions/Courses -->
    <div class="chart-card">
        <div class="chart-header">
            <h2 class="chart-title">
                <span class="title-icon">💼</span>
                Applications by Course/Position
            </h2>
        </div>
        <div class="bar-chart-container">
            <?php 
            if (!empty($job_stats)) {
                foreach ($job_stats as $job): 
                    $percentage = ($job['count'] / $max_job_count) * 100;
                    $job_name = isset($job_names[$job['job_reference']]) ? $job_names[$job['job_reference']] : $job['job_reference'];
            ?>
                <div class="bar-item">
                    <div class="bar-label-section">
                        <span class="bar-label"><?php echo htmlspecialchars($job_name); ?></span>
                        <span class="bar-sublabel"><?php echo htmlspecialchars($job['job_reference']); ?> - <?php echo (int)$job['count']; ?> applications</span>
                    </div>
                    <div class="bar-visual-section">
                        <div class="bar-track">
                            <div class="bar-fill bar-purple" style="width: <?php echo $percentage; ?>%">
                                <span class="bar-value"><?php echo (int)$job['count']; ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            <?php 
                endforeach;
            } else {
                echo '<p style="color: #94a3b8; text-align: center; padding: 20px;">No job data available</p>';
            }
            ?>
        </div>
    </div>

    <!-- Recent Applications Table -->
    <div class="chart-card">
        <div class="chart-header">
            <h2 class="chart-title">
                <span class="title-icon">🕐</span>
                Latest Applications
            </h2>
        </div>
        <div class="table-wrapper">
            <?php if (!empty($recent_apps)): ?>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Applicant</th>
                            <th>Email</th>
                            <th>Position</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recent_apps as $app): ?>
                            <tr>
                                <td>
                                    <span class="table-id">#<?php echo htmlspecialchars($app['EOInumber']); ?></span>
                                </td>
                                <td>
                                    <span class="table-name"><?php echo htmlspecialchars($app['first_name'] . ' ' . $app['last_name']); ?></span>
                                </td>
                                <td>
                                    <span class="table-email"><?php echo htmlspecialchars($app['email']); ?></span>
                                </td>
                                <td>
                                    <span class="table-job">
                                        <?php 
                                            $job_name = isset($job_names[$app['job_reference']]) ? $job_names[$app['job_reference']] : $app['job_reference'];
                                            echo htmlspecialchars($job_name);
                                        ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="status-pill status-<?php echo strtolower(htmlspecialchars($app['status'])); ?>">
                                        <?php echo htmlspecialchars($app['status']); ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="empty-state">
                    <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="10"></circle>
                        <line x1="12" y1="8" x2="12" y2="12"></line>
                        <line x1="12" y1="16" x2="12.01" y2="16"></line>
                    </svg>
                    <p>No applications received yet</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Navigation -->
    <div class="stats-navigation">
        <a href="manage.php" class="nav-button nav-primary">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M19 12H5M12 19l-7-7 7-7"/>
            </svg>
            <span>Back to Management</span>
        </a>
    </div>

</div>

<?php include 'footer.inc'; ?>

</body>
</html>