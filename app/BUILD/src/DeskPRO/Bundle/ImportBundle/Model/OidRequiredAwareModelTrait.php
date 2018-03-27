<?php

namespace DeskPRO\Bundle\ImportBundle\Model;

use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class OidAwareModelTrait.
 */
trait OidRequiredAwareModelTrait
{
    /**
     * @var int|string
     *
     * @JMS\Type("string")
     *
     * @Assert\NotBlank()
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
