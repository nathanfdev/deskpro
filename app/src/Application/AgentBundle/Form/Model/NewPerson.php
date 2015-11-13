<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2015, DeskPRO Ltd.
 *
 * The license agreement under which this software is released
 * can be found at https://www.deskpro.com/eula/
 *
 * By using this software, you acknowledge having read the license
 * and agree to be bound thereby.
 *
 * Please note that DeskPRO is not free software. We release the full
 * source code for our software because we trust our users to pay us for
 * the huge investment in time and energy that has gone into both creating
 * this software and supporting our customers. By providing the source code
 * we preserve our customers' ability to modify, audit and learn from our
 * work. We have been developing DeskPRO since 2001, please help us make it
 * another decade.
 *
 * Like the work you see? Think you could make it better? We are always
 * looking for great developers to join us: http://www.deskpro.com/jobs/
 *
 * ~ Thanks, Everyone at Team DeskPRO
 */

/**
 * DeskPRO.
 */
namespace Application\AgentBundle\Form\Model;

use Application\DeskPRO\App;
use Application\DeskPRO\Entity\Language;
use Application\DeskPRO\Entity\Organization;
use Application\DeskPRO\Entity\Person;

class NewPerson
{
    /** @var string */
    public $name;
    /** @var string */
    public $email;

    /** @var int */
    public $organization_id;
    /** @var string */
    public $organization_position;

    /** @var string */
    public $new_organization;

    /** @var array */
    public $labels = array();
    /** @var array */
    public $usergroup_ids = array();
    /** @var array */
    public $custom_fields = array();

    /** @var string */
    public $timezone;

    /** @var string */
    public $password;

    /** @var Language */
    public $language;

    /** @var Person */
    protected $_person;

    /**
     * @var \Doctrine\ORM\EntityManager
     */
    protected $_em;

    public function __construct(Person $person_context)
    {
        $this->_person_context = $person_context;

        $this->_em = App::getOrm();
    }

    public function setCustomFieldForm(array $form)
    {
        $this->custom_fields = isset($form['newperson_custom_fields']) ? $form['newperson_custom_fields'] : array();
    }

    public function save()
    {
        $this->_em->beginTransaction();

        $person = new Person();
        $person->getLabelManager()->setLabelsArray($this->labels);

        if ($this->name) {
            $person->name = $this->name;
        }

        if ($this->email) {
            $person->setEmail($this->email, true);
        }

        if ($this->timezone) {
            $person->timezone = $this->timezone;
        }

        if ($this->password) {
            $person->setPassword($this->password);
        }

        if ($this->language) {
            $person->language = $this->language;
        }

        if ($this->organization_id) {
            $org = $this->_em->find('DeskPRO:Organization', $this->organization_id);
            if ($org) {
                $person->organization          = $org;
                $person->organization_position = $this->organization_position;
            }
        }

        foreach ($this->usergroup_ids as $ug_id) {
            $ug = $this->_em->find('DeskPRO:Usergroup', $ug_id);
            if ($ug_id) {
                $person->usergroups->add($ug);
            }
        }

        $person->creation_system = Person::CREATED_WEB_AGENT;

        $this->_em->persist($person);
        $this->_em->flush();

        if ($this->custom_fields) {
            $manager = App::$container->getPersonFieldManager();
            $manager->saveFormToObject($this->custom_fields, $person);
        }

        $this->_em->flush();
        $this->_em->commit();

        if ($this->new_organization && $this->_person_context->hasPerm('agent_org.create')) {
            $org       = new Organization();
            $org->name = $this->new_organization;
            $this->_em->persist($org);

            $person->organization          = $org;
            $person->organization_position = $this->organization_position;
        }

        $this->_person = $person;
    }

    public function getPerson()
    {
        return $this->_person;
    }
}
