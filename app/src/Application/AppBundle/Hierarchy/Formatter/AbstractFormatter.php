<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. http://www.deskpro.com/   |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2012, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at http://www.deskpro.com/license                           |
|                                                                          |
| By using this software, you acknowledge having read the license          |
| and agree to be bound thereby.                                           |
|                                                                          |
| Please note that DeskPRO is not free software. We release the full       |
| source code for our software because we trust our users to pay us for    |
| the huge investment in time and energy that has gone into both creating  |
| this software and supporting our customers. By providing the source code |
| we preserve our customers' ability to modify, audit and learn from our   |
| work. We have been developing DeskPRO since 2001, please help us make it |
| another decade.                                                          |
|                                                                          |
| Like the work you see? Think you could make it better? We are always     |
| looking for great developers to join us: http://www.deskpro.com/jobs/    |
|                                                                          |
| ~ Thanks, Everyone at Team DeskPRO                                       |
\**************************************************************************/

/**
 * DeskPRO
 *
 * @package DeskPRO
 * @subpackage
 */

namespace Application\AppBundle\Hierarchy\Formatter;


use Application\AppBundle\Hierarchy\HierarchyFormatterInterface;
use Application\AppBundle\Hierarchy\HierarchyNode;
use Symfony\Component\PropertyAccess\PropertyAccess;

/**
 * The AbstractFormatter lets you easily get the string value of a node (optionally, with a property accessor)
 */
abstract class AbstractFormatter implements HierarchyFormatterInterface
{
    /**
     * @var string|null
     */
    private $stringPropertyPath;

    public function __construct($stringPropertyPath = null)
    {
        $this->stringPropertyPath = $stringPropertyPath;
    }

    public function getDataValue(HierarchyNode $node)
    {
        if ($this->stringPropertyPath) {
            $accessor = PropertyAccess::createPropertyAccessor();
            return $accessor->getValue($node->getData(), $this->stringPropertyPath);
        }

        return (string) $node->getData();
    }
}
 