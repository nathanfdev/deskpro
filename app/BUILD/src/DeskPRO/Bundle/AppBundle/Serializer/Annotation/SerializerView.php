<?php

namespace DeskPRO\Bundle\AppBundle\Serializer\Annotation;

use FOS\RestBundle\Controller\Annotations\View;

/**
 * We extend FOSRest View annotation to define serializer additional configuration params.
 *
 * @Annotation
 * @Target({"METHOD","CLASS"})
 */
class SerializerView extends View
{
    /**
     * @var array
     */
    protected $mapping = [];

    /**
     * @var bool
     */
    protected $serializeNull = true;

    /**
     * @return bool
     */
    public function isSerializeNull()
    {
        return $this->serializeNull;
    }

    /**
     * @param bool $serializeNull
     *
     * @return $this
     */
    public function setSerializeNull($serializeNull)
    {
        $this->serializeNull = $serializeNull;

        return $this;
    }

    /**
     * @return array
     */
    public function getMapping()
    {
        return $this->mapping;
    }

    /**
     * @param array $mapping
     */
    public function setMapping(array $mapping)
    {
        $this->mapping = $mapping;
    }
}
