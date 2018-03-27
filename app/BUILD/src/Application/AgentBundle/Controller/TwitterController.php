<?php

/**
 * DeskPRO.
 */

namespace Application\AgentBundle\Controller;

use Application\DeskPRO\App;
use Application\DeskPRO\Entity\TwitterAccount;
use Application\DeskPRO\Entity\TwitterAccountSearch;

/**
 * Handles creating/editing of Twitter Accounts.
 */
class TwitterController extends AbstractController
{
    public function getSectionDataAction()
    {
        $data = [];

        $group_updates = $this->in->getCleanValueArray('group_updates');
        foreach ($group_updates as $account_id => $groups) {
            if (!is_array($groups)) {
                continue;
            }

            foreach ($groups as $type => $group) {
                $this->person->setPreference("agent.ui.twitter-group.$account_id.$type", $group);
            }

            App::getOrm()->persist($this->person);
            App::getOrm()->flush();
        }

        $accounts = $this->person->getTwitterAccounts();
        $counts   = $this->em->getRepository('DeskPRO:TwitterAccountStatus')->getSectionCounts($accounts);

        $grouping_prefs = $this->em->getRepository('DeskPRO:PersonPref')->getPrefgroupForPersonId('agent.ui.twitter-group.', $this->person->getId());
        $groupings      = [];
        foreach ($accounts as $account) {
            $groupings[$account->id] = [];
            foreach (['mine', 'team', 'unassigned', 'all'] as $group) {
                $value                           = isset($grouping_prefs[$account->id.'.'.$group]) ? $grouping_prefs[$account->id.'.'.$group] : '';
                $data                            = $this->em->getRepository('DeskPRO:TwitterAccountStatus')->getGroupedSectionCount($account, $group, $value);
                $groupings[$account->id][$group] = ['group' => $value, 'data' => $data];
            }
        }

        $data['section_html'] = $this->renderView('AgentBundle:Twitter:window-section.html.twig', [
            'counts'    => $counts,
            'groupings' => $groupings,
            'accounts'  => $accounts,
            'agents'    => $this->em->getRepository('DeskPRO:Person')->getAgents(),
            'teams'     => $this->em->getRepository('DeskPRO:AgentTeam')->getTeams(),
        ]);

        return $this->createJsonResponse($data);
    }

    public function updateGroupingAction()
    {
        $account = $this->getAccount($this->in->getUint('account_id'));
        $type    = $this->in->getString('type');
        $group   = $this->in->getString('group');

        $this->person->setPreference("agent.ui.twitter-group.$account->id.$type", $group);

        App::getOrm()->persist($this->person);
        App::getOrm()->flush();

        switch ($type) {
            case 'mine': $route       = 'agent_twitter_mine_list'; break;
            case 'team': $route       = 'agent_twitter_team_list'; break;
            case 'unassigned': $route = 'agent_twitter_unassigned_list'; break;
            case 'all': $route        = 'agent_twitter_all_list'; break;
            default: $route           = '';
        }

        $data = $this->em->getRepository('DeskPRO:TwitterAccountStatus')->getGroupedSectionCount($account, $type, $group);

        return $this->createJsonResponse([
            'account_id' => $account->id,
            'type'       => $type,
            'group'      => $group,
            'html'       => $this->renderView('AgentBundle:Twitter:window-sub-grouping.html.twig', [
                'account'      => $account,
                'section_type' => $type,
                'group_by'     => $group,
                'data'         => $data,
                'route'        => $route,
                'agents'       => $this->em->getRepository('DeskPRO:Person')->getAgents(),
                'teams'        => $this->em->getRepository('DeskPRO:AgentTeam')->getTeams(),
            ]),
        ]);
    }

    public function newTweetAction()
    {
        $accounts = $this->person->getTwitterAccounts();
        if (!$accounts) {
            throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException();
        }

        $account = count($accounts) == 1 ? $accounts[0] : false;

        return $this->render('AgentBundle:Twitter:new.html.twig', [
            'accounts' => $accounts,
            'account'  => $account,
        ]);
    }

    public function newTweetSaveAction()
    {
        $accounts = $this->person->getTwitterAccounts();

        $text        = $this->in->getString('text');
        $split       = $this->in->getBool('split');
        $account_ids = $this->in->getCleanValueArray('account_ids', 'uint');

        $twitter_service = new \Application\DeskPRO\Service\Twitter();

        if (strlen($text)) {
            foreach ($accounts as $account) {
                if (in_array($account->id, $account_ids)) {
                    $twitter_service->sendAccountMessage('public', $text, $split, $account);
                }
            }
        }

        return $this->createJsonResponse(['success' => true]);
    }

