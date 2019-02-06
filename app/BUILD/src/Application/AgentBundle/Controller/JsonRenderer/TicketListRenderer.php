<?php

/**
 * DeskPRO.
 */

namespace Application\AgentBundle\Controller\JsonRenderer;

use Application\DeskPRO\App;
use Application\DeskPRO\Entity\Brand;
use Application\DeskPRO\Entity\Organization;
use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\People\PermissionChecker\TicketChecker;
use Application\DeskPRO\Tickets\TicketResultsDisplay;
use Application\DeskPRO\Util;
use Orb\Util\Arrays;

class TicketListRenderer
{
    /**
     * @var \Application\DeskPRO\DependencyInjection\DeskproContainer
     */
    private $container;

    /**
     * @var \Doctrine\ORM\EntityManager
     */
    private $em;

    /**
     * @var \Application\DeskPRO\DBAL\Connection
     */
    private $db;

    /**
     * @var \Application\DeskPRO\Entity\Organization[]
     */
    private $cache_orgs;

    /**
     * @var \Application\DeskPRO\Tickets\TicketResultsDisplay
     */
    private $ticket_display;

    /**
     * @var \Application\DeskPRO\Entity\Person|null
     */
    protected $person;

    /**
     * @param TicketResultsDisplay $ticket_display
     */
    public function __construct(TicketResultsDisplay $ticket_display)
    {
        $this->ticket_display = $ticket_display;
        $this->container      = App::getContainer();
        $this->em             = App::getContainer()->getEm();
        $this->db             = App::getContainer()->getDb();
        $this->person         = $this->container->get('session')->getPerson();
    }

    /**
     * @param null|callback $fn_visitor
     *
     * @return array
     */
    public function renderTicketDisplayArray($fn_visitor = null)
    {
        if (!$this->ticket_display->getCount()) {
            return [];
        }

        //------------------------------
        // Precache data
        //------------------------------

        $org_ids    = [];
        $person_ids = [];
        $ticket_ids = [];

        foreach ($this->ticket_display->getTickets() as $ticket) {
            $ticket_ids[] = $ticket->getId();
            if ($ticket->getPerson()) {
                $person_ids[] = $ticket->getPersonId();
            }

            if ($ticket->getOrganization()) {
                $org_ids[] = $ticket->getOrganization()->getId();
            }
        }

        if ($org_ids) {
            $this->cache_orgs = $this->em->getRepository(Organization::class)->getByIds($org_ids);
            $this->cache_orgs = Arrays::keyFromData($this->cache_orgs, 'id');
        }

        //------------------------------
        // Generate data array
        //------------------------------

        $json_array = [];

        foreach ($this->ticket_display->getTickets() as $ticket) {
            $data = $this->renderTicket($ticket);

            if ($fn_visitor) {
                $data = call_user_func($fn_visitor, $ticket, $data);
            }

            $json_array[] = $data;
        }

        return $json_array;
    }

    /**
     * @return string
     */
    public function renderTicketDisplayJson()
    {
        if (!$this->ticket_display->getCount()) {
            return '[]';
        }

        return Util::jsonEncode($this->renderTicketDisplayArray());
    }

