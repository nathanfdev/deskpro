<?php

namespace DeskPRO\Bundle\AppBundle\TermEngine\Engine\Php\TermCompiler;

use DeskPRO\Bundle\AppBundle\TermEngine\Engine\AbstractTermCompiler;
use DeskPRO\Bundle\AppBundle\TermEngine\Engine\Php\PhpBuilder\PhpCheck;
use DeskPRO\Bundle\AppBundle\TermEngine\Engine\Php\TermCompiler\Helper\MethodCheckHelper;
use DeskPRO\Bundle\AppBundle\TermEngine\Engine\Php\TermCompiler\Helper\PhpAgentHelper;
use DeskPRO\Bundle\AppBundle\TermEngine\Engine\Php\TermCompiler\Helper\PhpDateHelper;
use DeskPRO\Bundle\AppBundle\TermEngine\Engine\Php\TermCompiler\Helper\PhpStringHelper;
use DeskPRO\Bundle\AppBundle\TermEngine\TermInterface;

/**
 * Class AbstractPhpTermCompiler.
 */
abstract class AbstractPhpTermCompiler extends AbstractTermCompiler
{
    /**
     * Must return a PhpCheck.
     *
     * @param TermInterface $term
     *
     * @return PhpCheck
     */
    public function compile(TermInterface $term)
    {
        $check = parent::compile($term);

        $this->logCheck($check);

        return $check;
    }

    public function logCheck(PhpCheck $check)
    {
        $this->logDebug(
            'Constructed PhpCheck',
            [
                'expression' => $check->getExpression(),
                'vars'       => $check->getVariables(),
            ]
        );
    }

    /**
     * @return MethodCheckHelper
     */
    public function getMethodCheckHelper()
    {
        return $this->helperPool->getHelper('method_check');
    }

    /**
     * @return PhpDateHelper
     */
    public function getDateHelper()
    {
        return $this->helperPool->getHelper('date');
    }

    /**
     * @return PhpStringHelper
     */
    public function getStringHelper()
    {
        return $this->helperPool->getHelper('string');
    }

    /**
     * @return PhpAgentHelper
     */
    public function getAgentHelper()
    {
        return $this->helperPool->getHelper('agent');
    }
}
