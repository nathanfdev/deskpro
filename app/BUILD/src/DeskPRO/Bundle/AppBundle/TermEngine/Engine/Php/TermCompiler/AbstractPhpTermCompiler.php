<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2016, DeskPRO Ltd.
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

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\AppBundle\TermEngine\Engine\Php\TermCompiler;

use DeskPRO\Bundle\AppBundle\TermEngine\Engine\AbstractTermCompiler;
use DeskPRO\Bundle\AppBundle\TermEngine\Engine\Php\PhpBuilder\PhpCheck;
use DeskPRO\Bundle\AppBundle\TermEngine\Engine\Php\TermCompiler\Helper\MethodCheckHelper;
use DeskPRO\Bundle\AppBundle\TermEngine\TermInterface;

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

    public function turnArrayIntoPhpArrayString(array $values = [])
    {
        return 'array('.implode(',', $values).')';
    }
}
