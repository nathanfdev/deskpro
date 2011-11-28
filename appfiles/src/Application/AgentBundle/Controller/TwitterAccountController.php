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
use Application\DeskPRO\Entity\TwitterAccountSearch;

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

                $statuses = App::getEntityRepository('DeskPRO:TwitterStatus')->findTweetsForAgentTeamByAgentId($agentId);

                return $this->render('AgentBundle:TwitterAccount:team-tweets.html.twig', array(
                        'statuses' => $statuses
                ));
        }

        public function listSearchesAction($account_id)
        {
                $account = $this->getAccount($account_id);

                return $this->render('AgentBundle:TwitterAccount:list-searches.html.twig', array(
                        'account' => $account,
                ));
        }

        public function runSearchAction($account_id, $search_id)
        {
                $account = $this->getAccount($account_id);
                $search = App::getEntityRepository('DeskPRO:TwitterAccountSearch')->find($search_id);

                if (!$search) {
                        throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException(sprintf('There is no search with ID "%d"', $search_id));
                }

                $twitterSearcher = new \Zend_Service_Twitter_Search();
                $searchResults = $twitterSearcher->search($search->getTerm());

                return $this->render('AgentBundle:TwitterAccount:run-search.html.twig', array(
                        'account'       => $account,
                        'search'        => $search,
                        'results'       => $searchResults['results'],
                ));
        }

        public function newSearchAction($account_id)
        {
                $account = $this->getAccount($account_id);
                $search_term = $this->in->getValue('search_term');

                // Add the search term to the account
                $twitterAccountSearch = new TwitterAccountSearch();
                $twitterAccountSearch->setAccount($account);
                $twitterAccountSearch->setTerm($search_term);

                $em = App::getOrm();
                $em->persist($twitterAccountSearch);
                $em->flush();

                $twitterSearcher = new \Zend_Service_Twitter_Search();
                $searchResults = $twitterSearcher->search($search_term);

                return $this->render('AgentBundle:TwitterAccount:search-part.html.twig', array(
                        //'search' => $search,
                        'results' => $searchResults['results'],
                ));
        }

        /**
         * Check account security.
         *
         * @param integer $id The account id.
         * @return \Application\DeskPRO\Entity\TwitterAccount
         * @throws \Symfony\Component\Security\Core\Exception\AccessDeniedException
         * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
         */
        protected function getAccount($id)
        {
                // check if account id is in persons account id list
                if (!in_array($id, $this->person->getTwitterAccountIds())) {
                        throw new \Symfony\Component\Security\Core\Exception\AccessDeniedException();
                }

                // check if account exists
                $account = App::getOrm()->getRepository('DeskPRO:TwitterAccount')->find($id);
                if (!$account) {
                        throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException(sprintf('There is no account with ID "%d"', $id));
                }

                return $account;
        }
}
