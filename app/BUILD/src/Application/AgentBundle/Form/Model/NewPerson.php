<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2017, DeskPRO Ltd.
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
use Application\DeskPRO\Entity\Usergroup;
use Doctrine\ORM\EntityManager;

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
    public $labels = [];
    /** @var array */
    public $usergroup_ids = [];
    /** @var array */
    public $custom_fields = [];

    /** @var string */
    public $timezone;

    /** @var string */
    public $password;

    /** @var Language */
    public $language;

    /** @var Person */
    protected $person;

    /**
     * @var \Doctrine\ORM\EntityManager
     */
    protected $em;

    public function __construct(Person $personContext, EntityManager $entityManager)
    {
        $this->personContext = $personContext;
        $this->em            = $entityManager;
    }

    public function setCustomFieldForm(array $form)
    {
        $this->custom_fields = isset($form['newperson_custom_fields']) ? $form['newperson_custom_fields'] : [];
    }

    public function save()
    {
        $this->em->beginTransaction();

        $person = new Person();

        if ($this->name) {
            $person->setName($this->name);
        }

        if ($this->email) {
            $person->setEmail($this->email, true);
        }

        if ($this->timezone) {
            $person->setTimezone($this->timezone);
        }

        if ($this->password) {
            $person->setPassword($this->password);
        }

        if ($this->language) {
            $person->setLanguage($this->language);
        }

        $org = null;
        if ($this->organization_id) {
            $org = $this->em->find(Organization::class, $this->organization_id);
            if ($org) {
                $person->setOrganization($org)->setOrganizationPosition($this->organization_position);
            }
        }

        foreach ($this->usergroup_ids as $ug_id) {
            $ug = $this->em->find(Usergroup::class, $ug_id);
            if ($ug_id) {
                $person->addUsergroup($ug);
            }
        }

        $person->setCreationSystem(Person::CREATED_WEB_AGENT);

        $this->em->persist($person);
        $this->em->flush();

        if ($this->custom_fields) {
            $manager = App::$container->getPersonFieldManager();
            $manager->saveFormToObject($this->custom_fields, $person);
        }

        $this->em->flush();
        $this->em->commit();

        $person->getLabelManager()->setLabelsArray($this->labels);
        $this->em->flush();

        if (!$org && $this->new_organization && $this->personContext->hasPerm('agent_org.create')) {
            $org = new Organization();
            $org->setName($this->new_organization);
            $this->em->persist($org);
            $person->setOrganization($org)->setOrganizationPosition($this->organization_position);
        }

        $this->person = $person;
    }

    public function getPerson()
    {
        return $this->person;
    }
}
