<?php

/**
 * @file plugins/importexport/crossref/JGateExportDom.inc.php
 *
 * Copyright (c) 2013 Rodrigo De la Garza
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class JGateExportDom
 * @ingroup plugins_importexport_jgate
 *
 * @brief JGate XML export plugin DOM functions
 */

// $Id$


import('lib.pkp.classes.xml.XMLCustomWriter');

//define('JGATE_XMLNS_XSI' , 'http://www.w3.org/2001/XMLSchema-instance');
//define('JGATE_VERSION' , '4.3.0');

class JGateExportDom {

	/**
	 * Build article XML using DOM elements
	 * @return XMLNode
	 */
	function &generateJGateDom() {
		// create the output XML document in DOM with a root node
		$doc =& XMLCustomWriter::createDocument();
		return $doc;
	}

	/**
	 * Generate DOI batch DOM tree.
	 * @param $doc object
	 * @return XMLNode
	 */
	function &generateDoiBatchDom(&$doc) {

		// Generate the root node for the file first and set its attributes
		$root =& XMLCustomWriter::createElement($doc, 'articles');

		/* Root doi_batch tag attributes
		 * Change to these attributes must be accompanied by a review of entire output
		 */
		//XMLCustomWriter::setAttribute($root, 'xmlns:xsi', JGATE_XMLNS_XSI);
		
		//XMLCustomWriter::setAttribute($root, 'version', JGATE_VERSION);
		
		XMLCustomWriter::appendChild($doc, $root);

		return $root;
	}


	/**
	 * Generate depositor node
	 * @param $doc XMLNode
	 * @param $name string
	 * @param $email string
	 * @return XMLNode
	 */
	function &generateDepositorDom(&$doc, $name, $email) {
		$depositor =& XMLCustomWriter::createElement($doc, 'depositor');
		XMLCustomWriter::createChildWithText($doc, $depositor, 'name', $name);
		XMLCustomWriter::createChildWithText($doc, $depositor, 'email_address', $email);

		return $depositor;
	}


