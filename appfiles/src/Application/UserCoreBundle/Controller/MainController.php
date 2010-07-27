<?php

namespace Application\UserCoreBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller;

class MainController extends Controller
{
    public function indexAction()
    {
        return $this->render('UserCoreBundle:Main:index');
    }
}
