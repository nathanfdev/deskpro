<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\Reports;

use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\PersonPref;
use Application\DeskPRO\Entity\Sla;
use Application\DeskPRO\Log\Logger;
use Application\DeskPRO\Reports\Overview\ChatGroupingField;
use Application\DeskPRO\Reports\Overview\ChatsCreated;
use Application\DeskPRO\Reports\Overview\GroupingField;
use Application\DeskPRO\Reports\Overview\KbViewsHour;
use Application\DeskPRO\Reports\Overview\TicketsAwaitingAgent;
use Application\DeskPRO\Reports\Overview\TicketSlaStatus;
use Application\DeskPRO\Reports\Overview\TicketsOpenedHour;
use Application\DeskPRO\Reports\Overview\TicketsResolved;
use Application\DeskPRO\Reports\Overview\TicketsResponseTime;
use Application\DeskPRO\Reports\Overview\TicketsStatus;
use Application\DeskPRO\Reports\Overview\TicketsUserWaitingTime;
use Doctrine\ORM\EntityManager;
use Orb\Util\OptionsArray;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class Overview
{
    /**
     * @var \Doctrine\ORM\EntityManager
     */
    protected $em;

    /**
     * The currently logged in person.
     *
     * @var \Application\DeskPRO\Entity\Person
     */
    protected $person;

    /**
     * @var bool
     */
    protected $no_data_mode = false;

    /**
     * @var \Application\DeskPRO\Log\Logger
     */
    protected $logger;

    public function __construct(EntityManager $em)
    {
        $this->em     = $em;
        $logger       = new Logger();
        $this->logger = $logger;
    }

    /**
     * @param string $type
     * @param array  $options
     *
     * @return array
     */
    public function getOverviewData($type, array $options = [])
    {
        $this->person->loadPrefGroup('reports.ui.overview.options');

        return $this->getValues($type, $options);
    }

    /**
     * @param string $type
     * @param string $grouping_field
     * @param array  $options
     *
     * @throws NotFoundHttpException
     *
     * @return array
     */
    public function getStats($type, $grouping_field = null, $options = [])
    {
        $this->person->loadPrefGroup('reports.ui.overview.options');

        /** @var \Application\DeskPRO\EntityRepository\PersonPref $personPrefRepository */
        $personPrefRepository = $this->em->getRepository(PersonPref::class);

        switch ($type) {

            case 'tickets_awaiting_agent':

                $personPrefRepository->savePref(
                    $this->person,
                    'reports.ui.overview.options.tickets_awaiting_agent.grouping',
                    $grouping_field
                );

                return $this->getValues('tickets_awaiting_agent', ['grouping_field' => $grouping_field]);

            case 'tickets_resolved':

                $date_choice = $options['date_choice'];

                $personPrefRepository->savePref(
                    $this->person,
                    'reports.ui.overview.options.tickets_resolved.grouping',
                    $grouping_field
                );
                $personPrefRepository->savePref(
                    $this->person,
                    'reports.ui.overview.options.tickets_resolved.date_choice',
                    $date_choice
                );

                return $this->getValues(
                    'tickets_resolved',
                    ['grouping_field' => $grouping_field, 'date_choice' => $date_choice]
                );

            case 'tickets_response_time':

                $date_choice = $options['date_choice'];

                $personPrefRepository->savePref(
                    $this->person,
                    'reports.ui.overview.options.tickets_response_time.grouping',
                    $grouping_field
                );
                $personPrefRepository->savePref(
                    $this->person,
                    'reports.ui.overview.options.tickets_response_time.date_choice',
                    $date_choice
                );

                return $this->getValues(
                    'tickets_response_time',
                    ['grouping_field' => $grouping_field, 'date_choice' => $date_choice]
                );

            case 'tickets_user_waiting_time':

                $personPrefRepository->savePref(
                    $this->person,
                    'reports.ui.overview.options.tickets_user_waiting_time.grouping',
                    $grouping_field
                );

                return $this->getValues('tickets_user_waiting_time', ['grouping_field' => $grouping_field]);

            case 'tickets_opened_hour':

                $date_choice = $options['date_choice'];

                $personPrefRepository->savePref(
                    $this->person,
                    'reports.ui.overview.options.tickets_opened_hour.date_choice',
                    $date_choice
                );

                return $this->getValues('tickets_opened_hour', ['date_choice' => $date_choice]);

            case 'tickets_sla_status':

                $date_choice = $options['date_choice'];
                $sla_id      = $options['sla_id'];

                $personPrefRepository->savePref(
                    $this->person,
                    'reports.ui.overview.options.tickets_sla_status.date_choice',
                    $date_choice
                );
                $personPrefRepository->savePref(
                    $this->person,
                    'reports.ui.overview.options.tickets_sla_status.sla_id',
                    $sla_id
                );

                return $this->getValues(
                    'tickets_sla_status',
                    ['date_choice' => $date_choice, 'sla_id' => $sla_id]
                );

            case 'kb_views_hour':
                return $this->getValues('kb_views_hour');

            case 'chats_created':

                $date_choice = $options['date_choice'];

                $personPrefRepository->savePref(
                    $this->person,
                    'reports.ui.overview.options.chats_created.grouping',
                    $grouping_field
                );
                $personPrefRepository->savePref(
                    $this->person,
                    'reports.ui.overview.options.chats_created.date_choice',
                    $date_choice
                );

                return $this->getValues(
                    'chats_created',
                    ['grouping_field' => $grouping_field, 'date_choice' => $date_choice]
                );

            default:
                throw new NotFoundHttpException("Unknown type $type");
        }
    }

    /**
     * @param Person $person
     *
     * @return $this
     */
    public function setPerson(Person $person)
    {
        $this->person = $person;

        return $this;
    }

    /**
     * @param       $type
     * @param array $options
     *
     * @throws \InvalidArgumentException
     *
     * @return array
     */
    protected function getValues($type, array $options = [])
    {
        $options = new OptionsArray($options);

        if (!$options->get('grouping_field')) {
            $pref = $this->person->getPref('reports.ui.overview.options.'.$type.'.grouping');
            if ($pref) {
                $options->set('grouping_field', $pref);
            }
        }

        if (!$options->get('date_choice')) {
            $pref = $this->person->getPref('reports.ui.overview.options.'.$type.'.date_choice');
            if ($pref) {
                $options->set('date_choice', $pref);
            }
        }

        $agentTeam = $options->get('agent_team');

        switch ($type) {

            case 'tickets_status':
                $stat = new TicketsStatus();
                $stat->setLogger($this->logger);
                $stat->setAgentTeam($agentTeam);
                $sum = array_sum($stat->getValues());

                return [
                    'titles' => $stat->getTitles(),
                    'values' => $stat->getValues(),
                    'max'    => $stat->getMax(),
                    'sum'    => $sum,
                ];

            case 'tickets_opened_hour':

                $date_choice = $options->get('date_choice');
                switch ($date_choice) {
                    case 'this_week':
                        if (date('D') == 'Mon') {
                            $date = $this->person->getDateTime()->setTime(0, 0, 0);
                        } else {
                            $date = $this->person->getDateForTime('last monday')->setTime(0, 0, 0);
                        }

                        $date_group = 'weekday';
                        break;
                    case 'this_month':
                        $date = $this->person->getDateTime();
                        $date->setDate((int) $date->format('Y'), (int) $date->format('n'), 1)->setTime(0, 0, 0);
                        $date_group = 'day';
                        break;
                    case 'this_year':
                        $date = $this->person->getDateTime();
                        $date->setDate($date->format('Y'), 1, 1)->setTime(0, 0, 0);
                        $date_group = 'month';
                        break;
                    default:
                        $options->set('date_choice', 'today');
                        $date       = $this->person->getDateTime();
                        $date_group = 'hour';
                        break;
                }

                $date->setTime(0, 0, 0);

                $date2 = new \DateTime();

                if ($this->no_data_mode) {
                    return [
                        'date_choice' => $options->get('date_choice'),
                    ];
                }

                $stat = new TicketsOpenedHour($date_group, $date, $date2);
                $stat->setPersonContext($this->person);
                $stat->setLogger($this->logger);
                $stat->setAgentTeam($agentTeam);
                $sum = array_sum($stat->getValues());

                return [
                    'titles'      => $stat->getTitles(),
                    'date_choice' => $options->get('date_choice'),
                    'values'      => $stat->getValues(),
                    'max'         => $stat->getMax(),
                    'sum'         => $sum,
                ];

            case 'tickets_resolved':
                $date_choice = $options->get('date_choice');
                switch ($date_choice) {
                    case 'this_week':
                        $date     = $this->person->getDateTime();
                        $interval = new \DateInterval('P7D');
                        $date->sub($interval)->setTime(0, 0, 0);
                        break;
                    case 'this_month':
                        $date = $this->person->getDateTime();
                        $date->setDate($date->format('Y'), (int) $date->format('n'), 1)->setTime(0, 0, 0);
                        break;
                    case 'this_year':
                        $date = $this->person->getDateTime();
                        $date->setDate($date->format('Y'), 1, 1)->setTime(0, 0, 0);
                        break;
                    default:
                        $options->set('date_choice', 'today');
                        $date = $this->person->getDateTime();
                        $date->setTime(0, 0, 0);
                        break;
                }

                $date2 = new \DateTime();

                $gf = new GroupingField($options->get(
                    'grouping_field',
                    'department'
                ));

                if ($this->no_data_mode) {
                    return [
                        'grouping_field' => $options->get('grouping_field', 'department'),
                        'date_choice'    => $options->get('date_choice'),
                    ];
                }

                $stat = new TicketsResolved($gf, $date, $date2);
                $stat->setLogger($this->logger);
                $stat->setAgentTeam($agentTeam);
                $sum = array_sum($stat->getValues());

                return [
                    'grouping_field' => $options->get('grouping_field', 'department'),
                    'date_choice'    => $options->get('date_choice'),
                    'titles'         => $stat->getTitles(),
                    'values'         => $stat->getValues(),
                    'max'            => $stat->getMax(),
                    'sum'            => $sum,
                ];

            case 'tickets_response_time':
                $date_choice = $options->get('date_choice');
                switch ($date_choice) {
                    case 'this_week':
                        $date     = $this->person->getDateTime();
                        $interval = new \DateInterval('P7D');
                        $date->sub($interval)->setTime(0, 0, 0);
                        break;
                    case 'this_month':
                        $date = $this->person->getDateTime();
                        $date->setDate($date->format('Y'), (int) $date->format('n'), 1)->setTime(0, 0, 0);
                        break;
                    case 'this_year':
                        $date = $this->person->getDateTime();
                        $date->setDate($date->format('Y'), 1, 1)->setTime(0, 0, 0);
                        break;
                    default:
                        $options->set('date_choice', 'today');
                        $date = $this->person->getDateTime();
                        $date->setTime(0, 0, 0);
                        break;
                }

                $date2 = new \DateTime();

                if ($options->get('grouping_field')) {
                    $gf = new GroupingField($options->get('grouping_field'));
                } else {
                    $gf = null;
                }

                if ($this->no_data_mode) {
                    return [
                        'grouping_field' => $options->get('grouping_field'),
                        'date_choice'    => $options->get('date_choice'),
                    ];
                }

                $stat = new TicketsResponseTime($gf, $date, $date2);
                $stat->setLogger($this->logger);
                $stat->setAgentTeam($agentTeam);

                return [
                    'grouping_field' => $options->get('grouping_field'),
                    'group_max'      => $stat->getGroupMax(),
                    'date_choice'    => $options->get('date_choice'),
                    'titles'         => $stat->getTitles(),
                    'sub_titles'     => $stat->getSubgroupTitles(),
                    'group_keys'     => $stat->getGroupColors(),
                    'group_total'    => $stat->getGroupTotal(),
                    'values'         => $stat->getValues(),
                    'max'            => $stat->getMax(),
                ];

            case 'tickets_user_waiting_time':
                if ($options->get('grouping_field')) {
                    $gf = new GroupingField($options->get('grouping_field'));
                } else {
                    $gf = null;
                }
                $stat = new TicketsUserWaitingTime($gf);
                $stat->setLogger($this->logger);
                $stat->setAgentTeam($agentTeam);

                if ($this->no_data_mode) {
                    return [
                        'grouping_field' => $options->get('grouping_field'),
                    ];
                }

                return [
                    'grouping_field' => $options->get('grouping_field'),
                    'group_max'      => $stat->getGroupMax(),
                    'titles'         => $stat->getTitles(),
                    'sub_titles'     => $stat->getSubgroupTitles(),
                    'group_keys'     => $stat->getGroupColors(),
                    'group_total'    => $stat->getGroupTotal(),
                    'values'         => $stat->getValues(),
                    'max'            => $stat->getMax(),
                ];

            case 'tickets_awaiting_agent':
                $gf = new GroupingField($options->get(
                    'grouping_field',
                    'department'
                ));

                if ($this->no_data_mode) {
                    return [
                        'grouping_field' => $options->get('grouping_field', 'department'),
                    ];
                }

                $stat = new TicketsAwaitingAgent($gf);
                $stat->setLogger($this->logger);
                $stat->setAgentTeam($agentTeam);
                $sum = array_sum($stat->getValues());

                return [
                    'grouping_field' => $options->get('grouping_field', 'department'),
                    'titles'         => $stat->getTitles(),
                    'values'         => $stat->getValues(),
                    'max'            => $stat->getMax(),
                    'sum'            => $sum,
                ];

            case 'tickets_sla_status':
                $date_choice = $options->get('date_choice');
                switch ($date_choice) {
                    case 'this_week':
                        $date     = $this->person->getDateTime();
                        $interval = new \DateInterval('P7D');
                        $date->sub($interval)->setTime(0, 0, 0);
                        break;
                    case 'this_month':
                        $date = $this->person->getDateTime();
                        $date->setDate($date->format('Y'), (int) $date->format('n'), 1)->setTime(0, 0, 0);
                        break;
                    case 'this_year':
                        $date = $this->person->getDateTime();
                        $date->setDate($date->format('Y'), 1, 1)->setTime(0, 0, 0);
                        break;
                    default:
                        $options->set('date_choice', 'today');
                        $date = $this->person->getDateTime();
                        $date->setTime(0, 0, 0);
                        break;
                }

                $date2 = new \DateTime();

                if (!$options->get('sla_id')) {
                    $pref = $this->person->getPref('reports.ui.overview.options.'.$type.'.sla_id');
                    if ($pref) {
                        $options->set('sla_id', $pref);
                    }
                }

                $sla_id = $options->get('sla_id', null);
                if ($sla_id) {
                    $sla = $this->em->find(Sla::class, $sla_id);
                    if (!$sla) {
                        $sla = null;
                    }
                } else {
                    $sla_id = null;
                }

                $has_slas = $this->em->getConnection()->fetchColumn('SELECT COUNT(*) FROM slas LIMIT 1');

                $stat = new TicketSlaStatus($sla_id, $date, $date2);
                $stat->setLogger($this->logger);
                $stat->setAgentTeam($agentTeam);
                $sum = array_sum($stat->getValues());

                return [
                    'sla_id'      => $sla_id,
                    'date_choice' => $date_choice,
                    'titles'      => $stat->getTitles(),
                    'values'      => $stat->getValues(),
                    'max'         => $stat->getMax(),
                    'sum'         => $sum,
                    'has_slas'    => $has_slas,
                ];

                break;

            case 'chats_created':
                $date_choice = $options->get('date_choice');
                switch ($date_choice) {
                    case 'this_week':
                        $date     = $this->person->getDateTime();
                        $interval = new \DateInterval('P7D');
                        $date->sub($interval)->setTime(0, 0, 0);
                        break;
                    case 'this_month':
                        $date = $this->person->getDateTime();
                        $date->setDate($date->format('Y'), (int) $date->format('n'), 1)->setTime(0, 0, 0);
                        break;
                    case 'this_year':
                        $date = $this->person->getDateTime();
                        $date->setDate($date->format('Y'), 1, 1)->setTime(0, 0, 0);
                        break;
                    default:
                        $options->set('date_choice', 'today');
                        $date = $this->person->getDateTime();
                        $date->setTime(0, 0, 0);
                        break;
                }

                $date2 = new \DateTime();

                $gf = new ChatGroupingField($options->get(
                    'grouping_field',
                    'department'
                ));

                if ($this->no_data_mode) {
                    return [
                        'grouping_field' => $options->get('grouping_field', 'department'),
                        'date_choice'    => $options->get('date_choice'),
                    ];
                }

                $stat = new ChatsCreated($gf, $date, $date2);
                $stat->setLogger($this->logger);
                $stat->setAgentTeam($agentTeam);
                $sum = array_sum($stat->getValues());

                return [
                    'grouping_field' => $options->get('grouping_field', 'department'),
                    'date_choice'    => $options->get('date_choice'),
                    'titles'         => $stat->getTitles(),
                    'values'         => $stat->getValues(),
                    'max'            => $stat->getMax(),
                    'sum'            => $sum,
                ];

            case 'kb_views_hour':

                $date_choice = $options->get('date_choice');
                switch ($date_choice) {
                    case 'this_week':
                        $date     = $this->person->getDateTime();
                        $interval = new \DateInterval('P7D');
                        $date->sub($interval)->setTime(0, 0, 0);
                        break;
                    case 'this_month':
                        $date = $this->person->getDateTime();
                        $date->setDate($date->format('Y'), (int) $date->format('n'), 1)->setTime(0, 0, 0);
                        break;
                    case 'this_year':
                        $date = $this->person->getDateTime();
                        $date->setDate($date->format('Y'), 1, 1)->setTime(0, 0, 0);
                        break;
                    default:
                        $options->set('date_choice', 'today');
                        $date = $this->person->getDateTime();
                        $date->setTime(0, 0, 0);
                        break;
                }

                $date2 = new \DateTime();

                if ($this->no_data_mode) {
                    return [
                        'date_choice' => $options->get('date_choice'),
                    ];
                }

                $stat = new KbViewsHour($date, $date2);
                $stat->setLogger($this->logger);
                $stat->setAgentTeam($agentTeam);
                $sum = array_sum($stat->getValues());

                return [
                    'titles'      => $stat->getTitles(),
                    'date_choice' => $options->get('date_choice'),
                    'values'      => $stat->getValues(),
                    'max'         => $stat->getMax(),
                    'sum'         => $sum,
                ];

            default:
                throw new \InvalidArgumentException("Invalid type: $type");
        }
    }
}
