<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8"/>
        <meta name="description" content="job application form program"/>
        <meta name="keywords" content="HTML, Form, tags"/>
        <meta name="author" content="Samuel" />
        <title>Job Application - Control Alt Elite</title>
        <link rel="stylesheet" href="styles/styles.css">
    </head>

    <body>

<?php include 'header.inc'; ?>
        
        <!-- Intro message to applicants -->
        <aside>
            <h2>THANK YOU FOR CHOOSING CTRLALTELITE.CO</h2>
            <p id="alert-message">
                PLEASE FILL EACH AND EVERY PART OF THIS FORM TO ENSURE A SMOOTH SAIL TO YOUR POTENTIAL EMPLOYMENT OPPORTUNITY
            </p>
        </aside>

        <hr>

        <!-- Job application form starts here -->
        <form action="process_eoi.php" 
              method="post" 
              novalidate="novalidate">

            <!-- Personal details section -->
            <fieldset>
                <legend>PERSONAL DETAILS</legend>

                <!-- First name field -->
                <label for="firstname">First Name</label>
                <input type="text" id="firstname" name="firstname" size="20" maxlength="20" required>
                <br><br>

                <!-- Last name field -->
                <label for="lastname">Last Name</label>
                <input type="text" id="lastname" name="lastname" size="20" maxlength="20" required>
                <br><br>

                <!-- Date of birth field -->
                <label for="dob">Date Of Birth</label>
                <input type="date" id="dob" name="dob" required>
                <br><br>

                <!-- Email field -->
                <label for="email">Email Id</label>
                <input type="email" id="email" name="email" required>
                <br><br>

                <!-- Phone number field -->
                <label for="phone">Phone Number</label>
                <input type="tel" id="phone" name="phone" maxlength="12" required>
                <br><br>
            </fieldset>

            <!-- Gender selection -->
            <fieldset>
                <legend>Gender</legend>
                <input type="radio" id="male" name="gender" value="male" required>
                <label for="male">Male</label>

                <input type="radio" id="female" name="gender" value="female">
                <label for="female">Female</label>
            </fieldset>

            <!-- Address section -->
            <fieldset>
                <legend>Address Details</legend>

                <label for="unitnumber">Unit Number</label>
                <input type="text" id="unitnumber" name="unitnumber" maxlength="5" inputmode="numeric">
                <br><br>

                <label for="buildingnumber">Building Number</label>
                <input type="text" id="buildingnumber" name="buildingnumber" maxlength="5" inputmode="numeric">
                <br><br>

                <label for="streetname">Street Name</label>
                <input type="text" id="streetname" name="streetname" maxlength="40" required>
                <br><br>

                <label for="zone">Zone</label>
                <input type="text" id="zone" name="zone" maxlength="2" inputmode="numeric" required>
                <br><br>

                <label for="city">City</label>
                <select id="city" name="city" required>
                    <option value="">Please Select Your City</option>
                    <option value="Doha">Doha</option>
                    <option value="Al Wakra">Al Wakra</option>
                    <option value="Al Khor">Al Khor</option>
                    <option value="Dukhan">Dukhan</option>
                    <option value="Al Shamal">Al Shamal</option>
                    <option value="Mesaieed">Mesaieed</option>
                    <option value="Ras Laffan">Ras Laffan</option>
                </select>
                <br><br>
            </fieldset>

            <!-- Job details section -->
            <fieldset>
                <legend>JOB DETAILS</legend>

                <!-- Job reference dropdown -->
                <label for="ref">Job Reference Number</label>
                <select id="ref" name="ref" required>
                    <option value="">Please Select The Job Reference Number</option>
                    <option value="Software Developer">Software Developer - #SWD93</option>
                    <option value="Network Administrator">Network Administrator - #NAD88</option>
                    <option value="Cybersecurity">Cybersecurity Analyst - #CSA71</option>
                    <option value="Cloud Engineer">Cloud Engineer - #CEN54</option>
                </select>
                <br><br>

                <!-- Technical skills checklist -->
                <label>Required Technical Skills (select at least one)</label>
                <br><br>
                <input type="checkbox" id="html" name="skill1" value="HTML">
                <label for="html">HTML</label>
                <br><br>
                <input type="checkbox" id="css" name="skill2" value="CSS">
                <label for="css">CSS</label>
                <br><br>
                <input type="checkbox" id="python" name="skill3" value="Python">
                <label for="python">PYTHON</label>
                <br><br>
                <input type="checkbox" id="java" name="skill4" value="Java">
                <label for="java">JAVA</label>
                <br><br>

                <!-- Other skills textarea -->
                <label for="otherskills">Other Skills</label>
                <br><br>
                <textarea id="otherskills" name="otherskills" rows="4" cols="50" placeholder="Please Enter Your Other Skills"></textarea>
            </fieldset>

            <!-- Acknowledgement Section -->
            <fieldset>
                <legend>ACKNOWLEDGEMENT</legend>
                
                <p style="margin-bottom: 20px; line-height: 1.8; color: var(--neutral-700);">
                    I hereby declare that the information provided in this application form is true, complete, and accurate to the best of my knowledge. 
                    I understand that any false or misleading information may result in the rejection of my application or termination of employment if discovered after hiring.
                </p>
                
                <p style="margin-bottom: 20px; line-height: 1.8; color: var(--neutral-700);">
                    I acknowledge that Control Alt Elite will process my personal data in accordance with applicable data protection laws and 
                    will use this information solely for recruitment purposes. I consent to the collection, storage, and processing of my personal information.
                </p>
                
                <input type="checkbox" id="acknowledge" name="acknowledge" required>
                <label for="acknowledge" style="font-weight: 600; color: var(--primary);">
                    I acknowledge and agree to the above statements *
                </label>
                <br><br>
            </fieldset>

            <!-- Submit and reset buttons -->
            <input type="submit" value="Submit Application">
            <input type="reset" value="Reset Form">
        </form>

<?php include 'footer.inc'; ?>
        
    </body>
</html>