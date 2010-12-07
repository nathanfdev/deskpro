<?php

namespace Application\AdminBundle\Controller;

class MainController extends AbstractController
{
    public function indexAction()
    {
        return $this->render('AdminBundle:Main:index.twig');
    }
}
