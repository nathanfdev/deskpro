<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @subpackage UserBundle
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */

namespace Application\UserBundle\Controller;

use Application\DeskPRO\App;
use Application\DeskPRO\Entity;

use Orb\Util\Arrays;

class TestController extends AbstractController
{
	public function indexAction()
	{
		$this->container->getIn()->getCleaner()->clean('<b>test</b>', 'html_email');
		return $this->render('UserBundle:Test:index.html.twig');
	}
}
