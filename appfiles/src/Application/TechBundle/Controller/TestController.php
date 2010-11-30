<?php

namespace Application\TechBundle\Controller;

class TestController extends AbstractController
{
    public function indexAction()
    {
        return $this->render('TechBundle:Test:index.twig');
    }
}
