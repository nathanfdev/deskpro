<?php

namespace Application\TechBundle\Controller;

class TestController extends AbstractController
{
    public function indexAction()
    {
		$filter = $this->em->getRepository('DeskPRO:TicketQueue')->find(3);

		$searcher = $filter->getSearcher();
		$searcher->enableArchiveSearch();

		print_r($searcher);

		echo $searcher->getSql();

		exit;

        //return $this->render('TechBundle:Test:index.twig');
    }
}
