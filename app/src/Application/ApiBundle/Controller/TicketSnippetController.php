<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. http://www.deskpro.com/   |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2012, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at http://www.deskpro.com/license                           |
|                                                                          |
| By using this software, you acknowledge having read the license          |
| and agree to be bound thereby.                                           |
|                                                                          |
| Please note that DeskPRO is not free software. We release the full       |
| source code for our software because we trust our users to pay us for    |
| the huge investment in time and energy that has gone into both creating  |
| this software and supporting our customers. By providing the source code |
| we preserve our customers' ability to modify, audit and learn from our   |
| work. We have been developing DeskPRO since 2001, please help us make it |
| another decade.                                                          |
|                                                                          |
| Like the work you see? Think you could make it better? We are always     |
| looking for great developers to join us: http://www.deskpro.com/jobs/    |
|                                                                          |
| ~ Thanks, Everyone at Team DeskPRO                                       |
\**************************************************************************/

/**
 * DeskPRO
 *
 * @package DeskPRO
 * @subpackage ApiBundle
 */

namespace Application\ApiBundle\Controller;

use Application\DeskPRO\App;
use Application\DeskPRO\Entity\TicketSnippet;
use Application\DeskPRO\Entity\TicketSnippetCategory;

class TicketSnippetController extends AbstractController
{
	####################################################################################################################
	# get-snippets
	####################################################################################################################

	public function getSnippetsAction()
	{
		if (!$this->person->can_admin) {
			$agent_id = $this->person->getId();
		} else {
			$agent_id = $this->in->getUint('agent_id');
			if ($agent_id && !$this->container->getAgentData()->get($agent_id)) {
				return $this->createApiErrorResponse(404, 'Unknown agent ID', 404);
			}
		}

		$agent = null;
		if ($agent_id) {
			$agent = $this->container->getAgentData()->get($agent_id);
		}

		if ($agent) {
			$snippet_grouped = $this->em->getRepository('DeskPRO:TicketSnippet')->getSnippetsForAgent($agent);
			$data = array('snippets' => array());
			foreach ($snippet_grouped as $group) {
				foreach ($group['snippets'] as $s) {
					$data['snippets'][] = $s->toApiData();
				}
			}
		} else {
			$snippets = $this->em->getRepository('DeskPRO:TicketSnippet')->findAll();
			$data = array('snippets' => array());
			foreach ($snippets as $s) {
				$data['snippets'][] = $s->toApiData();
			}
		}

		return $this->createApiResponse($data);
	}

	####################################################################################################################
	# get-snippet
	####################################################################################################################

	public function getSnippetAction($snippet_id)
	{
		$snippet = $this->em->find('DeskPRO:TicketSnippet', $snippet_id);
		if (!$snippet) {
			return $this->createApiErrorResponse(404, 'Unknown snippet ID', 404);
		}

		if ($snippet->person->getId() != $this->person->getId() && $snippet->category->is_global && !$this->person->can_admin) {
			return $this->createApiErrorResponse(404, 'API user does not have permission to edit this snippet', 404);
		}

		return $this->createApiResponse(array('snippet' => $snippet->toApiData()));
	}

	####################################################################################################################
	# edit-snippet
	####################################################################################################################

