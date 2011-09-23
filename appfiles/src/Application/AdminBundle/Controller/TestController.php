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

use Application\DeskPRO\App;

/**
 * Handles creating/editing of Usersources
 */
class TestController extends AbstractController
{
	public function indexAction()
	{
		$x = App::getEntityRepository('DeskPRO:TicketPageDisplay')->find(26);

		echo '<pre>';
		echo print_r($x['data']);
		echo '<br /><br /><br /><br /><br /><br />';
		echo json_encode($x['data']);

		exit;
		return $this->render('AdminBundle:Test:index.html.twig', array('cat' => $cat));
	}
}
