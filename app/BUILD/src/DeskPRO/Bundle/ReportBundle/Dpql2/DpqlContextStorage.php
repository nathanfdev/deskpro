<?php

namespace DeskPRO\Bundle\ReportBundle\Dpql2;

/**
 * Class DpqlContextStorage.
 */
class DpqlContextStorage
{
    const MODE_RUN = 0;

    const MODE_EDIT = 1;

    /**
     * @var DpqlContext
     */
    private $context;

    /**
     * @var int the work mode, should not substract timezone when in edit mode
     */
    private $mode = self::MODE_RUN;

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

    /**
     * @return int|null
     */
    public function getMode()
    {
        return $this->mode;
    }

    /**
     * @param int|null $mode
     *
     * @return $this
     */
    public function setMode($mode)
    {
        $this->mode = $mode;

        return $this;
    }
}
