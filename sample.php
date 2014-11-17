<!DOCTYPE html>
<html lang="en">
<head>
<title>CS 148 Tables</title>
<meta charset="utf-8">
<meta name="author" content="Mandy Erdei">
<meta name="description" content="Index page for assignment two select.">
<!--[if lt IE 9]>
<script src="//html5shim.googlecode.com/sin/trunk/html5.js"></script>
<![endif]-->
</head>
<?php
$phpSelf = htmlentities($_SERVER['PHP_SELF'], ENT_QUOTES, "UTF-8");
$path_parts = pathinfo($phpSelf);
print '<body id="' . $path_parts['filename'] . '">';
?>
<p>Assignment 2.0</p>
<p>q01. <a href="q01.php">SQL:</a> SELECT pmkNetID FROM tblTeachers;</p>
<p>q02. <a href="q02.php">SQL:</a> SELECT fldDepartment FROM tblCourses WHERE fldCourseName="Elementary";</p>
<p>q03. <a href="q03.php">SQL:</a> SELECT fnkCourseID, fldCRN, fnkTeacherID, fldMaxStudents, fldNumStudents, fldSection, fldType, fldStart, fldStop, fldDays, fldBuilding, fldRoom FROM tblSections WHERE fldStart="15:00:00" and fldBuilding="KALKIN";</p>
<p>q04. <a href="q04.php">SQL:</a> SELECT pmkCourseID, fldCourseNumber, fldCourseName, fldDepartment, fldCredits FROM tblCourses WHERE fldCourseNumber="148" and fldDepartment="CS";</p>
<p>q05. <a href="q05.php">SQL:</a> SELECT fldFirstName FROM tblTeachers WHERE pmkNetId like'r%' and pmkNetId like '%o';</p>
<p>q06. <a href="q06.php">SQL:</a> SELECT fldCourseName FROM tblCourses WHERE fldCourseName LIKE '%data%' AND fldDepartment NOT LIKE 'CS';</p>
<p>q07. <a href="q07.php">SQL:</a> SELECT COUNT(DISTINCT fldDepartment) FROM tblCourses;</p>
<p>q08. <a href="q08.php">SQL:</a> SELECT DISTINCT fldBuilding, COUNT(fldSection) FROM tblSections GROUP BY fldBuilding;</p>
<p>q09. <a href="q09.php">SQL:</a> SELECT fldBuilding, COUNT(fldNumStudents) FROM tblSections WHERE fldDays LIKE "%W%" GROUP BY fldBuilding ORDER BY fldNumStudents DESC;
<p>q10. <a href="q10.php">SQL:</a> SELECT fnkCourseId AS courses FROM tblSections GROUP BY fnkCourseId HAVING COUNT(fldCRN) >= 50;
<p>q11. <a href="q11.php">SQL:</a> SELECT COUNT(fldNumStudents) FROM tblSections WHERE fldNumStudents > fldMaxStudents;</p>
<p>q12. <a href="q12.php">SQL:</a> SELECT DISTINCT fldCourseName, fldCredits FROM tblCourses WHERE fldDepartment="CS" and fldCourseName like "Intermediate Programming" or fldCourseName like "C Programming" or fldCourseName like "Artificial Intelligence" or fldCourseName like "Intermediate Programming-Java" or fldCourseName like "Engineering Capstone Design I" GROUP BY fldCredits ORDER BY fldCredits ASC;
</body>
</html>