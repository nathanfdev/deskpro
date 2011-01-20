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
use \Orb\Util\Strings;
use \Orb\Util\Arrays;
use Zend_Service_Twitter;

/**
 * Handles ticket searches
 */
class TwitterController extends AbstractController
{
	public function indexAction()
	{
            echo "god is love";
            return $this->render('AgentBundle:Twitter:list-blank.twig.html');
	}


        public function mensionsAction($handle){
                $active_accounts = App::getDb()->fetchAll("
                    SELECT id,twitter_handle,access_token
                    FROM twitter_accounts
                    WHERE deleted IS NULL");
                $twitter = new Zend_Service_Twitter(array(
                    'username' => $handle,
                    'accessToken' => unserialize($active_accounts[0]['access_token'])
                ));
                $response  = $twitter->status->userTimeline();
                //echo "<pre>";print_r($response);
                return $this->render('AgentBundle:Twitter:tweet-list.twig.html',array(
                    "tweets" => $response
                ));
                /*
                $style = $this->em->createQuery('
				SELECT s
				FROM DeskPRO:Style s
				WHERE s.id = ?1'
			)->setParameter(1, $style_id)->getSingleResult();
                 *
                 *
                 */
        }
        


        public function callBackAction(){
            
            $twitter = new Twitter($config);
            $twitter->handleCallback();
        }


        public function favouritesAction(){
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
		/*
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
                */
                $active_accounts = App::getDb()->fetchAll("
                    SELECT id,twitter_handle
                    FROM twitter_accounts
                    WHERE deleted IS NULL");

                return $this->render('AgentBundle:Twitter:pane-account.twig.html',array(
                 "active_accounts" => $active_accounts
                ));
	}



}