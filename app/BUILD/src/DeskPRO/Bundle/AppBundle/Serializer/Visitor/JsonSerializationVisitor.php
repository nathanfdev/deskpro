<?php

namespace DeskPRO\Bundle\AppBundle\Serializer\Visitor;

use JMS\Serializer\JsonSerializationVisitor as BaseVisitor;
use Orb\Util\Strings;

/**
 * Class JsonSerializationVisitor.
 */
class JsonSerializationVisitor extends BaseVisitor
{
    private $options;

    /**
     * {@inheritdoc}
     */
    public function getResult()
    {
        return Strings::jsonEncode($this->getRoot(), $this->options);
    }

    /**
     * {@inheritdoc}
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * {@inheritdoc}
     */
    public function setOptions($options)
    {
        $this->options = (int) $options;
    }
}