	/**
	 * Generate the article node (the heart of the file).
	 * @param $doc XMLNode
	 * @param $journal Journal
	 * @param $issue Issue
	 * @param $section Section
	 * @param $article Article
	 * @return XMLNode
	 */
	function &generateArticleDom(&$doc, &$journal, &$issue, &$section, &$article) {
		// Create the base node
		$articleNode =& XMLCustomWriter::createElement($doc, 'article');
		//XMLCustomWriter::setAttribute($articleNode, 'publication_type', 'full_text');

		/* Titles */
		$titlesNode =& XMLCustomWriter::createElement($doc, 'ArtTitle');
		$titlesTextNode =&XMLCustomWriter::createTextNode($doc, $article->getLocalizedTitle());
		XMLCustomWriter::appendChild($titlesNode, $titlesTextNode);
		XMLCustomWriter::appendChild($articleNode, $titlesNode);

		/* The registrant is assumed to be the Publishing institution */
		$publisherInstitution = $journal->getSetting('publisherInstitution');
		XMLCustomWriter::createChildWithText($doc, $articleNode, 'PubName', $publisherInstitution);

		/* Full Title of Journal */
		XMLCustomWriter::createChildWithText($doc, $articleNode, 'JournalName', $journal->getLocalizedTitle());

		/* Both ISSNs are permitted for CrossRef, so sending whichever one (or both) */
		if ( $ISSN = $journal->getSetting('onlineIssn') ) {
			$onlineISSN =& XMLCustomWriter::createChildWithText($doc, $articleNode, 'EISSN', $ISSN);
		}

		/* Both ISSNs are permitted for CrossRef so sending whichever one (or both) */
		if ( $ISSN = $journal->getSetting('printIssn') ) {
			$printISSN =& XMLCustomWriter::createChildWithText($doc, $articleNode, 'PISSN', $ISSN);
		}

		/* Generate publication date */
		if ($issue->getDatePublished()) {
			$publicationDateNode =& JGateExportDom::generatePublisherDateDom($doc, $issue->getDatePublished());
			XMLCustomWriter::appendChild($articleNode, $publicationDateNode);
		}

		/* Journal volume */
		XMLCustomWriter::createChildWithText($doc, $articleNode, 'volume', $issue->getVolume());

		/* Journal Issue */
		XMLCustomWriter::createChildWithText($doc, $articleNode, 'issue', $issue->getNumber());

		/* Supplementary files */
		$textSUP = 'No';
		if ($article->getSuppFiles())
		{
			$textSUP = 'Yes';
		}

		XMLCustomWriter::createChildWithText($doc, $articleNode, 'SupIssue', $textSUP);

		/* publication date */
		$parsedPubdate = strtotime($issue->getDatePublished());
		XMLCustomWriter::createChildWithText($doc, $articleNode, 'Pubdate', date('d-m-Y', $parsedPubdate));

		/* Authors */
		foreach ($article->getAuthors() as $author) {
			
			XMLCustomWriter::createChildWithText($doc, $articleNode, 'AuthorName', $author->getFullName());
			/* Affiliation */
			$affiliations = $author->getAffiliation(null);
				
			if (is_array($affiliations)) foreach ($affiliations as $locale => $affiliation) {

				$curr_affis = explode("\\", $affiliation);
				$curr_affis = array_filter($curr_affis);
				$curr_affis = array_values($curr_affis);

				foreach ($curr_affis as $curr_aff) {
					XMLCustomWriter::createChildWithText($doc, $articleNode, 'AuthorAffiliation', trim($curr_aff));
				}
			}

			/* email  */
			XMLCustomWriter::createChildWithText($doc, $articleNode, 'AuthorEmails', $author->getEmail());
		}


		/* publisher_item is the article pages */
		if ($article->getPages() != '') {
			XMLCustomWriter::createChildWithText($doc, $articleNode, 'PageNo', $article->getPages());
		}

		/* Generate DOI */
		XMLCustomWriter::createChildWithText($doc, $articleNode, 'DOI', $article->getDOI());


		/* Article Keywords TODO*/
		$subjects = array_map('trim', explode(';', $article->getLocalizedSubject()));

		foreach ($subjects as $keyword) {
			XMLCustomWriter::createChildWithText($doc, $articleNode, 'Keywords', $keyword, false);
		}
		
		/* --- Abstract --- */
		if ($article->getLocalizedAbstract()) {
			$abstractNode = XMLCustomWriter::createChildWithText($doc, $articleNode, 'Abstract', strip_tags($article->getLocalizedAbstract()), false);
		}


		/* URL */
		$URLsNode =& XMLCustomWriter::createElement($doc, 'URLs');
		XMLCustomWriter::createChildWithText($doc, $URLsNode, 'abstract', Request::url(null, 'article', 'view', $article->getId()));

		$fulltextNode =& XMLCustomWriter::createElement($doc, 'Fulltext');
		XMLCustomWriter::createChildWithText($doc, $fulltextNode, 'pdf', Request::url(null, 'article', 'download', array($article->getId(),'pdf')));

		XMLCustomWriter::appendChild($URLsNode, $fulltextNode);
		XMLCustomWriter::appendChild($articleNode, $URLsNode);


		return $articleNode;
	}


	/**
	 * Generate publisher date - order matters
	 * @param $doc XMLNode
	 * @param $pubdate string
	 * @return XMLNode
	 */
	function &generatePublisherDateDom(&$doc, $pubdate) {
		$publicationDateNode =& XMLCustomWriter::createElement($doc, 'pub-date');
		XMLCustomWriter::setAttribute($publicationDateNode, 'pub-type', 'epub');

		$parsedPubdate = strtotime($pubdate);
		XMLCustomWriter::createChildWithText($doc, $publicationDateNode, 'day', date('d', $parsedPubdate), false);
		XMLCustomWriter::createChildWithText($doc, $publicationDateNode, 'month', date('m', $parsedPubdate), false);
		XMLCustomWriter::createChildWithText($doc, $publicationDateNode, 'year', date('Y', $parsedPubdate));

		return $publicationDateNode;
	}
}

?>
