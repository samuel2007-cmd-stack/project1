<?php
session_start();
$errors = $_SESSION['form_errors'] ?? [];
unset($_SESSION['form_errors']); // Clear errors after displaying
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Official Job Application Form for Control Alt Elite IT Solutions."/>
    <title>Apply | Control Alt Elite</title>
    <link rel="stylesheet" href="styles/styles.css">
</head>
<body class="apply-page">

<?php include 'header.inc'; ?>

<main class="container">
    <div class="apply-layout">
        <aside class="apply-sidebar" role="note">
            <div class="alert-box">
                <h2>Application Protocol</h2>
                <p id="alert-message">
                    Please ensure all mandatory fields (marked with *) are completed. Our recruitment team uses this data for primary screening.
                </p>
                <hr>
                <ul class="requirements-checklist">
                    <li>Valid email for correspondence</li>
                    <li>Accurate Job Reference ID</li>
                    <li>Selection of core competencies</li>
                </ul>
            </div>
        </aside>

        <section class="form-container">
            <header class="section-header">
                <h1>Expression of Interest</h1>
                <p>Start your journey with Control Alt Elite today.</p>
            </header>

            <?php if (!empty($errors)): ?>
                <div class="error-summary" role="alert">
                    <h3>Submission Issues:</h3>
                    <ul>
                        <?php foreach ($errors as $error): ?>
                            <li><?= htmlspecialchars($error) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <form action="process_eoi.php" method="post" novalidate="novalidate" class="modern-form">
                
                <fieldset>
                    <legend>Personal Identity</legend>
                    <div class="form-grid">
                        <div class="input-group">
                            <label for="firstname">First Name *</label>
                            <input type="text" id="firstname" name="firstname" pattern="[A-Za-z]{1,20}" required>
                        </div>
                        <div class="input-group">
                            <label for="lastname">Last Name *</label>
                            <input type="text" id="lastname" name="lastname" pattern="[A-Za-z]{1,20}" required>
                        </div>
                        <div class="input-group">
                            <label for="dob">Date of Birth *</label>
                            <input type="date" id="dob" name="dob" required>
                        </div>
                        <div class="input-group">
                            <label for="gender">Gender *</label>
                            <select name="gender" id="gender" required>
                                <option value="">Select Gender</option>
                                <option value="male">Male</option>
                                <option value="female">Female</option>
                            </select>
                        </div>
                    </div>
                </fieldset>

                <fieldset>
                    <legend>Contact & Location</legend>
                    <div class="form-grid">
                        <div class="input-group full-width">
                            <label for="email">Email Address *</label>
                            <input type="email" id="email" name="email" placeholder="example@domain.com" required>
                        </div>
                        <div class="input-group">
                            <label for="phone">Phone Number *</label>
                            <input type="tel" id="phone" name="phone" placeholder="8-12 digits" required>
                        </div>
                        <div class="input-group">
                            <label for="unitnumber">Unit / Building</label>
                            <div class="dual-input">
                                <input type="text" id="unitnumber" name="unitnumber" placeholder="Unit" maxlength="5">
                                <input type="text" id="buildingnumber" name="buildingnumber" placeholder="Bldg" maxlength="5">
                            </div>
                        </div>
                        <div class="input-group">
                            <label for="streetname">Street Name *</label>
                            <input type="text" id="streetname" name="streetname" maxlength="40" required>
                        </div>
                        <div class="input-group">
                            <label for="city">City & Zone *</label>
                            <div class="dual-input">
                                <select id="city" name="city" required>
                                    <option value="Doha">Doha</option>
                                    <option value="Al Wakra">Al Wakra</option>
                                    <option value="Al Khor">Al Khor</option>
                                    <option value="Mesaieed">Mesaieed</option>
                                </select>
                                <input type="text" id="zone" name="zone" placeholder="Zone" maxlength="2" required>
                            </div>
                        </div>
                    </div>
                </fieldset>

                <fieldset>
                    <legend>Professional Selection</legend>
                    <div class="input-group">
                        <label for="ref">Job Reference *</label>
                        <select id="ref" name="ref" required>
                            <option value="">Choose Position</option>
                            <option value="Software Developer" <?= (isset($_GET['ref']) && $_GET['ref'] == 'SWD93') ? 'selected' : '' ?>>Software Developer (#SWD93)</option>
                            <option value="Network Administrator" <?= (isset($_GET['ref']) && $_GET['ref'] == 'NAD88') ? 'selected' : '' ?>>Network Administrator (#NAD88)</option>
                            <option value="Cybersecurity" <?= (isset($_GET['ref']) && $_GET['ref'] == 'CSA71') ? 'selected' : '' ?>>Cybersecurity (#CSA71)</option>
                            <option value="Cloud Engineer" <?= (isset($_GET['ref']) && $_GET['ref'] == 'CEN54') ? 'selected' : '' ?>>Cloud Engineer (#CEN54)</option>
                        </select>
                    </div>

                    <label class="label-heading">Technical Proficiencies</label>
                    <div class="checkbox-grid">
                        <label class="check-item"><input type="checkbox" name="skill1" value="HTML"> HTML</label>
                        <label class="check-item"><input type="checkbox" name="skill2" value="CSS"> CSS</label>
                        <label class="check-item"><input type="checkbox" name="skill3" value="Python"> Python</label>
                        <label class="check-item"><input type="checkbox" name="skill4" value="Java"> Java</label>
                    </div>

                    <div class="input-group">
                        <label for="otherskills">Additional Skills</label>
                        <textarea id="otherskills" name="otherskills" rows="4" placeholder="Certifications, other languages..."></textarea>
                    </div>
                </fieldset>

                <div class="form-actions">
                    <button type="submit" class="btn-primary">Submit Application</button>
                    <button type="reset" class="btn-secondary">Clear Form</button>
                </div>
            </form>
        </section>
    </div>
</main>

<?php include 'footer.inc'; ?>

</body>
</html>