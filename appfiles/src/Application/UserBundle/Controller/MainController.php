<?php

namespace Application\UserBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Application\DeskPRO\App;

class MainController extends AbstractController
{
    public function indexAction()
    {
        return $this->render('UserBundle:Main:index.html.twig');
    }

	public function standardErrorAction($error_message, $error_title = '', $code = 200)
	{
		$res = $this->render('UserBundle:Main:standard-error.html.twig', array(
			'error_message' => $error_message,
			'error_title'   => $error_title
		));

		$res->setStatusCode($code);

		return $res;
	}
}
