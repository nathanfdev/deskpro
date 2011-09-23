<?php

namespace Application\AdminBundle\Controller;

use Application\DeskPRO\App;

class MainController extends AbstractController
{
    public function indexAction()
	{
		// If we just came from the agent interface, lets redirect the
		// person back where they just were
		$ref = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : false;
		$old_url = App::getSession()->get('admin_last_page');
		if ($old_url AND $ref AND strpos($ref, '/agent/') !== false AND strpos($ref, '/admin/') === false) {
			App::getSession()->remove('admin_last_page');

			return $this->redirect($old_url);
		}

		return $this->render('AdminBundle:Main:index.html.twig');
	}
}
