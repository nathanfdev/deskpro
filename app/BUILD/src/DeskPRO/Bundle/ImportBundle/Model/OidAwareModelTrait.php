<?php

namespace DeskPRO\Bundle\ImportBundle\Model;

use JMS\Serializer\Annotation as JMS;

/**
 * Class OidAwareModelTrait.
 */
trait OidAwareModelTrait
{
    /**
     * @var int|string
     *
     * @JMS\Type("string")
     */
    protected $oid;

    /**
     * {@inheritdoc}
     */
    public function getOid()
    {
        return $this->oid;
    }

    /**
     * {@inheritdoc}
     */
    public function setOid($oid)
    {
        $this->oid = $oid;

        return $this;
    }
}
