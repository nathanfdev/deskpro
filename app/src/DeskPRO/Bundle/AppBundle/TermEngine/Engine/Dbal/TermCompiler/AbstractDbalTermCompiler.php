<?php
/**************************************************************************\
 * | DeskPRO (r) has been developed by DeskPRO Ltd. http://www.deskpro.com/   |
 * | a British company located in London, England.                            |
 * |                                                                          |
 * | All source code and content Copyright (c) 2012, DeskPRO Ltd.             |
 * |                                                                          |
 * | The license agreement under which this software is released              |
 * | can be found at http://www.deskpro.com/license                           |
 * |                                                                          |
 * | By using this software, you acknowledge having read the license          |
 * | and agree to be bound thereby.                                           |
 * |                                                                          |
 * | Please note that DeskPRO is not free software. We release the full       |
 * | source code for our software because we trust our users to pay us for    |
 * | the huge investment in time and energy that has gone into both creating  |
 * | this software and supporting our customers. By providing the source code |
 * | we preserve our customers' ability to modify, audit and learn from our   |
 * | work. We have been developing DeskPRO since 2001, please help us make it |
 * | another decade.                                                          |
 * |                                                                          |
 * | Like the work you see? Think you could make it better? We are always     |
 * | looking for great developers to join us: http://www.deskpro.com/jobs/    |
 * |                                                                          |
 * | ~ Thanks, Everyone at Team DeskPRO                                       |
 * \**************************************************************************/

/**
 * DeskPRO
 *
 * @package DeskPRO
 */

namespace DeskPRO\Bundle\AppBundle\TermEngine\Engine\Dbal\TermCompiler;

use DeskPRO\Bundle\AppBundle\TermEngine\Engine\Dbal\Query\DbalQueryPart;
use DeskPRO\Bundle\AppBundle\TermEngine\Engine\Dbal\TermCompiler\Helper\DbalDateHelper;
use DeskPRO\Bundle\AppBundle\TermEngine\Engine\Dbal\TermCompiler\Helper\DbalEntityHelper;
use DeskPRO\Bundle\AppBundle\TermEngine\Engine\Dbal\TermCompiler\Helper\DbalNumericHelper;
use DeskPRO\Bundle\AppBundle\TermEngine\Engine\Dbal\TermCompiler\Helper\DbalStringHelper;
use DeskPRO\Bundle\AppBundle\TermEngine\Engine\TermCompilerHelperPool;
use DeskPRO\Bundle\AppBundle\TermEngine\TermCompilerHelperInterface;
use DeskPRO\Bundle\AppBundle\TermEngine\TermInterface;

abstract class AbstractDbalTermCompiler
{
    /**
     * @var TermCompilerHelperPool
     */
    private $helper_pool;

    public function setHelperPool(TermCompilerHelperPool $helper_pool)
    {
        $this->helper_pool = $helper_pool;
    }

    /**
     * Get a registered helper by ID (TermCompilerHelperInterface::getId())
     *
     * @param $id
     * @return TermCompilerHelperInterface
     */
    public function getHelper($id)
    {
        return $this->helper_pool->getHelper($id);
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
     * @return DbalNumericHelper
     */
    public function getNumericHelper()
    {
        return $this->helper_pool->getHelper('numeric');
    }

    /**
     * Use this shortcut to see if two op codes are the same.
     *
     * This normalizes the codes and then does the comparrison in a safe way.
     *
     * @param string $op
     * @param string $code
     * @return bool
     */
    protected function isOp($op, $code)
    {
        return strtolower($op) === strtolower($code);
    }

    /**
     * Take a term and return a DbalQueryPart representing the term's query conditions.
     *
     * @param TermInterface $term
     * @return DbalQueryPart
     */
    public function compile(TermInterface $term)
    {
        return $this->doCompile($term);
    }

    /**
     * Take a term and return a DbalQueryPart representing the term's query conditions.
     *
     * @param TermInterface $term
     * @return DbalQueryPart
     */
    abstract protected function doCompile(TermInterface $term);
}
