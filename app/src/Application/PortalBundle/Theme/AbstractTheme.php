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

namespace Application\PortalBundle\Theme;

/**
 * Representation of a DeskPRO Theme
 */
abstract class AbstractTheme implements ThemeInterface
{
	/**
	 * @var Tag[]
	 */
	protected $tags;

	/**
	 * @var ThemeInterface|null
	 */
	private $parent;

	public function __construct(ThemeInterface $parent = null)
	{
		$this->parent = $parent;
		foreach ($this->getTags() as $tag) {
			$this->tags[$tag->getName()] = $tag;
		}
	}

	/**
	 * @return Tag[]
	 */
	abstract function getTags();

	public function getParent()
	{
		return $this->parent;
	}


	/**
	 * Get the tag for the given tag name.
	 *
	 * @param $tag_name
	 * @return Tag|null
	 */
	public function getTag($tag_name)
	{
		return isset($this->tags[$tag_name]) ? $this->tags[$tag_name] : null;
	}
}
 