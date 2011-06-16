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
use Application\DeskPRO\Entity\Idea;

use Application\DeskPRO\Searcher\IdaSearch;
use Application\AgentBundle\Controller\Helper\IdeaResults;
use Application\DeskPRO\UI\RuleBuilder;

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

		$status_counts = array();
		$status_counts['new']    = App::getEntityRepository('DeskPRO:Idea')->countNew();
		$status_counts['active'] = App::getEntityRepository('DeskPRO:Idea')->countActiveGrouped();
		$status_counts['closed'] = App::getEntityRepository('DeskPRO:Idea')->countClosedGrouped();
		$status_counts['hidden'] = App::getEntityRepository('DeskPRO:Idea')->countHiddenGrouped();

		$category_counts = App::getEntityRepository('DeskPRO:Idea')->countAllCategoriesGrouped();

		$idea_cats          = App::getEntityRepository('DeskPRO:IdeaCategory')->getCategoryHelper()->getFlatHierarchy();
		$active_status_cats = App::getEntityRepository('DeskPRO:IdeaStatusCategory')->getActiveCategories();
		$closed_status_cats = App::getEntityRepository('DeskPRO:IdeaStatusCategory')->getClosedCategories();

		$label_lister = new \Application\DeskPRO\Labels\LabelLister('ideas');
		$ideas_tag_index = $label_lister->getIndexList();

		$data['section_html'] = $this->renderView('AgentBundle:Ideas:window-section.html.twig', array(
			'counts'             => $counts,
			'status_counts'      => $status_counts,
			'category_counts'    => $category_counts,
			'idea_cats'          => $idea_cats,
			'active_status_cats' => $active_status_cats,
			'closed_status_cats' => $closed_status_cats,
			'ideas_tag_index'    => $ideas_tag_index
		));

		return $this->createJsonResponse($data);
	}


	/**
	 * List of ideas waiting to be validated
	 * 
	 * @return \Symfony\Bundle\FrameworkBundle\Controller\Response
	 */
	public function validatingListAction()
	{
		$result_helper = IdeaResults::newFromRequest($this, array(
			// Validating always has this term, the template doesnt let you change it
			'specific_options' => array(
				array('type' => 'hidden_status', 'op' => 'is', 'options' => array('status' => 'validating'))
			)
		));

		return $this->renderList(
			$result_helper,
			'AgentBundle:Ideas:validating-list.html.twig'
		);
	}

	/**
	 * Any general search. For example, status, category or label
	 *
	 * @return \Symfony\Bundle\FrameworkBundle\Controller\Response
	 */
	public function filterListAction()
	{
		$result_helper = IdeaResults::newFromRequest($this);

		return $this->renderList(
			$result_helper
		);
	}

	
	/**
	 * A shortcut to run a filter on a category
	 * 
	 * @param  $category_id
	 * @return
	 */
	public function categoryListAction($category_id)
	{
		$result_helper = IdeaResults::newFromRequest($this, array(
			'specific_options' => array(
				array('term' => 'category', 'op' => 'is', 'options' => array('category' => $category_id))
			)
		));

		return $this->renderList(
			$result_helper
		);
	}

	
	/**
	 * A shortcut to run a filter on a status
	 *
	 * @param  $category_id
	 * @return
	 */
	public function statusListAction($status)
	{
		// $status can be either a top-level name like active, closed or hidden,
		// or an integer which will be treated as a status category (Active > Planned for example)

		$result_helper = IdeaResults::newFromRequest($this, array(
			'specific_options' => array(
				array('term' => 'status', 'op' => 'is', 'options' => array('status' => $status))
			)
		));

		return $this->renderList(
			$result_helper
		);
	}


	/**
	 * This takes a result helper and just handles rendering it
	 * 
	 * @param  $result_helper
	 * @param string $template
	 * @param array $template_vars
	 * @return \Symfony\Bundle\FrameworkBundle\Controller\Response
	 */
	public function renderList($result_helper, $template = 'AgentBundle:Ideas:filter-list.html.twig', $template_vars = array())
	{
		$result_cache = $result_helper->getResultCache();

		$page = $this->in->getUint('p');
		if (!$page) $page = 1;

		$ideas = $result_helper->getIdeasForPage($page);

		if ($this->in->getBool('is_partial')) {
			$template = str_replace('.html.twig', '-part.html.twig', $template);
		}

		return $this->render($template, array_merge(array(
			'cache'        => $result_cache,
			'cache_id'     => $result_cache['id'],
			'ideas'        => $ideas
		), $template_vars));
	}
}