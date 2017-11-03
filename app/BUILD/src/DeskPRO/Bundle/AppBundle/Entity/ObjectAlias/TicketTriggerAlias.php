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
use Application\DeskPRO\Entity\TicketTrigger;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;

/**
 * @ORM\Entity(repositoryClass="DeskPRO\Bundle\AppBundle\Entity\ObjectAlias\Repository")
 * @JMS\ExclusionPolicy("all")
 */
class TicketTriggerAlias extends AbstractAlias
{
    /**
     * @ORM\ManyToOne(targetEntity="Application\DeskPRO\Entity\TicketTrigger", inversedBy="aliases")
     * @ORM\JoinColumn(name="ticket_triggers_id", referencedColumnName="id", nullable=true)
     *
     * @JMS\Expose()
     * @JMS\Type("entity<Application\DeskPRO\Entity\TicketTrigger>")
     *
     * @var TicketTrigger
     */
    private $object;

    /**
     * @return TicketTrigger
     */
    public function getObject()
    {
        return $this->object;
    }

    /**
     * @param TicketTrigger $object
     */
    public function setObject(TicketTrigger $object)
    {
        $this->object = $object;
    }

    /**
     * @return string
     */
    public function getObjectType()
    {
        return TicketTrigger::class;
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
}
