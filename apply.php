<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8"/>
        <meta name="description" content="job application form program"/>
        <meta name="keywords" content="HTML, Form, tags"/>
        <meta name="author" content="Samuel" />
        <title>Job Application HTML</title>
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
                <input type="text" id="firstname" name="firstname" size="20" maxlength="20">
                <br><br>

                <!-- Last name field -->
                <label for="lastname">Last Name</label>
                <input type="text" id="lastname" name="lastname" size="20" maxlength="20">
                <br><br>

                <!-- Date of birth field -->
                <label for="dob">Date Of Birth</label>
                <input type="date" id="dob" name="dob">
                <br><br>

                <!-- Email field -->
                <label for="email">Email Id</label>
                <input type="email" id="email" name="email">
                <br><br>

                <!-- Phone number field -->
                <label for="phone">Phone Number</label>
                <input type="tel" id="phone" name="phone" maxlength="12">
                <br><br>
            </fieldset>

            <!-- Gender selection -->
            <fieldset>
                <legend>Gender</legend>
                <input type="radio" id="male" name="gender" value="male">
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
                <input type="text" id="streetname" name="streetname" maxlength="40">
                <br><br>

                <label for="zone">Zone</label>
                <input type="text" id="zone" name="zone" maxlength="2" inputmode="numeric">
                <br><br>

                <label for="city">City</label>
                <select id="city" name="city">
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
                <select id="ref" name="ref">
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

            <!-- Submit and reset buttons -->
            <input type="submit" value="Submit">
            <input type="reset" value="Reset Form">
        </form>

<?php include 'footer.inc'; ?>
        
    </body>
</html>