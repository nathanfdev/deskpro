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

use Doctrine\ORM\Mapping as ORM_Mapping;

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
 *         'xxx' => 'xxx
 *     )
 * );
 * </pre>
 *
 * The type can be a short name in which case the full PHP namespace for DeskPRO's types
 * will be prepended (Application\DeskPRO\PageDisplay\Item\Portal\XXX). You may also use underscore
 * format which will be converted into camel case (some_type to SomeType).
 *
 * Keys in the data array are insignificant. They may be used to keep track of things in the designer.
 *
 * @ORM_Mapping\Entity(repositoryClass="Application\DeskPRO\EntityRepository\PortalPageDisplay")
 * @ORM_Mapping\Table(name="portal_page_display")
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
	const SECTION_FOOTER = 'footer';

	/**
	 * The class handler
	 *
	 * @var string
	 * @ORM_Mapping\Column(name="type", type="string", length=255)
	 */
	protected $type;

	/**
	 * @var int
	 * @ORM_Mapping\Column(name="display_order", type="integer")
	 */
	protected $display_order = 0;

	/**
	 * @var bool
	 * @ORM_Mapping\Column(name="is_enabled", type="boolean")
	 */
	protected $is_enabled = 0;
}
