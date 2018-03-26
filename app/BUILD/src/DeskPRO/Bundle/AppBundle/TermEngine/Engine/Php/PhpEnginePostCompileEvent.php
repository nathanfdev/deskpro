<?php

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\AppBundle\TermEngine\Engine\Php;

use DeskPRO\Bundle\AppBundle\Entity\FilterInterface;
use DeskPRO\Bundle\AppBundle\TermEngine\Engine\Php\PhpBuilder\PhpCheck;
use DeskPRO\Bundle\AppBundle\TermEngine\Engine\TermEngineContext;

class PhpEnginePostCompileEvent extends PhpEngineEvent
{
    /**
     * @var Filter
     */
    private $filter;

    /**
     * @var \DeskPRO\Bundle\AppBundle\TermEngine\Engine\Php\PhpBuilder\PhpClass
     */
    private $php_check;

    public function __construct(TermEngineContext $context, FilterInterface $filter, PhpCheck $php_check)
    {
        parent::__construct($context);
        $this->filter    = $filter;
        $this->php_check = $php_check;
    }

    /**
     * Retrieve the filter.
     *
     * @return FilterInterface the filter
     */
    public function getFilter()
    {
        return $this->filter;
    }

    /**
     * @return \DeskPRO\Bundle\AppBundle\TermEngine\Engine\Php\PhpBuilder\PhpClass
     */
    public function getPhpCheck()
    {
        return $this->php_check;
    }
}