	public function editSnippetAction($snippet_id = 0)
	{
		if ($snippet_id) {
			$snippet = $this->em->find('DeskPRO:TicketSnippet', $snippet_id);
			if (!$snippet) {
				return $this->createApiErrorResponse(404, 'Unknown snippet ID', 404);
			}

			if ($snippet->person->getId() != $this->person->getId() && $snippet->category->is_global && !$this->person->can_admin) {
				return $this->createApiErrorResponse(404, 'API user does not have permission to edit this snippet', 404);
			}
		} else {
			$snippet = new TicketSnippet();
			$snippet->person = $this->person;
		}

		if ($this->in->getUint('category_id')) {
			$cat = $this->em->find('DeskPRO:TicketSnippetCategory', $this->in->getUint('category_id'));
			if (!$cat) {
				return $this->createApiErrorResponse(404, 'Unknown category ID', 404);
			}
			if ($cat->person->getId() != $this->person->getId() && !$cat->is_global && !$this->person->can_admin) {
				return $this->createApiErrorResponse(404, 'API user does not have permission to use category', 404);
			}
			$snippet->category = $cat;
		}

		if ($this->in->getString('snippet_html')) {
			$snippet->snippet_html = $this->in->getString('snippet_html');
		}
		if ($this->in->getString('title')) {
			$snippet->title = $this->in->getString('title');
		}
		if ($this->in->getString('shortcut_code')) {
			$snippet->shortcut_code = $this->in->getString('shortcut_code');
		}

		$errors = array();
		if (!$snippet->category) {
			$errors['category_id'] = array('required_field', 'missing or empty');
		}
		if (!$snippet->title) {
			$errors['title'] = array('required_field', 'missing or empty');
		}
		if ($snippet->shortcut_code) {
			$exists = $this->db->fetchColumn("
				SELECT id FROM ticket_snippets
				WHERE shortcut_code = ?
				AND id != ?
			", array($snippet->shortcut_code, $snippet->id));

			if ($exists && $exists != $snippet->getId()) {
				$errors['shortcut_code'] = array('invalid_argument', 'Shortcut code already in use by snippet #'.$exists);
			}
		}

		if ($errors) {
			return $this->createApiMultipleErrorResponse($errors);
		}

		$this->em->persist($snippet);
		$this->em->flush();

		return $this->createApiCreateResponse(
			array('snippet_id' => $snippet->id),
			$this->generateUrl('api_ticketsnippets_get', array('snippet_id' => $snippet->id), true)
		);
	}

	####################################################################################################################
	# get-categories
	####################################################################################################################

	public function getCategoriesAction()
	{
		if (!$this->person->can_admin) {
			$agent_id = $this->person->getId();
		} else {
			$agent_id = $this->in->getUint('agent_id');
			if ($agent_id && !$this->container->getAgentData()->get($agent_id)) {
				return $this->createApiErrorResponse(404, 'Unknown agent ID', 404);
			}
		}

		$agent = null;
		if ($agent_id) {
			$agent = $this->container->getAgentData()->get($agent_id);
		}

		$cats = array();

		if ($agent) {
			$snippet_grouped = $this->em->getRepository('DeskPRO:TicketSnippet')->getSnippetsForAgent($agent);
			foreach ($snippet_grouped as $group) {
				foreach ($group['snippets'] as $s) {
					$cats[$s->category->getId()] = $s->category;
				}
			}
		} else {
			$snippets = $this->em->getRepository('DeskPRO:TicketSnippet')->findAll();
			foreach ($snippets as $s) {
				$cats[$s->category->getId()] = $s->category;
			}
		}

		$data = array('categories' => array());
		foreach ($cats as $c) {
			$data['categories'][] = $c->toApiData();
		}

		return $this->createApiResponse($data);
	}

	####################################################################################################################
	# get-category
	####################################################################################################################

	public function getCategoryAction($category_id)
	{
		$category = $this->em->find('DeskPRO:TicketSnippetCategory', $category_id);
		if (!$category) {
			return $this->createApiErrorResponse(404, 'Unknown category ID', 404);
		}

		if ($category->person->getId() != $this->person->getId() && $category->category->is_global && !$this->person->can_admin) {
			return $this->createApiErrorResponse(404, 'API user does not have permission to edit this category', 404);
		}

		return $this->createApiResponse(array('category' => $category->toApiData()));
	}

	####################################################################################################################
	# edit-category
	####################################################################################################################

	public function editCategoryAction($category_id = 0)
	{
		if ($category_id) {
			$category = $this->em->find('DeskPRO:TicketSnippetCategory', $category_id);
			if (!$category) {
				return $this->createApiErrorResponse(404, 'Unknown category ID', 404);
			}

			if ($category->person->getId() != $this->person->getId() && $category->category->is_global && !$this->person->can_admin) {
				return $this->createApiErrorResponse(404, 'API user does not have permission to edit this category', 404);
			}
		} else {
			$category = new TicketSnippetCategory();
			$category->person = $this->person;
		}

		return $this->createApiCreateResponse(
			array('category_id' => $category_id->id),
			$this->generateUrl('api_ticketsnippets_cats_get', array('category_id' => $category_id->id), true)
		);
	}
}