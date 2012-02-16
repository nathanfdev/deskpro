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

namespace Application\DeskPRO\PageDisplay\Item\Portal;

use Application\DeskPRO\Entity\PortalPageDisplay;
use Application\DeskPRO\Labels\ContentLabelCloud;

class Labels extends PortalItemAbstract implements CacheableItem
{
	public function getCacheOptions()
	{
		return array('tags' => array('labels'));
	}

	public function getHtml()
	{
		$content_cloud = new ContentLabelCloud();
		$cloud = $content_cloud->getCloud();

		$vars = array(
			'section' => $this->section,
			'options' => $this->options,
			'cloud' => $cloud
		);

		$html = $this->renderView('UserBundle:Portal:labels-sidebar.html.twig', $vars);

		return $html;
	}
}