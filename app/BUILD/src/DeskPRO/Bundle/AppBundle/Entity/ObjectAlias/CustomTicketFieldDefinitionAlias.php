<?php

namespace DeskPRO\Bundle\AppBundle\Entity\ObjectAlias;

use Application\DeskPRO\Entity\CustomDefTicket;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;

/**
 * @ORM\Entity(repositoryClass="DeskPRO\Bundle\AppBundle\Entity\ObjectAlias\Repository")
 * @JMS\ExclusionPolicy("all")
 */
class CustomTicketFieldDefinitionAlias extends AbstractAlias
{
    /**
     * @ORM\ManyToOne(targetEntity="Application\DeskPRO\Entity\CustomDefTicket", inversedBy="aliases")
     * @ORM\JoinColumn(name="custom_def_ticket_id", referencedColumnName="id", nullable=true)
     *
     * @JMS\Expose()
     * @JMS\Type("entity<Application\DeskPRO\Entity\CustomDefTicket>")
     *
     * @var CustomDefTicket
     */
    private $object;

    /**
     * @return CustomDefTicket
     */
    public function getObject()
    {
        return $this->object;
    }

    /**
     * @param CustomDefTicket $object
     */
    public function setObject(CustomDefTicket $object)
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
        return CustomDefTicket::class;
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
