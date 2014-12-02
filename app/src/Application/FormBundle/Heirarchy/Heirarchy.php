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

namespace Application\FormBundle\Heirarchy;

use Application\FormBundle\Form\ChoiceList\HeirarchyChoiceList;
use Application\FormBundle\Heirarchy\Formatter\FlatListFormatter;
use Doctrine\Common\Collections\ArrayCollection;
use Traversable;

/**
 * Represents a heirarchy.
 *
 * getChoiceList can be used directly in a form (choice type) and will use the heirarchy formatter to render options
 * and the ID of the entity (by default) as the value.
 */
class Heirarchy implements \Countable, \IteratorAggregate
{
    /**
     * @var HeirarchyFormatterInterface
     */
    private $formatter;

    /**
     * @var HeirarchyNode[]
     */
    private $root_nodes;

    /**
     * @param HeirarchyNode[]             $root_nodes
     * @param HeirarchyFormatterInterface $formatter
     */
    public function __construct(array $root_nodes, HeirarchyFormatterInterface $formatter = null)
    {
        $this->formatter = $formatter ?: new FlatListFormatter();
        $this->root_nodes = $root_nodes;
        foreach ($root_nodes as $root_node) {
            $root_node->setHeirarchy($this);
        }
    }

    /**
     * @return HeirarchyFormatterInterface
     */
    public function getFormatter()
    {
        return $this->formatter;
    }

    /**
     * @return HeirarchyNode[]
     */
    public function getRootNodes()
    {
        return $this->root_nodes;
    }

    /**
     * @return HeirarchyNode[]
     */
    public function getFlattened()
    {
        $collection = new ArrayCollection();
        foreach ($this->getRootNodes() as $root_node) {
            self::flatten($root_node, $collection);
        }

        return $collection;
    }

    /**
     * @param HeirarchyNode   $node
     * @param ArrayCollection $append_to_collection
     * @return ArrayCollection|HeirarchyNode[]
     */
    public static function flatten(HeirarchyNode $node, ArrayCollection $append_to_collection = null)
    {
        if (!$append_to_collection) {
            $append_to_collection = new ArrayCollection();
        }

        $append_to_collection->add($node);

        foreach ($node as $child) {
            self::flatten($child, $append_to_collection);
        }

        return $append_to_collection;
    }

    /**
     * @return HeirarchyChoiceList
     */
    public function getChoiceList()
    {
        return new HeirarchyChoiceList($this->getFlattened(), null, array(), null, 'data.id');
    }

    /**
     * @param HeirarchyFormatterInterface $formatter
     */
    public function setFormatter(HeirarchyFormatterInterface $formatter)
    {
        $this->formatter = $formatter;
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Retrieve an external iterator
     *
     * @link http://php.net/manual/en/iteratoraggregate.getiterator.php
     * @return Traversable An instance of an object implementing <b>Iterator</b> or
     *       <b>Traversable</b>
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->root_nodes);
    }

    /**
     * (PHP 5 &gt;= 5.1.0)<br/>
     * Count elements of an object
     *
     * @link http://php.net/manual/en/countable.count.php
     * @return int The custom count as an integer.
     *       </p>
     *       <p>
     *       The return value is cast to an integer.
     */
    public function count()
    {
        return count($this->root_nodes);
    }
}
 