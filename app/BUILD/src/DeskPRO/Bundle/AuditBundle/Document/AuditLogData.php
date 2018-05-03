<?php

namespace DeskPRO\Bundle\AuditBundle\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use JMS\Serializer\Annotation as JMS;
use Orb\Types\JsonObjectSerializable;

/**
 * Class AuditLogData.
 *
 * @ODM\EmbeddedDocument()
 * @JMS\ExclusionPolicy("none")
 */
class AuditLogData implements JsonObjectSerializable
{
    /**
     * @ODM\Field(type="hash")
     *
     * @JMS\Type("array")
     * @JMS\Groups("details")
     *
     * @var array
     */
    private $context;

    /**
     * @ODM\Field(type="hash")
     *
     * @JMS\Type("array")
     * @JMS\Groups("details")
     *
     * @var array
     */
    private $diff;

    /**
     * @return mixed
     */
    public function getContext()
    {
        return $this->context;
    }

    /**
     * @param mixed $context
     *
     * @return $this
     */
    public function setContext($context)
    {
        $this->context = $context;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getDiff()
    {
        return $this->diff;
    }

    /**
     * @param mixed $diff
     *
     * @return $this
     */
    public function setDiff($diff)
    {
        $this->diff = $diff;

        return $this;
    }

    /**
     * @return array
     */
    public function serializeJsonArray()
    {
        return [
            'context' => $this->context,
            'diff'    => $this->diff,
        ];
    }

    /**
     * @param array $data
     *
     * @return AuditLogData
     */
    public static function unserializeJsonArray(array $data)
    {
        $obj = new self();
        $obj->setDiff($data['diff'])->setContext($data['context']);

        return $obj;
    }
}
