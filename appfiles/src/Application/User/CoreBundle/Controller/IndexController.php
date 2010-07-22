<?php

namespace Application\User\CoreBundle\Controller;

use Symfony\Framework\FoundationBundle\Controller;

class IndexController extends Controller
{
    public function indexAction($name)
    {
        return $this->render('CoreBundle:Index:index', array('name' => $name));
    }
}
