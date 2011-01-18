<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @subpackage AgentBundle
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */

namespace Application\AgentBundle\Controller;

use \Application\DeskPRO\Entity\TicketQueue;
use \Application\DeskPRO\Entity\Ticket;
use \Application\DeskPRO\Entity;
use \Application\DeskPRO\App;
use \Application\DeskPRO\Twitter;
use \Orb\Util\Strings;
use \Orb\Util\Arrays;

/**
 * Handles ticket searches
 */
class TwitterController extends AbstractController
{
	public function indexAction()
	{
            $config = array(
                'callbackUrl' => 'http://basiltest.dyndns.biz/dp/DeskPRO/index_dev.php/agent/twitter/callback',
                'siteUrl' => 'http://twitter.com/oauth',
                'consumerKey' => 'MmuQ3021xYehoBzjjd3WFg',
                'consumerSecret' => 'RORtnJhEUkesx7jDZCSHXonIRjLVAeQ9hIXK8MBf9o'
            );
            $twitter = new Twitter($config);

            $twitter->requestAuth();
            return $this->render('AgentBundle:Twitter:list-blank.twig.html');
	}



        public function callBackAction(){
            $config = array(
                'callbackUrl' => 'http://basiltest.dyndns.biz/dp/DeskPRO/index_dev.php/agent/twitter/callback',
                'siteUrl' => 'http://twitter.com/oauth',
                'consumerKey' => 'MmuQ3021xYehoBzjjd3WFg',
                'consumerSecret' => 'RORtnJhEUkesx7jDZCSHXonIRjLVAeQ9hIXK8MBf9o'
            );
            $twitter = new Twitter($config);
            $twitter->handleCallback();
        }
	public function queuesPaneAction()
	{
		$queues = App::getApi('tickets.queues')->getQueuesForPerson($this->person);

		$order = $this->person->getPref('agent.ui.ticket-queues-order');
		if ($order) {
			$queues_unordered = $queues;
			$queues = array();

			foreach ($order as $id) {
				$queues[$id] = $queues_unordered[$id];
				unset($queues_unordered[$id]);
			}

			if (count($queues_unordered)) {
				foreach ($queues_unordered as $id => $q) {
					$queues[$id] = $q;
				}
			}
		}

		return $this->render('AgentBundle:Twitter:pane-queues.twig.html', array(
			'queues' => $queues
		));
	}



}