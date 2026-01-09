<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8"/>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta name="description" content="job application form program"/>
        <meta name="keywords" content="HTML, Form, tags"/>
        <meta name="author" content="Samuel" />
        <title>Job Application - Control Alt Elite</title>
        <link rel="stylesheet" href="styles/styles.css">
    </head>

    <body>

<?php include 'header.inc'; ?>
        
        <aside>
            <h2>THANK YOU FOR CHOOSING CTRLALTELITE.CO</h2>
            <p id="alert-message">
                PLEASE FILL EACH AND EVERY PART OF THIS FORM TO ENSURE A SMOOTH SAIL TO YOUR POTENTIAL EMPLOYMENT OPPORTUNITY
            </p>
        </aside>

        <hr>

        <form action="process_eoi.php" 
              method="post" 
              novalidate="novalidate">

            <fieldset>
                <legend>PERSONAL DETAILS</legend>

                <label for="firstname">First Name</label>
                <input type="text" id="firstname" name="firstname" size="20" maxlength="20" required>
                <br><br>

                <label for="lastname">Last Name</label>
                <input type="text" id="lastname" name="lastname" size="20" maxlength="20" required>
                <br><br>

                <label for="dob">Date Of Birth</label>
                <input type="text" id="dob" name="dob" placeholder="dd/mm/yyyy" required>
                <br><br>

                <label for="email">Email Id</label>
                <input type="email" id="email" name="email" required>
                <br><br>

                <label for="phone">Phone Number</label>
                <input type="tel" id="phone" name="phone" maxlength="8" required>
                <br><br>
            </fieldset>

            <fieldset>
                <legend>Gender</legend>
                <input type="radio" id="male" name="gender" value="male" required>
                <label for="male">Male</label>

                <input type="radio" id="female" name="gender" value="female">
                <label for="female">Female</label>
            </fieldset>

            <fieldset>
                <legend>Address Details</legend>

                <label for="streetaddress">Street Address</label>
                <input type="text" id="streetaddress" name="streetaddress" maxlength="40" required>
                <br><br>

                <label for="suburb">Suburb/Town</label>
                <input type="text" id="suburb" name="suburb" maxlength="40" required>
                <br><br>

                <label for="postcode">Postcode</label>
                <input type="text" id="postcode" name="postcode" maxlength="2" inputmode="numeric" required>
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

            <fieldset>
                <legend>JOB DETAILS</legend>

                <label for="ref">Job Reference Number</label>
                <select id="ref" name="ref" required>
                    <option value="">Please Select The Job Reference Number</option>
                    <option value="SWD93">Software Developer - #SWD93</option>
                    <option value="NAD88">Network Administrator - #NAD88</option>
                    <option value="CSA71">Cybersecurity Analyst - #CSA71</option>
                    <option value="CEN54">Cloud Engineer - #CEN54</option>
                </select>
                <br><br>

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

                <label for="otherskills">Other Skills</label>
                <br><br>
                <textarea id="otherskills" name="otherskills" rows="4" cols="50" placeholder="Please Enter Your Other Skills"></textarea>
            </fieldset>

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

            <input type="submit" value="Submit Application">
            <input type="reset" value="Reset Form">
        </form>

<?php include 'footer.inc'; ?>
        
    </body>
</html>