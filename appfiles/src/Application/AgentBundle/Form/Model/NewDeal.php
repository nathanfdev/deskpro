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
    public $deal_type;
    public $deal_stage;
    public $organizations;
    public $deal_currency;
    public $probability;
    public $deal_value;

    public $attach = array();

    /**
     * @var \Doctrine\ORM\EntityManager
     */
    protected $_em;
    protected $_person_context;
    protected $_deal;

    public function __construct(Person $person_context)
    {
            $this->person = new NewDealPerson();
            $this->organizations = new NewDealOrganization();
            $this->_person_context = $person_context;
    }

    public function save()
    {

        $em = App::getOrm();
		//$em->beginTransaction();

		#------------------------------
		# The user owner
		#------------------------------
 
		if ($this->person->id) {
                    
			$person = $em->find('DeskPRO:Person', $this->person->id);
		} else {
			$person = $em->getRepository('DeskPRO:Person')->findOneByEmail($this->person->email_address);
		}

		if (!$person) {
			$person = new Person();
			$person->addEmailAddressString($this->person->email_address);
		}

                if (!$person->name && $this->person->name) {
			$person->name = $this->person->name;
		}

		$em->persist($person);
                //$em->flush();

                if($this->organizations->id)
                {
                    $org = $em->find('DeskPRO:Organization',$this->organizations->id);
                }else if($this->organizations->name){
                
                    $org = $em->getRepository('DeskPRO:Organization')->findOneByName($this->organizations->name);
                }
                if (!$org) {
                        $org = new Organization();
                        $org['name'] = $this->organizations->name;
                }

                $em->persist($org);
                //$em->flush();
                
                
                #------------------------------
		# Deal
		#------------------------------

                $deal = new Deal();
                
                $deal->setDealTypeId($this->deal_type);
                $deal->setDealStageId($this->deal_stage);
                $deal->setPersonId($this->_person_context->id);
                $deal->setAsignedAgentId($this->agent_id);
                $deal['title'] = $this->title;
                $deal->setDealCurrencyId($this->deal_currency);
                $deal['probability'] = $this->probability;
                $deal['deal_value'] = $this->deal_value;
                $deal->addOrganizations($org);
                $deal->addPeoples($person);

                $em->persist($deal);
                

                // Deal Attachments
		foreach ($this->attach as $blob_id) {

			$blob = App::getOrm()->getRepository('DeskPRO:Blob')->find($blob_id);

			$attach = new DealAttachment();
			$attach['blob'] = $blob;
			$attach['person'] = $this->_person_context;
                        $attach['deal'] = $deal;

                        $em->persist($attach);
		}
                
                $em->flush();
                $this->_deal = $deal;
    }

    public function getDeal()
    {
        return $this->_deal;
    }
}