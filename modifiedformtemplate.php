<?php
include "top.php";
//%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%
//
// SECTION: 1 Initialize variables
//
// SECTION: 1a.
// variables for the classroom purposes to help find errors.
$debug = false;
if (isset($_GET["debug"])) { // ONLY do this in a classroom environment
	$debug = true;
}
if ($debug)
	print "<p>DEBUG MODE IS ON</p>";
//%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%
//
// SECTION: 1a-b. Get the database info
//
require_once('../bin/myDatabase.php');

$dbUserName = get_current_user() . '_reader';
$whichPass = "r"; //flag for which one to use.
$dbName = strtoupper(get_current_user()) . '_UVM_Courses';

$thisDatabase = new myDatabase($dbUserName, $whichPass, $dbName);

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
$title = "";
$number = "";
$building = "";
$startTime = "";
$professor = "";
//%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%
//
// SECTION: 1d form error flags
//
// Initialize Error Flags one for each form element we validate
// in the order they appear in section 1c.
$titleERROR = false;
$numberERROR = false;
$startTimeERROR = false;
$professorERROR = false;
//%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%
//
// SECTION: 1e misc variables
//
// create array to hold error messages filled (if any) in 2d displayed in 3c.
$errorMsg = array();
// array used to hold form values that will be written to a CSV file
$dataRecord = array();
//$mailed=false; // have we mailed the information to the user?
//@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
//
// SECTION: 2 Process for when the form is submitted
//
if (isset($_POST["btnSubmit"])) {
    if (isset($_POST['change']))
    {
    
    }
    else  if (isset($_POST['remove']))
    {
    
    
    }
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
	$title = htmlentities($_POST["txtTitle"], ENT_QUOTES, "UTF-8");
	$dataRecord[] = $title;
	$number = htmlentities($_POST["txtNumber"], ENT_QUOTES, "UTF-8");
	$dataRecord[] = $number;
	$building = htmlentities($_POST["lstBuilding"], ENT_QUOTES, "UTF-8");
	$dataRecord[] = $building;
	$startTime = htmlentities($_POST["txtStartTime"], ENT_QUOTES, "UTF-8");
	$dataRecord[] = $startTime;
	$professor = htmlentities($_POST["txtProfessor"], ENT_QUOTES, "UTF-8");
	$dataRecord[] = $professor;
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

	if ($title == (!alpha_num($title))) {
		$errorMsg[] = "Your subject name appears to contain one or more suspicous characters.";
		$titleERROR = true;
	} 
	if ($number == (!is_numeric($number))) {
		$errorMsg[] = "Your course number appears to contain one or more suspicous characters.";
		$numberERROR = true;
	} 
	if ($startTime == (!preg_match("/(2[0-3]|[01][0-9]):[0-5][0-9]/",$startTime))) {
		$errorMsg[] = "Your start time must appear like 08:45.";
		$startTimeERROR = true;
	} 
	if ($professor == (!ctype_alpha($professor))) {
		$errorMsg[] = "Your professor name appears to contain one or more suspicous characters.";
		$professorERROR = true;
	} 
	//@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
	//
	// SECTION: 2d Process Form - Passed Validation
	//
	// Process for when the form passes validation (the errorMsg array is empty)
	//
	if (!$errorMsg) {
		if ($debug)
			print "<p>Form is valid</p>";

		//OR: Build query - MAKE REALLY BIG!
		$data = array($title, $number, $building, $startTime, $professor);
		$query = "SELECT CONCAT(fldDepartment, ' ',fldCourseNumber) AS Course, 
		fldCRN AS CRN, 
		CONCAT(fldFirstName, ' ',fldLastName) AS Professor, 
		CONCAT(fldMaxStudents - fldNumStudents) AS 'Seats Available',
		fldSection AS Section, 
		fldType AS Type, 
		fldStart AS Start, 
		fldStop AS Stop, 
		fldDays AS Days, 
		fldBuilding AS Building, 
		fldRoom AS Room ";
		$query .= "FROM (tblSheetMusic
		INNER JOIN tblSections ON
		tblCourses.pmkCourseId = tblSections.fnkCourseId)
		INNER JOIN tblTeachers ON
		tblSections.fnkTeacherNetId=tblTeachers.pmkNetId ";
		$query .= " WHERE fldSheetTitle LIKE ?";
		$query .= " AND fldCourseNumber LIKE ?";
		$query .= " AND fldBuilding LIKE ?";
		$query .= " AND fldStart LIKE  ?";
		$query .= " AND fldLastName LIKE ?";
		//print "<p>sql: " . $query;

	//@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
	//
	// SECTION: 2e-f DISPLAY QUERY RESULTS
	//
	/* ##### Step three
	* Execute the query
	* */
	$results = $thisDatabase->select($query, $data);
	/* ##### Step four
	* prepare output and loop through array
	* */
	$numberRecords = count($results);
	print "<h2>Total Records: " . $numberRecords . "</h2>";
	print "<table>";
	$firstTime = true;
	/* since it is associative array display the field names */
	foreach ($results as $row) {
	if ($firstTime) {
	print "<thead><tr>";
	$keys = array_keys($row);
	foreach ($keys as $key) {
	if (!is_int($key)) {
	print "<th>" . $key . "</th>";
	}
	}
	print "</tr>";
	$firstTime = false;
	}
	/* display the data, the array is both associative and index so we are
	* skipping the index otherwise records are doubled up */
	print "<tr>";
	$first_time = true;

	foreach ($row as $field => $value) {
	if (!is_int($field)) {
	if ($first_time) {
	//print "<td>" . '<a href="https://aisweb1.uvm.edu/pls/owa_prod/bwckctlg.p_display_courses?term_in=201409&one_subj='.$classSubject.'&sel_crse_strt='.$number.'&sel_crse_end='.$number.'&sel_subj=&sel_levl=&sel_schd=&sel_coll=&sel_divs=&sel_dept=&sel_attr=">' . $row["Course"]. '</a>' . "</td>";
	//print "<td>" . '<a href="https://aisweb1.uvm.edu/pls/owa_prod/bwckctlg.p_display_courses?term_in=201409&one_subj='.$classSubject.'&sel_crse_strt='.$number.'&sel_crse_end='.$number.'&sel_subj=&sel_levl=&sel_schd=&sel_coll=&sel_divs=&sel_dept=&sel_attr=">' .$row["Course"]. '</a>' . "</td>";
	$first_time = false;
	}
	else {
	print "<td>" . $value . "</td>";
	}
	}
	}
	print "</tr>";
	}
	print "</table>";

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
//print "<h1>Your Request has ";
//if (!$mailed) {
//print "not ";
//}
//print "been processed</h1>";
//print "<p>A copy of this message has ";
//if (!$mailed) {
//print "not ";
//}
//print "been sent</p>";
//print "<p>To: " . $email . "</p>";
//print "<p>Mail Message:</p>";
//print $message;
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
<h1>Search for Sheet Music</h1>
</header>

<form action="<?php print $phpSelf; ?>"
method="post"
id="frmRegister">
<fieldset class="wrapper">
<legend class="customfont1">Spring Semester Searchable</legend>
<fieldset class="wrapperTwo">
<legend>Search to build your perfect spring semester</legend>
<fieldset class="contact">

<!--Subject-->
<label for="txtTitle" class="required">Title
<input type="text" id="txtTitle" name="txtTitle"
value="<?php print $title; ?>"
tabindex="100" maxlength="45" placeholder="Enter course abbreviation Ex. CS"
<?php if ($titleERROR) print 'class="mistake"'; ?>
onfocus="this.select()"
autofocus>
</label>
<!--Number-->
<label for="txtNumber" class="required">Number
<input type="text" id="txtNumber" name="txtNumber"
value="<?php print $number; ?>"
tabindex="200" maxlength="45" 
class="listbox" placeholder="Enter course number Ex. 142"
<?php if ($numberERROR) print 'class="mistake"'; ?>
onfocus="this.select()"
>
</label>
<!--Building-->
<label for="lstBuilding">Building
<select id="lstBuilding"
name="lstBuilding"
class="listbox"
tabindex="300" >
<option selected value=""> </option><option value="31 SPR">31 SPR</option><option value="481 MN">481 MN</option><option value="70S WL">70S WL</option><option value="AIKEN">AIKEN</option><option value="ALLEN">ALLEN</option><option value="ANGELL">ANGELL</option><option value="BLLNGS">BLLNGS</option><option value="COOK">COOK</option><option value="DELEHA">DELEHA</option><option value="DEWEY">DEWEY</option><option value="FAHC">FAHC</option><option value="FLEMIN">FLEMIN</option><option value="GIVN">GIVN</option><option value="GIVN B">GIVN B</option><option value="GIVN C">GIVN C</option><option value="GIVN E">GIVN E</option><option value="GUTRSN">GUTRSN</option><option value="HARRIS">HARRIS</option><option value="HILLS">HILLS</option><option value="HSRF">HSRF</option><option value="JEFFRD">JEFFRD</option><option value="JERCHO">JERCHO</option><option value="KALKIN">KALKIN</option><option value="L/L CM">L/L CM</option><option value="L/L-A">L/L-A</option><option value="L/L-B">L/L-B</option><option value="L/L-D">L/L-D</option><option value="LAFAYE">LAFAYE</option><option value="MANN">MANN</option><option value="MEDED">MEDED</option><option value="ML SCI">ML SCI</option><option value="MORRIL">MORRIL</option><option value="MRC">MRC</option><option value="MRC-CO">MRC-CO</option><option value="MUSIC">MUSIC</option><option value="OFFCMP">OFFCMP</option><option value="OLDMIL">OLDMIL</option><option value="OMANEX">OMANEX</option><option value="ONCMP">ONCMP</option><option value="ONLINE">ONLINE</option><option value="PATGYM">PATGYM</option><option value="PERKIN">PERKIN</option><option value="POMERO">POMERO</option><option value="ROWELL">ROWELL</option><option value="RT THR">RT THR</option><option value="SOUTHW">SOUTHW</option><option value="STAFFO">STAFFO</option><option value="TERRIL">TERRIL</option><option value="TRAVEL">TRAVEL</option><option value="UHTN">UHTN</option><option value="UHTN23">UHTN23</option><option value="UHTS">UHTS</option><option value="UHTS23">UHTS23</option><option value="VOTEY">VOTEY</option><option value="WATERM">WATERM</option><option value="WHEELR">WHEELR</option><option value="WILLMS">WILLMS</option>
</select>
</label>
<!--Start Time-->
<label for="txtStartTime" class="required">Military Start Time
<input type="text" id="txtStartTime" name="txtStartTime"
value="<?php print $startTime; ?>"
tabindex="400" maxlength="45" placeholder="Enter start time Ex. 08:00, 15:00"
<?php if ($startTimeERROR) print 'class="mistake"'; ?>
onfocus="this.select()"
>
</label>
<!--Professor-->
<label for="txtProfessor" class="required">Professor Last Name
<input type="text" id="txtProfessor" name="txtProfessor"
value="<?php print $professor; ?>"
tabindex="500" maxlength="45" placeholder="Enter professor last name"
<?php if ($professorERROR) print 'class="mistake"'; ?>
onfocus="this.select()"
>
</label>
</fieldset> <!-- ends contact -->
</fieldset> <!-- ends wrapper Two -->
<fieldset class="buttons">
<legend></legend>
<input type="submit" id="btnSubmit" name="btnSubmit" value="Find a Class NOW" tabindex="900" class="button">
</fieldset> <!-- ends buttons -->
</fieldset> <!-- Ends Wrapper -->
</form>
<?php
} // end body submit
?>
</article>
<?php include "footer.php"; ?>
</body>
</html>