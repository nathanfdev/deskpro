<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @category Entities
 * @copyright Copyright (c) 2011 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Abdullah Kiser <kiser.bd@gmail.com>
 */

namespace Application\AgentBundle\Controller;

use Orb\Util\Arrays;
use Application\DeskPRO\Entity;
use Application\DeskPRO\Entity\Deal;
use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\PersonEmail;
use Application\DeskPRO\Entity\PersonContactData;
use Application\DeskPRO\Entity\PersonNote;
use Application\DeskPRO\Entity\Organization;
use Application\DeskPRO\App;
use Application\DeskPRO\Entity\DealNote;
use Application\DeskPRO\Entity\DealStage;
use Application\DeskPRO\Entity\TaskComment;
use Application\AgentBundle\Form\Type\NewTask;
use Symfony\Component\HttpFoundation\Response;

/**
 * Handles viewing and editing deals
 */
class DealController extends AbstractController
{
    private $_entityManager;
    private $_currentUser;
    private $_task_repository;


    public function newAction()
    {

        $deal_type = App::getEntityRepository('DeskPRO:DealType')->findAll();
        $deal_stage = App::getEntityRepository('DeskPRO:DealStage')->getDealStagesByDealType(1);
        $agents = App::getEntityRepository('DeskPRO:Person')->getAgents();
        $deal_currency = App::getEntityRepository('DeskPRO:Currency')->findAll();

        return $this->render('AgentBundle:Deal:newdeal.html.twig', array(
           'deal_type' => $deal_type,
           'deal_stage' => $deal_stage,
           'agents' => $agents,
           'person' => $this->person,
           'deal_currency' => $deal_currency

        ));
    }


    public function newSaveAction() {
        $success = false;
        $newdeal = new \Application\AgentBundle\Form\Model\NewDeal($this->person);

        $formtype = new \Application\AgentBundle\Form\Type\NewDeal();
        $form = $this->get('form.factory')->create($formtype, $newdeal);

        if ($this->get('request')->getMethod() == 'POST') {
            $form->bindRequest($this->get('request'));
            $form->isValid();
            $newdeal->save();

            $deal = $newdeal->getDeal();
            $success = true;
        }
        return $this->createJsonResponse(array(
            'success' => $success,
            'deal_id' => $deal['id']
        ));
    }

    /**
     * Generate the category wise list gor task.
     * @return html
     */

    public function getSectionDataAction()
    {
        
        $deal_repository = $this->em->getRepository('DeskPRO:Deal');
        $person = $this->person;

        $my_open_deals = $deal_repository->findDealsForPerson($person);
        $my_total_opendeals = $deal_repository->countDealsForPerson($person);

        $my_won_deals = $deal_repository->findDealsForPerson($person, 1);
        $my_total_wondeals = $deal_repository->countDealsForPerson($person, 1);

        $my_lost_deals = $deal_repository->findDealsForPerson($person, 2);
        $my_total_lostdeals = $deal_repository->countDealsForPerson($person, 2);

        $other_open_deals = $deal_repository->findDealsForOther($person);
        $other_total_opendeals = $deal_repository->countDealsForOther($person);

        $other_won_deals = $deal_repository->findDealsForOther($person, 1);
        $other_total_wondeals = $deal_repository->countDealsForOther($person, 1);

        $other_lost_deals = $deal_repository->findDealsForOther($person, 2);
        $other_total_lostdeals = $deal_repository->countDealsForOther($person, 2);

        
        $section_html = $this->renderView('AgentBundle:Deal:window-section.html.twig',array(
            'myopendeals' => $my_open_deals,
            'my_total_opendeals' => $my_total_opendeals,
            'otheropendeals' => $other_open_deals,
            'other_total_opendeals' => $other_total_opendeals,
            'my_won_deals' => $my_total_wondeals,
            'my_lost_deals' => $my_total_lostdeals,
            'my_close_total_deals' => $my_total_wondeals + $my_total_lostdeals,
            'other_won_deals' => $other_total_wondeals,
            'other_lost_deals' => $other_total_lostdeals,
            'other_close_total_deals' => $other_total_wondeals + $other_total_lostdeals,

        ));

        return $this->createJsonResponse(array(
            'section_html' => $section_html,
        ));
    }
    

