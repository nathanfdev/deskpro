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

namespace DeskPRO\Bundle\AppBundle\Entity\ObjectAlias;

use DeskPRO\Bundle\AppBundle\Entity\AppStore\AppInstance;
use DeskPRO\Bundle\AppBundle\ObjectAlias;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;

/**
 * @ORM\Entity
 * @ORM\Table(
 *  name="object_aliases", uniqueConstraints={
 *     @ORM\UniqueConstraint(name="unique_alias", columns={"alias", "app_instance_id"})
 *  })
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="object_type", type="string")
 * @ORM\DiscriminatorMap({
 *     "custom_def_ticket" = "CustomTicketFieldDefinitionAlias",
 *     "custom_def_organization" = "CustomOrganizationFieldDefinitionAlias",
 *     "custom_def_people" = "CustomPeopleFieldDefinitionAlias",
 *     "ticket_triggers" = "TicketTriggerAlias"
 * })
 * @JMS\ExclusionPolicy("all")
 */
abstract class AbstractAlias implements ObjectAlias\ObjectAliasInterface
{
    /**
     * @ORM\Id()
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="IDENTITY")
     *
     * @JMS\Expose()
     * @JMS\Type("integer")
     *
     * @var integer
     */
    private $id;

    /**
     * @ORM\Column(type="string", nullable=false, length=200)
     *
     * @JMS\Expose()
     * @JMS\Type("string")
     *
     * @var string
     */
    private $alias;

    /**
     * @ORM\ManyToOne(targetEntity="DeskPRO\Bundle\AppBundle\Entity\AppStore\AppInstance")
     * @ORM\JoinColumn(name="app_instance_id", referencedColumnName="id", nullable=true, onDelete="CASCADE")
     *
     * @JMS\Expose()
     * @JMS\Type("entity<DeskPRO\Bundle\AppBundle\Entity\AppStore\AppInstance>")
     *
     * @var AppInstance
     */
    private $appInstance;

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return string
     */
    public function getAlias()
    {
        return $this->alias;
    }

    /**
     * @param string $alias
     */
    public function setAlias($alias)
    {
        $this->alias = $alias;
    }

    /**
     * @return AppInstance
     */
    public function getAppInstance()
    {
        return $this->appInstance;
    }

    /**
     * @param AppInstance $app
     */
    public function setAppInstance( AppInstance $app)
    {
        $this->appInstance = $app;
    }

    /**
     * @return string[]
     */
    public function getQualifiers()
    {
        $qualifiers = [];

        if ($this->appInstance) {
            $qualifiers[] = ['app', $this->appInstance->getId()];
        }

        return $qualifiers;
    }

    /**
     * @return mixed
     */
    abstract public function getObject();

    /**
     * @param mixed $object
     * @return boolean
     */
    abstract public function tryAndSetObject($object);
}
