<?php

namespace Application\AgentBundle\Controller;

class TestController extends AbstractController
{
    public function indexAction()
    {
		return $this->renderJson('AgentBundle:Test:test.phpj');

		$queue = $this->em->getRepository('DeskPRO:TicketQueue')->find(3);

		$searcher = $queue->getSearcher();
		$searcher->enableArchiveSearch();

		print_r($searcher);

		echo $searcher->getSql();

		exit;

        //return $this->render('AgentBundle:Test:index.twig');
    }
}
