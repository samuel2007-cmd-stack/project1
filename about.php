<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8"/>
        <meta name="description" content="job application form program"/>
        <meta name="keywords" content="HTML"/>
        <meta name="author" content="Samuel" />
        <title>Job Application HTML</title>
        <link rel="stylesheet" href="styles/styles.css">
    </head>

<body>

<?php include 'header.inc'; ?>

<!-- Banner image for About Us page -->
<section class="about-banner">
    <img src="images/luca-bravo-impeI_8oGW0-unsplash.jpg" alt="Background" class="banner-bg">
    <h1>ABOUT US</h1>
</section>

<!-- Section listing group members -->
<section class="member-box" aria-labelledby="members-heading">
  <h2 id="members-heading">Meet Our Group Members</h2>
  <ul class="member-list">
    <li>Samuel Moraes
      <ul><li>ID: 106205571</li></ul>
    </li>
    <li>Anantroop Singh Sahi
      <ul><li>ID: 106221933</li></ul>
    </li>
    <li>Beatrice Thomas
      <ul><li>ID: 106194132</li></ul>
    </li>
  </ul>
</section> 

<!-- Section detailing contributions of each member -->
<section class="members-contribution" aria-labelledby="members-contribution-heading">
    <h2 id="members-contribution-heading" class="memberscont-title">Our Contributions</h2>
    <div class="memberscont-container">
        <!-- Samuel Moraes contributions -->
        <dl class="memberscont-dl"> 
            <dt>Samuel Moraes</dt>
            <dd>Implemented the database-backed features including the EOI table design.</dd>
            <dd>Developed process_eoi.php with server-side validation and database insertion.</dd>
            <dd>Created manage.php to allow HR managers to query, update, and delete EOIs.</dd>
            <dd>Implemented dynamic job listings by storing job data in the database.</dd>
            <dd>Handled MySQL integration using settings.php.</dd>
        </dl>

        <!-- Anantroop Singh Sahi contributions -->
        <dl class="memberscont-dl">
            <dt>Anantroop Singh Sahi</dt>
            <dd>Completed the enhancements component of the project (enhancements.php).</dd>
            <dd>Implemented advanced features such as manager-side sorting and access control.</dd>
            <dd>Ensured enhancements were documented clearly according to project specifications.</dd>
            <dd>Assisted with project testing and refinement.</dd>
        </dl>

        <!-- Beatrice Thomas contributions -->
        <dl class="memberscont-dl">
            <dt>Beatrice Thomas</dt>
            <dd>Modularised the website using PHP includes for header, navigation, and footer.</dd>
            <dd>Updated and structured the About page to clearly document team contributions.</dd>
            <dd>Ensured semantic HTML, accessibility, and consistency across pages.</dd>
            <dd>Collaborated with teammates to integrate frontend and backend components.</dd>
        </dl>
    </div>
</section> 

<!-- Section displaying group photo -->
<section class="group-image" aria-labelledby="group-image-heading">
    <img src="images/luca-bravo-impeI_8oGW0-unsplash.jpg" alt="group-image-bg" class="groupimg-bg">
    <h2 id="group-image-heading" class="groupimg-title">Introducing our Team</h2>
    <div class="groupimg-photo-box">
        <figure class="group-figure">
            <img src="images/group_pic.jpeg" alt="Group photo of Anantroop, Samuel and Beatrice" class="groupimg-photo" width="600" height="400">
            <figcaption>Team Control Alt Elite — (left to right) Anantroop, Samuel, Beatrice.</figcaption>
        </figure>
    </div>
</section>

<!-- Section showing members and their interests -->
<section class="interests-section" aria-labelledby="interests-heading">
  <h2 id="interests-heading" class="members-interests-heading">Members and Interests</h2>

  <table class="members-interests">
    <caption>Group Members and Their Interests</caption>
    <thead>
      <tr>
        <th>SERIAL NUMBER</th>
        <th>GROUP MEMBER NAME</th>
        <th>INTERESTS</th>
      </tr>
    </thead>
    <tbody>
      <tr>
        <td>1</td>
        <td colspan="2">Samuel Moraes — Coding, Gaming and Football</td>
      </tr>
      <tr>
        <td>2</td>
        <td colspan="2">Anantroop Singh Sahi — Gaming and Cricket</td>
      </tr>
      <tr>
        <td>3</td>
        <td colspan="2">Beatrice Thomas — Basketball, Gaming and Designing</td>
      </tr>
    </tbody>
  </table>
</section>

<?php include 'footer.inc'; ?>

</body>
</html>