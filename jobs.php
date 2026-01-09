<?php
require_once 'settings.php';

$sql = "SELECT * FROM jobs";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Jobs Description - Control Alt Elite</title>
<meta name="description" content="Explore career opportunities at Control Alt Elite">
<meta name="keywords" content="IT jobs, software developer, cybersecurity, cloud engineer, network administrator">
<link rel="stylesheet" href="styles/styles.css">
</head>

<body>

<?php include 'header.inc'; ?>

<section class="job-banner">
  <img src="images/businessman-8818855_1920.jpg" alt="Career Banner" class="job-banner-img">
  <h1 class="job-banner-title">Empowering Your Career Journey</h1>
  <p class="job-banner-subtitle">Explore roles that align with your skills and goals.</p>
</section>

<main>

<?php if ($result && $result->num_rows > 0): ?>

<?php while ($job = $result->fetch_assoc()): ?>

<article class="job-posting">
  <details class="job-card">

    <summary class="job-header">
      <span class="job-top">
        <h2 class="job-title"><?= htmlspecialchars($job['title']) ?></h2>
        <span class="job-ref">#<?= htmlspecialchars($job['job_reference']) ?></span>
      </span>

      <span class="job-meta">
        <span><?= htmlspecialchars($job['salary']) ?></span>
        <span><?= htmlspecialchars($job['job_type']) ?></span>
        <span><?= htmlspecialchars($job['location']) ?></span>
        <span>Reports to: <?= htmlspecialchars($job['reports_to']) ?></span>
      </span>
    </summary>

    <div class="job-body">

      <section class="job-summary">
        <p><?= htmlspecialchars($job['summary']) ?></p>
      </section>

      <section class="key-responsibilities">
        <h3>Key Responsibilities</h3>
        <ul>
          <?php foreach (explode('|', $job['responsibilities']) as $responsibility): ?>
            <li><?= htmlspecialchars($responsibility) ?></li>
          <?php endforeach; ?>
        </ul>
      </section>

      <section class="job-skills">
        <h3>Skills and Qualifications</h3>
        <table>
          <thead>
            <tr>
              <th>Required Skills</th>
              <th>Preferred Skills</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td>
                <ol>
                  <?php foreach (explode('|', $job['required_skills']) as $skill): ?>
                    <li><?= htmlspecialchars($skill) ?></li>
                  <?php endforeach; ?>
                </ol>
              </td>
              <td>
                <ol>
                  <?php foreach (explode('|', $job['preferred_skills']) as $skill): ?>
                    <li><?= htmlspecialchars($skill) ?></li>
                  <?php endforeach; ?>
                </ol>
              </td>
            </tr>
          </tbody>
        </table>
      </section>

    </div>
  </details>
</article>

<?php endwhile; ?>

<?php else: ?>
  <p>No job positions available at the moment.</p>
<?php endif; ?>

</main>

<?php include 'footer.inc'; ?>

</body>
</html>


<!-- ================= REFERENCES & AI DISCLOSURE ================= -->
<!-- 
1. Layout inspiration source:
   https://themewagon.github.io/jobfinderportal/job_listing.html

2. Job descriptions generated with GenAI (ChatGPT).
   Prompt used:
   "I need detailed and realistic job descriptions for different IT roles 
   including Software Developer, Cyber Security Analyst, Cloud Engineer, 
   and Network Administrator."
