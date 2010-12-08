<?php

namespace Application\TechBundle\Controller;

class MainController extends AbstractController
{
    public function indexAction()
    {
        return $this->render('TechBundle:Main:index.twig');
    }
}
