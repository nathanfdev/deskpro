<?php

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\AppBundle\TermEngine\Engine\Dbal\TermCompiler;

use DeskPRO\Bundle\AppBundle\TermEngine\Engine\AbstractTermCompiler;
use DeskPRO\Bundle\AppBundle\TermEngine\Engine\Dbal\Query\DbalQueryPart;
use DeskPRO\Bundle\AppBundle\TermEngine\Engine\Dbal\TermCompiler\Helper\DbalDateHelper;
use DeskPRO\Bundle\AppBundle\TermEngine\Engine\Dbal\TermCompiler\Helper\DbalEntityHelper;
use DeskPRO\Bundle\AppBundle\TermEngine\Engine\Dbal\TermCompiler\Helper\DbalJoinedHelper;
use DeskPRO\Bundle\AppBundle\TermEngine\Engine\Dbal\TermCompiler\Helper\DbalNumericHelper;
use DeskPRO\Bundle\AppBundle\TermEngine\Engine\Dbal\TermCompiler\Helper\DbalStringHelper;

/**
 * Class AbstractDbalTermCompiler.
 */
abstract class AbstractDbalTermCompiler extends AbstractTermCompiler
{
    /**
     * @param DbalQueryPart $part
     */
    public function logQueryPart(DbalQueryPart $part)
    {
        $this->logDebug('Constructed QueryPart', [
            'where'        => $part->getWhereString(),
            'params'       => $part->getParameters(),
            'joins'        => $part->getJoins(),
            'unique_joins' => $part->getUniqueJoins(),
        ]);
    }

    /**
     * @return DbalEntityHelper
     */
    public function getEntityHelper()
    {
        return $this->helperPool->getHelper('entity');
    }

    /**
     * @return DbalStringHelper
     */
    public function getStringHelper()
    {
        return $this->helperPool->getHelper('string');
    }

    /**
     * @return DbalDateHelper
     */
    public function getDateHelper()
    {
        return $this->helperPool->getHelper('date');
    }

    /**
     * @return DbalJoinedHelper
     */
    public function getJoinedHelper()
    {
        return $this->helperPool->getHelper('joined');
    }

    /**
     * @return DbalNumericHelper
     */
    public function getNumericHelper()
    {
        return $this->helperPool->getHelper('numeric');
    }
}
