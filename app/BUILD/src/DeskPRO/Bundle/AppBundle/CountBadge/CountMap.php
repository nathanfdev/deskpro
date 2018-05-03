<?php

namespace DeskPRO\Bundle\AppBundle\CountBadge;

use JMS\Serializer\Annotation as JMS;

/**
 * Class CountMap.
 */
class CountMap extends AbstractCount
{
    /**
     * Nested counts.
     *
     * @JMS\Type("map<DeskPRO\Bundle\AppBundle\CountBadge\CountMap>")
     * @JMS\MaxDepth(0)
     *
     * @var CountMap[]
     */
    protected $nested = [];

    /**
     * @param int    $value
     * @param string $id
     * @param string $type
     * @param string $title
     * @param bool   $sumToValue If need to increase $this->value by nested count value
     *
     * @return $this
     */
    public function addNested($value, $id, $type, $title = '', $sumToValue = false)
    {
        $count = new static();
        $count->setCount($value);
        $count->setId($id);
        $count->setType($type);
        $count->setTitle($title);

        $this->nested[$id] = $count;

        if ($sumToValue) {
            $this->add($value);
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function addNestedInstance(AbstractCount $instance, $sumToValue = false)
    {
        $this->nested[$instance->getId()] = $instance;
        if ($sumToValue) {
            $this->add($instance->getCount());
        }
        if ($instance->getType()) {
            $this->groupedBy = $instance->getType();
        }
    }

    /**
     * @return CountMap[]
     */
    public function getNested()
    {
        return $this->nested;
    }
}
