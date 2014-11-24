<!DOCTYPE html>
<html lang="en">
<head>
<title>UVM Choral Library Database</title>
<meta charset="utf-8">
<meta name="author" content="Mandy Erdei">
<meta name="description" content="Search the UVM Choral Library or Login to Modify Records">
<meta name="viewport" content="width=device-width, initial-scale=1">
<!--[if lt IE 9]>
<script src="//html5shim.googlecode.com/sin/trunk/html5.js"></script>
<![endif]-->
<link rel="stylesheet" href="style.css" type="text/css" media="screen">
<?php
//Will put in display all errors since = 1
ini_set('display_errors',1);
error_reporting(E_ALL);
//end of will's stuff
$debug = false;
// %^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%
//
// Step one: generally code is in top.php
require_once('../bin/myDatabase.php');

$dbUserName = get_current_user() . '_reader';
$whichPass = "r"; //flag for which one to use.
$dbName = strtoupper(get_current_user()) . '_CHORAL_LIBRARY';

$thisDatabase = new myDatabase($dbUserName, $whichPass, $dbName);

// PATH SETUP
//
// $domain = "https://www.uvm.edu" or http://www.uvm.edu;
$domain = "http://";
if (isset($_SERVER['HTTPS'])) {
if ($_SERVER['HTTPS']) {
$domain = "https://";
}
}
$server = htmlentities($_SERVER['SERVER_NAME'], ENT_QUOTES, "UTF-8");
$domain .= $server;
$phpSelf = htmlentities($_SERVER['PHP_SELF'], ENT_QUOTES, "UTF-8");
$path_parts = pathinfo($phpSelf);
if ($debug) {
print "<p>Domain" . $domain;
print "<p>php Self" . $phpSelf;
print "<p>Path Parts<pre>";
print_r($path_parts);
print "</pre>";
}
// %^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%
//
// inlcude all libraries
//
require_once('lib/security.php');
//if($path_parts['filename'] == "newForm") {
//	}
	include "lib/mail-message.php";
	include "lib/validation-functions.php";
	
//if ($path_parts['filename'] == "form") {
//	include "lib/validation-functions.php";
//	include "lib/mail-message.php";
//	}
?>	
</head>
<!-- ################ body section ######################### -->
<?php
print '<body id="' . $path_parts['filename'] . '">';
include "nav-search.php";
?>