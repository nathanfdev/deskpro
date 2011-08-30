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
use Application\DeskPRO\Entity\IdeaComment;

use Application\DeskPRO\Searcher\IdeaSearch;
use Application\AgentBundle\Controller\Helper\IdeaResults;
use Application\DeskPRO\UI\RuleBuilder;

use Application\DeskPRO\ContentSearch\RelatedContentFinder;
use Application\DeskPRO\Publish\RelatedContentUpdate;

use Application\DeskPRO\ContentRevision\Util as ContentRevisionUtil;

use Application\DeskPRO\Publish\Ideas\GroupingCounter;

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

		$idea_comments = $idea->comments;

		$active_status_cats = App::getEntityRepository('DeskPRO:IdeaStatusCategory')->getActiveCategories();
		$closed_status_cats = App::getEntityRepository('DeskPRO:IdeaStatusCategory')->getClosedCategories();

		$idea_revisions = $idea->getRevisions();
		$sticky_search_words = $this->em->getRepository('DeskPRO:SearchStickyResult')->getWordsForObject($idea);

		$related_finder = new RelatedContentFinder($this->person, $idea);
		$related_content = $related_finder->getRelatedEntities();

		$rated_searches = App::getEntityRepository('DeskPRO:SearchLog')->getRatedSearchesFor('idea', $idea['id'], 'counted');

		$state = App::getOrm()->getRepository('DeskPRO:PersonPref')->getPrefForPersonId('agent.ui.state.editidea', $this->person->id);

		$content_rating = new \Application\UserBundle\Controller\Helper\ContentRating($idea, $this->person, $this->session->getVisitor());
		$my_vote = $content_rating->getRating();

		return $this->render('AgentBundle:Ideas:view.html.twig', array(
			'idea'           => $idea,
			'idea_comments'  => $idea_comments,
			'idea_revisions' => $idea_revisions,
			'state'          => $state,

			'my_vote' => $my_vote,

			'rated_searches'      => $rated_searches,
			'related_content'     => $related_content,
			'sticky_search_words' => $sticky_search_words,

			'active_status_cats' => $active_status_cats,
			'closed_status_cats' => $closed_status_cats,
		));
	}

	public function whoVotedAction($idea_id)
	{
		$idea = App::findEntity('DeskPRO:Idea', $idea_id);

		$idea_votes = $idea->votes->toArray();

		// Sort by votes, top votes on top
		usort($idea_votes, function($a, $b) {
			if ($a['num_votes'] == $b['num_votes']) return 0;
			return ($a['num_votes'] > $b['num_votes']) ? -1 : 1;
		});

		return $this->render('AgentBundle:Ideas:view-who-voted.html.twig', array(
			'idea' => $idea,
			'idea_votes' => $idea_votes,
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

	public function ajaxSaveLabelsAction($idea_id)
	{
		$idea = App::findEntity('DeskPRO:Idea', $idea_id);

		$labels = $this->in->getCleanValueArray('labels', 'string', 'discard');

		$idea->getLabelManager()->setLabelsArray($labels);

		App::getOrm()->persist($idea);
		App::getOrm()->flush();

		return $this->createJsonResponse(array('success' => 1));
	}

	public function ajaxSaveCommentAction($idea_id)
	{
		$idea = App::findEntity('DeskPRO:Idea', $idea_id);

		$comment = new IdeaComment();
		$comment->idea = $idea;
		$comment->person = $this->person;
		$comment['content'] = $this->in->getString('content');

		if ($this->in->getBool('agent_only')) {
			$comment['status'] = 'agent';
		} else {
			$comment['status'] = 'visible';
		}

		$comment['date_created']  = new \DateTime();

		App::getOrm()->persist($comment);
		App::getOrm()->flush();

		return $this->render('AgentBundle:Ideas:view-comment.html.twig', array(
			'comment' => $comment
		));
	}

	public function ajaxSaveAction($idea_id)
	{
		$idea = App::findEntity('DeskPRO:Idea', $idea_id);
		$rev = null;

		$action = $this->in->getString('action');

		$data = array('success' => 1);

		$this->em->beginTransaction();

		switch ($action) {

			case 'status':
				$idea['status_code'] = $this->in->getString('status');
				break;

			case 'title':
				$idea['title'] = $this->in->getString('title');

				$rev = ContentRevisionUtil::findOrCreate($idea, 'title', $this->person);
				$rev['title'] = $idea['title'];

				break;

			case 'add-related':
				$updater = new RelatedContentUpdate($idea);
				$updater->addRelated(
					$this->in->getString('content_type'),
					$this->in->getString('content_id')
				);
				break;

			case 'remove-related':
				$updater = new RelatedContentUpdate($idea);
				$updater->removeRelated(
					$this->in->getString('content_type'),
					$this->in->getString('content_id')
				);
				break;

			case 'content':

				App::getOrm()->getRepository('DeskPRO:PersonPref')->deletePrefForPersonId('agent.ui.state.editidea', $this->person->id);

				$idea['content'] = $this->in->getString('content');

				$data['content_html'] = $this->renderView('AgentBundle:Ideas:view-content-tab.html.twig', array(
					'idea' => $idea
				));

				$rev = ContentRevisionUtil::findOrCreate($idea, array('content'), $this->person);
				$rev['content'] = $idea['content'];

				break;

			case 'category':
				$cat = $this->em->find('DeskPRO:IdeaCategory', $this->in->getUint('category_id'));
				$idea['category'] = $cat;
				$data['category_id'] = $cat['id'];
				break;

			case 'vote':

				$content_rating = new \Application\UserBundle\Controller\Helper\ContentRating($idea, $this->person, $this->session->getVisitor());
				$content_rating->setRating(1);

				break;

			case 'clear-vote':

				$content_rating = new \Application\UserBundle\Controller\Helper\ContentRating($idea, $this->person, $this->session->getVisitor());
				$vote = $content_rating->getRating();

				if ($vote) {
					$idea->removeRating($vote);

					$this->em->remove($vote);
				}

				break;
		}

		$this->em->persist($idea);

		if ($rev) {
			$this->em->persist($rev);
		}

		$this->em->flush();
		$this->em->commit();

		if ($rev) {
			$data['revision_id'] = $rev['id'];
		} else {
			$data['revision_id'] = null;
		}

		return $this->createJsonResponse($data);
	}

	public function validateAction($idea_id)
	{
		$idea = App::findEntity('DeskPRO:Idea', $idea_id);
		$idea['status'] = Idea::STATUS_NEW;

		App::getOrm()->transactional(function ($em) use ($idea) {
			$em->persist($idea);
			$em->flush();
		});

		$next = $this->_getNextInResult($idea_id);
		if (!$next) {
			$next = $this->generateUrl('agent_ideas_view', array('idea_id' => $idea['id']));
		}

		return $this->createJsonResponse(array(
			'success' => true,
			'next_url' => $next
		));
	}

	public function validateDeleteAction($idea_id)
	{
		$idea = App::findEntity('DeskPRO:Idea', $idea_id);
		$idea['status_code'] = 'hidden.deleted';

		App::getOrm()->transactional(function ($em) use ($idea) {
			$em->persist($idea);
			$em->flush();
		});

		$next = $this->_getNextInResult($idea_id);
		if (!$next) {
			$next = $this->generateUrl('agent_ideas_view', array('idea_id' => $idea['id']));
		}

		return $this->createJsonResponse(array(
			'success' => true,
			'next_url' => $next
		));
	}

	public function validateSkipAction($idea_id)
	{
		$next = $this->_getNextInResult($idea_id);

		return $this->createJsonResponse(array(
			'success' => true,
			'next_url' => $next
		));
	}

	protected function _getNextInResult($idea_id)
	{
		$next = false;

		// Or if we came froma  result list,
		// load the next one in the list
		if ($this->in->getUint('from_result_id')) {
			$result_cache = App::findEntity('DeskPRO:ResultCache', $this->in->getUint('from_result_id'));
			$results = $result_cache['results'];
			if (($k = array_search($idea_id, $results)) !== false) {
				$k++;
				if (isset($results[$k])) {
					$next = $this->generateUrl('agent_ideas_view', array('idea_id' => $results[$k]));
				}
			}
		}

		return $next;
	}

	############################################################################
	# get-section-data
	############################################################################

	public function getSectionDataAction()
	{
		$data = array();

		$counts = array();
		$counts['ideas_awaiting_validation']    = App::getEntityRepository('DeskPRO:Idea')->countAwaitingValidation();
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
			null,
			array(
				'list_type' => 'validating'
			)
		);
	}

	/**
	 * Any general search. For example, status, category or label
	 *
	 * @return \Symfony\Bundle\FrameworkBundle\Controller\Response
	 */
	public function filterListAction()
	{
		$vars = array('list_type' => 'filter');

		$result_helper = IdeaResults::newFromRequest($this);

		$result_cache = $result_helper->getResultCache();

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
		if ($this->in->getString('subgroup')) {
			$result_helper = IdeaResults::newFromRequest($this, array(
				'specific_terms' => array(
					'category' => array('type' => 'category', 'op' => 'is', 'category' => $category_id),
					'status' => array('type' => 'status', 'op' => 'is', 'status' => $this->in->getString('subgroup')),
					'v_status' => array('type' => 'hidden_status', 'op' => 'not', 'hidden_status' => 'validating')
				)
			));
		} else {
			$result_helper = IdeaResults::newFromRequest($this, array(
				'specific_terms' => array(
					'category' => array('type' => 'category', 'op' => 'is', 'category' => $category_id),
					'v_status' => array('type' => 'hidden_status', 'op' => 'not', 'hidden_status' => 'validating')
				)
			));
		}

		$cat = App::findEntity('DeskPRO:IdeaCategory', $category_id);

		$grouping = new GroupingCounter();
		$grouping->setGrouping('category_id', 'status');
		$grouped = $grouping->getDisplayArray();

		$grouped_key = $category_id;

		return $this->renderList(
			$result_helper,
			null,
			array(
				'list_type' => 'category',
				'category_id' => $category_id,
				'page_title' => $cat->getFullTitle(),
				'grouped' => $grouped,
				'grouped_key' => $grouped_key,
				'subgroup' => $this->in->getString('subgroup'),
			)
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
				array('type' => 'status', 'op' => 'not', 'status' => 'hidden'),
				array('type' => 'hidden_status', 'op' => 'not', 'hidden_status' => 'validating')
			),
		));

		return $this->renderList(
			$result_helper,
			null,
			array(
				'list_type' => 'label',
				'label' => $label,
				'page_title' => $label
			)
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

		if ($this->in->getString('subgroup')) {
			$result_helper = IdeaResults::newFromRequest($this, array(
				'specific_terms' => array(
					'status' => array('type' => 'status', 'op' => 'is', 'status' => $status),
					'category' => array('type' => 'category', 'op' => 'is', 'category' => $this->in->getString('subgroup')),
					'v_status' => array('type' => 'hidden_status', 'op' => 'not', 'hidden_status' => 'validating')
				)
			));
		} else {
			$result_helper = IdeaResults::newFromRequest($this, array(
			'specific_terms' => array(
				'status' => array('type' => 'status', 'op' => 'is', 'status' => $status),
				'v_status' => array('type' => 'hidden_status', 'op' => 'not', 'hidden_status' => 'validating')
			)
		));
		}

		$grouping = new GroupingCounter();
		$grouping->setGrouping('status', 'category_id');
		$grouped = $grouping->getDisplayArray();

		if (ctype_digit($status)) {
			$status_cat = App::findEntity('DeskPRO:IdeaStatusCategory', $status);
			$status_name = $status_cat['title'];
			$grouped_key = $status_cat['status_type'] . '.' . $status_cat['id'];
		} else {
			$status_name = App::getTranslator()->phrase('core_ideas.status_' . $status);
			$grouped_key = $status;
		}

		return $this->renderList(
			$result_helper,
			null,
			array(
				'list_type' => 'status',
				'status' => $status,
				'grouped' => $grouped,
				'grouped_key' => $grouped_key,
				'page_title' => $status_name,
				'subgroup' => $this->in->getString('subgroup'),
			)
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

		$display_fields = $this->person->getPref('agent.ui.idea-filter-display-fields.' . $result_cache['id']);
		if (!$display_fields) {
			$display_fields = $this->person->getPref('agent.ui.idea-filter-display-fields.0');
		}
		if (!$display_fields) {
			$display_fields = array('num_ratings', 'date_created');
		}

		return $this->render($template, array_merge(array(
			'cache'        => $result_cache,
			'cache_id'     => $result_cache['id'],
			'ideas'        => $ideas,

			 'idea_cats'          => $idea_cats,
			 'active_status_cats' => $active_status_cats,
			 'closed_status_cats' => $closed_status_cats,

			 'display_fields' => $display_fields,
		), $template_vars));
	}

	############################################################################
	# newidea
	############################################################################

	public function newIdeaAction()
	{
		$idea_categories    = App::getEntityRepository('DeskPRO:IdeaCategory')->getCategoryHelper()->getFlatHierarchy();
		$active_status_cats = App::getEntityRepository('DeskPRO:IdeaStatusCategory')->getActiveCategories();
		$closed_status_cats = App::getEntityRepository('DeskPRO:IdeaStatusCategory')->getClosedCategories();

		$state = App::getOrm()->getRepository('DeskPRO:PersonPref')->getPrefForPersonId('agent.ui.state.newidea', $this->person->id);

		return $this->render('AgentBundle:Ideas:newidea.html.twig', array(
			'idea_categories'    => $idea_categories,
			'active_status_cats' => $active_status_cats,
			'closed_status_cats' => $closed_status_cats,
			'state'              => $state
		));
	}

	public function newIdeaSaveAction()
	{
		$newidea = new \Application\AgentBundle\Form\Model\NewIdea($this->person);

		$formType = new \Application\AgentBundle\Form\Type\NewIdea();
		$form = $this->get('form.factory')->create($formType, $newidea);

		if ($this->get('request')->getMethod() == 'POST') {
			$form->bindRequest($this->get('request'));
			$form->isValid();

			$newidea->save();

			$idea = $newidea->getIdea();

			App::getOrm()->getRepository('DeskPRO:PersonPref')->deletePrefForPersonId('agent.ui.state.newidea', $this->person->id);

			return $this->createJsonResponse(array(
				'success' => true,
				'idea_id' => $idea['id']
			));
		} else {
			return $this->createJsonResponse(array(
				'success' => false,
			));
		}
	}
}
