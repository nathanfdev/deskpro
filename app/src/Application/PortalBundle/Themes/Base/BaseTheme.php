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
 * @subpackage
 */

namespace Application\PortalBundle\Themes\Base;

use Application\PortalBundle\Theme\AbstractTheme;
use Application\PortalBundle\Theme\Tag;

class BaseTheme extends AbstractTheme
{
	public function getTags()
	{
		return array(
			new Tag('alerts', 'Theme:Portal:alerts'),
			new Tag('top_bar', 'Theme:Portal:topBar'),
			new Tag('top_search', 'Theme:Portal:topSearch'),
			new Tag('news_list', 'Theme:News:list'),
		);
	}


	/**
	 * {@inheritdoc}
	 */
	public function getId()
	{
		return 'base';
	}


	/**
	 * {@inheritdoc}
	 */
	public function getName()
	{
		return 'Base';
	}


	/**
	 * {@inheritdoc}
	 */
	public function getBaseTemplateDir()
	{
		return __DIR__ . '/Resources/views';
	}


	/**
	 * @return string|null base namespace of controllers, like: Application\PortalBundle\Themes\Standard
	 */
	public function getNamespace()
	{
		return __NAMESPACE__;
	}
}
