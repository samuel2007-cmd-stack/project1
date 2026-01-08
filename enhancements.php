<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Technical report of website enhancements and advanced PHP features.">
    <title>Technical Enhancements | Control Alt Elite</title>
    <link rel="stylesheet" href="styles/styles.css">
</head>
<body class="enhancements-page">

<?php include 'header.inc'; ?>

<main class="container">
    <header class="enhancement-header">
        <h1>Technical Enhancements</h1>
        <p class="subtitle">Advanced features implemented to improve security, scalability, and administrative control.</p>
    </header>

    <section class="enhancement-card">
        <div class="card-header">
            <span class="badge">Security</span>
            <h2>1. Secure Manager Registration & Validation</h2>
        </div>
        <div class="card-body">
            <p><strong>Rationale:</strong> Standard security protocols require administrative accounts to be protected by strong, unique credentials to prevent unauthorized access to sensitive applicant data.</p>
            
            <div class="implementation-grid">
                <div class="impl-desc">
                    <h3>Implementation Details</h3>
                    <ul class="feature-list">
                        <li><strong>Server-Side Logic:</strong> Utilized PHP <code>preg_match</code> to enforce complexity (uppercase, numbers, length).</li>
                        <li><strong>Data Protection:</strong> Implemented <code>password_hash()</code> using the BCRYPT algorithm.</li>
                        <li><strong>Integrity:</strong> Query-based checks ensure no duplicate usernames exist in the database.</li>
                    </ul>
                </div>
                <div class="impl-code">
                    <h3>Schema Design</h3>
                    <code>CREATE TABLE managers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE,
    password VARCHAR(255)
);</code>
                </div>
            </div>
            <p class="link-ref">View feature in action: <a href="register.php">Registration Page</a></p>
        </div>
    </section>

    <section class="enhancement-card">
        <div class="card-header">
            <span class="badge">Session Management</span>
            <h2>2. Robust Access Control System</h2>
        </div>
        <div class="card-body">
            <p><strong>Rationale:</strong> To maintain privacy and comply with data protection standards, administrative functions are hidden behind a session-based authentication wall.</p>
            
            

            <div class="implementation-grid">
                <div class="impl-desc">
                    <h3>Technical Workflow</h3>
                    <ul class="feature-list">
                        <li><strong>Verification:</strong> Uses <code>password_verify()</code> to compare input against hashed database values.</li>
                        <li><strong>State Persistence:</strong> PHP <code>$_SESSION</code> variables track authentication status across the domain.</li>
                        <li><strong>Middleware Logic:</strong> Restricted pages check for session variables and redirect unauthorized users via <code>header()</code>.</li>
                    </ul>
                </div>
                <div class="impl-code">
                    <h3>Security Logic</h3>
                    <pre>if (!isset($_SESSION['auth'])) {
    header("Location: login.php");
    exit();
}</pre>
                </div>
            </div>
            <p class="link-ref">View feature in action: <a href="login.php">Manager Login</a></p>
        </div>
    </section>

    <section class="enhancement-card">
        <div class="card-header">
            <span class="badge">Anti-Brute Force</span>
            <h2>3. Intelligent Login Attempt Lockout</h2>
        </div>
        <div class="card-body">
            <p><strong>Rationale:</strong> To defend against automated "brute-force" attacks, the system throttles login attempts and temporarily disables accounts after consecutive failures.</p>
            
            <div class="implementation-grid">
                <div class="impl-desc">
                    <h3>Mechanics</h3>
                    <ul class="feature-list">
                        <li><strong>Tracking:</strong> Failed attempts are logged in the database with a timestamp.</li>
                        <li><strong>Throttling:</strong> Access is suspended for 15 minutes after 3 failed attempts.</li>
                        <li><strong>Auto-Recovery:</strong> The system compares <code>lockout_time</code> with <code>NOW()</code> to restore access automatically.</li>
                    </ul>
                </div>
                <div class="impl-code">
                    <h3>SQL Evolution</h3>
                    <code>ALTER TABLE managers 
ADD failed_attempts INT DEFAULT 0,
ADD lockout_time DATETIME;</code>
                </div>
            </div>
        </div>
    </section>

    <section class="file-manifest">
        <h2>Architecture Manifest</h2>
        <table>
            <thead>
                <tr>
                    <th>File</th>
                    <th>Role</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <tr><td>register.php</td><td>Secure user intake with server-side validation.</td><td>Enhanced</td></tr>
                <tr><td>login.php</td><td>Authentication gateway with lockout logic.</td><td>Enhanced</td></tr>
                <tr><td>manage.php</td><td>Protected dashboard for EOI data manipulation.</td><td>Secured</td></tr>
                <tr><td>header.inc</td><td>Conditional UI rendering (Login vs Logout).</td><td>Modular</td></tr>
            </tbody>
        </table>
    </section>
</main>

<?php include 'footer.inc'; ?>

</body>
</html>