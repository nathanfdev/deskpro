<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @category Entities
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */

namespace Application\DeskPRO\Entity;

use Application\DeskPRO\App;
use Application\DeskPRO\PageDisplay\Item\Portal\PortalItemAbstract;

use Orb\Util\Strings;
use Orb\Util\Arrays;

/**
 * Description of layout of the user portal
 *
 * = $data format =
 * <pre>
 * array(
 *     array(
 *         'type' => 'some_type',
 *         'xxx' => 'xxx
 *     )
 * );
 * </pre>
 *
 * The data format is mostly open except that the first level must have a 'type' which defines
 * the class handler for the item. The whole sub-array is passed to the handler as its "options"
 *
 * The type can be a short name in which case the full PHP namespace for DeskPRO's types
 * will be prepended (Application\DeskPRO\PageDisplay\Item\Portal\XXX). You may also use underscore
 * format which will be converted into camel case (some_type to SomeType).
 *
 * Keys in the data array are insignificant. They may be used to keep track of things in the designer.
 *
 * @orm:Entity(repositoryClass="Application\DeskPRO\EntityRepository\PortalPageDisplay")
 * @orm:Table(name="portal_page_display")
 */
class PortalPageDisplay extends PageDisplayAbstract
{
	/**
	 * Portal (main page)
	 */
	const SECTION_PORTAL = 'portal';

	/**
	 * Across the top (not columned)
	 */
	const SECTION_PAGETOP = 'pagetop';

	/**
	 * The sidebar.
	 */
	const SECTION_SIDEBAR = 'sidebar';

	/**
	 * The header content. Usually just one item thats rendered into the header.
	 */
	const SECTION_HEADER = 'header';

	/**
	 * The footer content. Usually just one item thats rendered into the footer.
	 */
	const SECTION_FOOTER = 'header';
}