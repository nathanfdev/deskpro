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
 * Similar to Content except this just takes a template name and renders it.
 * 
 * @option string  tpl   The template name
 */
class Template extends PortalItemAbstract
{
	public function getHtml()
	{
		$vars = $this->getVars();
		$vars = array_merge($vars, array(
			'section' => $this->section,
			'options' => $this->options
		));
		
		$html = $this->renderView($this->getOption('tpl'), $vars);

		return $html;
	}


	public function getVars()
	{
		return array();
	}
}