<?php

/**
 * @file plugins/importexport/galleyExtract/GalleyExtractDom.inc.php
 *
 * Copyright (c) 2013 Rodrigo De la Garza
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class GalleyExtractDom
 * @ingroup plugins_importexport_galleyExtract
 *
 * @brief files export plugin
 */

// $Id$


class GalleyExtractDom {

	/**
	 * Create new zip file
	 * 
	 */
	function &createZip($filename)
	{	
		if(file_exists($filename))
		{
			unlink($filename);
		} 
		
		$zip = new ZipArchive();
		//var_dump($filename);exit;
		if ($zip->open($filename, ZipArchive::CREATE)!==TRUE) {
		    exit("cannot open <$filename>\n");
		}
		return $zip;
	}
	
	/**
	 * 
	 * Close the created zip
	 */
	function &closeZip(&$zip)
	{
		$zip->close();
	}
	
	/**
	 * 
	 * Add articles galley to zip file
	 */
	function &addArticleSuppToZip(&$zip,$article,$journal,$issue)
	{	
		/*
		 * create issue folder name
		 */
		$issuefolderName = 'issueID_'.$issue->getId();
		$issueNewFolderName = '';
		
		if($issue->getVolume() > 0 && $issue->getYear() > 0)
		{
			$issueNewFolderName = 'Vol_'.$issue->getVolume().'('.$issue->getYear().')_';
		}
		if($issue->getNumber() > 0)
		{
			$issueNewFolderName .='Issue_'.$issue->getNumber();
		}
		
		if(strlen(trim($issueNewFolderName))>0)
		{
			$issuefolderName = $issueNewFolderName;
		}
		
		$article_supfiles = $article->getSuppFiles();
		
		foreach ($article_supfiles as $key => $sup) {
					
			$suppId = $sup->getId();
			//$galleyId =$galley->getPublicGalleyId();
					
			$suppFileDao =& DAORegistry::getDAO('SuppFileDAO');
			if ($journal->getSetting('enablePublicSuppFileId')) {
				$suppFile =& $suppFileDao->getSuppFileByBestSuppFileId($article->getId(), $suppId);
			} else {
				$suppFile =& $suppFileDao->getSuppFile((int) $suppId, $article->getId());
			}
			
			if ($article && $suppFile) {
				import('classes.file.ArticleFileManager');
				$articleFileManager = new ArticleFileManager($article->getId());
			
				$suppFileName = $article->getBestArticleId($journal).'-SUP'.($suppFile->getSequence()+1).'.'.$suppFile->getFileExtension();
			
				$articleFile =& $articleFileManager->getFile($suppFile->getFileId(),null,false,$suppFileName);
			
				if (isset($articleFile)) {
					$fileType = $articleFile->getFileType();
					$filePath = $articleFile->getFilePath();
					if(file_exists($filePath))
					{
						$filename = $suppFile->getFileName();
						if($article->getStoredDOI())
						{
							$ext = pathinfo($filename, PATHINFO_EXTENSION);
							$doi = $article->getStoredDOI();
							$filename = preg_replace('/(.*)\//i', '', $doi).'_SP'.($key++).'.'.$ext;
						}
						//var_dump($filePath, $issuefolderName.'/'.$filename);
						$zip->addFile($filePath, $issuefolderName.'/'.$filename);
					}
				}
			}
		}
	}
	
	
	/**
	 * 
	 * Add supplementary files to zip
	 */
	function &addArticleToZip(&$zip,$article,$journal,$issue)
	{
		$artGalleys = $article->getGalleys();
		
		/*
		 * create issue folder name
		 */
		$issuefolderName = 'issueID_'.$issue->getId();
		$issueNewFolderName = '';
		
		if($issue->getVolume() > 0 && $issue->getYear() > 0)
		{
			$issueNewFolderName = 'Vol_'.$issue->getVolume().'('.$issue->getYear().')_';
		}
		if($issue->getNumber() > 0)
		{
			$issueNewFolderName .='Issue_'.$issue->getNumber();
		}
		
		if(strlen(trim($issueNewFolderName))>0)
		{
			$issuefolderName = $issueNewFolderName;
		}
		
		
		foreach ($artGalleys as $galley) {
					
			$galleyId =$galley->getPublicGalleyId();
				
			$galleyDao =& DAORegistry::getDAO('ArticleGalleyDAO');
			if ($journal->getSetting('enablePublicGalleyId')) {
				$galley =& $galleyDao->getGalleyByBestGalleyId($galleyId, $article->getId());
			} else {
				$galley =& $galleyDao->getGalley($galleyId, $article->getId());
			}
			if ($galley) $galleyDao->incrementViews($galley->getId());

			if ($article && $galley) {
				$articleFileDao =& DAORegistry::getDAO('ArticleFileDAO');
				$articleFile =& $articleFileDao->getArticleFile($galley->getFileId(), null, $article->getId());
				
				if (isset($articleFile)) {
					$fileType = $articleFile->getFileType();
					$filePath = $articleFile->getFilePath();
					if(file_exists($filePath))
					{
						$filename = $articleFile->getFileName();
						if($article->getStoredDOI())
						{							
							$ext = pathinfo($filename, PATHINFO_EXTENSION);
							$doi = $article->getStoredDOI();
							$filename = preg_replace('/(.*)\//i', '', $doi).'.'.$ext;;
						}
						//var_dump($filePath, $issuefolderName.'/'.$filename);
						$zip->addFile($filePath, $issuefolderName.'/'.$filename);
					}
				}
			}
		}
	}
}

?>