    /**
     * @param Ticket $ticket
     *
     * @return array
     */
    private function renderTicket(Ticket $ticket)
    {
        $data = [];

        $data['id']                     = $ticket->getId();
        $data['ref']                    = $ticket->getRef();
        $data['auth']                   = $ticket->getAuth();
        $data['sent_to_address']        = $ticket->getSentToAddresses();
        $data['creation_system']        = $ticket->getCreationSystem();
        $data['creation_system_option'] = $ticket->getCreationSystemOption();
        $data['ticket_hash']            = $ticket->getTicketHash();
        $data['status']                 = $ticket->getStatus();
        $data['hidden_status']          = $ticket->getHiddenStatus();
        $data['is_hold']                = $ticket->isHold();
        $data['urgency']                = $ticket->getUrgency();
        $data['count_agent_replies']    = $ticket->getCountAgentReplies();
        $data['count_user_replies']     = $ticket->getCountUserReplies();
        $data['feedback_rating']        = $ticket->getFeedbackRating();

        $dateFields = [
            'date_feedback_rating'    => 'getDateFeedbackRating',
            'date_created'            => 'getDateCreated',
            'date_resolved'           => 'getDateResolved',
            'date_archived'           => 'getDateArchived',
            'date_first_agent_assign' => 'getDateFirstAgentAssign',
            'date_first_agent_reply'  => 'getDateFirstAgentReply',
            'date_last_agent_reply'   => 'getDateLastAgentReply',
            'date_last_user_reply'    => 'getDateLastUserReply',
            'date_agent_waiting'      => 'getDateAgentWaiting',
            'date_user_waiting'       => 'getDateUserWaiting',
            'date_status'             => 'getDateStatus',
            'date_locked'             => 'getDateLocked',
        ];

        $timezone = $this->person ? new \DateTimeZone($this->person->getTimezone()) : null;
        foreach ($dateFields as $field => $getter) {
            $dateValue = $ticket->$getter();
            if ($dateValue instanceof \DateTime && $timezone) {
                $dateValue->setTimezone($timezone);

                $data[$field]        = $dateValue->format('Y-m-d H:i:s');
                $data["{$field}_ts"] = $dateValue->getTimestamp();
            }
        }

        $data['date_last_reply_ts'] = max(
            $data['date_created_ts'],
            isset($data['date_last_user_reply_ts']) ? $data['date_last_user_reply_ts'] : 0,
            isset($data['date_last_agent_reply_ts']) ? $data['date_last_agent_reply_ts'] : 0
        );
        $data['date_last_reply_is_agent_reply'] = isset($data['date_last_agent_reply_ts']) &&
            $data['date_last_agent_reply_ts'] > (isset($data['date_last_user_reply_ts']) ? $data['date_last_user_reply_ts'] : 0)
            && $data['date_last_agent_reply_ts'] > $data['date_created_ts'];

        $data['total_user_waiting']   = $ticket->getTotalUserWaiting();
        $data['total_to_first_reply'] = $ticket->getTotalToFirstReply();
        $data['subject']              = $ticket->getSubject();
        $data['properties']           = $ticket->getProperties();
        $data['worst_sla_status']     = $ticket->getWorstSlaStatus();
        $data['waiting_times']        = $ticket->getWaitingTimes();

        $relationFields = [
            'language',
            'department',
            'brand',
            'category',
            'priority',
            'workflow',
            'product',
            'person',
            'agent',
            'agent_team',
            'organization',
            'locked_by_agent',
        ];

        foreach ($relationFields as $field) {
            $data[$field] = null;

            if (!$ticket->$field) {
                continue;
            }

            switch ($field) {
                case 'language':
                    $lang = $this->container->getLanguageData()->get($ticket->language->getId());
                    if ($lang) {
                        $data['language'] = ['id' => $lang->id, 'title' => $lang->title];
                    }
                    break;

                case 'department':
                    $dep = $this->container->getDataService('Department')->get($ticket->department->getId());
                    if ($dep) {
                        $data['department'] = [
                            'id'         => $dep->id,
                            'title'      => $dep->title,
                            'title_full' => $dep->getFullTitle(),
                        ];

                        foreach ([80, 64, 50, 45, 32, 22, 16] as $size) {
                            $data['department']['avatar_url_'.$size] = $dep->getAvatarUrl($size);
                        }
                    }
                    break;

                case 'brand':
                    $brand = $ticket->getBrand();
                    if ($brand) {
                        $data['brand'] = [
                            'id'   => $brand->getId(),
                            'name' => $brand->getName(),
                        ];
                    }
                    break;

                case 'organization':
                    if (isset($this->cache_orgs[$ticket->organization->getId()])) {
                        $data['organization'] = [
                            'id'   => $this->cache_orgs[$ticket->organization->getId()]->getId(),
                            'name' => $this->cache_orgs[$ticket->organization->getId()]->getName(),
                        ];
                    }
                    break;

                case 'person':
                    $data['person'] = $this->renderPerson($this->ticket_display->getPerson($ticket));
                    break;

                case 'agent':
                    $agent     = $ticket->getAgent();
                    $agentData = $this->ticket_display->getAgent($ticket);

                    $data['agent'] = $agentData ? $this->renderPerson($agentData) : null;

                    // Deleted agent, render from the related object
                    if ($agent && !$data['agent']) {
                        $data['agent'] = $this->renderPerson($agent);
                    }
                    break;

                case 'agent_team':
                    $data['agent_team'] = [
                        'id'   => $ticket->agent_team['id'],
                        'name' => $ticket->agent_team['name'],
                    ];

                    foreach ([80, 64, 50, 45, 32, 22, 16] as $size) {
                        $data['agent_team']['avatar_url_'.$size] = $ticket->agent_team->getAvatarUrl($size);
                    }

                    break;

                case 'locked_by_agent':
                    $data[$field] = $this->container->getAgentData()->has($ticket->$field->getId())
                        ? $this->renderPerson($this->container->getAgentData()->get($ticket->$field->getId()))
                        : null;
                    break;

                default:
                    $data[$field] = $ticket->$field->toApiData(false, false);
            }
        }

        $data['labels']   = $this->ticket_display->getTicketLabels($ticket);
        $data['problems'] = $this->ticket_display->getTicketProblems($ticket);

        $custom_data = $this->ticket_display->getTicketFieldData($ticket);
        if ($custom_data) {
            $field_manager = $this->container->getSystemService('ticket_fields_manager');

            $rendered_data = $field_manager->getRenderedToText($field_manager->createFieldDataFromArray($custom_data));
            foreach ($rendered_data as $fid => $v) {
                $data["field{$fid}"] = $v['rendered'];
            }
        }

        $data['ticket_slas'] = [];
        foreach ($this->ticket_display->getTicketSlas($ticket) as $sla) {
            $sla['sla'] = [
                'id'    => $sla['sla_id'],
                'title' => $sla['title'],
            ];
            if ($sla['warn_date']) {
                $sla['warn_date_ts'] = \DateTime::createFromFormat('YYYY-mm-dd H:i:s', $sla['warn_date']);
            } else {
                $sla['warn_date_ts'] = 0;
            }
            if ($sla['fail_date']) {
                $sla['fail_date_ts'] = \DateTime::createFromFormat('YYYY-mm-dd H:i:s', $sla['fail_date']);
            } else {
                $sla['fail_date_ts'] = 0;
            }

            $times = [];
            if ($sla['warn_date_ts']) {
                $times[] = $sla['warn_date_ts'];
            }
            if ($sla['fail_date_ts']) {
                $times[] = $sla['fail_date_ts'];
            }
            if ($times) {
                $sla['next_trigger_date_ts'] = min($times);
            } else {
                $sla['next_trigger_date_ts'] = 0;
            }

            $data['ticket_slas'][] = $sla;
        }

        $data['previews'] = [];
        $previews         = $this->ticket_display->getTicketPreview($ticket);
        foreach ($previews as $m) {
            $data['previews'][] = [
                'message' => [
                    'id'              => $m['id'],
                    'preview_text'    => $m['preview_text'],
                    'date_created'    => $m['date_created']->format('Y-m-d H:i:s'),
                    'date_created_ts' => $m['date_created']->getTimestamp(),
                    'status'          => $m['status'],
                    'is_voice_call'   => $m['is_voice_call'],
                ],
                'person' => [
                    'id'             => $m['person_id'],
                    'display_name'   => $m['display_name'],
                    'is_agent'       => $m['is_agent'],
                    'picture_url_16' => $m['picture_url_16'],
                ],
            ];
        }

        $data['flag'] = $this->ticket_display->getFlaggedColor($ticket);
        $this->renderAvailableActions($ticket, $data);

        return $data;
    }

