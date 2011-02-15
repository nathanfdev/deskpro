<?php

namespace Application\AdminBundle\Controller;

class MainController extends AbstractController
{
    public function indexAction()
    {
		return $this->redirectRoute('admin_labels', array('label_type' => 'tickets'));
        return $this->render('AdminBundle:Main:index.html.twig');
    }
}
