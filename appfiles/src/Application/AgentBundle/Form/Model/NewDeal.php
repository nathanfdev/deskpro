<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @subpackage AgentBundle
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Abdullah Kiser <kiser.bd@gmail.com>
 */


namespace Application\AgentBundle\Form\Model;

use Application\DeskPRO\App;
use Application\DeskPRO\Entity\Deal;
use Application\DeskPRO\Entity\DealNote;
use Application\DeskPRO\Entity\DealAttachment;
use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\Organization;


class NewDeal
{
    public $person;
    public $title;
    public $agent_id;
    public $attach = array();
    public $deal_type;
    public $deal_stage;

    /**
	 * @var \Doctrine\ORM\EntityManager
	 */
    protected $_em;
    protected $_person_context;

    public function __construct(Person $person_context)
    {
            $this->person = new NewTicketPerson();
            $this->_person_context = $person_context;
    }
}