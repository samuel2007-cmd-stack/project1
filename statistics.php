<?php
/**
 * Enhanced Statistics Dashboard with Pure CSS Charts
 * Displays comprehensive analytics with bar charts and pie charts
 * No JavaScript required for charts
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once 'settings.php';

// Check authentication
if (!isset($_SESSION['manager_logged_in']) || $_SESSION['manager_logged_in'] !== true) {
    header("Location: login.php");
    exit();
}

$conn = getDatabaseConnection();

// ===== TOTAL APPLICATIONS =====
$total_query = "SELECT COUNT(*) as total FROM eoi";
$total_result = mysqli_query($conn, $total_query);
$total_apps = mysqli_fetch_assoc($total_result)['total'];

// ===== MALE APPLICANTS =====
$male_query = "SELECT COUNT(*) as count FROM eoi WHERE gender='male'";
$male_result = mysqli_query($conn, $male_query);
$male_apps = mysqli_fetch_assoc($male_result)['count'];

// ===== FEMALE APPLICANTS =====
$female_query = "SELECT COUNT(*) as count FROM eoi WHERE gender='female'";
$female_result = mysqli_query($conn, $female_query);
$female_apps = mysqli_fetch_assoc($female_result)['count'];

// ===== APPLICATIONS BY GENDER =====
$gender_query = "SELECT gender, COUNT(*) as count FROM eoi WHERE gender IS NOT NULL GROUP BY gender";
$gender_result = mysqli_query($conn, $gender_query);
$gender_stats = [];
$gender_percentages = [];
while ($row = mysqli_fetch_assoc($gender_result)) {
    $gender_stats[$row['gender']] = $row['count'];
    if ($total_apps > 0) {
        $gender_percentages[$row['gender']] = ($row['count'] / $total_apps) * 100;
    }
}

// Calculate cumulative for gender pie chart
$cumulative_gender = 0;
$gender_pie_data = [];
$gender_colors = ['male' => '#3b82f6', 'female' => '#ec4899', 'other' => '#8b5cf6'];

foreach ($gender_percentages as $gender => $percentage) {
    $gender_pie_data[$gender] = [
        'percentage' => $percentage,
        'count' => $gender_stats[$gender],
        'start' => $cumulative_gender,
        'color' => isset($gender_colors[strtolower($gender)]) ? $gender_colors[strtolower($gender)] : '#6366f1'
    ];
    $cumulative_gender += $percentage;
}

// ===== APPLICATIONS BY STATE/LOCATION =====
$state_query = "SELECT state, COUNT(*) as count FROM eoi GROUP BY state ORDER BY count DESC LIMIT 10";
$state_result = mysqli_query($conn, $state_query);
$state_stats = [];
while ($row = mysqli_fetch_assoc($state_result)) {
    $state_stats[] = $row;
}
$max_state_count = !empty($state_stats) ? $state_stats[0]['count'] : 1;

// ===== APPLICATIONS BY JOB POSITION/COURSE =====
$job_query = "SELECT job_reference, COUNT(*) as count FROM eoi GROUP BY job_reference ORDER BY count DESC";
$job_result = mysqli_query($conn, $job_query);
$job_stats = [];
while ($row = mysqli_fetch_assoc($job_result)) {
    $job_stats[] = $row;
}
$max_job_count = !empty($job_stats) ? $job_stats[0]['count'] : 1;

// ===== JOB POSITION NAMES =====
$job_names = [
    'SWD93' => 'Software Developer',
    'NAD88' => 'Network Administrator',
    'CSA71' => 'Cybersecurity Analyst',
    'CEN54' => 'Cloud Engineer'
];

// ===== RECENT APPLICATIONS =====
$recent_query = "SELECT job_reference, first_name, last_name, status, EOInumber, email 
                 FROM eoi 
                 ORDER BY EOInumber DESC 
                 LIMIT 5";
$recent_result = mysqli_query($conn, $recent_query);
$recent_apps = [];
while ($row = mysqli_fetch_assoc($recent_result)) {
    $recent_apps[] = $row;
}

mysqli_close($conn);
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
            <div class="pie-chart" aria-label="Gender distribution pie chart" style="background: conic-gradient(
                <?php
                $angle = 0;
                $gradients = [];
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
            <div class="pie-legend">
                <?php 
                if (!empty($gender_pie_data)) {
                    foreach ($gender_pie_data as $gender => $data): 
                ?>
                    <div class="legend-item">
                        <span class="legend-color" style="background-color: <?php echo $data['color']; ?>"></span>
                        <span class="legend-label"><?php echo ucfirst($gender); ?></span>
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
            <?php foreach ($state_stats as $state): 
                $percentage = ($state['count'] / $max_state_count) * 100;
            ?>
                <div class="bar-item">
                    <div class="bar-label-section">
                        <span class="bar-label"><?php echo htmlspecialchars($state['state']); ?></span>
                        <span class="bar-sublabel"><?php echo $state['count']; ?> applications</span>
                    </div>
                    <div class="bar-visual-section">
                        <div class="bar-track">
                            <div class="bar-fill bar-blue" style="width: <?php echo $percentage; ?>%">
                                <span class="bar-value"><?php echo $state['count']; ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
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
            foreach ($job_stats as $job): 
                $percentage = ($job['count'] / $max_job_count) * 100;
                $job_name = isset($job_names[$job['job_reference']]) ? $job_names[$job['job_reference']] : $job['job_reference'];
            ?>
                <div class="bar-item">
                    <div class="bar-label-section">
                        <span class="bar-label"><?php echo htmlspecialchars($job_name); ?></span>
                        <span class="bar-sublabel"><?php echo htmlspecialchars($job['job_reference']); ?> - <?php echo $job['count']; ?> applications</span>
                    </div>
                    <div class="bar-visual-section">
                        <div class="bar-track">
                            <div class="bar-fill bar-purple" style="width: <?php echo $percentage; ?>%">
                                <span class="bar-value"><?php echo $job['count']; ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
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
                                    <span class="table-id">#<?php echo $app['EOInumber']; ?></span>
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
                                    <span class="status-pill status-<?php echo strtolower($app['status']); ?>">
                                        <?php echo $app['status']; ?>
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