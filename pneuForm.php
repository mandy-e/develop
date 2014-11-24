<?php
include "top-search.php";

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
// SECTION: 1a-b. Get the database info
//
// Step one: generally code is in top.php
require_once('../bin/myDatabase.php');
	$querySource = 'SELECT DISTINCT fldSource ';
	$querySource .= ' FROM tblSource ';
	$querySource .= ' ORDER BY fldSource ';
	print "<p>querySource = " . $querySource;
	
	$queryPublisher = 'SELECT DISTINCT fldPublisher ';
	$queryPublisher .= ' FROM tblPublisher ';
	$queryPublisher .= ' ORDER BY fldPublisher ';
	print "<p>queryPublisher = " . $queryPublisher;
	

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

//INIIALIZE the variables before they're filled in from database
$title = "";
$composer = "";
$isource = "";
$ipublisher="";



$source = $thisDatabase->select($querySource);
$publisher = $thisDatabase->select($queryPublisher);
//print "<p><pre>";
//print_r ($publisher);
//die();
$voicing = "";
$primary_language = "";
$secondary_language = "";

//%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%
//
// SECTION: 1d form error flags
//
// Initialize Error Flags one for each form element we validate
// in the order they appear in section 1c.
$titleERROR = false;
$composerERROR = false;

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
    $composer = htmlentities($_POST["txtComposer"], ENT_QUOTES, "UTF-8");
    $dataRecord[] = $composer;
	$source = htmlentities($_POST["lstSource"], ENT_QUOTES, "UTF-8");
	$dataRecord[] = $source;
	$publisher = htmlentities($_POST["lstPublisher"], ENT_QUOTES, "UTF-8");
	$dataRecord[] = $publisher;
	$voicing = htmlentities($_POST["chkVoicing"], ENT_QUOTES, "UTF-8");
	$dataRecord[] = $voicing;
	$primary_language = htmlentities($_POST["chkPrimary_Language"], ENT_QUOTES, "UTF-8");
	$dataRecord[] = $primary_language;
	$secondary_language = htmlentities($_POST["chkSecondary_Language"], ENT_QUOTES, "UTF-8");
	$dataRecord[] = $secondary_language;
	$christmas = htmlentities($_POST["radChristmas"], ENT_QUOTES, "UTF-8");
	$dataRecord[] = $christmas;
	$acappella = htmlentities($_POST["radAcappella"], ENT_QUOTES, "UTF-8");
	$dataRecord[] = $acappella;
	$piano = htmlentities($_POST["radPiano"], ENT_QUOTES, "UTF-8");
	$dataRecord[] = $piano;
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

    if (!verifyAlphaNum($title)) {
        $errorMsg[] = "Title appears to have a non-alpha numeric character.";
        $titleERROR = true;
    }
    if (!verifyAlphaNum($composer)) {
        $errorMsg[] = "Composer appears to have a non-alpha numeric character.";
        $composerERROR = true;
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
		// Step Two: code can be in initialize variables or where step four needs to be
		$data = array($title, $composer, $source, $publisher, $voicing, $primary_language, $secondary_language, $christmas, $acappella, $piano);
		$query = 'SELECT pmkSheetMusicId AS "Sheet Music ID",  
		fldLocation AS Location,
		fldSheetTitle AS Title, 
		fldComposer AS Composer,
		fldSource AS "Text Source",
		fldArranger AS "Arranger",
		fldPublisher AS Publisher,
		fldPublicationDate AS "Publication Date", 
		fldNumberOfCopies AS "Number of Copies",  
		fldVoicing AS Voicing,
		fldPrimaryLanguage AS "Primary Language",
		fldSecondaryLanguage AS "Secondary Language",
		fldChristmasStatus AS "Is Christmas Music", 
		fldAcappellaStatus AS "Performed A Cappella",
		fldPianoStatus AS "Two-Hand Piano Accompanyment",
		fldNotes AS "Accompanyment Notes"';
		$query .= 'FROM tblSheetMusic INNER JOIN tblArrangerSheetMusic ON 
		tblSheetMusic.pmkSheetMusicId = tblArrangerSheetMusic.fnkSheetMusicId 
		INNER JOIN tblArranger ON 
		tblArrangerSheetMusic.fnkArrangerId=tblArranger.pmkArrangerID
		INNER JOIN tblComposerSheetMusic ON 
		tblSheetMusic.pmkSheetMusicId=tblComposerSheetMusic.fnkSheetMusicId 
		INNER JOIN tblComposer ON
		tblComposerSheetMusic.fnkComposerId = tblComposer.pmkComposerId
		INNER JOIN tblAccompanyment ON 
		tblSheetMusic.pmkSheetMusicId=tblAccompanyment.fnkSheetMusicId 
		INNER JOIN tblPublisherSheetMusic ON 
		tblSheetMusic.pmkSheetMusicId = tblPublisherSheetMusic.fnkSheetMusicId
		INNER JOIN tblPublisher 
		ON tblPublisherSheetMusic.fnkPublisherId=tblPublisher.pmkPublisherId
		INNER JOIN tblSource ON
		tblSheetMusic.fnkSourceId = tblSource.pmkSourceId';
		$query .= "WHERE fldSheetTitle LIKE ?";
		$query .= "AND fldComposer LIKE ?";
		$query .= "AND fldSource LIKE ?";
		$query .= "AND fldPublisher LIKE  ?";
		$query .= "AND fldVoicing LIKE ?";
		$query .= "AND fldPrimaryLanguage LIKE ?";
		$query .= "AND fldSecondaryLanguage LIKE ?";
		$query .= "AND fldChristmasStatus LIKE ?";
		$query .= "AND fldAcappellaStatus LIKE ?";
		$query .= "AND fldPianoStatus LIKE ?";
		$query .= 'ORDER BY fldComposer, fldSheetTitle';
		//print "<p>sql: " . $query;
		
	//@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
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

	//@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
        
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
        /*print "<h1>Your Request has ";

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
        print "<p>Mail Message:</p>";

        print $message;*/
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
                <legend>Register Today</legend>
                <p>You information will greatly help us with our research.</p>

                <fieldset class="wrapperTwo">
                    <legend>Please complete the following form</legend>

                    <!--<fieldset class="contact">
                        <legend>Contact Information</legend>-->
                        <label for="txtTitle" class="required">Sheet Music Title
                            <input type="text" id="txtTitle" name="txtTitle"
                                   value="<?php print $title; ?>"
                                   tabindex="100" maxlength="45" placeholder="Enter the sheet music title"
                                   <?php if ($titleERROR) print 'class="mistake"'; ?>
                                   onfocus="this.select()"
                                   autofocus>
                        </label>
                        <label for="txtComposer" class="required">Composer
                            <input type="text" id="txtComposer" name="txtComposer"
                                   value="<?php print $title; ?>"
                                   tabindex="110" maxlength="45" placeholder="Enter the composer's name"
                                   <?php if ($titleERROR) print 'class="mistake"'; ?>
                                   onfocus="this.select()"
                                   
                        </label> 
                        
			<fieldset class="checkbox">
				<legend>Which vocal range would you like to select? (check all that apply):</legend>
				<label for="chkBackpacking"><input type="checkbox" 
												   id="chkBackpacking" 
												   name="chkBackpacking" 
												   value="2">Backpacking
				</label>
				<label for="chkCross-Country-Skiing"><input type="checkbox" 
															id="chkCross-Country-Skiing" 
															name="chkCross-Country-Skiing" 
															value="3">Cross Country Skiing
				</label>
			</fieldset>
						
                        //start of publsiher listbox
						// or you can print it out
						print '</select></label>';	
                        print '<label for="lstPublisher">Publisher ';
						print '<select id="lstPublisher" ';
						print '        name="lstPublisher"';
						print '        tabindex="160" >';


						foreach ($publisher as $row) {

							print '<option ';
							if ($publisher == $row["fldPublisher"])
								print " selected ";

							print 'value="' . $row["fldPublisher"] . '">' . $row["fldPublisher"];

							print '</option>';
						}

						print '</select></label>';
						//end of publisher listbox output
						
						//START of voicing checkbox
						foreach ($voicing as $row) {

							$output[] = '<label for="chk' . str_replace(" ", "-", $row["fldVoicing"]) . '"><input type="checkbox" ';
							$output[] = ' id="chk' . str_replace(" ", "-", $row["fldVoicing"]) .  '" ';
							$output[] = ' name="chk' . str_replace(" ", "-", $row["fldVoicing"]) .  '" ';             
							$output[] = 'value="' . $row["pmkSheetMusicId"] . '">' . $row["fldVoicing"];
							$output[] = '</label>';
						}

						//$output[] = '</fieldset>';

						//print join("\n", $output);  // this prints each line as a separate  line in html
						//end of voicing checkbox
						
						//START of primary language checkbox
						foreach ($primary_language as $row) {

							$output[] = '<label for="chk' . str_replace(" ", "-", $row["fldPrimaryLanguage"]) . '"><input type="checkbox" ';
							$output[] = ' id="chk' . str_replace(" ", "-", $row["fldPrimaryLanguage"]) .  '" ';
							$output[] = ' name="chk' . str_replace(" ", "-", $row["fldPrimaryLanguage"]) .  '" ';             
							$output[] = 'value="' . $row["pmkSheetMusicId"] . '">' . $row["fldPrimaryLanguage"];
							$output[] = '</label>';
						}

						//$output[] = '</fieldset>';

						//print join("\n", $output);  // this prints each line as a separate  line in html
						//end of primary language checkbox
						
						//START of secondary language checkbox
						foreach ($secondary_language as $row) {

							$output[] = '<label for="chk' . str_replace(" ", "-", $row["fldSecondaryLanguage"]) . '"><input type="checkbox" ';
							$output[] = ' id="chk' . str_replace(" ", "-", $row["fldSecondaryLanguage"]) .  '" ';
							$output[] = ' name="chk' . str_replace(" ", "-", $row["fldSecondaryLanguage"]) .  '" ';             
							$output[] = 'value="' . $row["pmkSheetMusicId"] . '">' . $row["fldSecondaryLanguage"];
							$output[] = '</label>';
						}

						//$output[] = '</fieldset>';

						print join("\n", $output);  // this prints each line as a separate  line in html
						//end of secondary language checkbox
						
						//START of christmas radio
						$output = array();
						$output[] = '<fieldset class="radio">';
						$output[] = '<legend>Pick whether you want a Christmas piece:</legend>';

						foreach ($christmas as $row) {

							$output[] = '<label for="rad' . str_replace(" ", "-", $row["fldChristmasStatus"]) . '"><input type="radio" ';
							$output[] = ' id="rad'. str_replace(" ", "-", $row["fldChristmasStatus"]) .  '" ';
							$output[] = ' name="radChristmas" ';  
	
							if ($christmas == $row["pmkSheetMusicId"])
								$output[] =  " checked ";
	
							$output[] = 'value="' . $row["pmkSheetMusicId"] . '">' . $row["fldChristmasStatus"];
							$output[] = '</label>';
						}
						$output[] = '</fieldset>';
						$output[] = '</form>';

						print join("\n", $output);  // this prints each line as a separate  line in html
						//end of christmas

						//START of acappella radio
						$output = array();
						$output[] = '<fieldset class="radio">';
						$output[] = '<legend>Pick whether you want an a cappella piece:</legend>';

						foreach ($acappella as $row) {

							$output[] = '<label for="rad' . str_replace(" ", "-", $row["fldAcappellaStatus"]) . '"><input type="radio" ';
							$output[] = ' id="rad'. str_replace(" ", "-", $row["fldAcappellaStatus"]) .  '" ';
							$output[] = ' name="radAcappella" ';  
	
							if ($acappella == $row["pmkAccompanymentId"])
								$output[] =  " checked ";
	
							$output[] = 'value="' . $row["pmkAccompanymentId"] . '">' . $row["fldAcappellaStatus"];
							$output[] = '</label>';
						}
						$output[] = '</fieldset>';
						$output[] = '</form>';

						print join("\n", $output);  // this prints each line as a separate  line in html
						//end of acappella
						
						//START of piano radio
						$output = array();
						$output[] = '<fieldset class="radio">';
						$output[] = '<legend>Pick whether you want a two-handed piano accompanyment piece:</legend>';

						foreach ($piano as $row) {

							$output[] = '<label for="rad' . str_replace(" ", "-", $row["fldPianoStatus"]) . '"><input type="radio" ';
							$output[] = ' id="rad'. str_replace(" ", "-", $row["fldPianoStatus"]) .  '" ';
							$output[] = ' name="radPiano" ';  
	
							if ($piano == $row["pmkAccompanymentId"])
								$output[] =  " checked ";
	
							$output[] = 'value="' . $row["pmkAccompanymentId"] . '">' . $row["fldPianoStatus"];
							$output[] = '</label>';
						}
						$output[] = '</fieldset>';
						$output[] = '</form>';

						print join("\n", $output);  // this prints each line as a separate  line in html
						//end of christmas
						?>       
                
                    <!--</fieldset> ends contact -->
                    
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

<?php include "footer.php"; ?>

</body>
</html>

