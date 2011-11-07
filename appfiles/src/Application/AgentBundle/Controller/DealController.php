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
        $my_open_deals = $deal_repository->findOenDealsForPerson($person);
        $my_total_opendeals = count($my_open_deals);

        $other_open_deals = $deal_repository->findOenDealsForOther($person);
        $other_total_opendeals = count($other_open_deals);
        $section_html = $this->renderView('AgentBundle:Deal:window-section.html.twig',array(
            'myopendeals' => $my_open_deals,
            'my_total_opendeals' => $my_total_opendeals,
            'otheropendeals' => $other_open_deals,
            'other_total_opendeals' => $other_total_opendeals
        ));

        return $this->createJsonResponse(array(
            'section_html' => $section_html,
        ));
    }

    private function _loadModels() {

        $this->_entityManager = $this->get('doctrine')->getEntityManager();
        $this->_currentUser = $user = App::getCurrentPerson();
        $this->_deal_repository = App::getEntityRepository('DeskPRO:Deal');
    }


}