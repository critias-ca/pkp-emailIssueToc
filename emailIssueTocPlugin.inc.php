<?php

/**
 * @filesource plugins/generic/emailIssueToc/emailIssueToc.inc.php
 * 
 * @class emailIssueTocPlugin
 * @ingroup plugins_generic_emailIssueToc
 * 
 * @brief EmailIssueToc plugin class
 * @author suk117
 */

import('lib.pkp.classes.plugins.GenericPlugin');

require_once ('Ads_Subs.php');
require 'vendor/autoload.php';
use Dompdf\Dompdf;

class emailIssueTocPlugin extends GenericPlugin
{
	/**
	 * @copydoc LazyLoadPlugin::register()
	 */
	function register($category, $path, $mainContextId = NULL)
	{
		$success = parent::register($category, $path, $mainContextId);
		if (!Config::getVar('general', 'installed') || defined('RUNNING_UPGRADE'))
			return true;
		if ($success && $this->getEnabled()) {
			HookRegistry::register('NotificationManager::getNotificationMessage', array(&$this, 'sendToc'));
		}
		return $success;
	}

	/**
	 * @copydoc PKPPlugin::getDisplayName()
	 */
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

	/**
	 * Add the Table of Content in the email message
	 * @param string $hookname notificationmanager::getnotificationmessage
	 * @param array $args
	 * @return boolean False to continue execution
	 */
	function sendToc($hookname, $args)
	{
		$application = Application::get();
		$request = $application->getRequest();
		$notification = $args[0];
		$message =& $args[1];
		$journal = $request->getJournal();
		if ($notification->getType() == NOTIFICATION_TYPE_PUBLISHED_ISSUE) {
			if ($notification->getAssocType() == ASSOC_TYPE_ISSUE) {
				$issueId = $notification->getAssocId();
				$issueDao = DAORegistry::getDAO('IssueDAO');
				$issue = $issueDao->getById($issueId);
				if ($issue) {
					$dispatcher = $application->getDispatcher();
					$originalRouter = $request->getRouter();
					$originalDispatcher = $request->getDispatcher();
					// The TemplateManager needs to see this Request based on a PageRouter, not the current ComponentRouter
					import('classes.core.PageRouter');
					$pageRouter = new PageRouter();
					$pageRouter->setApplication($application);
					$pageRouter->setDispatcher($dispatcher);
					$request->setRouter($pageRouter);
					$request->setDispatcher($dispatcher);
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
					$dbhost = Config::getVar('database', 'host');
					$dbuser = Config::getVar('database', 'username');
					$dbpass = Config::getVar('database', 'password');
					$dbname = Config::getVar('database', 'name');
					if (!isset($dbhost, $dbuser, $dbpass, $dbname)) {
						error_log("Error: Database connection variables are not set.");
					}

					//Check if Full Issue PDF exists
					$fileDir = __DIR__ . "/../../../../../../OJS-files/";
					if (!file_exists($fileDir . "FullIssue[$issueId].pdf")) {
						error_log("No Full Issue pdf, Creating...");

						//Cover/email template with embedded images
						//add logo
						$message = $templateMgr->fetch('/../plugins/generic/emailIssueToc/objects/issue_logo.tpl');
						//add Table of Contents
						$message .= $templateMgr->fetch('/../plugins/generic/emailIssueToc/objects/issue_toc_pdf.tpl');

						//set Advertisers sql and add to message
						$ADV = "";
						load_file_content($ADV, __DIR__ . "/sql/ads.sql");
						$message .= print_ads_embedded($ADV, $dbhost, $dbuser, $dbpass, $dbname);
						//set SustainingSubs SQL and add to message
						$ASL = "";
						load_file_content($ASL, __DIR__ . "/sql/subs.sql");
						$message .= print_subs_embedded($ASL, $dbhost, $dbuser, $dbpass, $dbname);

						//add footer
						$message .= $templateMgr->fetch('/../plugins/generic/emailIssueToc/objects/issue_footer_pdf.tpl');

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
							if ($file != ""){
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
							if ($file != ""){
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

					//Reassign variable message for email template with linked images (easier on SMTP)

					//add logo
					$message = $templateMgr->fetch('/../plugins/generic/emailIssueToc/objects/issue_logo.tpl');
					//add Table of Contents
					$message .= $templateMgr->fetch('/../plugins/generic/emailIssueToc/objects/issue_toc_ads.tpl');

					//set Advertisers sql and add to message
					$ADV = "";
					load_file_content($ADV, __DIR__ . "/sql/ads.sql");
					$message .= print_ads_linked($ADV, $dbhost, $dbuser, $dbpass, $dbname);
					//set SustainingSubs SQL and add to message
					$ASL = "";
					load_file_content($ASL, __DIR__ . "/sql/subs.sql");
					$message .= print_subs_linked($ASL, $dbhost, $dbuser, $dbpass, $dbname);

					//add footer
					$message .= $templateMgr->fetch('/../plugins/generic/emailIssueToc/objects/issue_footer.tpl');

					$request->setRouter($originalRouter);
					$request->setDispatcher($originalDispatcher);
				}
			}
		}
		return false;
	}
}