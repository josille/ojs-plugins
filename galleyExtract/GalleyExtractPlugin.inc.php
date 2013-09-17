<?php

/**
 * @file plugins/importexport/galleyExtract/GalleyExtractPlugin.inc.php
 *
 * Copyright (c) 2013 Rodrigo De la Garza
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class GalleyExtractPlugin
 * @ingroup plugins_importexport_galleyExtract
 *
 * @brief files export plugin
 */

// $Id$


import('classes.plugins.ImportExportPlugin');

class GalleyExtractPlugin extends ImportExportPlugin {
	/**
	 * Called as a plugin is registered to the registry
	 * @param $category String Name of category plugin was registered to
	 * @return boolean True if plugin initialized successfully; if false,
	 * 	the plugin will not be registered.
	 */
	function register($category, $path) {
		$success = parent::register($category, $path);
		$this->addLocaleData();
		return $success;
	}

	/**
	 * Get the name of this plugin. The name must be unique within
	 * its category.
	 * @return String name of plugin
	 */
	function getName() {
		return 'GalleyExtractPlugin';
	}

	function getDisplayName() {
		return __('plugins.importexport.galleyExtract.displayName');
	}

	function getDescription() {
		return __('plugins.importexport.galleyExtract.description');
	}

	function display(&$args) {
		$templateMgr =& TemplateManager::getManager();
		parent::display($args);

		$issueDao =& DAORegistry::getDAO('IssueDAO');

		$journal =& Request::getJournal();

		switch (array_shift($args)) {							
			case 'exportIssues':
				$issueIds = Request::getUserVar('issueId');
				
				if (!isset($issueIds)) $issueIds = array();
				$issues = array();
				foreach ($issueIds as $issueId) {
					$issue =& $issueDao->getIssueById($issueId);
					if (!$issue) Request::redirect();
					$issues[] =& $issue;
				}
				$this->exportIssues($journal, $issues);
				break;
			case 'exportIssue':
				$issueId = array_shift($args);
				$issue =& $issueDao->getIssueById($issueId);
				if (!$issue) Request::redirect();
				$issues = array($issue);
				$this->exportIssues($journal, $issues);
				break;
			default:
				$this->setBreadcrumbs(array(), true);
				AppLocale::requireComponents(array(LOCALE_COMPONENT_OJS_EDITOR));
				$issueDao =& DAORegistry::getDAO('IssueDAO');
				$issues =& $issueDao->getPublishedIssues($journal->getId(), Handler::getRangeInfo('issues'));

				$templateMgr->assign_by_ref('issues', $issues);
				
				$templateMgr->display($this->getTemplatePath() . 'index.tpl');
		}
	}

	/*
	 * Export the selected Issues into a zip file and 
	 * present download dialog box
	 */
	function exportIssues(&$journal, &$issues) {
	
		$this->import('GalleyExtractDom');
		$filesDir = Config::getVar('files', 'files_dir');
		$filename = $filesDir."/galleyExtract.zip";
		$zipName = 'galleyIssue_';
		
		$zip =& GalleyExtractDom::createZip($filename);
		
		$journal =& Request::getJournal();
		$sectionDao =& DAORegistry::getDAO('SectionDAO');
		$publishedArticleDao =& DAORegistry::getDAO('PublishedArticleDAO');
	
		$zipName.=$journal->getLocalizedInitials().'_';
		
		foreach ($issues as $issue) {
			$zipName.=$issue->getId().'_';
			foreach ($sectionDao->getSectionsForIssue($issue->getId()) as $section) {
				foreach ($publishedArticleDao->getPublishedArticlesBySectionId($section->getId(), $issue->getId()) as $article) {

					GalleyExtractDom::addArticleToZip($zip,$article,$journal,$issue);
					
					if ($article->getSuppFiles())
					{
						GalleyExtractDom::addArticleSuppToZip($zip,$article,$journal,$issue);
					}
				}
			}
		}
	
	GalleyExtractDom::closeZip($zip);

	
	header("Content-Type: application/zip");
	header("Cache-Control: private");
	header("Content-Disposition: attachment; filename=".$zipName.".zip");
	readfile($filename);
	exit;
	
		
	if(file_exists($filename)){
    	unlink($filename);
	}
	
	}

}

?>