    public function dealListAction($owner_type = null, $deal_status = null, $deal_type_id = null)
    {
        $deal_repository = $this->em->getRepository('DeskPRO:Deal');
        $person = $this->person;

        if($owner_type == 'my')
        {
            if($deal_status == 'open'){
                $deals = $deal_repository->filterDealsForPerson($person, 0, $deal_type_id);
            }
            else if($deal_status == 'close'){
                $deals = $deal_repository->filterDealsForPerson($person, -1, $deal_type_id);
            }else if($deal_status == 'won'){
                $deals = $deal_repository->filterDealsForPerson($person, 1, $deal_type_id);
            }else if($deal_status == 'lost'){
                $deals = $deal_repository->filterDealsForPerson($person, 2, $deal_type_id);
            }

        }else if($owner_type == 'other'){

            if($deal_status == 'open'){
                $deals = $deal_repository->filterDealsForOther($person, 0, $deal_type_id);
            }
            else if($deal_status == 'close'){
                $deals = $deal_repository->filterDealsForOther($person, -1, $deal_type_id);
            }else if($deal_status == 'won'){
                $deals = $deal_repository->filterDealsForOther($person, 1, $deal_type_id);
            }else if($deal_status == 'lost'){
                $deals = $deal_repository->filterDealsForOther($person, 2, $deal_type_id);
            }
        }

        $tpl = 'AgentBundle:Deal:deal-list.html.twig';
        return $this->render($tpl, array(
            'deals' => $deals
        ));
    }

    public function viewAction($deal_id = null)
    {
        if($deal_id)
        {
            $deal = $this->getDealOr404($deal_id);
        } else{
            $deal = new Deal();
        }
        
        $notes = App::getEntityRepository('DeskPRO:DealNote')->getNotesForDeal($deal);
        $agents = App::getEntityRepository('DeskPRO:Person')->getAgents();
        $deal_type = App::getEntityRepository('DeskPRO:DealType')->findAll();
        $deal_stage = App::getEntityRepository('DeskPRO:DealStage')->getDealStagesByDealType($deal->getDealType()->getId());
        
        $participant_person_ids = array();
        $participant_org_ids = array();

        foreach($deal->getPeoples() as $person)
        {
            $participant_person_ids[] = $person->id;
        }

        foreach($deal->getOrganizations() as $organization)
        {
            $participant_org_ids[] = $organization->id;
        }
        
        $tpl = 'AgentBundle:Deal:deal-view.html.twig';
        return $this->render($tpl, array(
            'deal' => $deal,
            'notes' => $notes,
            'agents' => $agents,
            'deal_types' => $deal_type,
            'deal_stage' => $deal_stage,
            'participant_person_ids' => $participant_person_ids,
            'participant_org_ids' => $participant_org_ids,            
            'person' => $this->person

        ));
    }

    // TODO error checking
	public function ajaxSaveNoteAction($deal_id)
	{
		if($deal_id)
                {
                    $deal = $this->getDealOr404($deal_id);
                } else{
                    $deal = new Deal();
                }

		$note_txt = $this->in->getString('note');

		$em = $this->em;
		
		$note = new DealNote();
		$note['agent'] = $this->person;
		$note['deal'] = $deal;
		$note['note'] = $note_txt;
		$em->persist($note);

		$em->flush();
		//$em->commit();

		return $this->createJsonResponse(array(
			'success' => true,
			'deal_id' => $deal['id'],
			'note_li_html' => $this->renderView('AgentBundle:Person:note-li.html.twig', array('note' => $note))
		));
	}

        ############################################################################
	# ajax-save-labels
	############################################################################

	public function ajaxSaveLabelsAction($deal_id)
	{
		if($deal_id)
                {
                    $deal = $this->getDealOr404($deal_id);
                } else{
                    $deal = new Deal();
                }

		$labels = $this->in->getCleanValueArray('labels', 'string', 'discard');

		$deal->getLabelManager()->setLabelsArray($labels);

		App::getOrm()->persist($deal);
		App::getOrm()->flush();

		return $this->createJsonResponse(array('success' => 1));
	}


