<?php
/**
 * Homepage - index.php
 * Main landing page for Control Alt Elite website
 * 
 * Part of COS10026 Web Technology Project Part 2
 * Control Alt Elite - Group Project
 */
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="Control Alt Elite - Innovative IT Solutions for Tomorrow's Challenges">
  <meta name="keywords" content="IT solutions, software development, cybersecurity, cloud engineering, network administration">
  <meta name="author" content="Control Alt Elite">
  <title>Control Alt Elite - Innovative IT Solutions</title>
  <link rel="stylesheet" href="styles/styles.css">
</head>
<body>
  
  <?php include 'header.inc'; ?>
  
  <section class="hero">
    <div class="hero-overlay">
      <h2>Innovative IT Solutions for Tomorrow's Challenges</h2>
      <p>At Control Alt Elite, we specialize in cutting-edge technology solutions that empower businesses to thrive in the digital age.</p>
      <a href="jobs.php" class="cta-button">View Open Positions</a>
    </div>
  </section>
  
  <!-- Enhanced "Who We Are" section with gradient background -->
  <section class="section-gradient company-overview-section">
    <h2>Who We Are</h2>
    <div class="overview-content">
      <div class="overview-text">
        <p>Control Alt Elite is a leading technology consulting firm dedicated to delivering innovative solutions that drive business transformation. With over a decade of experience, we've helped hundreds of organizations modernize their IT infrastructure and achieve their digital goals.</p>
        <p>Our team of expert consultants brings deep technical expertise across software development, cybersecurity, cloud infrastructure, and network administration. We pride ourselves on staying ahead of industry trends and providing our clients with cutting-edge solutions that deliver measurable results.</p>
        <p>Whether you're looking to build custom software, enhance your security posture, migrate to the cloud, or optimize your network infrastructure, Control Alt Elite has the expertise and experience to help you succeed.</p>
      </div>
      <div class="overview-image">
        <img src="images/hq.png" alt="Control Alt Elite Headquarters">
      </div>
    </div>
  </section>
  
  <section class="services section-dark">
    <h2>Our Services</h2>
    <div class="services-grid">
      
      <div class="service-card">
        <h3>Software Development</h3>
        <p>Custom software solutions tailored to your business needs, from web applications to enterprise systems. Our development team uses the latest technologies and best practices to deliver robust, scalable solutions.</p>
      </div>
      
      <div class="service-card">
        <h3>Cybersecurity</h3>
        <p>Comprehensive security solutions to protect your digital assets from evolving threats. We provide security assessments, penetration testing, security architecture design, and ongoing monitoring services.</p>
      </div>
      
      <div class="service-card">
        <h3>Cloud Engineering</h3>
        <p>Scalable cloud infrastructure and migration services to optimize your operations. We help organizations leverage the power of cloud platforms like AWS, Azure, and Google Cloud to achieve greater agility and cost efficiency.</p>
      </div>
      
      <div class="service-card">
        <h3>Network Administration</h3>
        <p>Expert management, monitoring, and maintenance of your organization's computer networks, ensuring security, connectivity, and efficient IT operations. We provide 24/7 network support and proactive infrastructure management.</p>
      </div>
      
    </div>
  </section>
  
  <section class="values">
    <h2>Our Core Values</h2>
    <div class="values-list">
      
      <div class="value-item">
        <h3>Innovation</h3>
        <p>We constantly push the boundaries of technology to deliver cutting-edge solutions that give our clients a competitive advantage.</p>
      </div>
      
      <div class="value-item">
        <h3>Excellence</h3>
        <p>We are committed to delivering the highest quality work in everything we do, from code quality to customer service.</p>
      </div>
      
      <div class="value-item">
        <h3>Integrity</h3>
        <p>We build trust through transparency, honesty, and ethical business practices in all our client relationships.</p>
      </div>
      
      <div class="value-item">
        <h3>Collaboration</h3>
        <p>We believe in the power of teamwork and work closely with our clients to achieve shared success.</p>
      </div>
      
    </div>
  </section>
  
  <section class="section-cta">
    <div class="cta-content">
      <h2>Ready to Transform Your Business?</h2>
      <p>Join the hundreds of organizations that trust Control Alt Elite for their technology needs. We're currently hiring talented professionals to join our growing team.</p>
      <a href="jobs.php" class="cta-button">Explore Career Opportunities</a>
    </div>
  </section>
  
  <?php include 'footer.inc'; ?>
  
</body>
</html>