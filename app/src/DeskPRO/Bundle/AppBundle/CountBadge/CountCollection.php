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
 * DeskPRO.
 */

namespace DeskPRO\Bundle\AppBundle\CountBadge;

use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Represents a collection of counts. Typically used when returning counts
 * grouped by some sort of variable.
 */
class CountCollection implements \IteratorAggregate, \Countable
{
    /**
     * @var array
     */
    private $group_meta;

    /**
     * @var Count[]
     */
    private $counts;

    /**
     * @param Count[] $counts       The actual counts
     * @param array   $group_meta   Any information about this group of counts (e.g., how they are grouped).
     */
    public function __construct(array $counts, array $group_meta = array())
    {
        $this->counts = $counts;

        $resolver = new OptionsResolver();
        $this->configureOptions($resolver);

        $this->group_meta = $resolver->resolve($group_meta);
    }

    /**
     * Sub-classes may implement this to define a custom resolver
     * @param OptionsResolver $resolver
     */
    protected function configureOptions(OptionsResolver $resolver)
    {
    }

    /**
     * @param string $k
     * @param mixed $default
     * @return array
     */
    public function getGroupMeta($k, $default = null)
    {
        return array_key_exists($k, $this->group_meta) ? $this->group_meta[$k] : $default;
    }

    /**
     * @return array
     */
    public function getAllGroupMeta()
    {
        return $this->group_meta;
    }

    /**
     * @return Count[]
     */
    public function getCounts()
    {
        return $this->counts;
    }

    /**
     * {@inheritdoc}
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->counts);
    }

    /**
     * {@inheritdoc}
     */
    public function count()
    {
        return count($this->counts);
    }
}