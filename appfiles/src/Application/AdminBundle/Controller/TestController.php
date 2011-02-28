<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @subpackage AdminBundle
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */

namespace Application\AdminBundle\Controller;

use \Application\DeskPRO\App;

/**
 * Handles creating/editing of Usersources
 */
class TestController extends AbstractController
{
	public function indexAction()
	{
		$cat = App::getEntityRepository('DeskPRO:Department')->find(1);

		//$tr = App::get('deskpro.core.translate');
		//echo $tr->phrase($cat);

		//exit;
		return $this->render('AdminBundle:Test:index.html.twig', array('cat' => $cat));
	}
}