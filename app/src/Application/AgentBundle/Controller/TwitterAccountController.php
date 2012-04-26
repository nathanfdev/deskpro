<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. http://www.deskpro.com/   |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2012, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at http://www.deskpro.com/license                           |
|                                                                          |
| By using this software, you acknowledge having read the license          |
| and agree to be bound thereby.                                           |
|                                                                          |
| Please note that DeskPRO is not free software. We release the full       |
| source code for our software because we trust our users to pay us for    |
| the huge investment in time and energy that has gone into both creating  |
| this software and supporting our customers. By providing the source code |
| we preserve our customers' ability to modify, audit and learn from our   |
| work. We have been developing DeskPRO since 2001, please help us make it |
| another decade.                                                          |
|                                                                          |
| Like the work you see? Think you could make it better? We are always     |
| looking for great developers to join us: http://www.deskpro.com/jobs/    |
|                                                                          |
| ~ Thanks, Everyone at Team DeskPRO                                       |
\**************************************************************************/


/**
 * DeskPRO
 *
 * @package DeskPRO
 * @subpackage AgentBundle
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

                $statuses = $this->em->getRepository('DeskPRO:TwitterStatus')->findStarredTweetsForAgentId($agentId);

                return $this->render('AgentBundle:TwitterAccount:starred-tweets.html.twig', array(
                        'statuses' => $statuses
                ));
        }

        public function myTweetsAction()
        {
                $agentId = $accounts = $this->person->getId();

                $statuses = $this->em->getRepository('DeskPRO:TwitterStatus')->findTweetsForAgentId($agentId);

                return $this->render('AgentBundle:TwitterAccount:my-tweets.html.twig', array(
                        'statuses' => $statuses
                ));

        }

        public function teamTweetsAction()
        {
                $agentId = $accounts = $this->person->getId();

                $statuses = $this->em->getRepository('DeskPRO:TwitterStatus')->findTweetsForAgentTeamByAgentId($agentId);

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
                $search = $this->em->getRepository('DeskPRO:TwitterAccountSearch')->find($search_id);

                if (!$search) {
                        throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException(sprintf('There is no search with ID "%d"', $search_id));
                }

                $twitterSearcher = new \Zend\Service\Twitter\Search();
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

                $twitterSearcher = new \Zend\Service\Twitter\Search();
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
                $account = $this->em->getRepository('DeskPRO:TwitterAccount')->find($id);
                if (!$account) {
                        throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException(sprintf('There is no account with ID "%d"', $id));
                }

                return $account;
        }
}