        public function ajaxSaveCustomFieldsAction($deal_id)
	{
		$deal = $this->getDealOr404($deal_id);

		$this->em->beginTransaction();

		try {
			$field_manager = $this->container->getSystemService('ticket_fields_manager');
			$post_custom_fields = $this->request->request->get('custom_fields', array());
			if (!empty($post_custom_fields)) {
				$field_manager->saveFormToObject($post_custom_fields, $org);
			}

			$this->em->flush();
			$this->em->commit();
		} catch (\Exception $e) {
			$this->em->rollback();
			throw $e;
		}

		$custom_fields = $field_manager->getDisplayArrayForObject($org);


		$ticket_options = App::getApi('tickets')->getTicketOptions($this->person);

		return $this->render('AgentBundle:Ticket:view-page-display-holders.html.twig', array(
			'ticket' => $ticket,
			'ticket_options' => $ticket_options,
			'custom_fields' => $custom_fields,
		));
	}

        
        public function setAgentParticipantsAction($deal_id, $agent_id)
	{
		$deal = $this->getDealOr404($deal_id);		
                $agent_id = ($agent_id == 0) ? null : $agent_id;
                
		$this->db->beginTransaction();

		try {
			$deal->setAsignedAgentId($agent_id);
			$this->em->persist($deal);
			$this->em->flush();
			$this->db->commit();
		} catch (\Exception $e) {
			$this->db->rollback();
			throw $e;
		}

		return $this->createJsonResponse(array('sucess' => true));
	}

        public function ajaxSaveAction($deal_id) {

            $deal = $this->getDealOr404($deal_id);
            $this->em->beginTransaction();
            $data = array(
                'success' => true
            );
            switch ($this->in->getString('action')) {
                case 'remove-person':
                    $person = App::findEntity('DeskPRO:Person', $this->in->getUint('person_id'));
                    if ($person) {
                        $deal->deletePeople($person);
                        $this->em->persist($deal);
                        $data['remove_person_id'] = $person['id'];
                    }
                    break;
                case 'remove-organization':
                    $organization = App::findEntity('DeskPRO:Organization', $this->in->getUint('organization_id'));
                    if ($organization) {
                        $deal->deleteOrganization($organization);
                        $this->em->persist($deal);
                        $data['remove_organization_id'] = $organization['id'];
                    }
                    break;
                case 'change-dealtype':
                    
                    $deal_type = App::findEntity('DeskPRO:DealType', $this->in->getUint('deal_type_id'));
                    if($deal_id){
                        $deal->setDealTypeId($this->in->getUint('deal_type_id'));
                        $deal->setDealStageId(null);
                        $this->em->persist($deal);
                    }
                    $data['change_deal_type_id'] = $deal_type['id'];
                    $deal_stage = App::getEntityRepository('DeskPRO:DealStage')->getDealStagesByDealType($deal_type->getId());

                     
                    $tpl = $this->renderView('AgentBundle:Deal:select-deal-options.html.twig', array(
                        'name'=> 'actions[dealtype]',
                        'with_blank'=> true,
                        'with_blank2'=> true,
                        'blank_title'=> 'Set Deal Stage',
                        'options'=> $deal_stage,
                        'selected'=> '',
                        'add_classname'=> 'select-deal-stage'
                    ));

                    $data['deal_stage'] = $tpl;
                    
                    break;

                case 'change-dealstage':

                    $deal->setDealStageId($this->in->getUint('deal_stage_id'));
                    $this->em->persist($deal);
                    $data['change_deal_stage_id'] = $this->in->getUint('deal_stage_id');
                    break;
                    
            }

            $this->em->flush();
            $this->em->commit();

            return $this->createJsonResponse($data);
        }

