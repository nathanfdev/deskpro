<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @subpackage PageDisplay
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */

namespace Application\DeskPRO\PageDisplay\Page;

use Application\DeskPRO\Entity\PageDisplayAbstract;
use Application\DeskPRO\Entity\PortalPageDisplay;
use Application\DeskPRO\Entity\Person;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Application\DeskPRO\People\PersonContextInterface;
use Application\DeskPRO\PageDisplay\Item\Portal\PortalItemAbstract;

use Orb\Util\Strings;

class PortalPageLoader
{
	protected $em;

	public function __construct($em)
	{
		$this->em = $em;
	}

	public function load($section)
	{
		
	}

	public function __invoke($section)
	{
		return $this->load($section);
	}
}