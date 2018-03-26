<?php

namespace DeskPRO\Bundle\ImportBundle\Model;

/**
 * Class BatcnPointer.
 */
class BatcnPointer
{
    /**
     * @var string
     */
    private $modelClass;

    /**
     * @var int
     */
    private $batchId;

    /**
     * Constructor.
     *
     * @param string $modelClass
     * @param int    $batchId
     */
    public function __construct($modelClass, $batchId)
    {
        $this->modelClass = $modelClass;
        $this->batchId    = $batchId;
    }

    /**
     * @return string
     */
    public function getModelClass()
    {
        return $this->modelClass;
    }

    /**
     * @return int
     */
    public function getBatchId()
    {
        return $this->batchId;
    }
}
