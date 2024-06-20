<?php

set_include_path(__DIR__ . '/../../../');

require_once(__DIR__ . '/../../../lib/pkp/classes/plugins/Plugin.inc.php');

//require_once(__DIR__ . '/../../../lib/pkp/classes/plugins/LazyLoadPlugin.inc.php');

//require_once(__DIR__ . '/../../../lib/pkp/classes/plugins/GenericPlugin.inc.php');

class MyPluginHelper extends Plugin {
    public function __construct() {
    }
    public function getName() {
    }
    public function getDisplayName() {

    }
    public function getDescription() {
    }
}

// Instantiate the helper class to handle the import
$helper = new MyPluginHelper();
$helper->import('lib.pkp.classes.plugins.GenericPlugin');

class OJS2pdfPlugin extends GenericPlugin
{

    function getDisplayName()
	{
		return __('plugins.generic.emailIssueToc.displayName');
	}

	/**
	 * @copydoc PKPPlugin::getDescription()
	 */
	function getDescription()
	{
		return __('plugins.generic.emailIssueToc.description');
	}

    public function ojs2pdf()
    {
        $application = Application::get();
        $request = $application->getRequest();
        $journal = $request->getJournal();
        $issueId = 303; //hardcoded for now
        $issueDao = DAORegistry::getDAO('IssueDAO');
        $issue = $issueDao->getById($issueId);
        if ($issue) {
            //$dispatcher = $application->getDispatcher();
            //$originalRouter = $request->getRouter();
            //$originalDispatcher = $request->getDispatcher();
            // The TemplateManager needs to see this Request based on a PageRouter, not the current ComponentRouter
            //import('classes.core.PageRouter');
            //$pageRouter = new PageRouter();
            //$pageRouter->setApplication($application);
            //$pageRouter->setDispatcher($dispatcher);
            //$request->setRouter($pageRouter);
            //$request->setDispatcher($dispatcher);
            $templateMgr = TemplateManager::getManager($request);
            $sections = Application::get()->getSectionDao()->getByIssueId($issueId);
            $issueSubmissionsInSection = [];
            foreach ($sections as $section) {
                $issueSubmissionsInSection[$section->getId()] = [
                    'title' => $section->getLocalizedTitle(),
                    'articles' => [],
                ];
            }
            import('classes.submission.Submission');
            $allowedStatuses = [STATUS_PUBLISHED];
            if (!$issue->getPublished()) {
                $allowedStatuses[] = STATUS_SCHEDULED;
            }
            $issueSubmissions = iterator_to_array(Services::get('submission')->getMany([
                'contextId' => $journal->getId(),
                'issueIds' => [$issueId],
                'status' => $allowedStatuses,
                'orderBy' => 'seq',
                'orderDirection' => 'ASC',
            ]));
            foreach ($issueSubmissions as $submission) {
                if (!$sectionId = $submission->getCurrentPublication()->getData('sectionId')) {
                    continue;
                }
                $issueSubmissionsInSection[$sectionId]['articles'][] = $submission;
            }
            $templateMgr->assign('issue', $issue);
            $templateMgr->assign('publishedSubmissions', $issueSubmissionsInSection);

            //variables for Database connection
            require (__DIR__ . '/dbconnect.php');
            if (!isset($dbhost, $dbuser, $dbpass, $dbname)) {
                error_log("Error: Database connection variables are not set.");
            }
            //Check if Full Issue PDF exists
            $fileDir = __DIR__ . "/../../../../../../OJS-files/";


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
        }

        error_log("Complete");

    }
}


