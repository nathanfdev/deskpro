<?php

/**
 * DeskPRO.
 */

namespace DpTestSrc\TestBundle;

/**
 * AbstractDbSet does so much work that is is basically what this class *should* be, but this is just a service we
 * use in test code to just install a db set.
 */
class DataSetManager
{
    /**
     * @var DataSet\DataSetInterface[]
     */
    private $dataSets;

    public function __construct(array $dataSets)
    {
        $this->dataSets = $dataSets;
    }

    public function install($dbSetId, $recreateStructure = false)
    {
        foreach ($this->dataSets as $dbSet) {
            if ($dbSetId === $dbSet->getId()) {
                $dbSet->install($recreateStructure);

                return;
            }
        }

        throw new \InvalidArgumentException('data set "'.$dbSetId.'" not found');
    }
}
