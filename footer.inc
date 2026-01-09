<?php
/**
 * Footer Include File
 * Displays the website footer with contact information, quick links,
 * social media links, and project management links
 * 
 * Part of COS10026 Web Technology Project Part 2
 * Control Alt Elite - Group Project
 */
?>

<footer class="footer-box">
  <div class="container">
    <div class="footer-content">
      
      <!-- Contact Information Section -->
      <div class="footer-section">
        <h3>Contact Us</h3>
        <p>
          <strong>Email:</strong> 
          <a href="mailto:info@controlaltelite.com?subject=Contact%20from%20Website" 
             class="footer-link">
            info@controlaltelite.com
          </a>
        </p>
        <p>
          <strong>Phone:</strong> 
          <a href="tel:+97471427215" class="footer-link">
            +974 71427215
          </a>
        </p>
        <p>
          <strong>Address:</strong> 
          7 Al Marsah Street, Doha, Qatar
        </p>
      </div>
      
      <!-- Quick Navigation Links Section -->
      <div class="footer-section">
        <h3>Quick Links</h3>
        <ul>
          <li><a href="index.php">Home</a></li>
          <li><a href="jobs.php">Job Descriptions</a></li>
          <li><a href="apply.php">Apply Now</a></li>
          <li><a href="about.php">About Us</a></li>
          <li><a href="enhancements.php">Enhancements</a></li>
          <li>
            <a href="mailto:info@controlaltelite.com?subject=Inquiry%20from%20Control%20Alt%20Elite%20Website&body=Dear%20Control%20Alt%20Elite%20Team%2C%0A%0AI%20am%20writing%20to%20inquire%20about...%0A%0AThank%20you." 
               target="_blank" 
               rel="noopener noreferrer">
              Email Us
            </a>
          </li>
        </ul>
      </div>
      
      <!-- Social Media and Professional Links Section -->
      <div class="footer-section">
        <h3>Connect With Us</h3>
        <div class="social-links">
          <a href="https://www.linkedin.com/company/controlaltelite" 
             target="_blank" 
             rel="noopener noreferrer" 
             aria-label="Visit our LinkedIn page">
            LinkedIn
          </a>
          <a href="https://twitter.com/controlaltelite" 
             target="_blank" 
             rel="noopener noreferrer" 
             aria-label="Follow us on Twitter">
            Twitter
          </a>
          <a href="https://www.facebook.com/controlaltelite" 
             target="_blank" 
             rel="noopener noreferrer" 
             aria-label="Like us on Facebook">
            Facebook
          </a>
        </div>
      </div>
      
    </div>
    
    <!-- Footer Bottom Section with Copyright and Project Links -->
    <div class="footer-bottom">
      <p>&copy; <?php echo date('Y'); ?> Control Alt Elite. All rights reserved.</p>
      <p>
        <a href="https://07samuelmoraes.atlassian.net/jira/software/projects/CAEG/boards/34?atlOrigin=eyJpIjoiNDBlNzEyOGE5MTkxNDc4NGJmMWY0MWQ5YjA0YTE5ZDciLCJwIjoiaiJ9" 
           target="_blank" 
           rel="noopener noreferrer" 
           aria-label="View our Jira project management board">
          View Our Jira Project
        </a>
      </p>
      <p>
        <a href="https://github.com/samuel2007-cmd-stack/project1.git" 
           target="_blank" 
           rel="noopener noreferrer" 
           aria-label="View our GitHub repository">
          View Our Git Repository
        </a>
      </p>
    </div>
    
  </div>
</footer>