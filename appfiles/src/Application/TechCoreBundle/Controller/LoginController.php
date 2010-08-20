<?php

namespace Application\TechCoreBundle\Controller;
use Symfony\Bundle\FrameworkBundle\Controller;

class LoginController extends Controller
{
    public function indexAction()
    {
        return $this->render('TechCoreBundle:Login:index:twig');
    }
}
