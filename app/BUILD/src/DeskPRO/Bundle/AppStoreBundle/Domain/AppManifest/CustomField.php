<?php

namespace DeskPRO\Bundle\AppStoreBundle\Domain\AppManifest;

use JMS\Serializer\Annotation as JMS;


class CustomField
{
    /**
     * @JMS\Type("string")
     *
     * @var string
     */
    private $type;

    /**
     * @JMS\Type("string")
     * @JMS\SerializedName("attachedTo")
     *
     * @var string
     */
    private $attachedTo;

    /**
     * @JMS\Type("string")
     *
     * @var string
     */
    private $alias;

    /**
     * @JMS\Type("string")
     *
     * @var string
     */
    private $title;

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param string $type
     */
    public function setType( $type )
    {
        $this->type = $type;
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
    public function setAlias( $alias )
    {
        $this->alias = $alias;
    }

    /**
     * @return string
     */
    public function getAttachedTo()
    {
        return $this->attachedTo;
    }

    /**
     * @param string $attachedTo
     */
    public function setAttachedTo( $attachedTo )
    {
        $this->attachedTo = $attachedTo;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param string $title
     */
    public function setTitle( $title )
    {
        $this->title = $title;
    }

}
