<?php

namespace DeskPRO\Bundle\VoiceBundle\TaskRouter\Model;

/**
 * Class TaskQueue.
 */
class TaskQueue extends AbstractModel
{
    /**
     * @var string
     */
    protected $type;

    /**
     * @var string
     */
    protected $typeId;

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param string $type
     *
     * @return $this
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * @return string
     */
    public function getTypeId()
    {
        return $this->typeId;
    }

    /**
     * @param string $typeId
     *
     * @return $this
     */
    public function setTypeId($typeId)
    {
        $this->typeId = $typeId;

        return $this;
    }
}
