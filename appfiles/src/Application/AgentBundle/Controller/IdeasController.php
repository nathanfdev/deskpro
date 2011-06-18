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

use Application\DeskPRO\Searcher\IdeaSearch;
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
	############################################################################
	# view
	############################################################################

	public function viewAction($idea_id)
	{
		$idea = App::findEntity('DeskPRO:Idea', $idea_id);

		$idea_cats          = App::getEntityRepository('DeskPRO:IdeaCategory')->getCategoryHelper()->getFlatHierarchy();
		$active_status_cats = App::getEntityRepository('DeskPRO:IdeaStatusCategory')->getActiveCategories();
		$closed_status_cats = App::getEntityRepository('DeskPRO:IdeaStatusCategory')->getClosedCategories();

		return $this->render('AgentBundle:Ideas:view.html.twig', array(
			'idea' => $idea,

			'idea_cats'          => $idea_cats,
			'active_status_cats' => $active_status_cats,
			'closed_status_cats' => $closed_status_cats,
		));
	}

	public function ajaxSaveEditablesAction($idea_id)
	{
		$idea = App::findEntity('DeskPRO:Idea', $idea_id);

		$ret = '';

		switch ($this->in->getString('action')) {
			case 'title':
				$value = $this->in->getString('title');
				$idea['title'] = $value;
				$ret = array('html' => htmlspecialchars($idea['title']));
				break;
		}

		App::getOrm()->transactional(function ($em) use ($idea) {
			$em->persist($idea);
			$em->flush();
		});

		return $this->createJsonResponse(array(
			'success' => true,
			'idea_id' => $idea['id'],
			'html' => $ret
		));
	}

	public function ajaxUpdateCategoryAction($idea_id, $category_id)
	{
		$idea = App::findEntity('DeskPRO:Idea', $idea_id);
		$cat  = App::findEntity('DeskPRO:IdeaCategory', $category_id);

		$idea->category = $cat;

		App::getOrm()->transactional(function ($em) use ($idea) {
			$em->persist($idea);
			$em->flush();
		});

		return $this->createJsonResponse(array(
			'success' => true,
			'idea_id' => $idea['id'],
		));
	}

	public function ajaxUpdateStatusAction($idea_id, $status_code)
	{
		$idea = App::findEntity('DeskPRO:Idea', $idea_id);
		$idea['status_code'] = $status_code;

		App::getOrm()->transactional(function ($em) use ($idea) {
			$em->persist($idea);
			$em->flush();
		});

		return $this->createJsonResponse(array(
			'success' => true,
			'idea_id' => $idea['id'],
		));
	}
	
	############################################################################
	# get-section-data
	############################################################################

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
			'specific_terms' => array(
				array('type' => 'hidden_status', 'op' => 'is', 'status' => 'validating')
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
			$result_helper,
			null,
			array('list_type' => 'filter')
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
			'specific_terms' => array(
				'category' => array('type' => 'category', 'op' => 'is', 'category' => $category_id),
				'status' => array('type' => 'status', 'op' => 'is', 'status' => 'visible')
			)
		));

		$cat = App::findEntity('DeskPRO:IdeaCategory', $category_id);

		return $this->renderList(
			$result_helper,
			null,
			array('list_type' => 'category', 'page_title' => $cat->getFullTitle())
		);
	}


	/**
	 * A shortcut to run a filter on a label
	 *
	 * @param  $category_id
	 * @return
	 */
	public function labelListAction($label)
	{
		$result_helper = IdeaResults::newFromRequest($this, array(
			'specific_terms' => array(
				array('type' => 'label', 'op' => 'is', 'label' => $label),
				array('type' => 'status', 'op' => 'not', 'status' => 'hidden')
			)
		));

		return $this->renderList(
			$result_helper,
			null,
			array('list_type' => 'label', 'page_title' => $label, 'no_filter_form' => true)
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
			'specific_terms' => array(
				'status' => array('type' => 'status', 'op' => 'is', 'status' => $status)
			)
		));

		if (ctype_digit($status)) {
			$status_cat = App::findEntity('DeskPRO:IdeaStatusCategory', $status);
			$status_name = $status_cat['title'];
		} else {
			$status_name = App::getTranslator()->phrase('core_ideas.status_' . $status);
		}

		return $this->renderList(
			$result_helper,
			null,
			array('list_type' => 'status', 'page_title' => $status_name)
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
	public function renderList($result_helper, $template = null, array $template_vars = array())
	{
		if (!$template) {
			$template = 'AgentBundle:Ideas:filter-list.html.twig';
		}

		$result_cache = $result_helper->getResultCache();

		$page = $this->in->getUint('p');
		if (!$page) $page = 1;

		$ideas = $result_helper->getIdeasForPage($page);

		if ($this->in->getBool('is_partial')) {
			$template = str_replace('.html.twig', '-part.html.twig', $template);
		}

		// Options for the filter form
		$idea_cats          = App::getEntityRepository('DeskPRO:IdeaCategory')->getCategoryHelper()->getFlatHierarchy();
		$active_status_cats = App::getEntityRepository('DeskPRO:IdeaStatusCategory')->getActiveCategories();
		$closed_status_cats = App::getEntityRepository('DeskPRO:IdeaStatusCategory')->getClosedCategories();

		$filter_form_values = !empty($result_cache['extra']['form']) ? $result_cache['extra']['form'] : array();

		return $this->render($template, array_merge(array(
			'cache'        => $result_cache,
			'cache_id'     => $result_cache['id'],
			'ideas'        => $ideas,
			 
			'filter_form' => $filter_form_values,

			 'idea_cats'          => $idea_cats,
			 'active_status_cats' => $active_status_cats,
			 'closed_status_cats' => $closed_status_cats,
		), $template_vars));
	}
}