<?php

namespace DeskPRO\Bundle\VoiceBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 * @ORM\Table(name="voice_task_queues")
 *
 * Class TaskQueue.
 */
class TaskQueue extends AbstractEntity
{
    /**
     * @ORM\Column(name="type", type="string")
     *
     * @var string
     */
    protected $type;

    /**
     * @ORM\Column(name="type_id", type="integer")
     *
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
        $this->setModelField('type', $type);

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
        $this->setModelField('typeId', $typeId);

        return $this;
    }
}
