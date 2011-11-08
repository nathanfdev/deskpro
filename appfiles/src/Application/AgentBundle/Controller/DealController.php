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
        $my_total_opendeals = count($my_open_deals);

        $my_won_deals = $deal_repository->findDealsForPerson($person, 1);
        $my_total_wondeals = count($my_won_deals);

        $my_lost_deals = $deal_repository->findDealsForPerson($person, 2);
        $my_total_lostdeals = count($my_lost_deals);

        $other_open_deals = $deal_repository->findDealsForOther();
        $other_total_opendeals = count($other_open_deals);

        $other_won_deals = $deal_repository->findDealsForOther(1);
        $other_total_wondeals = count($other_won_deals);

        $other_lost_deals = $deal_repository->findDealsForOther(2);
        $other_total_lostdeals = count($other_lost_deals);

        
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

}