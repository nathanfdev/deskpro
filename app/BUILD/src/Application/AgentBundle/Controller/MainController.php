<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2017, DeskPRO Ltd.
 *
 * The license agreement under which this software is released
 * can be found at https://www.deskpro.com/eula/
 *
 * By using this software, you acknowledge having read the license
 * and agree to be bound thereby.
 *
 * Please note that DeskPRO is not free software. We release the full
 * source code for our software because we trust our users to pay us for
 * the huge investment in time and energy that has gone into both creating
 * this software and supporting our customers. By providing the source code
 * we preserve our customers' ability to modify, audit and learn from our
 * work. We have been developing DeskPRO since 2001, please help us make it
 * another decade.
 *
 * Like the work you see? Think you could make it better? We are always
 * looking for great developers to join us: http://www.deskpro.com/jobs/
 *
 * ~ Thanks, Everyone at Team DeskPRO
 */

/**
 * DeskPRO.
 */

namespace Application\AgentBundle\Controller;

use Application\DeskPRO\App;
use Application\DeskPRO\Chat\UserChat\AvailableTrigger;
use Application\DeskPRO\DependencyInjection\SystemServices\LanguageDataService;
use Application\DeskPRO\DependencyInjection\SystemServices\OrganizationDataService;
use Application\DeskPRO\DependencyInjection\SystemServices\UsergroupDataService;
use Application\DeskPRO\Entity\AgentTeam;
use Application\DeskPRO\Entity\DataStore;
use Application\DeskPRO\Entity\Organization;
use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\TextSnippetCategory;
use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Entity\TicketDeleted;
use Application\DeskPRO\EntityRepository\DataStore as DataStoreRepository;
use Application\DeskPRO\EntityRepository\Organization as OrganizationRepository;
use Application\DeskPRO\EntityRepository\Person as PersonRepository;
use Application\DeskPRO\EntityRepository\TextSnippetCategory as TextSnippetCategoryRepository;
use Application\DeskPRO\EntityRepository\Ticket as TicketRepository;
use Application\DeskPRO\NewSearch\Manager\Doctrine;
use Application\DeskPRO\NewSearch\Manager\Elasticsearch;
use Application\DeskPRO\People\PrefNoticeSet;
use DeskPRO\Bundle\AppBundle\Serializer\Model\Notifications\NotificationClient;
use DeskPRO\Bundle\AppBundle\Settings\PortalSettingsResolver;
use DeskPRO\Component\Filesystem\SafeFile;
use Doctrine\DBAL\Connection;
use DpSys\License;
use DpSys\LowError\SystemErrorHandler;
use Orb\Util\Strings;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class MainController extends AbstractController
{
    protected $deleted_tickets = [];

    /**
     * @param string     $action
     * @param array|null $arguments
     *
     * @return bool
     */
    public function requireRequestToken($action, $arguments = null)
    {
        if ($action == 'indexAction') {
            return false;
        }

        return parent::requireRequestToken($action, $arguments);
    }

    /**
     * @return Response
     */
    public function indexAction()
    {
        $this->person->loadPrefGroup('agent.ui');

        $last_message_id = -1;

        // Used in some header menus for search options
        $titles = [];
        /** @var OrganizationDataService $organizationDataService */
        /* @var UsergroupDataService $usergroupDataService */
        $organizationDataService = $this->container->getDataService('Organization');
        $usergroupDataService    = $this->container->getDataService('Usergroup');
        $titles['organizations'] = $organizationDataService->getOrganizationNames();
        $titles['usergroups']    = $usergroupDataService->getUsergroupNames();

        /** @var LanguageDataService $languageDataService */
        $languageDataService = $this->container->getDataService('Language');
        if ($languageDataService->isMultiLang()) {
            $titles['languages'] = $languageDataService->getTitles();
        }

        // Ticket options for search pane of tickets menu
        $ticket_options = App::getApi('tickets')->getTicketOptions($this->person);

        // Agent info

        /** @var PersonRepository $personRepository */
        $personRepository = $this->em->getRepository(Person::class);
        $agents           = $personRepository->getAgents();
        $agent_teams      = $this->em->getRepository(AgentTeam::class)->findAll();

        $ticket_field_defs                      = App::getApi('custom_fields.tickets')->getEnabledFields();
        $custom_fields                          = App::getApi('custom_fields.tickets')->getFieldsDisplayArray($ticket_field_defs);
        $ticket_options['custom_ticket_fields'] = $custom_fields;

        // People stuff
        $ticket_options['people_organizations'] = $organizationDataService->getOrganizationNames();
        $people_field_defs                      = App::getApi('custom_fields.people')->getEnabledFields();
        $ticket_options['custom_people_fields'] = $custom_fields = App::getApi('custom_fields.people')->getFieldsDisplayArray($people_field_defs);

        $people_options                         = $titles;
        $people_options['custom_people_fields'] = $ticket_options['custom_people_fields'];

        $cutoff = date('Y-m-d H:i:s', time() - $this->container->getSetting('core_chat.agent_timeout'));

        $online_chat_agent_ids = $this->db->fetchAllCol('
            SELECT p.id
            FROM sessions s
            JOIN people AS p ON p.id = s.person_id
            WHERE s.is_chat_available = 1 AND p.is_agent = true AND s.date_last > ?
        ', [$cutoff]);

        $with_chat_perm    = [];
        $without_chat_perm = [];
        foreach ($this->container->getAgentData()->getAgents() as $a) {
            if ($a->hasPerm('agent_chat.use')) {
                $with_chat_perm[] = $a->id;
            } elseif (in_array($a->id, $online_chat_agent_ids)) {
                $without_chat_perm[] = $a->id;
            }
        }

        if ($without_chat_perm) {
            $this->db->executeUpdate(
                'UPDATE sessions SET is_chat_available = ? WHERE person_id IN (?)',
                [0, $without_chat_perm],
                [\PDO::PARAM_INT, Connection::PARAM_INT_ARRAY]
            );
        }

        if ($with_chat_perm) {
            $agent_chat_depmap = $this->db->fetchAllGrouped("
                SELECT department_permissions.person_id, department_permissions.department_id
                FROM department_permissions
                WHERE
                    department_permissions.person_id IN (?)
                    AND department_permissions.app = 'chat'
                    AND department_permissions.value = 1
                    AND department_permissions.is_active = 1
            ", [$with_chat_perm], 'person_id', null, 'department_id', [Connection::PARAM_INT_ARRAY]);

            foreach ($agent_chat_depmap as &$v) {
                if ($v) {
                    $v = array_unique($v, \SORT_NUMERIC);
                }
            }
        } else {
            $agent_chat_depmap = [];
        }

        $is_first_login      = false;
        $is_first_login_name = false;

        if ($this->person->getPref('agent.first_login')) {
            $is_first_login      = true;
            $is_first_login_name = $this->person->getPref('agent.first_login_name');
        }

        if ($this->container->get('deskpro.app_env')->getConfig('settings.raw_assets')
            || $this->container->get('deskpro.app_env')->getConfig('paths.raw_assets')) {
            $has_raw_assets = true;
        } else {
            $has_raw_assets = false;
        }

        AvailableTrigger::update();

        /** @var TextSnippetCategoryRepository $textSnippetCategoryRepository */
        $textSnippetCategoryRepository = $this->em->getRepository(TextSnippetCategory::class);
        $ticket_snippet_cats           = $textSnippetCategoryRepository->getCatsForAgent('tickets', $this->person);
        $chat_snippet_cats             = $textSnippetCategoryRepository->getCatsForAgent('chat', $this->person);

        $is_billing_error = false;
        $lic              = License::getLicense();
        if ($lic->isPastExpireDate()) {
            $is_billing_error = true;
        } elseif (defined('DPC_IS_CLOUD') && DPC_BILL_FAILED) {
            $is_billing_error = true;
        }

        return $this->render('AgentBundle:Main:index.html.twig', [
            'has_raw_assets'      => $has_raw_assets,
            'is_demo'             => $this->in->checkIsset('show-demo-bar'),
            'is_billing_error'    => $is_billing_error,
            'last_message_id'     => $last_message_id,
            'js_debug'            => App::getConfig('debug.js', []),
            'reduce_poll_rate'    => $this->shouldReducePollRate(),
            'is_first_login'      => $is_first_login,
            'is_first_login_name' => $is_first_login_name,
            'timezones'           => \DateTimeZone::listIdentifiers(),
            'agents'              => $agents,
            'agent_teams'         => $agent_teams,
            'agent_chat_depmap'   => $agent_chat_depmap,
            'chat_dep_ids'        => $this->person->getHelper('AgentPermissions')->getAllowedDepartments('chat'),
            'ticket_snippet_cats' => $ticket_snippet_cats,
            'chat_snippet_cats'   => $chat_snippet_cats,
            'brand_app_settings'  => $this->getBrandAppSettings(),
        ]);
    }

    private function shouldReducePollRate()
    {
        $clients = $this->get('deskpro.notification.service')->getClientsSetup()->getClients();
        $client  = $clients && count($clients) === 1 ? $clients[0] : false;
        /* @var NotificationClient|bool $client */
        return !$client ?: $client->getType() === 'pusher';
    }

    /**
     * @param int $id
     *
     * @return Response
     */
    public function loadVersionNoticeAction($id)
    {
        $id         = preg_replace('#[^a-zA-Z0-9_\-]#', 'x', $id);
        $target_dir = DP_ROOT.'/docs/changelog/'.$id;
        if (!is_dir($target_dir)) {
            throw $this->createNotFoundException();
        }

        $html = SafeFile::fileGetContents($target_dir.'/log.html', DP_ROOT.'/docs/changelog');
        $html = Strings::extractRegexMatch('#<body>(.*?)</body>#s', $html, 1);

        if (preg_match_all('#<[^>]+src=(\'|")(.*?)(\'|")[^>]+>#', $html, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $m) {
                $attach_path = $target_dir.'/'.$m[2];
                if (file_exists($attach_path)) {
                    if (Strings::getExtension($attach_path) == 'png') {
                        $type = 'image/png;';
                    } elseif (Strings::getExtension($attach_path) == 'gif') {
                        $type = 'image/gif;';
                    } else {
                        $type = '';
                    }
                    $url = "data:{$type}base64,".base64_encode(SafeFile::fileGetContents($attach_path, DP_ROOT.'/docs/changelog'));

                    $str  = $m[0];
                    $str  = str_replace($m[2], $url, $str);
                    $html = str_replace($m[0], $str, $html);
                }
            }
        }

        return $this->createResponse($html);
    }

    /**
     * @param int $id
     *
     * @return Response
     */
    public function dismissVersionNoticeAction($id)
    {
        $version_notices = new PrefNoticeSet(
            $this->db,
            $this->person,
            'agent.ui.version_notices',
            DP_ROOT.'/docs/changelog/docs.php'
        );

        if ($id == 'ALL') {
            foreach ($version_notices->getWaitingIds() as $id) {
                $version_notices->dismiss($id);
            }
        } else {
            $version_notices->dismiss($id);
        }
        $version_notices->save();

        return $this->createJsonResponse(['success' => true]);
    }

    /**
     * @return Response
     */
    public function getCombinedSectionDataAction()
    {
        $data = [];

        foreach ($this->in->getCleanValueArray('section_ids', 'str_simple', 'discard') as $name) {
            switch ($name) {
                case 'tickets_section':
                    $data[$name] = json_decode($this->forward('AgentBundle:TicketSearch:getSectionData')->getContent());
                    break;

                case 'chat_section':
                    $data[$name] = json_decode($this->forward('AgentBundle:UserChat:getSectionData')->getContent());
                    break;

                case 'twitter_section':
                    $data[$name] = json_decode($this->forward('AgentBundle:Twitter:getSectionData')->getContent());
                    break;

                case 'people_section':
                    $data[$name] = json_decode($this->forward('AgentBundle:PeopleSearch:getSectionData')->getContent());
                    break;

                case 'feedback_section':
                    $data[$name] = json_decode($this->forward('AgentBundle:Feedback:getSectionData')->getContent());
                    break;

                case 'publish_section':
                    $data[$name] = json_decode($this->forward('AgentBundle:Publish:getSectionData')->getContent());
                    break;

                case 'tasks_section':
                    $data[$name] = json_decode($this->forward('AgentBundle:Task:getSectionData')->getContent());
                    break;

                case 'agent_chat_section':
                    $data[$name] = json_decode($this->forward('AgentBundle:AgentChat:getSectionData')->getContent());
                    break;
            }
        }

        return $this->createJsonResponse($data);
    }

    /**
     * @return Response
     */
    public function loadRecentTabsAction()
    {
        $recent_tabs = $this->db->fetchColumn("
            SELECT value_array
            FROM people_prefs
            WHERE person_id = ? AND name = 'agent.ui.recent_tabs_collection'
        ", [$this->person->getId()]);

        if ($recent_tabs) {
            $recent_tabs = @unserialize($recent_tabs);
        }

        if (!$recent_tabs) {
            $recent_tabs = [];
        } else {
            uasort($recent_tabs, function ($a, $b) {
                if ($a[4] == $b[4]) {
                    return 0;
                }

                return ($a[4] < $b[4]) ? -1 : 1;
            });
        }

        return $this->createJsonResponse(array_values($recent_tabs));
    }

    /**
     * @return Response
     */
    public function quickSearchAction()
    {
        $q    = $this->in->getString('q');
        $sort = $this->in->getString('sort');

        $results = [
            'article'              => [],
            'download'             => [],
            'feedback'             => [],
            'news'                 => [],
            'ticket'               => [],
            'person'               => [],
            'person_related'       => [],
            'organization'         => [],
            'organization_related' => [],
            'chat'                 => [],
            'topic'                => [],
        ];

        $resultMeta = [];
        $peopleTop  = false;

        if (!$q) {
            return $this->render('AgentBundle:Main:quicksearch.json.jsonphp', [
                'q'           => $q,
                'router'      => App::getRouter(),
                'results'     => $results,
                'result_meta' => $resultMeta,
                'people_top'  => $peopleTop,
            ]);
        }

        if ($this->container->getSetting('elastica.enabled')) {
            try {
                return $this->searchInElasticsearch($q, $sort);
            } catch (\Exception $e) {
                SystemErrorHandler::logException($e);

                // fallback on DB search
                return $this->searchInDB($q);
            }
        } else {
            return $this->searchInDB($q);
        }
    }

    /**
     * @param string      $q
     * @param string|null $sort
     *
     * @return Response
     */
    private function searchInElasticsearch($q, $sort = null)
    {
        /** @var Elasticsearch $elasticsearch */
        $elasticsearch = $this->container->get('deskpro.search_manager.elasticsearch');
        $elasticsearch->setPersonContext($this->person);

        try {
            list($results, $resultMeta, $peopleTop) = $elasticsearch->quickSearch($q, $sort);

            $returnResults = [];

            if ($results) {
                foreach ($results as $type => $rawRows) {
                    $rows = [];
                    foreach ($rawRows as $r) {
                        $rows[] = $r;
                    }

                    $returnResults[] = [
                        'type'    => $type,
                        'title'   => $this->container->getTranslator()->phrase('agent.search.type_'.$type),
                        'results' => $rows,
                    ];
                }
            }

            foreach ($returnResults as &$group) {
                $group['results'] = $this->renderSearchResults($group['type'], $group['results']);
            }

            /** @var DataStoreRepository $dataStoryRepository */
            $dataStoryRepository = $this->em->getRepository(DataStore::class);
            $esStatus            = $dataStoryRepository->getByName('sys.es_indexer', false);
            $timecut             = new \DateTime('-10 minutes');
            if ($esStatus && $esStatus->getData('status') == 'running' && $esStatus->getData('date_last') && $esStatus->getData('date_last') > $timecut) {
                $indexRunning = true;
            } else {
                $indexRunning = false;
            }

            $returnResults[] = $this->getDeletedTicketResults($q);

            return $this->createJsonResponse([
                'grouped_results' => $returnResults,
                'index_running'   => $indexRunning,
                'is_elastic'      => true,
            ]);
        } catch (\Exception $e) {
            $elasticsearch->testVersion();

            throw $e;
        }
    }

    /**
     * @param string $q
     *
     * @return Response
     */
    private function searchInDB($q)
    {
        /** @var Doctrine $doctrine */
        $doctrine = $this->container->get('deskpro.search_manager.doctrine');
        $doctrine->setPersonContext($this->person);

        list($results, $result_meta, $people_top) = $doctrine->quickSearch($q);

        $return_results = [];

        if ($results) {
            foreach ($results as $type => $raw_rows) {
                $rows = [];
                foreach ($raw_rows as $r) {
                    if (is_object($r)) {
                        $rows[] = $r;
                    }
                }

                $return_results[] = [
                    'type'    => $type,
                    'title'   => $this->container->getTranslator()->phrase('agent.search.type_'.$type),
                    'results' => $rows,
                ];
            }
        }

        foreach ($return_results as &$group) {
            $group['results'] = $this->renderSearchResults($group['type'], $group['results']);
        }

        $return_results[] = $this->getDeletedTicketResults($q);

        return $this->createJsonResponse([
            'grouped_results' => $return_results,
        ]);
    }

    /**
     * @param string $query
     *
     * @return array
     */
    protected function getDeletedTicketResults($query)
    {
        $res = [
            'type'    => 'deleted_tickets',
            'title'   => $this->container->getTranslator()->phrase('agent.search.type_ticket_deleted'),
            'results' => [],
        ];

        if ($sub = preg_replace('/[^\d]/', '', $query)) {
            if ($deleted = $this->em->find(TicketDeleted::class, $sub)) {
                if (isset($this->deleted_tickets[$deleted['ticket_id']])) {
                    $res['results'][] = [
                        'id'     => $deleted->getTicketId(),
                        'reason' => $this->deleted_tickets[$deleted->getTicketId()]->title,
                    ];
                    unset($this->deleted_tickets[$deleted['ticket_id']]);
                } else {
                    $res['results'][] = [
                        'id'     => $deleted->getTicketId(),
                        'reason' => $deleted->getReason(),
                    ];
                }
            }
        }

        foreach ($this->deleted_tickets as $deleted) {
            if ($deleted instanceof Ticket) {
                $res['results'][] = [
                    'id'     => $deleted->getId(),
                    'reason' => $deleted->getTitle(),
                ];
            } elseif ($deleted instanceof TicketDeleted) {
                $res['results'][] = [
                    'id'     => $deleted->getTicketId(),
                    'reason' => $deleted->getReason(),
                ];
            }
        }
        $this->deleted_tickets = [];

        return $res;
    }

    /**
     * @param string $type
     * @param array  $results
     *
     * @return array
     */
    private function renderSearchResults($type, array $results)
    {
        $rows = [];

        $render_person = function (Person $person, array $counts = []) {
            $data                   = [];
            $data['picture_url']    = $person->getPictureUrl();
            $data['picture_url_80'] = $person->getPictureUrl(80);
            $data['picture_url_64'] = $person->getPictureUrl(64);
            $data['picture_url_50'] = $person->getPictureUrl(50);
            $data['picture_url_45'] = $person->getPictureUrl(45);
            $data['picture_url_32'] = $person->getPictureUrl(32);
            $data['picture_url_22'] = $person->getPictureUrl(22);
            $data['picture_url_16'] = $person->getPictureUrl(16);
            foreach (['id', 'first_name', 'last_name', 'name', 'display_name', 'override_display_name'] as $k) {
                $data[$k] = $person[$k];
            }

            if ($person->primary_email) {
                $data['primary_email'] = [
                    'id'    => (int) $person->primary_email->id,
                    'email' => $person->primary_email->email,
                ];
            } else {
                $data['primary_email'] = null;
            }

            if (isset($counts[$person['id']])) {
                $data['tickets_count'] = $counts[$person['id']];
            }

            return $data;
        };

        $render_org = function (Organization $org, array $counts = []) {
            return [
                'id'      => $org->getId(),
                'name'    => $org->getName(),
                'members' => isset($counts[$org->getId()]) ? $counts[$org->getId()] : 0,
            ];
        };

        switch ($type) {
            case 'ticket':
                $ticket_display = new \Application\DeskPRO\Tickets\TicketResultsDisplay($results);
                $ticket_display->setPersonContext($this->person);

                foreach ($results as $r) {
                    $ticket_info = [
                        'id'      => $r->id,
                        'subject' => $r->subject,
                        'status'  => $r->status,
                        'urgency' => $r->urgency,
                        'person'  => null,
                        'agent'   => null,
                    ];

                    $agent = $ticket_display->getAgent($r);
                    if ($agent) {
                        $ticket_info['agent'] = $render_person($agent);
                    }

                    $person = $ticket_display->getPerson($r);
                    if ($person) {
                        $ticket_info['person'] = $render_person($person);
                    }

                    if ('deleted' === $r->hidden_status) {
                        $this->deleted_tickets[$r->id] = $r;
                    } else {
                        $rows[] = $ticket_info;
                    }
                }
                break;

            case 'person':
                /** @var TicketRepository $ticketRepository */
                $ticketRepository = $this->em->getRepository(Ticket::class);
                $counts           = $ticketRepository->getTicketCountsForPeople($results, $this->getPerson());
                foreach ($results as $r) {
                    $rows[] = $render_person($r, $counts);
                }
                break;

            case 'chat_conversation':
                foreach ($results as $r) {
                    $chat_info = [
                        'id'      => $r->id,
                        'subject' => $r->subject,
                        'person'  => null,
                        'agent'   => null,
                    ];

                    $agent = $r->agent;
                    if ($agent) {
                        $chat_info['agent'] = $render_person($agent);
                    }

                    $person = $r->person;
                    if ($person) {
                        $chat_info['person'] = $render_person($person);
                    }

                    $rows[] = $chat_info;
                }
                break;

            case 'organization':
                /** @var OrganizationRepository $organizationRepository */
                $organizationRepository = $this->em->getRepository('DeskPRO:Organization');
                $counts                 = $organizationRepository->countMembers($results);
                foreach ($results as $r) {
                    $rows[] = $render_org($r, $counts);
                }
                break;

            case 'article':
            case 'news':
            case 'feedback':
            case 'download':
            case 'topic':
                foreach ($results as $r) {
                    $rows[] = [
                        'id'    => $r->id,
                        'title' => $r->title,
                    ];
                }
        }

        return $rows;
    }

    /**
     * @param Request $request
     *
     * @return Response
     */
    public function getPersonTicketsAction(Request $request)
    {
        if (!$person = $this->em->find(Person::class, $request->get('person_id'))) {
            throw new NotFoundHttpException();
        }

        $sort = 'date_created';
        if ('date_active' === $request->get('sort')) {
            $sort = 'date_last_reply';
        }

        /** @var TicketRepository $rep */
        $rep   = $this->em->getRepository(Ticket::class);
        $limit = $request->get('all') ? null : 15;

        $permissionsHelper          = $this->getPerson()->getHelper('AgentPermissions');
        $allowedTicketDepartmentIds = $permissionsHelper->getAllowedDepartments('tickets', false, 'assign');

        $tickets = $rep->getPersonTickets($person, $this->getPerson(), $limit, $sort, 'DESC', $allowedTicketDepartmentIds);

        return $this->createJsonResponse([
            'results' => $this->renderSearchResults('ticket', $tickets),
        ]);
    }

    /**
     * @param Request $request
     *
     * @return Response
     */
    public function getOrgMembersAction(Request $request)
    {
        if (!$org = $this->em->find(Organization::class, $request->get('org_id'))) {
            throw new NotFoundHttpException();
        }

        /** @var OrganizationRepository $rep */
        $rep     = $this->em->getRepository(Organization::class);
        $limit   = $request->get('all') ? null : 15;
        $members = $rep->getOrgMembers($org, $limit);

        return $this->createJsonResponse([
            'results' => $this->renderSearchResults('person', $members),
        ]);
    }

    /**
     * @return array
     */
    protected function getBrandAppSettings()
    {
        $appSettings = [
            PortalSettingsResolver::APPS_KB        => false,
            PortalSettingsResolver::APPS_DOWNLOADS => false,
            PortalSettingsResolver::APPS_NEWS      => false,
            PortalSettingsResolver::APPS_FEEDBACK  => false,
            PortalSettingsResolver::APPS_GUIDES    => false,
            'core.apps_tasks'                      => false,
        ];

        $brandSettingsResolver = $this->get('brand_aware_settings_resolver');
        foreach ($appSettings as $name => $default) {
            $appSettings[$name] = (bool) $brandSettingsResolver->getAnyBrandSetting($name, $default);
        }

        return $appSettings;
    }
}
