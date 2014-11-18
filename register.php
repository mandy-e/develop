<?php
/* the purpose of this page is to display a form to allow a person to register
 * the form will be sticky meaning if there is a mistake the data previously 
 * entered will be displayed again. Once a form is submitted (to this same page)
 * we first sanitize our data by replacing html codes with the html character.
 * then we check to see if the data is valid. if data is valid enter the data 
 * into the table and we send and dispplay a confirmation email message. 
 * 
 * if the data is incorrect we flag the errors.
 * 
 * Written By: Robert Erickson robert.erickson@uvm.edu
 * Last updated on: October 17, 2014
 * 
 * 
  -- --------------------------------------------------------
  --
  -- Table structure for table `tblRegister`
  --

  CREATE TABLE IF NOT EXISTS `tblRegister` (
  `pkRegisterId` int(11) NOT NULL AUTO_INCREMENT,
  `fldEmail` varchar(65) DEFAULT NULL,
  `fldDateJoined` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `fldConfirmed` tinyint(1) NOT NULL DEFAULT '0',
  `fldApproved` tinyint(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`pkPersonId`)
  ) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

 * I am using a surrogate key for demonstration, 
 * email would make a good primary key as well which would prevent someone
 * from entering an email address in more than one record.
 */

include "top.php";
//%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%
//
// SECTION: 1 Initialize variables
//
// SECTION: 1a.
// variables for the classroom purposes to help find errors.
$debug = true;
if (isset($_GET["debug"])) { // ONLY do this in a classroom environment
    $debug = true;
}
if ($debug)
    print "<p>DEBUG MODE IS ON</p>";


//%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%
//
// SECTION: 1b Security
//
// define security variable to be used in SECTION 2a.
$yourURL = $domain . $phpSelf;

//%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%
//
// SECTION: 1c form variables
//
// Initialize variables one for each form element
// in the order they appear on the form
$username = "";
$password = "";
$email = "";

//%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%
//
// SECTION: 1d form error flags
//
// Initialize Error Flags one for each form element we validate
// in the order they appear in section 1c.
$usernameERROR = false;
$passwordERROR = false;
$emailERROR = false;

//%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%
//
// SECTION: 1e misc variables
//
// create array to hold error messages filled (if any) in 2d displayed in 3c.
$errorMsg = array();

// used for building email message to be sent and displayed
$mailed = false;
$messageA = "";
$messageB = "";
$messageC = "";

