<?php

// import('lib.pkp.classes.core.PKPApplication');
require_once('../../../classes/core/Application.inc.php');

//variables for Database connection
require (__DIR__ . '/dbconnect.php');
if (!isset($dbhost, $dbuser, $dbpass, $dbname)) {
    error_log("Error: Database connection variables are not set.");
}

// Create connection
$conn = new mysqli($dbhost, $dbuser, $dbpass, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

//Check if Full Issue PDF exists
$fileDir = __DIR__ . "/../../../../../../OJS-files/";

//SQL for Current Issue ID
$IssueIdsql = "SELECT `variable_value` FROM `automatex` WHERE `variable_name` = 'Issue_no'";
$result = $conn->query($IssueIdsql);

// Check if query was successful and fetch the result
if ($result && $result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $issueId = $row['variable_value'];
} else {
    error_log("Error: No result found or query failed.");
    $issueId = null; // or set a default value if needed
}

$sections = Application::get()->getSectionDao()->getByIssueId($issueId);
$issueSubmissionsInSection = [];
foreach ($sections as $section) {
    $issueSubmissionsInSection[$section->getId()] = [
        'title' => $section->getLocalizedTitle(),
        'articles' => [],
    ];
}

foreach ($issueSubmissions as $submission) {
    if (!$sectionId = $submission->getCurrentPublication()->getData('sectionId')) {
        continue;
    }
    $issueSubmissionsInSection[$sectionId]['articles'][] = $submission;
}


$templateMgr->assign('issue', $issue);
$templateMgr->assign('publishedSubmissions', $issueSubmissionsInSection);


//Cover/email template with embedded images
//add logo
$message = $templateMgr->fetch('frontend/objects/issue_logo.tpl');
//add Table of Contents
$message .= $templateMgr->fetch('frontend/objects/issue_toc_pdf.tpl');

//set Advertisers sql and add to message
$ADV = "";
load_file_content($ADV, __DIR__ . "/sql/ads.sql");
$message .= print_ads_embedded($ADV, $dbhost, $dbuser, $dbpass, $dbname);
//set SustainingSubs SQL and add to message
$ASL = "";
load_file_content($ASL, __DIR__ . "/sql/subs.sql");
$message .= print_subs_embedded($ASL, $dbhost, $dbuser, $dbpass, $dbname);

//add footer
$message .= $templateMgr->fetch('frontend/objects/issue_footer_pdf.tpl');

//create Cover pdf
$dompdf = new Dompdf();
$dompdf->loadHtml($message);

$dompdf->render();
file_put_contents($fileDir . "FullIssue[$issueId]cover.pdf", $dompdf->output());
error_log("Cover created.");

//get lists of articles
$listA = "";
load_file_content($listA, __DIR__ . "/sql/list_articles.sql");
$articlespaths = filepath($listA, $dbhost, $dbuser, $dbpass, $dbname);
$articlesArray = explode(";", $articlespaths);

$listB = "";
load_file_content($listB, __DIR__ . "/sql/list_other.sql");
$otherpaths = filepath($listB, $dbhost, $dbuser, $dbpass, $dbname);
$otherArray = explode(";", $otherpaths);

$outputName = $fileDir . "FullIssue[$issueId].pdf";
$cmd = "gswin64 -q -dNOPAUSE -dBATCH -sDEVICE=pdfwrite -sOutputFile=$outputName ";
//add cover
$cmd .= $fileDir . "FullIssue[$issueId]cover.pdf" . " ";
//Add each pdf file to the end of the command
foreach ($articlesArray as $file) {
    if ($file != "") {
        $file = $fileDir . $file;
        if (file_exists($file)) {
            error_log("Adding " . $file);
            $cmd .= $file . " ";
        } else if (!file_exists($file)) {
            error_log("file does not exist: " . $file);
        }
    }
}
foreach ($otherArray as $file) {
    if ($file != "") {
        $file = $fileDir . $file;
        if (file_exists($file)) {
            error_log("Adding " . $file);
            $cmd .= $file . " ";
        } else if (!file_exists($file)) {
            error_log("file does not exist: " . $file);
        }
    }
}

//Merge all to Full Issue PDF
$result = shell_exec($cmd);

//Close DB connection
$conn->close();

error_log("Complete");





