<?php

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
