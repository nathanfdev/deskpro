<?php

/**
 * DeskPRO.
 */

namespace Application\AgentBundle\Controller;

use Application\DeskPRO\App;
use Application\DeskPRO\Entity\TwitterAccountFriend;
use Application\DeskPRO\Entity\TwitterUser;
use DeskPRO\Bundle\AppBundle\Notification\Event\LegacySystemEvent;

/**
 * Handles creating/editing of Twitter Users.
 */
class TwitterUserController extends AbstractController
{
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
    protected function getAccountOr404($id)
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
     * @param int $id
     *
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     *
     * @return \Application\DeskPRO\Entity\TwitterUser
     */
    protected function getUserOr404($id)
    {
        $user = $this->em->getRepository('DeskPRO:TwitterUser')->find($id);
        if (!$user) {
            throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException(sprintf('There is no user with ID "%d"', $id));
        }

        return $user;
    }

    public function findAction()
    {
        $accounts = $this->person->getTwitterAccounts();
        if (!$accounts) {
            throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException();
        }

        $name = $this->in->getString('name');
        if ($name && $name[0] == '@') {
            $name = substr($name, 1);
        }

        $user = $this->em->getRepository('DeskPRO:TwitterUser')->getByScreenName($name, true);
        if ($user) {
            $this->em->persist($user);
            $this->em->flush();
        }

        if ($this->in->getBool('tab')) {
            return $this->viewAction($user ? $user->id : 0);
        } else {
            if ($user) {
                return $this->createJsonResponse([
                    'success' => true,
                    'url'     => $this->generateUrl('agent_twitter_user', ['user_id' => $user->id]),
                ]);
            } else {
                return $this->createJsonResponse(['success' => false]);
            }
        }
    }

    public function viewAction($user_id)
    {
        $user                     = $this->_getUser($user_id);
        list($account, $accounts) = $this->_getCurrentAccounts();

        if (!$user->last_profile_update || $user->last_profile_update->getTimeStamp() < time() - TwitterUser::PROFILE_UPDATE_FREQUENCY) {
            $user->updateProfile();
            $this->em->persist($user);
            $this->em->flush();
        }

        $statuses = $user->getStatuses();
        $messages = $user->getMessages();
        $mentions = $user->getMentions();

        if ($account) {
            $status_ids       = array_merge(array_keys($statuses), array_keys($messages), array_keys($mentions));
            $status_ids       = array_unique($status_ids);
            $status_ids       = array_values($status_ids);
            $account_statuses = $this->em->getRepository('DeskPRO:TwitterAccountStatus')->getByTwitterIdsAndAccount($status_ids, $account);
        } else {
            $account_statuses = [];
        }

        return $this->render('AgentBundle:TwitterUser:view.html.twig', [
            'user'             => $user,
            'accounts'         => $accounts,
            'account'          => $account,
            'statuses'         => $statuses,
            'messages'         => $messages,
            'mentions'         => $mentions,
            'account_statuses' => $account_statuses,
        ]);
    }

    public function viewUserStatusesAction($user_id)
    {
        $user                     = $this->_getUser($user_id);
        list($account, $accounts) = $this->_getCurrentAccounts();

        $page     = $this->in->getUint('page');
        $page     = max(1, $page);
        $per_page = 25;

        $statuses = $user->getStatuses($page, $per_page);
        $more     = count($user->getStatuses(($page + 1) * $per_page, 1)) > 0;
        if ($more) {
            $statuses = array_slice($statuses, 0, $per_page, true);
        }

        if ($account) {
            $status_ids       = array_keys($statuses);
            $account_statuses = $this->em->getRepository('DeskPRO:TwitterAccountStatus')->getByTwitterIdsAndAccount($status_ids, $account);
        } else {
            $account_statuses = [];
        }

        return $this->render('AgentBundle:TwitterUser:view-user-statuses.html.twig', [
            'user'             => $user,
            'accounts'         => $accounts,
            'account'          => $account,
            'statuses'         => $statuses,
            'account_statuses' => $account_statuses,
            'more'             => $more,
            'more_page'        => $page + 1,
        ]);
    }

    public function viewUserFollowingAction($user_id)
    {
        $user                     = $this->_getUser($user_id);
        list($account, $accounts) = $this->_getCurrentAccounts();

        if (!$user->last_follow_update || $user->last_follow_update->getTimeStamp() < time() - TwitterUser::FOLLOW_UPDATE_FREQUENCY) {
            $user->updateFollows();
            $this->em->persist($user);
            $this->em->flush();
        }

        $page     = $this->in->getUint('page');
        $page     = max(1, $page);
        $per_page = 25;

        $friends = $user->getFriends($page, $per_page);
        $more    = count($user->getFriends(($page + 1) * $per_page, 1)) > 0;
        if ($more) {
            $friends = array_slice($friends, 0, $per_page, true);
        }

        foreach ($friends as $friend) {
            $friend->friend_user->registerStub();
        }

        return $this->render('AgentBundle:TwitterUser:view-user-following.html.twig', [
            'user'      => $user,
            'accounts'  => $accounts,
            'account'   => $account,
            'friends'   => $friends,
            'more'      => $more,
            'more_page' => $page + 1,
        ]);
    }