    /**
     * @param Person $person
     *
     * @return array
     */
    private function renderPerson(Person $person)
    {
        $data = [];

        $data['id']                    = $person->getId();
        $data['is_contact']            = $person->isContact();
        $data['is_user']               = $person->isAgent();
        $data['is_agent']              = $person->isAdmin();
        $data['was_agent']             = $person->wasAgent();
        $data['can_agent']             = $person->canAgent();
        $data['can_admin']             = $person->canAdmin();
        $data['is_confirmed']          = $person->isConfirmed();
        $data['is_deleted']            = $person->isDeleted();
        $data['is_disabled']           = $person->isDisabled();
        $data['creation_system']       = $person->getCreationSystem();
        $data['name']                  = $person->getName();
        $data['first_name']            = $person->getFirstName();
        $data['last_name']             = $person->getLastName();
        $data['title_prefix']          = $person->getTitlePrefix();
        $data['override_display_name'] = $person->getOverrideDisplayName();
        $data['summary']               = $person->getSummary();
        $data['organization_position'] = $person->getOrganizationPosition();
        $data['organization_manager']  = $person->isOrganizationManager();
        $data['timezone']              = $person->getTimezone();

        $dateCreated = $person->getDateCreated();

        $data['date_created']    = $dateCreated->format('Y-m-d H:i:s');
        $data['date_created_ts'] = $dateCreated->getTimestamp();

        $data['display_name'] = $person->getDisplayName();

        $primaryEmail = $person->getPrimaryEmail();
        if ($primaryEmail) {
            $data['primary_email'] = [
                'id'    => $primaryEmail->getId(),
                'email' => $primaryEmail->getEmail(),
            ];
        }

        $pictureSizes = [80, 64, 50, 45, 32, 22, 16];
        $urlTemplate  = $person->getPictureUrl('{{size}}');
        $encodedTag   = urlencode('{{size}}');
        foreach ($pictureSizes as $pictureSize) {
            $data['picture_url_'.$pictureSize] = $urlTemplate ? str_replace($encodedTag, $pictureSize, $urlTemplate) : null;
        }

        $data['picture_url'] = $data['picture_url_80'];

        $custom_data = $this->ticket_display->getUserFieldData($person);
        if ($custom_data) {
            $field_manager = $this->container->getSystemService('person_fields_manager');

            $rendered_data = $field_manager->getRenderedToText($field_manager->createFieldDataFromArray($custom_data));
            foreach ($rendered_data as $fid => $v) {
                $data["field{$fid}"] = $v['rendered'];
            }
        }

        return $data;
    }

    protected function renderAvailableActions(Ticket $ticket, array &$display)
    {
        $display['actions_allowed'] = [];
        if (!$this->person) {
            return;
        }
        /** @var TicketChecker $checker */
        $checker = $this->person->PermissionsManager->TicketChecker;
        $actions = ['set_resolved', 'set_awaiting_user', 'set_awaiting_agent', 'assign_self', 'assign_agent', 'assign_team'];
        foreach ($actions as $action) {
            if ($checker->canModify($ticket, $action)) {
                $display['actions_allowed'][] = $action;
            }
        }
    }
}