    public function runSearchAction($account_id, $search_id)
    {
        $account = $this->getAccount($account_id);
        $search  = $this->em->getRepository('DeskPRO:TwitterAccountSearch')->find($search_id);

        if (!$search) {
            throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException(sprintf('There is no search with ID "%d"', $search_id));
        }

        $this->person->setPreference('agent.ui.last_twitter_account', $account->id);

        $page = $this->in->getUint('page');
        if (!$page) {
            $page = 1;
        }
        $per_page = TwitterAccount::DEFAULT_LIMIT;

        if ($this->in->getBool('partial')) {
            $tpl = 'AgentBundle:Twitter:part-search.html.twig';
        } else {
            $tpl = 'AgentBundle:Twitter:run-search.html.twig';
        }

        $includeArchived = $this->in->getBool('include.archived');

        if ($this->in->getBool('since_id')) {
            $statuses = $search->updateSearch(true, $this->in->getString('since_id'));
            $added    = count($statuses);
            $statuses = array_slice($statuses, 0, $per_page);
        } else {
            $statuses = $search->getAccountStatuses($includeArchived, $page, $per_page);
            $added    = count($statuses);
        }
        $total_count = $search->countAccountStatuses($includeArchived);

        $max_id = 0;
        foreach ($statuses as $status) {
            if ($status->status->id > $max_id) {
                $max_id = $status->status->id;
            }
        }

        return $this->render($tpl, [
            'account'     => $account,
            'search'      => $search,
            'statuses'    => $statuses,
            'total_count' => $total_count,
            'per_page'    => $per_page,
            'page'        => $page,
            'showing_to'  => min($total_count, $page * $per_page),
            'max_id'      => $max_id,
            'added'       => $added,
        ]);
    }

    public function deleteSearchAction($account_id, $security_token)
    {
        $account = $this->getAccount($account_id);

        $search_id = $this->in->getUint('search_id');
        if ($search_id) {
            $search = $this->em->getRepository('DeskPRO:TwitterAccountSearch')->find($search_id);
        } else {
            $search_term = $this->in->getString('search_term');
            $search      = $this->em->getRepository('DeskPRO:TwitterAccountSearch')->getExistingSearch($search_term, $account);
        }

        if (!$search) {
            throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException(sprintf('There is no search with ID "%d"', $search_id));
        }

        $this->ensureAuthToken('delete_search', $security_token);

        $this->em->remove($search);
        $this->em->flush();

        return $this->createJsonResponse(['success' => true]);
    }

    public function newSearchAction($account_id)
    {
        $account     = $this->getAccount($account_id);
        $search_term = $this->in->getString('search_term');

        $search = $this->em->getRepository('DeskPRO:TwitterAccountSearch')->getExistingSearch($search_term, $account);

        if (!$search) {
            $search          = new TwitterAccountSearch();
            $search->account = $account;
            $search->term    = $search_term;

            $this->em->persist($search);
            $this->em->flush();
        }

        return $this->runSearchAction($account_id, $search->id);
    }

    /**
     * Check account security.
     *
     * @param int $id The account id
     *
     * @throws \Symfony\Component\Security\Core\Exception\AccessDeniedException
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     *
     * @return \Application\DeskPRO\Entity\TwitterAccount
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

    /**
     * @return string
     */
    protected function getSortByDate()
    {
        // sort by date, ascending or descending
        $sortByDate = $this->in->getValue('sortbydate');
        if (!$sortByDate) {
            $sortByDate = 'desc';
        }

        return $sortByDate;
    }

    protected function adjustPage($count, $page = null, $per_page = null)
    {
        if (!$per_page) {
            $per_page = TwitterAccount::DEFAULT_LIMIT;
        }
        if ($page === null) {
            $page = $this->in->getUint('page');
        }
        if (!$page) {
            $page = 1;
        }

        $start = ($page - 1) * $per_page;
        if ($start >= $count) {
            $page = ($count ? ceil($count / $per_page) : 1);
        }

        return $page;
    }

    /**
     * @param array $statuses
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    protected function renderList(array $statuses, $template, $total_count, $page, $sort_by_date = null)
    {
        if ($sort_by_date === null) {
            $sort_by_date = $this->getSortByDate();
        }

        $per_page = TwitterAccount::DEFAULT_LIMIT;

        $parameters = [
            'statuses'     => $statuses,
            'person'       => $this->getPerson(),
            'sort_by_date' => $sort_by_date,
            'total_count'  => $total_count,
            'per_page'     => $per_page,
            'page'         => $page,
            'showing_to'   => min($total_count, $page * $per_page),
            'agents'       => $this->em->getRepository('DeskPRO:Person')->getAgents(),
        ];

        // check if is partial
        if ($this->in->getBool('partial')) {
            return $this->render('AgentBundle:TwitterStatus:part-status.html.twig', $parameters);
        }

        // render html response
        return $this->render($template, $parameters);
    }
}
