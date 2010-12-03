<?php

namespace Application\TechBundle\Controller;

class TestController extends AbstractController
{
    public function indexAction()
    {
		$filter = $this->em->getRepository('CoreBundle:TicketQueue')->find(1);

		$searcher = $filter->getSearcher();

		print_r($searcher);

		echo $searcher->getSql();

		exit;

        //return $this->render('TechBundle:Test:index.twig');
    }
}
