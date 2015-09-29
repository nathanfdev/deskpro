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

/**
 * DeskPRO.
 */
namespace DeskPRO\Bundle\AppBundle\TermEngine\Engine\Dbal\TermCompiler;

use DeskPRO\Bundle\AppBundle\TermEngine\Engine\AbstractTermCompiler;
use DeskPRO\Bundle\AppBundle\TermEngine\Engine\Dbal\Query\DbalQueryPart;
use DeskPRO\Bundle\AppBundle\TermEngine\Engine\Dbal\TermCompiler\Helper\DbalDateHelper;
use DeskPRO\Bundle\AppBundle\TermEngine\Engine\Dbal\TermCompiler\Helper\DbalEntityHelper;
use DeskPRO\Bundle\AppBundle\TermEngine\Engine\Dbal\TermCompiler\Helper\DbalNumericHelper;
use DeskPRO\Bundle\AppBundle\TermEngine\Engine\Dbal\TermCompiler\Helper\DbalStringHelper;

abstract class AbstractDbalTermCompiler extends AbstractTermCompiler
{
    public function logQueryPart(DbalQueryPart $part)
    {
        $this->logDebug('Constructed QueryPart', array(
            'where'        => $part->getWhereString(),
            'params'       => $part->getParameters(),
            'joins'        => $part->getJoins(),
            'unique_joins' => $part->getUniqueJoins(),
        ));
    }

    /**
     * @return DbalEntityHelper
     */
    public function getEntityHelper()
    {
        return $this->helper_pool->getHelper('entity');
    }

    /**
     * @return DbalStringHelper
     */
    public function getStringHelper()
    {
        return $this->helper_pool->getHelper('string');
    }

    /**
     * @return DbalDateHelper
     */
    public function getDateHelper()
    {
        return $this->helper_pool->getHelper('date');
    }

    /**
     * @return DbalJoinedHelper
     */
    public function getJoinedHelper()
    {
        return $this->helper_pool->getHelper('joined');
    }

    /**
     * @return DbalNumericHelper
     */
    public function getNumericHelper()
    {
        return $this->helper_pool->getHelper('numeric');
    }
}
