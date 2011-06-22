<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @subpackage AdminBundle
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */

namespace Application\AdminBundle\Controller;

use Application\DeskPRO\App;
use Application\DeskPRO\Entity;

class TicketFiltersController extends AbstractController
{
	############################################################################
	# index
	############################################################################

	public function indexAction()
	{
		$sections = array();
		$sections['global'] = $this->forward('AdminBundle:TicketFilters:getGlobalList')->getContent();
		$sections['team']   = $this->forward('AdminBundle:TicketFilters:getTeamList')->getContent();
		$sections['agent']  = $this->forward('AdminBundle:TicketFilters:getAgentList')->getContent();

 		return $this->render('AdminBundle:TicketFilters:index.html.twig', array(
			'sections' => $sections
		));
	}

	public function getGlobalListAction()
	{
		$filters = App::getEntityRepository('DeskPRO:TicketFilter')->getAllGlobalFilters();

 		return $this->render('AdminBundle:TicketFilters:list-global.html.twig', array(
			'filters' => $filters,
		));
	}

	public function getTeamListAction()
	{
		$filters_grouped = App::getEntityRepository('DeskPRO:TicketFilter')->getAllTeamFilters();

 		return $this->render('AdminBundle:TicketFilters:list-team.html.twig', array(
			'filters_grouped' => $filters_grouped,
		));
	}

	public function getAgentListAction()
	{
		$filters_grouped = App::getEntityRepository('DeskPRO:TicketFilter')->getAllAgentFilters();

 		return $this->render('AdminBundle:TicketFilters:list-agent.html.twig', array(
			'filters_grouped' => $filters_grouped,
		));
	}

	############################################################################
	# edit
	############################################################################

	public function newChooseTypeAction()
	{
 		return $this->render('AdminBundle:TicketFilters:new-choosetype.html.twig', array(
			'agent_names'      => App::getOrm()->getRepository('DeskPRO:Person')->getAgentNames(),
			'agent_team_names' => App::getOrm()->getRepository('DeskPRO:AgentTeam')->getTeamNames(),
		));
	}

	public function editAction($filter_id)
	{
		if (!$filter_id) {
			$filter = new Entity\TicketFilter();
			$filter['person_id'] = $this->person['id'];

			if ($this->in->getBool('is_new')) {
				switch ($this->in->getString('filter_visibility')) {
					case 'filter_agent_team':
						$filter['agent_team_id'] = $this->in->getUint('filter.agent_team_id');
						break;

					case 'filter_agent':
						$filter['person_id'] = $this->in->getUint('filter.person_id');
						break;

					case 'filter_global':
					default:
						$filter['is_global'] = true;
						break;
				}
			} else {
				$filter['is_global'] = $this->in->getBool('filter.is_global');
				$filter['agent_team_id'] = $this->in->getUint('filter.agent_team_id');
				$filter['person_id'] = $this->in->getUint('filter.person_id');
			}
		} else {
			$filter = App::getEntityRepository('DeskPRO:TicketFilter')->find($filter_id);
			if (!$filter) {
				return $this->createNotFoundException();
			}
		}

		$is_saved = false;
		if ($this->in->getBool('process')) {
			$filter['title'] = $this->in->getString('filter.title');
			$term_rules = \Application\DeskPRO\UI\RuleBuilder::newTermsBuilder();
			$filter['terms'] = $term_rules->readForm($this->in->getCleanValueArray('terms', 'raw' , 'discard'));

			APp::getOrm()->transactional(function($em) use ($filter) {
				$em->persist($filter);
				$em->flush();
			});

			$is_saved = true;
		}

		$term_options = App::getApi('tickets.search')->getSearchOptions($this->person);

		return $this->render('AdminBundle:TicketFilters:edit.html.twig', array(
			'filter' => $filter,
			'term_options' => $term_options,
			'is_saved' => false
		));
	}
}