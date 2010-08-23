<?php

namespace Application\TechBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller;

class MainController extends AbstractController
{
    public function indexAction()
    {
        return $this->render('TechBundle:Main:index');
    }
}
