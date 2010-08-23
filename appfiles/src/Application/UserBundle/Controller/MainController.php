<?php

namespace Application\UserBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller;

class MainController extends Controller
{
    public function indexAction()
    {
        return $this->render('UserBundle:Main:index:twig', array('test' => 0));
    }
}
