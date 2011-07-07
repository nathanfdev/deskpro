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

/**
 * The Content type renders arbitary HTML content into a slot.
 *
 * @option bool    no_wrapper             Do not use a wrapper template. This means the HTML is rendered
 *                                        raw into the slot.
 * @option string  custom_wrapper_tpl     Use a custom wrapper template
 */
class Content extends PortalItemAbstract
{
	public function getHtml()
	{
		$html = $this->getOption('html', '');

		if (!$this->getOption('no_wrapper')) {
			$tpl = $this->getOption('custom_wrapper_tpl');
			if (!$tpl) {
				$tpl = 'UserBundle:Portal:content-' . $this->section . '.html.twig';
			}

			$html = $this->controller->renderView($tpl, array(
				'html' => $html,
				'section' => $this->section,
				'options' => $this->options
			));
		}

		return $html;
	}
}