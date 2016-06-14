<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2015, DeskPRO Ltd.
 *
 * The license agreement under which this software is released
 * can be found at https://www.deskpro.com/eula/
 *
 * By using this software, you acknowledge having read the license
 * and agree to be bound thereby.
 *
 * Please note that DeskPRO is not free software. We release the full
 * source code for our software because we trust our users to pay us for
 * the huge investment in time and energy that has gone into both creating
 * this software and supporting our customers. By providing the source code
 * we preserve our customers' ability to modify, audit and learn from our
 * work. We have been developing DeskPRO since 2001, please help us make it
 * another decade.
 *
 * Like the work you see? Think you could make it better? We are always
 * looking for great developers to join us: http://www.deskpro.com/jobs/
 *
 * ~ Thanks, Everyone at Team DeskPRO
 */

namespace Application\ImportBundle\Entity;

use Orb\Util\Strings;
use Symfony\Component\Validator\Constraints;
use Symfony\Component\Validator\Mapping\ClassMetadata;

/**
 * Base exporting entity.
 *
 * Class AbstractEntity
 */
abstract class AbstractEntity implements EntityInterface
{
    /**
     * @var array
     */
    protected $raw_data = array();

    /**
     * @var string
     */
    protected $import_map_key;

    /**
     * @var int|string
     */
    protected $oid;

    /**
     * @var string
     */
    protected $destination;

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
    public function setRawData($raw_data)
    {
        $this->raw_data = $raw_data;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getImportMapKey()
    {
        return $this->import_map_key;
    }

    /**
     * {@inheritdoc}
     */
    public function setImportMapKey($import_map_key)
    {
        $this->import_map_key = $import_map_key;

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
     * Set entity oid.
     *
     * @param int|string $oid
     *
     * @return $this
     */
    public function setOid($oid)
    {
        $this->oid = $oid;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getDestination()
    {
        return $this->destination;
    }

    /**
     * Set entity destination
     * It could be a file name or db name.
     *
     * @param string $destination
     *
     * @return $this
     */
    public function setDestination($destination)
    {
        $this->destination = $destination;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getDestinationPrefix()
    {
        $class = new \ReflectionClass($this);
        $name  = $class->getShortName();

        return Strings::camelCaseToUnderscore($name);
    }

    /**
     * Validator class metadata.
     *
     * @param ClassMetadata $metadata
     */
    public static function loadValidatorMetadata(ClassMetadata $metadata)
    {
        $metadata
            ->addPropertyConstraint('oid', new Constraints\NotBlank())
            ->addPropertyConstraint('destination', new Constraints\NotBlank())
        ;
    }
}
