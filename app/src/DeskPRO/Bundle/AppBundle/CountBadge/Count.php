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
 * Represents a count, typically used to show counters/badges in a UI.
 */
class Count
{
    /**
     * @var int
     */
    private $count;

    /**
     * @var array
     */
    private $count_meta = array();

    /**
     * @var CountCollection
     */
    private $grouped_counts;

    /**
     * @param int   $count                    The count itself
     * @param array $count_meta               Any extra information about the count (such as a title or ID for the thing this is a count of)
     * @param CountCollection $grouped_counts A collection of sub-counts
     */
    public function __construct($count, array $count_meta = array(), CountCollection $grouped_counts = null)
    {
        $this->count = $count;

        $resolver = new OptionsResolver();
        $this->configureOptions($resolver);

        $this->count_meta = $resolver->resolve($count_meta);

        $this->grouped_counts = $grouped_counts ?: new CountCollection(null, array());
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
    public function getCountMeta($k, $default = null)
    {
        return array_key_exists($k, $this->count_meta) ? $this->count_meta[$k] : $default;
    }

    /**
     * @return array
     */
    public function getAllCountMeta()
    {
        return $this->count_meta;
    }

    /**
     * @return CountCollection
     */
    public function getGroupedCounts()
    {
        return $this->grouped_counts;
    }

    /**
     * @return bool
     */
    public function hasGroupedCounts()
    {
        return $this->grouped_counts->count() > 0;
    }

    /**
     * @return int
     */
    public function getCount()
    {
        return $this->count;
    }
}