    public function viewUserFollowersAction($user_id)
    {
        $user                     = $this->_getUser($user_id);
        list($account, $accounts) = $this->_getCurrentAccounts();

        if (!$user->last_follow_update || $user->last_follow_update->getTimeStamp() < time() - TwitterUser::FOLLOW_UPDATE_FREQUENCY) {
            $user->updateFollows();
            $this->em->persist($user);
            $this->em->flush();
        }

        $page     = $this->in->getUint('page');
        $page     = max(1, $page);
        $per_page = 25;

        $followers = $user->getFollowers($page, $per_page + 1);
        $more      = count($user->getFollowers(($page + 1) * $per_page, 1)) > 0;
        if ($more) {
            $followers = array_slice($followers, 0, $per_page, true);
        }

        foreach ($followers as $follower) {
            $follower->follower_user->registerStub();
        }

        return $this->render('AgentBundle:TwitterUser:view-user-followers.html.twig', [
            'user'      => $user,
            'accounts'  => $accounts,
            'account'   => $account,
            'followers' => $followers,
            'more'      => $more,
            'more_page' => $page + 1,
        ]);
    }

    protected function _getUser($user_id)
    {
        if (!$user_id) {
            throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException();
        }

        $user = $this->em->getRepository('DeskPRO:TwitterUser')->find($user_id);
        if (!$user) {
            $account = $this->em->getRepository('DeskPRO:TwitterAccount')->getFirst();

            try {
                $response = $account->getTwitterApi()->get_usersShow(['user_id' => $user_id]);
                if ($response->id_str) {
                    $user = \Application\DeskPRO\Entity\TwitterUser::createFromJson($response);
                    $this->em->persist($user);
                    $this->em->flush();
                }
            } catch (\EpiTwitterException $e) {
            } catch (\EpiOAuthException $e) {
            }
        }

        if (!$user) {
            throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException();
        }

        return $user;
    }

    protected function _getCurrentAccounts()
    {
        $account_id = $this->in->getUint('account_id');
        if ($account_id) {
            $this->person->setPreference('agent.ui.last_twitter_account', $account_id);
        } else {
            $account_id = $this->person->getPref('agent.ui.last_twitter_account');
        }

        if ($account_id) {
            $accounts = $this->person->getTwitterAccounts();
            $account  = $this->em->getRepository('DeskPRO:TwitterAccount')->find($account_id);
        } else {
            $accounts = $this->person->getTwitterAccounts();
            $account  = $accounts->first();
        }

        if (!$account || !$account->hasPerson($this->person)) {
            $account = null;
        }

        return [$account, $accounts];
    }

    public function messageOverlayAction($user_id)
    {
        $user = $this->getUserOr404($user_id);

        $accounts = $this->person->getTwitterAccounts();
        if (!$accounts) {
            throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException();
        }

        $account = count($accounts) == 1 ? $accounts[0] : false;

        return $this->render('AgentBundle:TwitterUser:message-overlay.html.twig', [
            'user'     => $user,
            'accounts' => $accounts,
            'account'  => $account,
        ]);
    }

    public function ajaxSaveFollowAction()
    {
        $account = $this->getAccountOr404($this->in->getInt('account_id'));
        $user    = $this->getUserOr404($this->in->getInt('user_id'));

        try {
            $account->getTwitterApi()->post_friendshipsCreate([
                'user_id' => $user->id,
            ]);
        } catch (\EpiTwitterException $e) {
            // likely already following
        }

        $friend = $this->em->getRepository('DeskPRO:TwitterAccountFriend')
            ->findOneByAccountIdAndUserId($account['id'], $user['id']);

        if (!$friend) {
            $friend            = new TwitterAccountFriend();
            $friend['account'] = $account;
            $friend['user']    = $user;

            $this->em->persist($friend);
            $this->em->flush();

            App::getContainer()->get('event_dispatcher')->dispatch(LegacySystemEvent::EVENT_NAME, new LegacySystemEvent(
                'agent.twitter-friend',
                [
                    'action'     => 'new',
                    'account_id' => $account->getId(),
                ]
            ));

            $follower = $this->em->getRepository('DeskPRO:TwitterAccountFollower')
                ->findOneByAccountIdAndUserId($account['id'], $user['id']);
            if ($follower) {
                $old = $follower->is_archived;

                $follower->is_archived = true;

                $this->em->persist($follower);
                $this->em->flush();

                if ($follower->is_archived != $old) {
                    App::getContainer()->get('event_dispatcher')->dispatch(LegacySystemEvent::EVENT_NAME, new LegacySystemEvent(
                        'agent.twitter-follower',
                        [
                            'action'     => $follower->is_archived ? 'archived' : 'unarchived',
                            'account_id' => $account->getId(),
                        ]
                    ));
                }
            }
        }

        $success = true;

        return $this->createJsonResponse(['success' => $success]);
    }

