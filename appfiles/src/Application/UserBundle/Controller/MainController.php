<?php

namespace Application\UserBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class MainController extends AbstractController
{
    public function indexAction()
    {
        return $this->render('UserBundle:Main:index.html.twig');
    }
}
