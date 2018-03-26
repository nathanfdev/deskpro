<?php

namespace DeskPRO\Bundle\ImportBundle\Model;

use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Base exporting entity.
 *
 * Class AbstractEntity
 */
trait PrimaryImportModelTrait
{
    /**
     * @var array
     *
     * @JMS\Exclude()
     *
     * @Assert\NotBlank()
     */
    protected $raw_data = [];

    /**
     * @var int|string
     *
     * @JMS\Exclude()
     *
     * @Assert\NotBlank()
     */
    protected $oid;

    /**
     * @var string
     *
     * @JMS\Type("string")
     */
    protected $oid_prefix;

    /**
     * {@inheritdoc}
     */
    public function getRawData()
    {
        return $this->raw_data;
    }

    /**
     * {@inheritdoc}
     */
    public function setRawData($rawData)
    {
        $this->raw_data = $rawData;

        return $this;
    }

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

    /**
     * {@inheritdoc}
     */
    public function setOidPrefix($prefix)
    {
        $this->oid_prefix = $prefix;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getOidPrefix()
    {
        return $this->oid_prefix;
    }
}