//@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
//
// SECTION: 2 Process for when the form is submitted
//
if (isset($_POST["btnSubmit"])) {
//@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
//
// SECTION: 2a Security
//
    if (!securityCheck(true)) {
        $msg = "<p>Sorry you cannot access this page. ";
        $msg.= "Security breach detected and reported</p>";
        die($msg);
    }

//@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
//
// SECTION: 2b Sanitize (clean) data
// remove any potential JavaScript or html code from users input on the
// form. Note it is best to follow the same order as declared in section 1c.
	$username = htmlentities($_POST["txtUsername"], ENT_QUOTES, "UTF-8");
	$password = htmlentities($_POST["txtUsername"], ENT_QUOTES, "UTF-8");
    $email = filter_var($_POST["txtEmail"], FILTER_SANITIZE_EMAIL);

//@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
//
// SECTION: 2c Validation
//
// Validation section. Check each value for possible errors, empty or
// not what we expect. You will need an IF block for each element you will
// check (see above section 1c and 1d). The if blocks should also be in the
// order that the elements appear on your form so that the error messages
// will be in the order they appear. errorMsg will be displayed on the form
// see section 3b. The error flag ($emailERROR) will be used in section 3c.

//Make Validation IF statements
	if ($username == "") {
		$errorMsg[] = "Please create a 5-20 character username";
		$usernameERROR = true;
	} elseif (!verifyAlphaNum($username)) {
		$errorMsg[] = "Your username must be only letters and digits";
		$usernameERROR = true;
	} elseif (strlen($username)<5) {
		$errorMsg[] = "Username must be between 5-20 characters long";
		$usernameERROR = true;
	}
	
	if ($password == "") {
		$errorMsg[] = "Please create a 5-12 character password";
		$passwordERROR = true;
	} elseif (strlen($password)<5) {
		$errorMsg[] = "Password must be between 5-12 characters long";
		$passwordERROR = true;
	}
    if ($email == "") {
        $errorMsg[] = "Please enter your email address";
        $emailERROR = true;
    } elseif (!verifyEmail($email)) {
        $errorMsg[] = "Your email address appears to be invalid";
        $emailERROR = true;
    }

//@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
//
// SECTION: 2d Process Form - Passed Validation
//
// Process for when the form passes validation (the errorMsg array is empty)
//
    if (!$errorMsg) {
        if ($debug)
            print "<p>Form submitted. An email has been sent to your address with a confirmation hyperlink to complete the registration process.</p>";

        //@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
        //
        // SECTION: 2e Save Data
        //
		// Make tblRegister
        $primaryKey = "";
        $dataEntered = false;
        try {
            $thisDatabase->db->beginTransaction();
            #1. CREATE SQL STATEMENT
            $data = array();
			
            //INSERT INTO... MAKE SURE flds line up with # of values = question marks
            $query = 'INSERT INTO tblRegister (fldEmail, fldUsername, fldPassword) values (?,?,?)';
            $data[] = $email;
            $data[] = $username;
            $data[] = $password;
            //use if want to print data array? use --> 
            //print_r($data);
                        
            if ($debug) {
                print "<p>sql " . $query;
                print"<p><pre>";
                print_r($data);
                print"</pre></p>";
            }
            #2. RUN YOUR QUERY
            $results = $thisDatabase->insert($query, $data);
			#3 DO YOU NEED PRIMARY KEY? (YES - so use the lastInsert line
            $primaryKey = $thisDatabase->lastInsert();
            if ($debug)
                print "<p>pmk= " . $primaryKey;

// all sql statements are done so lets commit to our changes
            
            $dataEntered = $thisDatabase->db->commit();
            
            $dataEntered = true;
            if ($debug)
                print "<p>transaction complete ";
        } catch (PDOExecption $e) {
            $thisDatabase->db->rollback();
            if ($debug)
                print "Error!: " . $e->getMessage() . "</br>";
            $errorMsg[] = "There was a problem with accpeting your data please contact us directly.";
        }
        // If the transaction was successful, give success message
        if ($dataEntered) {
            if ($debug)
                print "<p>data entered now prepare keys ";
            //#################################################################
            // create a key value for confirmation

            $query = "SELECT fldDateJoined FROM tblRegister WHERE pmkRegisterId=" . $primaryKey;
            $results = $thisDatabase->select($query);

            $dateSubmitted = $results[0]["fldDateJoined"];

            $key1 = sha1($dateSubmitted);
            $key2 = $primaryKey;

            if ($debug)
                print "<p>key 1: " . $key1;
            if ($debug)
                print "<p>key 2: " . $key2;


            //#################################################################
            //
            //Put forms information into a variable to print on the screen
            //MESSAGE B IS IMPORTANT - SENDS THE Q=TIME STAMP AND W=INDEX NUMBER! IMPORTANT!!!!!!

            $messageA = '<h2>Thank you for registering for access to the choral library records.</h2>';

            $messageB = "<p>Click this link to confirm your registration: ";
            $messageB .= '<a href="' . $domain . $path_parts["dirname"] . '/confirmation.php?q=' . $key1 . '&amp;w=' . $key2 . '">Confirm Registration</a></p>';
            $messageB .= "<p>or copy and paste this url into a web browser: ";
            $messageB .= $domain . $path_parts["dirname"] . '/confirmation.php?q=' . $key1 . '&amp;w=' . $key2 . "</p>";

            $messageC .= "<p><b>Email Address:</b><i>   " . $email . "</i></p>";

            //##############################################################
            //
            // email the form's information
            //
            $to = $email; // the person who filled out the form
            $cc = "";
            $bcc = "";
            $from = "UVM Choral Library <noreply@choral-library.com>";
            $subject = "Confirm your registration";
    		$mailed = sendMail($to, $cc, $bcc, $from, $subject, $messageA . $messageB . $messageC);
        } //data entered  
    } // end form is valid
} // ends if form was submitted.
//#############################################################################
//
// SECTION 3 Display Form
//
?>
<article id="main">
    <?php
