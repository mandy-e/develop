<?php
/* the purpose of this page is to let the admin confirm the user as a member  
 * 
 * Written By: Robert Erickson robert.erickson@uvm.edu
 * Last updated on: October 17, 2014
 * 
 * 
 */

include "top.php";

print '<article id="main">';

print '<h1>Approval Confirmation</h1>';

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

$adminEmail = "merdei@uvm.edu";
$message = "<p>I am sorry but this request cannot be approved at this time. Please email everythingnonpersonal@gmail.com for help in resolving this matter.</p>";


//##############################################################
//
// SECTION: 2 
// 
// process request

if (isset($_GET["q"])) {
    $key1 = htmlentities($_GET["q"], ENT_QUOTES, "UTF-8");
    $key2 = htmlentities($_GET["w"], ENT_QUOTES, "UTF-8");
    $data = array($key2);
    //##############################################################
    // get the membership record 

    $query = "SELECT fldDateJoined, fldEmail FROM tblRegister WHERE pmkRegisterId = ? ";
    $results = $thisDatabase->select($query, $data);
    $dateSubmitted = $results[0]["fldDateJoined"];
    $email = $results[0]["fldEmail"];

    $k1 = sha1($dateSubmitted);
	
    if ($debug) {
        print "<p>Date: " . $dateSubmitted;
        print "<p>email: " . $email;
        print "<p><pre>";
        print_r($results);
        print "</pre></p>";
        print "<p>k1: " . $k1;
        print "<p>q : " . $key1;
    }
    //##############################################################
    // update confirmed
    if ($key1 == $k1) {
        if ($debug) {
            print "<h1>Approved</h1>";
            }

        $query = "UPDATE tblRegister SET fldApproved=1 WHERE pmkRegisterId = ? ";
        $results = $thisDatabase->update($query, $data);

        if ($debug) {
            print "<p>Query: " . $query;
            print "<p><pre>";
            print_r($results);
            print_r($data);
            print "</pre></p>";
        }
        // notify admin
        $message = '<h2>The following person has been approved:</h2>';

        $message = "<p>This person can now add, modify, and delete records. </p>";
        /*$message .= '<a href="' . $domain . $path_parts["dirname"] . '/approve.php?q=' . $key2 . '">Approve Registration</a></p>';
        $message .= "<p>or copy and paste this url into a web browser: ";
        $message .= $path_parts["dirname"] . '/approve.php?q=' . $key2 . "</p>";*/

        if ($debug) {
            print "<p>" . $message;
            }

        $to = $adminEmail;
        $cc = "";
        $bcc = "";
        $from = "UVM Choral Library <noreply@choral-library.com>";
        $subject = "Library Access Approved";

        $mailed = sendMail($to, $cc, $bcc, $from, $subject, $message);

        if ($debug) {
            print "<p>";
            if (!$mailed) {
                print "NOT ";
            }
            print "mailed to admin ". $to . ".</p>";
        }

        // notify user
        $to = $email;
        $cc = "";
        $bcc = "";
        $from = "UVM Choral Library <noreply@choral-library.com>";
        $subject = "Approval Confirmed - You may now access the choral library records";
        $message = "<p>Congratulations! You have been granted access to add, modify, and delete records from the Choral Library Database.</p>";
        $mailed = sendMail($to, $cc, $bcc, $from, $subject, $message);
		print $mailed;
        if ($debug) {
            print "<p>";
            if (!$mailed) {
                print "NOT ";
            }
            print "mailed to member: " . $to . ".</p>";
        }
    }else{
        print $message;
    }
} // ends isset get q
?>

</article>

<?php
include "footer.php";
if ($debug)
    print "<p>END OF PROCESSING</p>";
?>

</body>
</html>