        public function newdealGetPersonRowAction($person_id)
	{
		if (!$person_id && $this->in->getUint('person_id')) {
			$person_id = $this->in->getUint('person_id');
		}

		$person = false;
		if ($person_id) {
			$person = $this->em->find('DeskPRO:Person', $person_id);
		}
		if (!$person && $this->in->getString('email')) {
			$person = $this->em->getRepository('DeskPRO:Person')->findOneByEmail($this->in->getString('email'));
		}

		$session = null;
		if ($this->in->getUint('session_id')) {
			$session = $this->em->find('DeskPRO:Session', $this->in->getUint('session_id'));
		}
		if ($session && $session->person) {
			$person = $session;
		}

		if (!$person) {
			$person = new Person();
			if ($session) {
				$person->name = $session->visitor->name;
				if ($session->visitor->email) {
					$person->setEmail($session->visitor->email);
				}
			}
		}

		return $this->render('AgentBundle:Deal:newdeal-person-row.html.twig', array(
			'person' => $person
		));
	}

        public function newdealGetOrganizationRowAction($org_id)
	{
		$organization = false;
		if ($org_id) {
			$organization = $this->em->find('DeskPRO:Organization', $org_id);
		}

		return $this->render('AgentBundle:Deal:newdeal-organization-row.html.twig', array(
			'organization' => $organization
		));
        }

        public function newdealCreatePersonRowAction($person_id)
        {
                if ($person_id)
                {
			$person = $this->em->find('DeskPRO:Person', $person_id);
		} else{
                    $person = new Person();
                }

                return $this->render('AgentBundle:Deal:create-person-row.html.twig', array(
			'person' => $person
		));
        }

        public function newdealSetPersonRowAction($person_id)
	{
                $deal_repository = $this->em->getRepository('DeskPRO:Deal');
                $deal = $this->getDealOr404($this->in->getString('deal_id'));

                $email = $this->in->getString('email');
                $name = $this->in->getString('name');
                
                if ($person_id) {
                    $person = $this->em->find('DeskPRO:Person', $person_id);
                    
		} else if($email){
                    $person = $this->em->getRepository('DeskPRO:Person')->findOneByEmail($email);
                }

                if (!$person) {
			$person = new Person();
			$person->addEmailAddressString($email);
		}

                if (!$person->name && $name) {
			$person->name = $name;
		}

                $this->em->persist($person);
                $this->em->flush();

              // Checked if the person already added to the deal.
              if($deal_repository->findPersonInDeal($person, $this->in->getString('deal_id')) <= 0)
              {
                  $deal->addPeoples($person);
                  $this->em->persist($deal);
                  $this->em->flush();

                  return $this->render('AgentBundle:Deal:person-li.html.twig', array(
			'person' => $person
                  ));
              }else{
                  return new Response('failed');
              }
        }

        public function newdealCreateOrganizationRowAction($org_id)
        {
            $organization = false;
            if ($org_id) {
                    $organization = $this->em->find('DeskPRO:Organization', $org_id);
            }

            return $this->render('AgentBundle:Deal:create-organization-row.html.twig', array(
                    'organization' => $organization
            ));
        }

        public function newdealSetOrganizationRowAction($org_id)
	{
		$deal_repository = $this->em->getRepository('DeskPRO:Deal');
                $deal = $this->getDealOr404($this->in->getString('deal_id'));
                $name = $this->in->getString('name');
                $organization = false;
                
		if ($org_id) {
			$organization = $this->em->find('DeskPRO:Organization', $org_id);
		}else if($name){
                    $organization = $this->em->getRepository('DeskPRO:Organization')->findOneByName($name);

                }
                
                if(!$organization)
                {
                    $organization = new Organization();
                    $organization->name = $name;
                }

                $this->em->persist($organization);                

		// Checked if the person already added to the deal.
              if($deal_repository->findOrganizationInDeal($organization, $this->in->getString('deal_id')) <= 0)
              {
                  $deal->addOrganizations($organization);
                  $this->em->persist($deal);
                  $this->em->flush();

                  return $this->render('AgentBundle:Deal:org-li.html.twig', array(
			'organization' => $organization
                  ));
              }else{
                  return new Response('failed');
              }
        }


        /**
	 * @return Application\DeskPRO\Entity\Deal
	 */
	protected function getDealOr404($deal_id)
	{
		try {
			$deal = $this->em->find('DeskPRO:Deal', $deal_id);
		} catch (\Doctrine\ORM\NoResultException $e) {
			throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException("There is no deal with ID $deal_id");
		}

		return $deal;
	}

}