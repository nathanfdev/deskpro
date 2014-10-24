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
interface ThemeInterface 
{
	/**
	 * Templates have a string identifier. "base", or "default", or "default-modified", etc. Must be unique.
	 *
	 * @return string
	 */
	public function getId();


	/**
	 * A human readable name of the theme (used in dropdown, reporting, etc)
	 *
	 * @return string
	 */
	public function getName();


	/**
	 * Returns the parent of this theme. This theme will inherit templates/tags/controllers of the parent, and can
	 * override them.
	 *
	 * @return ThemeInterface|null
	 */
	public function getParent();


	/**
	 * @return string relative path to the root of this theme's templates - it is relative to the kernel root dir
	 */
	public function getBaseTemplateDir();


	/**
	 * @return string|null base namespace of controllers, like: Application\PortalBundle\Themes\Standard
	 */
	public function getNamespace();


	/**
	 * Get the tag for the given tag name.
	 *
	 * @param $tag_name
	 * @return Tag|null
	 */
	public function getTag($tag_name);
}
 