    public function ajaxSaveUnfollowAction()
    {
        $account = $this->getAccountOr404($this->in->getInt('account_id'));
        $user    = $this->getUserOr404($this->in->getInt('user_id'));

        try {
            $account->getTwitterApi()->post_friendshipsDestroy([
                'user_id' => $user->id,
            ]);
        } catch (\EpiTwitterException $e) {
            // likely not following already
        }

        $friend = $this->em->getRepository('DeskPRO:TwitterAccountFriend')
            ->findOneByAccountIdAndUserId($account['id'], $user['id']);

        if ($friend) {
            $this->em->remove($friend);
            $this->em->flush();

            App::getContainer()->get('event_dispatcher')->dispatch(LegacySystemEvent::EVENT_NAME, new LegacySystemEvent(
                'agent.twitter-friend',
                [
                    'action'     => 'removed',
                    'account_id' => $account->getId(),
                ]
            ));
        }

        $success = true;

        return $this->createJsonResponse(['success' => $success]);
    }

    public function ajaxSaveArchiveAction()
    {
        $account = $this->getAccountOr404($this->in->getInt('account_id'));
        $user    = $this->getUserOr404($this->in->getInt('user_id'));

        $follower = $this->em->getRepository('DeskPRO:TwitterAccountFollower')
            ->findOneByAccountIdAndUserId($account['id'], $user['id']);

        if ($follower) {
            $old = $follower->is_archived;

            $follower->is_archived = $this->in->getBool('archive');

            $this->em->persist($follower);
            $this->em->flush();

            if ($follower->is_archived != $old) {
                App::getContainer()->get('event_dispatcher')->dispatch(LegacySystemEvent::EVENT_NAME, new LegacySystemEvent(
                    'agent.twitter-follower',
                    [
                        'action'     => $follower->is_archived ? 'archived' : 'unarchived',
                        'account_id' => $account->getId(),
                    ]
                ));
            }
        }

        return $this->createJsonResponse(['success' => true]);
    }

    public function ajaxSavePersonAction()
    {
        $user = $this->getUserOr404($this->in->getInt('user_id'));

        $person = $this->em->getRepository('DeskPRO:Person')->find($this->in->getUint('person_id'));
        if ($person) {
            $details = $this->_addTwitterAssociation($person, $user->id, $user->screen_name);
            if ($details) {
                return $this->createJsonResponse([
                    'success' => true,
                    'html'    => $this->renderView('AgentBundle:TwitterUser:part-possible-person.html.twig', [
                        'person' => $person,
                    ]),
                ]);
            }
        }

        return $this->createJsonResponse(['success' => false]);
    }

    public function ajaxSaveOrganizationAction()
    {
        $user = $this->getUserOr404($this->in->getInt('user_id'));

        $org = $this->em->getRepository('DeskPRO:Organization')->find($this->in->getUint('organization_id'));
        if ($org) {
            $details = $this->_addTwitterAssociation($org, $user->id, $user->screen_name);
            if ($details) {
                return $this->createJsonResponse([
                    'success' => true,
                    'html'    => $this->renderView('AgentBundle:TwitterUser:part-possible-organization.html.twig', [
                        'org' => $org,
                    ]),
                ]);
            }
        }

        return $this->createJsonResponse(['success' => false]);
    }

