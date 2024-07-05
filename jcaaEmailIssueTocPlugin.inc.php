<?php

/**
 * @filesource plugins/generic/jcaaEmailIssueToc/jcaaEmailIssueToc.inc.php
 *
 * @class jcaaEmailIssueTocPlugin
 * @ingroup plugins_generic_emailIssueToc
 *
 * @brief EmailIssueToc plugin class
 * @author suk117
 */

import('lib.pkp.classes.plugins.GenericPlugin');

require_once('Ads_Subs.php');

class jcaaEmailIssueTocPlugin extends GenericPlugin
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
			HookRegistry::register('TemplateResource::getFilename', [$this, '_overridePluginTemplates']);
			HookRegistry::register('NotificationManager::getNotificationMessage', array(&$this, 'sendToc'));
		}
		return $success;
	}

	/**
	 * @copydoc PKPPlugin::getDisplayName()
	 */
	function getDisplayName()
	{
		return __('plugins.generic.jcaaEmailIssueToc.displayName');
	}

	/**
	 * @copydoc PKPPlugin::getDescription()
	 */
	function getDescription()
	{
		return __('plugins.generic.jcaaEmailIssueToc.description');
	}

	/**
	 * Add the Table of Content in the email message
	 * @param string $hookname notificationmanager::getnotificationmessage
	 * @param array $args
	 * @return boolean False to continue execution
	 */
	function sendToc($hookname, $args)
	{
		static $cachedMessage = null;
		$application = Application::get();
		$request = $application->getRequest();
		$notification = $args[0];
		$message =& $args[1];

		$journal = $request->getJournal();
		if ($notification->getType() == NOTIFICATION_TYPE_PUBLISHED_ISSUE) {
			if ($notification->getAssocType() == ASSOC_TYPE_ISSUE) {

				if ($cachedMessage) {
					$message = $cachedMessage;
					return false;
				}

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
					//add logo
					$message = $templateMgr->fetch($this->getTemplateResource( 'issue_logo.tpl'));
					//add Table of Contents
					$message .= $templateMgr->fetch($this->getTemplateResource('frontend/objects/issue_toc.tpl'));

					//set Advertisers sql and add to message
					$message .= print_ads();
					//set SustainingSubs SQL and add to message
					$message .= print_subs();
					//add footer
					$message .= $templateMgr->fetch($this->getTemplateResource('issue_footer.tpl'));

					$request->setRouter($originalRouter);
					$request->setDispatcher($originalDispatcher);
					$cachedMessage = $message;
				}
			}
		}
		return false;
	}
}
