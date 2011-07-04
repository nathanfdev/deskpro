<?php

namespace Application\SysBundle\Controller;

class TestController extends \Symfony\Bundle\FrameworkBundle\Controller\Controller
{
	public function testAction()
	{
		return $this->render('SysBundle:Test:test.html.php');
	}
}