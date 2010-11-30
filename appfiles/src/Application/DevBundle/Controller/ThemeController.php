<?php

namespace Application\DevBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class ThemeController extends Controller
{
    public function indexAction()
    {
        return $this->render('DevBundle:Theme:index:twig.twig');
    }
}
