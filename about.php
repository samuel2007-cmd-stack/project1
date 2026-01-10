<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Meet the Control Alt Elite team and learn about our contributions"/>
    <meta name="keywords" content="about us, team members, project contributions, Control Alt Elite"/>
    <meta name="author" content="Control Alt Elite Team"/>
    <title>About Us - Control Alt Elite</title>
    <link rel="stylesheet" href="styles/styles.css">
</head>

<body>

<?php include 'header.inc'; ?>

<section class="about-banner">
    <img src="images/luca-bravo-impeI_8oGW0-unsplash.jpg" alt="Background" class="banner-bg">
    <h1>ABOUT US</h1>
</section>

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

<section class="members-contribution" aria-labelledby="members-contribution-heading">
    <h2 id="members-contribution-heading" class="memberscont-title">Our Contributions</h2>
    <div class="memberscont-container">
        
        <dl class="memberscont-dl"> 
            <dt>Samuel Moraes</dt>
            <dd>Implemented the database-backed features including the EOI table design.</dd>
            <dd>Developed process_eoi.php with server-side validation and database insertion.</dd>
            <dd>Created manage.php to allow HR managers to query, update, and delete EOIs.</dd>
            <dd>Implemented dynamic job listings by storing job data in the database.</dd>
            <dd>Handled MySQL integration using settings.php.</dd>
        </dl>

        <dl class="memberscont-dl">
            <dt>Anantroop Singh Sahi</dt>
            <dd>Completed the enhancements component of the project (enhancements.php).</dd>
            <dd>Implemented advanced features such as manager-side sorting and access control.</dd>
            <dd>Ensured enhancements were documented clearly according to project specifications.</dd>
            <dd>Assisted with project testing and refinement.</dd>
        </dl>

        <dl class="memberscont-dl">
            <dt>Beatrice Thomas</dt>
            <dd>Modularised the website using PHP includes for header, navigation, and footer.</dd>
            <dd>Updated and structured the About page to clearly document team contributions.</dd>
            <dd>Ensured semantic HTML, accessibility, and consistency across pages.</dd>
            <dd>Collaborated with teammates to integrate frontend and backend components.</dd>
        </dl>
    </div>
</section> 

<section class="group-image" aria-labelledby="group-image-heading">
    
    <h2 id="group-image-heading" class="groupimg-title">Introducing our Team</h2>
    <div class="group-image-content">
    
    <!-- Text descriptions on the left -->
    <div class="group-image-description">
        <h3>💡 Our Story</h3>
        <p>Control Alt Elite is a dynamic team of IT professionals who came together with a shared passion for technology and innovation. We are students at Swinburne University, collaborating to deliver cutting-edge web solutions that make a difference.</p>
        
        <h3>🎯 Our Mission</h3>
        <p>We believe in collaboration, continuous learning, and pushing the boundaries of what's possible in technology. Each team member brings unique skills—from database architecture to frontend design—that combine to create exceptional digital experiences.</p>
        
        <h3>⚡ What We Do</h3>
        <p>Our mission is to empower businesses through technology, creating solutions that are not only functional but also elegant and user-friendly. We specialize in full-stack web development, database management, and creating intuitive user interfaces.</p>
        
        <h3>🚀 Our Expertise</h3>
        <p>With expertise spanning web development, database management, user experience design, and system architecture, we deliver comprehensive solutions that meet modern business needs. Our collaborative approach ensures every project benefits from diverse perspectives and technical excellence.</p>
    </div>
    
    <!-- Image and caption on the right -->
    <div class="groupimg-photo-box">
        <figure class="group-figure">
            <img src="images/group_pic.jpeg" alt="Group photo of Anantroop, Samuel and Beatrice" class="groupimg-photo" width="600" height="400">
            <figcaption>🤝 Team Control Alt Elite — (left to right) Anantroop, Samuel, Beatrice.</figcaption>
        </figure>
    </div>
    
</div>
    
</section>

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
        <td>Samuel Moraes</td>
        <td>Coding, Gaming and Football</td>
      </tr>
      <tr>
        <td>2</td>
        <td>Anantroop Singh Sahi</td>
        <td>Gaming and Cricket</td>
      </tr>
      <tr>
        <td>3</td>
        <td>Beatrice Thomas</td>
        <td>Basketball, Gaming and Designing</td>
      </tr>
    </tbody>
  </table>
</section>

<?php include 'footer.inc'; ?>

</body>
</html>