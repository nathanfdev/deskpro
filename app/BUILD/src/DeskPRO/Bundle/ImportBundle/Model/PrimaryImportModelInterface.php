<?php

namespace DeskPRO\Bundle\ImportBundle\Model;

/**
 * Exporting entity interface.
 *
 * Interface EntityInterface
 */
interface PrimaryImportModelInterface extends OidAwareModelInterface
{
    /**
     * Returns raw data.
     *
     * @return array
     */
    public function getRawData();

    /**
     * Set raw data.
     *
     * @param array $raw_data
     */
    public function setRawData($raw_data);

    /**
     * Set entity oid.
     *
     * @param int|string $oid
     *
     * @return $this
     */
    public function setOid($oid);

    /**
     * @param string $prefix
     *
     * @return $this
     */
    public function setOidPrefix($prefix);

    /**
     * @return string
     */
    public function getOidPrefix();
}
