<?php

namespace DeskPRO\Bundle\AppBundle\Entity\ObjectAlias;
use Application\DeskPRO\Entity\CustomDefOrganization;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;

/**
 * @ORM\Entity(repositoryClass="DeskPRO\Bundle\AppBundle\Entity\ObjectAlias\Repository")
 * @JMS\ExclusionPolicy("all")
 */
class CustomOrganizationFieldDefinitionAlias extends AbstractAlias
{
    /**
     * @ORM\ManyToOne(targetEntity="Application\DeskPRO\Entity\CustomDefOrganization", inversedBy="aliases")
     * @ORM\JoinColumn(name="custom_def_organization_id", referencedColumnName="id", nullable=true)
     *
     * @JMS\Expose()
     * @JMS\Type("entity<Application\DeskPRO\Entity\CustomDefOrganization>")
     *
     * @var CustomDefOrganization
     */
    private $object;

    /**
     * @return CustomDefOrganization
     */
    public function getObject()
    {
        return $this->object;
    }

    /**
     * @param CustomDefOrganization $object
     */
    public function setObject(CustomDefOrganization $object)
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
        return CustomDefOrganization::class;
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
