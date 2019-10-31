<?php

namespace DeskPRO\Bundle\AppBundle\Entity\ObjectAlias;

use Application\DeskPRO\Entity\CustomDefChat;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;

/**
 * @ORM\Entity(repositoryClass="DeskPRO\Bundle\AppBundle\Entity\ObjectAlias\Repository")
 * @JMS\ExclusionPolicy("all")
 */
class CustomChatFieldDefinitionAlias extends AbstractAlias
{
    /**
     * @ORM\ManyToOne(targetEntity="Application\DeskPRO\Entity\CustomDefChat", inversedBy="aliases")
     * @ORM\JoinColumn(name="custom_def_chat_id", referencedColumnName="id", nullable=true)
     *
     * @JMS\Expose()
     * @JMS\Type("entity<Application\DeskPRO\Entity\CustomDefChat>")
     *
     * @var CustomDefChat
     */
    private $object;

    /**
     * @return CustomDefChat
     */
    public function getObject()
    {
        return $this->object;
    }

    /**
     * @param CustomDefChat $object
     */
    public function setObject(CustomDefChat $object)
    {
        $this->object = $object;
    }

    /**
     * @param mixed $object
     *
     * @return bool
     */
    public function tryAndSetObject($object)
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
        return CustomDefChat::class;
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
