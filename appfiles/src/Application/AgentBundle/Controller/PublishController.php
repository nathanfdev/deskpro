<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @subpackage AgentBundle
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */

namespace Application\AgentBundle\Controller;

use Application\DeskPRO\App;
use Application\DeskPRO\Entity\GlossaryWord;

use Orb\Util\Strings;
use Orb\Util\Arrays;
use Orb\Util\Util;

class PublishController extends AbstractController
{
	public function getSectionDataAction()
	{
		$data = array();

		#------------------------------
		# KB
		#------------------------------

		$kb_counts = array();
		$kb_counts['awaiting_validation_articles'] = App::getDb()->fetchColumn("SELECT COUNT(*) FROM articles WHERE hidden_status = ?", array('validating'));
		$kb_counts['awaiting_validation_edits']    = App::getDb()->fetchColumn("SELECT COUNT(*) FROM article_validating_edits");
		$kb_counts['awaiting_validation']          = $kb_counts['awaiting_validation_articles'] + $kb_counts['awaiting_validation_edits'];
		$kb_counts['drafts']                       = App::getDb()->fetchColumn("SELECT COUNT(*) FROM articles WHERE hidden_status = ?", array('draft'));
		$kb_counts['pending']                      = App::getDb()->fetchColumn("SELECT COUNT(*) FROM article_pending_create");

		$kb_cats = App::getEntityRepository('DeskPRO:ArticleCategory')->getUserCategoryHelper()->getFlatHierarchy();

		#------------------------------
		# News
		#------------------------------

		$news_cats = App::getEntityRepository('DeskPRO:NewsCategory')->getCategoryHelper()->getFlatHierarchy();

		#------------------------------
		# Downloads
		#------------------------------

		$download_cats = App::getEntityRepository('DeskPRO:DownloadCategory')->getCategoryHelper()->getFlatHierarchy();

		#------------------------------
		# Glossary
		#------------------------------

		$glossary_words = App::getEntityRepository('DeskPRO:GlossaryWord')->getWords();
		$glossary_words = Arrays::sortIntoAlphabeticalIndex($glossary_words, null, true, true);


		$data['section_html'] = $this->renderView('AgentBundle:Publish:window-section.html.twig', array(
			'kb_counts' => $kb_counts,
			'kb_cats' => $kb_cats,
			'news_cats' => $news_cats,
			'download_cats' => $download_cats,
			'glossary_words' => $glossary_words,
		));

		return $this->createJsonResponse($data);
	}
}