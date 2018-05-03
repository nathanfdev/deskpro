<?php

namespace DeskPRO\Bundle\AppBundle\Serializer\Sideload;

/**
 * Class InlineSideload.
 */
class InlineEntitySideload
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var string
     */
    private $type;

    /**
     * Constructor.
     *
     * @param $type
     * @param $id
     */
    public function __construct($type, $id)
    {
        $this->type = $type;
        $this->id   = $id;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }
}
