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

use Application\DeskPRO\App;

/**
 *
 */
class TwitterAccountController extends AbstractController
{
        public function starredTweetsAction()
        {
                $agentId = $accounts = $this->person->getId();

                $statuses = App::getEntityRepository('DeskPRO:TwitterStatus')->findStarredTweetsForAgentId($agentId);

                return $this->render('AgentBundle:TwitterAccount:starred-tweets.html.twig', array(
                        'statuses' => $statuses
                ));
        }

        public function myTweetsAction()
        {
                $agentId = $accounts = $this->person->getId();

                $statuses = App::getEntityRepository('DeskPRO:TwitterStatus')->findTweetsForAgentId($agentId);

                return $this->render('AgentBundle:TwitterAccount:my-tweets.html.twig', array(
                        'statuses' => $statuses
                ));

        }

        public function teamTweetsAction()
        {
                $agentId = $accounts = $this->person->getId();

                $statuses = App::getEntityRepository('DeskPRO:TwitterStatus')->findTweetsForAgentTeamId($agentId);

                return $this->render('AgentBundle:TwitterAccount:team-tweets.html.twig', array(
                        'statuses' => $statuses
                ));
        }
}
