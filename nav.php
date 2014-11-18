<!-- ###################### Main Navigation ########################## -->
<nav>
<ol>
<?php
/* This sets the current page to not be a link. Repeat this if block for
* each menu item */
	if ($path_parts['filename'] == "home") {
		print '<li class="activePage">Reset</li>';
	} else {
		print '<li><a href="register.php" id="reset">Reset</a></li>';
	}
/* example of repeating */
//if ($path_parts['filename'] == "form") {
//print '<li class="activePage">Join</li>';
//} else {
//print '<li><a href="form.php">Join</a></li>';
//}
?>
</ol>
</nav>