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
use Application\DeskPRO\Entity\Task;
use Application\DeskPRO\Entity\TaskComment;
use Application\AgentBundle\Form\Type\NewTask;



/**
 * Handles viewing and editing deals
 */
class DealController extends AbstractController
{
    private $_entityManager;
    private $_currentUser;
    private $_task_repository;


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

        $other_open_deals = $deal_repository->findDealsForOther();
        $other_total_opendeals = $deal_repository->countDealsForOther();

        $other_won_deals = $deal_repository->findDealsForOther(1);
        $other_total_wondeals = $deal_repository->countDealsForOther(1);

        $other_lost_deals = $deal_repository->findDealsForOther(2);
        $other_total_lostdeals = $deal_repository->countDealsForOther(2);

        
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
                $deals = $deal_repository->filterDealsForOther(0, $deal_type_id);
            }
            else if($deal_status == 'close'){
                $deals = $deal_repository->filterDealsForOther(-1, $deal_type_id);
            }else if($deal_status == 'won'){
                $deals = $deal_repository->filterDealsForOther(1, $deal_type_id);
            }else if($deal_status == 'lost'){
                $deals = $deal_repository->filterDealsForOther(2, $deal_type_id);
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

        $tpl = 'AgentBundle:Deal:deal-view.html.twig';
        return $this->render($tpl, array(
            'deal' => $deal
        ));
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