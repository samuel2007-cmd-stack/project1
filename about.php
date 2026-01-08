<?php
require_once 'settings.php';

$team_members = [
    [
        "name" => "Samuel Moraes",
        "id" => "106205571",
        "interests" => "Coding, Gaming and Football",
        "tasks" => [
            "Implemented database-backed features and EOI table design.",
            "Developed process_eoi.php with server-side validation.",
            "Created manage.php for HR querying, updates, and deletion.",
            "Implemented dynamic job listings via database storage.",
            "Handled MySQL integration using settings.php."
        ]
    ],
    [
        "name" => "Anantroop Singh Sahi",
        "id" => "106221933",
        "interests" => "Gaming and Cricket",
        "tasks" => [
            "Completed enhancements component (enhancements.php).",
            "Implemented manager-side sorting and access control.",
            "Documented enhancements per project specifications.",
            "Assisted with project testing and refinement."
        ]
    ],
    [
        "name" => "Beatrice Thomas",
        "id" => "106194132",
        "interests" => "Basketball, Gaming and Designing",
        "tasks" => [
            "Modularised website using PHP includes for templates.",
            "Structured About page for clear contribution documentation.",
            "Ensured semantic HTML, accessibility, and consistency.",
            "Collaborated on frontend and backend integration."
        ]
    ]
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8"/>
    <meta name="description" content="About Us - Team Contributions"/>
    <meta name="keywords" content="PHP, HTML, Team"/>
    <meta name="author" content="Samuel" />
    <title>About Us | Control Alt Elite</title>
    <link rel="stylesheet" href="styles/styles.css">
</head>
<body>

<?php include 'header.inc'; ?>

<section class="about-banner">
    <img src="images/luca-bravo-impeI_8oGW0-unsplash.jpg" alt="Background" class="banner-bg">
    <h1>ABOUT US</h1>
</section>

<section class="member-box">
    <h2>Meet Our Group Members</h2>
    <ul class="member-list">
        <?php foreach ($team_members as $member): ?>
            <li><?= htmlspecialchars($member['name']) ?>
                <ul><li>ID: <?= htmlspecialchars($member['id']) ?></li></ul>
            </li>
        <?php endforeach; ?>
    </ul>
</section> 

<section class="members-contribution">
    <h2 class="memberscont-title">Our Contributions</h2>
    <div class="memberscont-container">
        <?php foreach ($team_members as $member): ?>
            <dl class="memberscont-dl"> 
                <dt><?= htmlspecialchars($member['name']) ?></dt>
                <?php foreach ($member['tasks'] as $task): ?>
                    <dd><?= htmlspecialchars($task) ?></dd>
                <?php endforeach; ?>
            </dl>
        <?php endforeach; ?>
    </div>
</section> 

<section class="group-image">
    <h2 class="groupimg-title">Introducing our Team</h2>
    <div class="groupimg-photo-box">
        <figure class="group-figure">
            <img src="images/group_pic.jpeg" alt="Group photo" class="groupimg-photo" width="600" height="400">
            <figcaption>Team Control Alt Elite — (left to right) Anantroop, Samuel, Beatrice.</figcaption>
        </figure>
    </div>
</section>

<section class="interests-section">
  <h2 class="members-interests-heading">Members and Interests</h2>
  <table class="members-interests">
    <caption>Group Members and Their Interests</caption>
    <thead>
      <tr>
        <th>SERIAL</th>
        <th>NAME</th>
        <th>INTERESTS</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($team_members as $index => $member): ?>
      <tr>
        <td><?= $index + 1 ?></td>
        <td><?= htmlspecialchars($member['name']) ?></td>
        <td><?= htmlspecialchars($member['interests']) ?></td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</section>

<?php include 'footer.inc'; ?>

</body>
</html>