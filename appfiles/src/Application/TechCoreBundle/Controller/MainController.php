<?php

namespace Application\TechCoreBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller;

class MainController extends AbstractController
{
    public function indexAction()
    {
        return $this->render('TechCoreBundle:Main:index');
    }
}
