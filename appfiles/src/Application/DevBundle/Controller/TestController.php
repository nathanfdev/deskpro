<?php

namespace Application\DevBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class TestController extends Controller
{
    public function indexAction()
    {
		$in = $this['deskpro.core.input_reader'];
		
		var_dump($in->getString('test'));

		$content = '';
        return $this->createResponse($content);
    }
}
