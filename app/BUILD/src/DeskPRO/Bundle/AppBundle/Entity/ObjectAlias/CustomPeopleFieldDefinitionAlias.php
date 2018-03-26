<?php

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
