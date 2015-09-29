<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2015, DeskPRO Ltd.
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
use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Entity\TicketDeleted;
use Application\DeskPRO\EntityRepository\Ticket as TicketRepository;
use Application\DeskPRO\People\PrefNoticeSet;
use DeskPRO\Kernel\KernelErrorHandler;
use Orb\Util\Strings;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class MainController extends AbstractController
{
    public function requireRequestToken($action, $arguments = null)
    {
        if ($action == 'indexAction') {
            return false;
        }

        return parent::requireRequestToken($action, $arguments);
    }

    public function indexAction()
    {
        $this->person->loadPrefGroup('agent.ui');

        $last_message_id = $this->db->fetchColumn('
            SELECT id
            FROM client_messages
            ORDER BY id DESC
            LIMIT 1
        ');
        if (!$last_message_id) {
            $last_message_id = -1;
        }

        $is_first_login      = false;
        $is_first_login_name = false;

        if ($this->person->getPref('agent.first_login')) {
            $is_first_login      = true;
            $is_first_login_name = $this->person->getPref('agent.first_login_name');
        }

        if (App::getConfig('debug.raw_assets')) {
            $has_raw_assets = true;
        } else {
            $has_raw_assets = false;
        }

        \Application\DeskPRO\Chat\UserChat\AvailableTrigger::update();

        return $this->render('AgentBundle:Main:index.html.twig', array(
            'has_raw_assets'      => $has_raw_assets,
            'is_demo'             => $this->in->checkIsset('show-demo-bar'),
            'last_message_id'     => $last_message_id,
            'js_debug'            => App::getConfig('debug.js', array()),
            'is_first_login'      => $is_first_login,
            'is_first_login_name' => $is_first_login_name,
            'timezones'           => \DateTimeZone::listIdentifiers(),
        ));
    }

    public function loadVersionNoticeAction($id)
    {
        $id         = preg_replace('#[^a-zA-Z0-9_\-]#', 'x', $id);
        $target_dir = DP_ROOT.'/docs/changelog/'.$id;
        if (!is_dir($target_dir)) {
            throw $this->createNotFoundException();
        }

        $html = file_get_contents($target_dir.'/log.html');
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
                    $url = "data:{$type}base64,".base64_encode(file_get_contents($attach_path));

                    $str  = $m[0];
                    $str  = str_replace($m[2], $url, $str);
                    $html = str_replace($m[0], $str, $html);
                }
            }
        }

        return $this->createResponse($html);
    }

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

        return $this->createJsonResponse(array('success' => true));
    }

    public function getCombinedSectionDataAction()
    {
        $data = array();

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

                case 'twitter_section':
                    $data[$name] = json_decode($this->forward('AgentBundle:Twitter:getSectionData')->getContent());
                    break;

                case 'agent_chat_section':
                    $data[$name] = json_decode($this->forward('AgentBundle:AgentChat:getSectionData')->getContent());
                    break;
            }
        }

        return $this->createJsonResponse($data);
    }

    public function loadRecentTabsAction()
    {
        $recent_tabs = $this->db->fetchColumn("
            SELECT value_array
            FROM people_prefs
            WHERE person_id = ? AND name = 'agent.ui.recent_tabs_collection'
        ", array($this->person->getId()));

        if ($recent_tabs) {
            $recent_tabs = @unserialize($recent_tabs);
        }

        if (!$recent_tabs) {
            $recent_tabs = array();
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

    public function quickSearchAction()
    {
        $q    = $this->in->getString('q');
        $sort = $this->in->getString('sort');

        $results = array(
            'article'              => array(),
            'download'             => array(),
            'feedback'             => array(),
            'news'                 => array(),
            'ticket'               => array(),
            'person'               => array(),
            'person_related'       => array(),
            'organization'         => array(),
            'organization_related' => array(),
            'chat'                 => array(),
        );

        $result_meta = array();
        $people_top  = false;

        if (!$q) {
            return $this->render('AgentBundle:Main:quicksearch.json.jsonphp', array(
                'q'           => $q,
                'router'      => App::getRouter(),
                'results'     => $results,
                'result_meta' => $result_meta,
                'people_top'  => $people_top,
            ));
        }

        if ($this->container->getSetting('elastica.enabled')) {
            try {
                return $this->searchInElasticsearch($q, $sort);
            } catch (\Exception $e) {
                KernelErrorHandler::logException($e);

                // fallback on DB search
                return $this->searchInDB($q);
            }
        } else {
            return $this->searchInDB($q);
        }
    }

    private function searchInElasticsearch($q, $sort = null)
    {
        $elasticsearch = $this->container->get('deskpro.search_manager.elasticsearch');
        $elasticsearch->setPersonContext($this->person);

        list($results, $result_meta, $people_top) = $elasticsearch->quickSearch($q, $sort);

        $return_results = array();

        if ($results) {
            foreach ($results as $type => $raw_rows) {
                $rows = array();
                foreach ($raw_rows as $r) {
                    $rows[] = $r;
                }

                $return_results[] = array(
                    'type'    => $type,
                    'title'   => $this->container->getTranslator()->phrase('agent.search.type_'.$type),
                    'results' => $rows,
                );
            }
        }

        foreach ($return_results as &$group) {
            $group['results'] = $this->renderSearchResults($group['type'], $group['results']);
        }

        $es_status = $this->em->getRepository('DeskPRO:DataStore')->getByName('sys.es_indexer', false);
        $timecut   = new \DateTime('-10 minutes');
        if ($es_status && $es_status->getData('status') == 'running' && $es_status->getData('date_last') && $es_status->getData('date_last') > $timecut) {
            $index_running = true;
        } else {
            $index_running = false;
        }

        $return_results[] = $this->getDeletedTicketResults($q);

        return $this->createJsonResponse(array(
            'grouped_results' => $return_results,
            'index_running'   => $index_running,
            'is_elastic'      => true,
        ));
    }

    private function searchInDB($q)
    {
        $doctrine = $this->container->get('deskpro.search_manager.doctrine');
        $doctrine->setPersonContext($this->person);

        list($results, $result_meta, $people_top) = $doctrine->quickSearch($q);

        $return_results = array();

        if ($results) {
            foreach ($results as $type => $raw_rows) {
                $rows = array();
                foreach ($raw_rows as $r) {
                    if (is_object($r)) {
                        $rows[] = $r;
                    }
                }

                $return_results[] = array(
                    'type'    => $type,
                    'title'   => $this->container->getTranslator()->phrase('agent.search.type_'.$type),
                    'results' => $rows,
                );
            }
        }

        foreach ($return_results as &$group) {
            $group['results'] = $this->renderSearchResults($group['type'], $group['results']);
        }

        $return_results[] = $this->getDeletedTicketResults($q);

        return $this->createJsonResponse(array(
            'grouped_results' => $return_results,
        ));
    }

    protected function getDeletedTicketResults($query)
    {
        $res = array(
            'type'    => 'deleted_tickets',
            'title'   => $this->container->getTranslator()->phrase('agent.search.type_ticket_deleted'),
            'results' => array(),
        );

        if (!$query = preg_replace('/[^\d]/', '', $query)) {
            return $res;
        }

        /** @var $deleted TicketDeleted */
        if (!$deleted = $this->em->find('DeskPRO:TicketDeleted', $query)) {
            return $res;
        }

        $res['results'][] = array(
            'id'     => $deleted['ticket_id'],
            'reason' => $deleted['reason'],
        );

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
        $rows = array();

        $render_person = function (Person $person, array $counts = array()) {
            $data                   = array();
            $data['picture_url']    = $person->getPictureUrl();
            $data['picture_url_80'] = $person->getPictureUrl(80);
            $data['picture_url_64'] = $person->getPictureUrl(64);
            $data['picture_url_50'] = $person->getPictureUrl(50);
            $data['picture_url_45'] = $person->getPictureUrl(45);
            $data['picture_url_32'] = $person->getPictureUrl(32);
            $data['picture_url_22'] = $person->getPictureUrl(22);
            $data['picture_url_16'] = $person->getPictureUrl(16);
            foreach (array('id', 'first_name', 'last_name', 'name', 'display_name', 'override_display_name') as $k) {
                $data[$k] = $person[$k];
            }

            if ($person->primary_email) {
                $data['primary_email'] = array(
                    'id'    => (int) $person->primary_email->id,
                    'email' => $person->primary_email->email,
                );
            } else {
                $data['primary_email'] = null;
            }

            if (isset($counts[$person['id']])) {
                $data['tickets_count'] = $counts[$person['id']];
            }

            return $data;
        };

        switch ($type) {
            case 'ticket':
                $ticket_display = new \Application\DeskPRO\Tickets\TicketResultsDisplay($results);
                $ticket_display->setPersonContext($this->person);

                foreach ($results as $r) {
                    $ticket_info = array(
                        'id'      => $r->id,
                        'subject' => $r->subject,
                        'status'  => $r->status,
                        'urgency' => $r->urgency,
                        'person'  => null,
                        'agent'   => null,
                    );

                    $agent = $ticket_display->getAgent($r);
                    if ($agent) {
                        $ticket_info['agent'] = $render_person($agent);
                    }

                    $person = $ticket_display->getPerson($r);
                    if ($person) {
                        $ticket_info['person'] = $render_person($person);
                    }

                    $rows[] = $ticket_info;
                }
                break;

            case 'person':
                $counts = $this->em->getRepository('DeskPRO:Ticket')->getTicketCountsForPeople($results);
                foreach ($results as $r) {
                    $rows[] = $render_person($r, $counts);
                }
                break;

            case 'chat_conversation':
                foreach ($results as $r) {
                    $chat_info = array(
                        'id'      => $r->id,
                        'subject' => $r->subject,
                        'person'  => null,
                        'agent'   => null,
                    );

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
                foreach ($results as $r) {
                    $rows[] = array(
                        'id'   => $r->id,
                        'name' => $r->name,
                    );
                }
                break;

            case 'article':
            case 'news':
            case 'feedback':
            case 'download':
                foreach ($results as $r) {
                    $rows[] = array(
                        'id'    => $r->id,
                        'title' => $r->title,
                    );
                }
            break;
            case 'deleted_tickets':

        }

        return $rows;
    }

    public function getPersonTicketsAction(Request $request)
    {
        if (!$person = $this->em->find('DeskPRO:Person', $request->get('person_id'))) {
            throw new NotFoundHttpException();
        }

        $sort = 'date_created';
        if ('date_active' === $request->get('sort')) {
            $sort = 'date_last_reply';
        }

        /** @var TicketRepository $rep */
        $rep     = $this->em->getRepository('DeskPRO:Ticket');
        $limit   = $request->get('all') ? null : 15;
        $tickets = $rep->getPersonTickets($person, $limit, $sort);

        return $this->createJsonResponse(array(
            'results' => $this->renderSearchResults('ticket', $tickets),
        ));
    }
}
