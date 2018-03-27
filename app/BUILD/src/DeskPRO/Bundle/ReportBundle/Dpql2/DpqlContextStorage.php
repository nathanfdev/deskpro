<?php

namespace DeskPRO\Bundle\ReportBundle\Dpql2;

/**
 * Class DpqlContextStorage.
 */
class DpqlContextStorage
{
    /**
     * @var DpqlContext
     */
    private $context;

    /**
     * @return DpqlContext
     */
    public function getContext()
    {
        return $this->context;
    }

    /**
     * @param DpqlContext $context
     */
    public function setContext(DpqlContext $context)
    {
        $this->context = $context;
    }
}