    protected function _addTwitterAssociation($entity, $user_id, $screen_name)
    {
        if ($entity instanceof \Application\DeskPRO\Entity\Person) {
            $table  = 'people_twitter_users';
            $column = 'person_id';
        } else {
            $table  = 'organizations_twitter_users';
            $column = 'organization_id';
        }

        App::getDb()->executeUpdate("
            INSERT IGNORE INTO $table
                ($column, twitter_user_id, screen_name, is_verified)
            VALUES (?, ?, ?, 0)
        ", [$entity->id, $user_id, $screen_name]);

        $has_account = false;
        foreach ($entity->getContactData('twitter') as $twitter_details) {
            if ($twitter_details->field_1 == $screen_name || ($twitter_details->field_3 && $twitter_details->field_3 == $user_id)) {
                $has_account = true;
            }
        }

        $twitter_details = null;

        if (!$has_account) {
            if ($table == 'organizations_twitter_users') {
                $twitter_details = new \Application\DeskPRO\Entity\OrganizationContactData();
            } else {
                $twitter_details = new \Application\DeskPRO\Entity\PersonContactData();
            }
            $twitter_details->contact_type = 'twitter';
            if ($table == 'organizations_twitter_users') {
                $twitter_details->organization = $entity;
            } else {
                $twitter_details->person = $entity;
            }
            $twitter_details->field_1  = $screen_name;
            $twitter_details->field_2  = '0';
            $twitter_details->field_3  = $user_id;
            $twitter_details->field_10 = '';
            $this->em->persist($twitter_details);
        }

        $this->em->flush();

        return $twitter_details;
    }

    public function ajaxSaveMessageAction()
    {
        $account = $this->getAccountOr404($this->in->getInt('account_id'));
        $user    = $this->getUserOr404($this->in->getInt('user_id'));

        $success = false;
        $error   = null;

        $text  = $this->in->getString('text');
        $type  = $this->in->getValue('type');
        $split = $this->in->getBool('split');
        if (strlen($text)) {
            if ($type == 'public' && strpos($text, '@'.$user->screen_name) === false) {
                $text = '@'.$user->screen_name.' '.$text;
            }

            $twitter_service = new \Application\DeskPRO\Service\Twitter();
            $response        = $twitter_service->sendAccountMessage($type, $text, $split, $account, null, $user);

            $success = $response['success'];
            $error   = $response['error'];
        } else {
            $error = 'No text specified.';
        }

        return $this->createJsonResponse([
            'success' => $success,
            'error'   => $error,
        ]);
    }

    /**
     * List the followers for an account.
     */
    public function listFollowersAction($account_id)
    {
        $account = $this->getAccountOr404($account_id);

        $this->person->setPreference('agent.ui.last_twitter_account', $account->id);

        $page = $this->in->getUint('page');
        if (!$page) {
            $page = 1;
        }
        $per_page = 100;

        $total_count = $account->countFollowers();

        $params = [
            'account'     => $account,
            'followers'   => $account->getFollowers($page, $per_page),
            'page'        => $page,
            'per_page'    => $per_page,
            'total_count' => $total_count,
            'showing_to'  => min($total_count, $page * $per_page),
        ];

        if ($this->in->getBool('partial')) {
            return $this->render('AgentBundle:TwitterUser:part-followers.html.twig', $params);
        }

        return $this->render('AgentBundle:TwitterUser:list-followers.html.twig', $params);
    }

    /**
     * List the followers for an account.
     */
    public function listFollowingAction($account_id)
    {
        $account = $this->getAccountOr404($account_id);

        $this->person->setPreference('agent.ui.last_twitter_account', $account->id);

        $page = $this->in->getUint('page');
        if (!$page) {
            $page = 1;
        }
        $per_page = 100;

        $total_count = $account->countFollowing();

        $params = [
            'account'     => $account,
            'followers'   => $account->getFollowing($page, $per_page),
            'page'        => $page,
            'per_page'    => $per_page,
            'total_count' => $total_count,
            'showing_to'  => min($total_count, $page * $per_page),
        ];

        if ($this->in->getBool('partial')) {
            return $this->render('AgentBundle:TwitterUser:part-followers.html.twig', $params);
        }

        return $this->render('AgentBundle:TwitterUser:list-following.html.twig', $params);
    }

    public function listNewFollowersAction($account_id)
    {
        $account = $this->getAccountOr404($account_id);

        $this->person->setPreference('agent.ui.last_twitter_account', $account->id);

        $page = $this->in->getUint('page');
        if (!$page) {
            $page = 1;
        }
        $per_page = 100;

        $total_count = $account->countNewFollowers();

        $params = [
            'account'     => $account,
            'followers'   => $account->getNewFollowers($page, $per_page),
            'page'        => $page,
            'per_page'    => $per_page,
            'total_count' => $total_count,
            'showing_to'  => min($total_count, $page * $per_page),
        ];

        if ($this->in->getBool('last')) {
            $params['follower'] = end($params['followers']);

            return $this->render('AgentBundle:TwitterUser:part-follower.html.twig', $params);
        }

        if ($this->in->getBool('partial')) {
            return $this->render('AgentBundle:TwitterUser:part-followers.html.twig', $params);
        }

        return $this->render('AgentBundle:TwitterUser:list-new-followers.html.twig', $params);
    }
}