//####################################
//
// SECTION 3a.
//
//
//
//
// If its the first time coming to the form or there are errors we are going
// to display the form.
    if (isset($_POST["btnSubmit"]) AND empty($errorMsg)) { // closing of if marked with: end body submit
        print "<h1>Your Request has ";
        if (!$mailed) {
            print "not ";
        }
        print "been processed</h1>";
        print "<p>A copy of this message has ";
        if (!$mailed) {
            print "not ";
        }
        print "been sent</p>";
        print "<p>To: " . $email . "</p>";
        print "<p>Mail Message: Please check your inbox and confirm your registration.</p>";
        print $messageA . $messageC;
    } else {
//####################################
//
// SECTION 3b Error Messages
//
// display any error messages before we print out the form
        if ($errorMsg) {
            print '<div id="errors">';
            print "<ol>\n";
            foreach ($errorMsg as $err) {
                print "<li>" . $err . "</li>\n";
            }
            print "</ol>\n";
            print '</div>';
        }
//####################################
//
// SECTION 3c html Form
//
        /* Display the HTML form. note that the action is to this same page. $phpSelf
          is defined in top.php
          NOTE the line:
          value="<?php print $email; ?>
          this makes the form sticky by displaying either the initial default value (line 35)
          or the value they typed in (line 84)
          NOTE this line:
          <?php if($emailERROR) print 'class="mistake"'; ?>
          this prints out a css class so that we can highlight the background etc. to
          make it stand out that a mistake happened here.
         */
        ?>
        <header>
        <h1> Register for Access to Choral Library Records </h1>
        </header>
        <form action="<?php print $phpSelf; ?>"
              method="post"
              id="frmRegister">
            <fieldset class="wrapper">
                <legend>Register for Access to the UVM Choral Library Records</legend>
                <p>Are you beautiful? pretty? cute? ... interesting?<br>
                	Do you like to dress up? act? entertain?<br>
                	Are you often bored and have too much time on your hands?<br>
                	Do you find yourself wishing you could help make others happy?<br>
                	If so, now is the time to join Ouran High School Host Club for spring semester.</p>
                <fieldset class="wrapperTwo">
                    <legend>Join our host club by completing this form</legend>
                    <fieldset class="contact">
                        <legend>Basic Information</legend>
                        
						<!--Subject-->
						<label for="txtUsername" class="required">Username
							<input type="text" id="txtUsername" name="txtUsername"
									value="<?php print $username; ?>"
									tabindex="100" maxlength="20" placeholder="Create a 5-20 character username"
									<?php if ($usernameERROR) print 'class="mistake"'; ?>
									onfocus="this.select()"
									autofocus
									>
						</label>
						
                        <label for="txtPassword" class="required">Password
                            <input type="text" id="txtPassword" name="txtPassword"
                                   value="<?php print $password; ?>"
                                   tabindex="200" maxlength="12" placeholder="Create a 5-12 character password"
                                   <?php if ($passwordERROR) print 'class="mistake"'; ?>
                                   onfocus="this.select()"
                                   >
                        </label>
                        <label for="txtEmail" class="required">Email
                            <input type="text" id="txtEmail" name="txtEmail"
                                   value="<?php print $email; ?>"
                                   tabindex="300" maxlength="45" placeholder="your.email@ouran.edu"
                                   <?php if ($emailERROR) print 'class="mistake"'; ?>
                                   onfocus="this.select()"
                                   >
                        </label>
                    </fieldset> <!-- ends contact -->
                </fieldset> <!-- ends wrapper Two -->
                <fieldset class="buttons">
                    <legend></legend>
                    <input type="submit" id="btnSubmit" name="btnSubmit" value="Register" tabindex="900" class="button">
                </fieldset> <!-- ends buttons -->
            </fieldset> <!-- Ends Wrapper -->
        </form>
        <?php
    } // end body submit
    ?>
</article>



<?php
include "footer.php";
if ($debug)
    print "<p>END OF PROCESSING</p>";
?>
</body>
</html>

