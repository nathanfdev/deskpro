<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */

namespace Application\DeskPRO\UI;

use Application\DeskPRO\Entity;

use Symfony\Component\DependencyInjection\ContainerInterface;

use Orb\Util\Arrays;

/**
 * Generates information useful for a tag cloud
 */
class TagCloud
{
	protected $_tag_counts = array();
	protected $_min_count = 0;
	protected $_max_count = 0;
	protected $_spread = 1;
	protected $_max_size = 10;
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
			$this->_spread = max(1, $this->_max_count - $this->_min_count);
		}
	}

	public function getCloud()
	{
		$cloud = array();

		foreach ($this->_tag_counts as $tag => $count) {
			$size = round(1 + (($count - $this->_min_count) * (($this->_max_count - 1) / $this->_spread)));
			$size_class = $this->_class_prefix . $size;

			$cloud[$tag] = $size_class;
		}

		Arrays::shuffleAssoc($cloud);

		return $cloud;
	}
}
