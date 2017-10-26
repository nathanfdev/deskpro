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
use Application\DeskPRO\Entity\CustomDefPerson;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;

/**
 * @ORM\Entity(repositoryClass="DeskPRO\Bundle\AppBundle\Entity\ObjectAlias\Repository")
 * @JMS\ExclusionPolicy("all")
 */
class CustomPeopleFieldDefinitionAlias extends AbstractAlias
{
    /**
     * @ORM\ManyToOne(targetEntity="Application\DeskPRO\Entity\CustomDefPerson", inversedBy="aliases")
     * @ORM\JoinColumn(name="custom_def_people_id", referencedColumnName="id", nullable=true)
     *
     * @JMS\Expose()
     * @JMS\Type("entity<Application\DeskPRO\Entity\CustomDefPerson>")
     *
     * @var CustomDefPerson
     */
    private $object;

    /**
     * @return CustomDefPerson
     */
    public function getObject()
    {
        return $this->object;
    }

    /**
     * @param CustomDefPerson $object
     */
    public function setObject(CustomDefPerson $object)
    {
        $this->object = $object;
    }

    /**
     * @param mixed $object
     * @return boolean
     */
    public function tryAndSetObject( $object )
    {
        try {
            $this->setObject($object);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * @return string
     */
    public function getObjectType()
    {
        return CustomDefPerson::class;
    }

    /**
     * @return string
     */
    public function getObjectId()
    {
        if ($this->object) {
            return (string) $this->object->getId();
        }
    }
}
