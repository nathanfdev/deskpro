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
use Application\DeskPRO\Entity\Article;
use Application\DeskPRO\Entity\ArticlePendingCreate;
use Application\DeskPRO\Entity\ArticleValidatingEdit;
use Application\DeskPRO\Entity\GlossaryWord;

use Orb\Util\Strings;
use Orb\Util\Arrays;
use Orb\Util\Util;

use FineDiff;

/**
 * Handles ticket searches
 */
class IdeasController extends AbstractController
{
	public function getSectionDataAction()
	{
		$data = array();

		$counts = array();
		$counts['ideas_awaiting_validation']    = App::getEntityRepository('DeskPRO:Idea')->countAwaitingValidation();
		$counts['popular_ideas']                = App::getEntityRepository('DeskPRO:Idea')->countPopular();
		$counts['comments_awaiting_validation'] = App::getEntityRepository('DeskPRO:IdeaComment')->countAwaitingValidation();

		$idea_cats          = App::getEntityRepository('DeskPRO:IdeaCategory')->getCategoryHelper()->getFlatHierarchy();
		$active_status_cats = App::getEntityRepository('DeskPRO:IdeaStatusCategory')->getActiveCategories();
		$closed_status_cats = App::getEntityRepository('DeskPRO:IdeaStatusCategory')->getClosedCategories();

		$label_lister = new \Application\DeskPRO\Labels\LabelLister('ideas');
		$ideas_tag_index = $label_lister->getIndexList();

		$data['section_html'] = $this->renderView('AgentBundle:Ideas:window-section.html.twig', array(
			'counts'             => $counts,
			'idea_cats'          => $idea_cats,
			'active_status_cats' => $active_status_cats,
			'closed_status_cats' => $closed_status_cats,
			'ideas_tag_index'    => $ideas_tag_index
		));

		return $this->createJsonResponse($data);
	}
}