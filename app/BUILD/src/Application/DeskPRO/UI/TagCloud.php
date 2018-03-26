<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\UI;

use Orb\Util\Arrays;

/**
 * Generates information useful for a tag cloud.
 */
class TagCloud
{
    /** @var array */
    protected $_tag_counts = [];
    /** @var int */
    protected $_min_count = 0;
    /** @var int */
    protected $_max_count = 0;
    /** @var int */
    protected $_spread = 1;
    /** @var int */
    protected $_max_size = 10;
    /** @var string */
    protected $_class_prefix = 'tag-size';

    /**
     * $tag_counts must be an array of tag=>count for all tags you want to include in
     * the cloud.
     *
     * @param array $tag_counts
     */
    public function __construct(array $tag_counts)
    {
        $this->_tag_counts = $tag_counts;

        if ($tag_counts) {
            $this->_min_count = min($this->_tag_counts);
            $this->_max_count = max($this->_tag_counts);
            $this->_spread    = max(1, $this->_max_count - $this->_min_count);
        }
    }

    public function getCloud()
    {
        $cloud = [];

        foreach ($this->_tag_counts as $tag => $count) {
            $size       = round(1 + (($count - $this->_min_count) * (($this->_max_count - 1) / $this->_spread)));
            $size_class = $this->_class_prefix.$size;

            $cloud[$tag] = $size_class;
        }

        Arrays::shuffleAssoc($cloud);

        return $cloud;
    }
}
