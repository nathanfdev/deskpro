<?php

/**
 * DeskPRO.
 */

namespace Application\AgentBundle\Form\Model;

use Application\DeskPRO\App;
use Application\DeskPRO\Entity\Organization;
use Application\DeskPRO\Entity\Person;

class NewOrganization
{
    /** @var string */
    public $name;
    /** @var array */
    public $labels = [];
    /** @var array */
    public $usergroup_ids = [];
    /** @var array */
    public $custom_fields = [];
    /** @var Organization */
    protected $_org;

    /**
     * @var \Doctrine\ORM\EntityManager
     */
    protected $_em;

    public function __construct(Person $person_context = null)
    {
        $this->_person_context = $person_context;

        $this->_em = App::getOrm();
    }

    public function setCustomFieldForm(array $form)
    {
        $this->custom_fields = isset($form['org_custom_fields']) ? $form['org_custom_fields'] : [];
    }

    public function save()
    {
        $this->_em->beginTransaction();

        $org       = new Organization();
        $org->name = $this->name;

        foreach ($this->usergroup_ids as $ug_id) {
            $ug = $this->_em->find('DeskPRO:Usergroup', $ug_id);
            if ($ug_id) {
                $org->usergroups->add($ug);
            }
        }

        $this->_em->persist($org);
        $this->_em->flush();

        if ($this->custom_fields) {
            $field_manager = App::getSystemService('org_fields_manager');
            $field_manager->saveFormToObject($this->custom_fields, $org);
        }

        $this->_em->flush();
        $this->_em->commit();

        $org->getLabelManager()->setLabelsArray($this->labels);
        $this->_em->flush();

        return $this->_org = $org;
    }

    public function getOrganization()
    {
        return $this->_org;
    }
}
