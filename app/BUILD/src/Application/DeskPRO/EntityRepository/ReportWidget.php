<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2018, DeskPRO Ltd.
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
 *
 * @category Entities
 */

namespace Application\DeskPRO\EntityRepository;

use Application\DeskPRO\App;
use Application\DeskPRO\Entity\CustomDefOrganization as CustomDefOrganizationEntity;
use Application\DeskPRO\Entity\CustomDefPerson as CustomDefPersonEntity;
use Application\DeskPRO\Entity\CustomDefTicket as CustomDefTicketEntity;
use Application\DeskPRO\Entity\ReportWidget as ReportWidgetEntity;
use Application\DeskPRO\EntityRepository\CustomDefOrganization as CustomDefOrganizationRepository;
use Application\DeskPRO\EntityRepository\CustomDefPerson as CustomDefPersonRepository;
use Application\DeskPRO\EntityRepository\CustomDefTicket as CustomDefTicketRepository;
use DeskPRO\Bundle\ReportBundle\Dashboard\DashboardWidgetManager;

class ReportWidget extends AbstractEntityRepository
{
    /**
     * Gets all reports.
     *
     * @return ReportWidgetEntity[]
     */
    public function getAllReports()
    {
        return $this->findBy([], ['display_order' => 'ASC', 'title' => 'ASC']);
    }

    /**
     * @param $key
     *
     * @return mixed
     */
    public function getByUniqueKey($key)
    {
        return $this->findOneBy(['unique_key' => $key]);
    }

    /**
     * @param ReportWidgetEntity $report
     * @param Person             $person
     * @param array              $params
     *
     * @return mixed
     */
    public function findFavorite(
        ReportWidgetEntity $report,
        Person $person = null,
        array $params = []
    ) {
        if (!$person) {
            $person = App::getCurrentPerson();
        }

        ksort($params);

        return $this->getEntityManager()->createQuery('
            SELECT f
            FROM DeskPRO:ReportWidgetFavorite f
            WHERE f.report_widget = ?0 AND f.person = ?1 AND f.params = ?2
        ')->setParameters([$report, $person, $params ? implode(',', $params) : ''])->getOneOrNullResult();
    }

    /**
     * @param Person $person
     *
     * @return mixed
     */
    public function getFavoritesForPerson(Person $person = null)
    {
        if (!$person) {
            $person = App::getCurrentPerson();
        }

        return $this->getEntityManager()->createQuery('
            SELECT f, r
            FROM DeskPRO:ReportWidgetFavorite f
            JOIN f.report_widget r
            WHERE f.person = ?0
            ORDER BY r.title
        ')->execute([$person]);
    }

    /**
     * @param array $favorites
     *
     * @return array
     */
    public function getFavoritesSimplified(array $favorites)
    {
        $output = [];
        foreach ($favorites as $fav) {
            $output[] = ['id' => $fav->report_widget->id, 'params' => $fav->params];
        }

        return $output;
    }

    /**
     * @return array
     *
     * @deprecated
     */
    public function getCustomReports()
    {
        $reports = $this->getAllReports();
        $custom  = [];

        foreach ($reports as $report) {
            if ($report->isCustom()) {
                $custom[] = $report->toApiData();
            }
        }

        return $custom;
    }

    /**
     * @return array
     *
     * @deprecated
     */
    public function getBuiltInReports()
    {
        $reports = $this->getAllReports();
        $builtIn = [];
        foreach ($reports as $report) {
            if (!$report->isCustom()) {
                $builtIn[] = $report->toApiData();
            }
        }

        return $builtIn;
    }
    /**
     * @return bool
     */
    public function canManageBuiltInReports()
    {
        return (bool) App::getConfig('debug.dev');
    }

    /**
     * @return array
     */
    public function getReportGroupParams()
    {
        $return = [
            'fields' => [
                'tickets' => [
                    'department'    => ['department', 'DPQL_ALIAS(DPQL_STACK_GROUP(%1$s.department, COALESCE(%1$s.department.parent.title, %1$s.department.title)), \'Department\')'],
                    'agent'         => ['agent', '%s.agent'],
                    'agent_team'    => ['agent team', '%s.agent_team'],
                    'person'        => ['person', '%s.person'],
                    'organization'  => ['organization', '%s.organization'],
                    'language'      => ['language', '%s.language'],
                    'urgency'       => ['urgency', '%s.urgency'],
                    'category'      => ['category', '%s.category'],
                    'product'       => ['product', '%s.product'],
                    'priority'      => ['priority', '%s.priority'],
                    'workflow'      => ['workflow', '%s.workflow'],
                    'sla'           => ['SLA', '%s.ticket_slas'],
                    'sla_status'    => ['SLA status', '%s.ticket_slas.sla_status'],
                    'agent_replies' => ['number of agent replies', 'DPQL_ALIAS(%s.count_agent_replies, \'Agent Replies\')'],
                    'user_replies'  => ['number of user replies', 'DPQL_ALIAS(%s.count_user_replies, \'User Replies\')'],
                    'replies'       => ['number of replies', 'DPQL_ALIAS(%1$s.count_user_replies + %1$s.count_agent_replies, \'Total Replies\')'],
                    // todo: ticket rating
                    'hour_created'                            => ['hour created', 'DPQL_ALIAS(DPQL_HOUR(%s.date_created), \'Hour Created\')'],
                    'day_week_created'                        => ['day of week created', 'DPQL_ALIAS(DPQL_DAYNAME(%s.date_created), \'Day of Week Created\')'],
                    'day_month_created'                       => ['day of month created', 'DPQL_ALIAS(DPQL_DAYOFMONTH(%s.date_created), \'Day of Month Created\')'],
                    'month_created'                           => ['month created', 'DPQL_ALIAS(DPQL_MONTHNAME(%s.date_created), \'Month Created\')'],
                    'year_created'                            => ['year created', 'DPQL_ALIAS(DPQL_YEAR(%s.date_created), \'Year Created\')'],
                    'date_created'                            => ['date created', 'DPQL_ALIAS(DPQL_DATE(%s.date_created), \'Date Created\')'],
                    'hour_resolved'                           => ['hour created', 'DPQL_ALIAS(DPQL_HOUR(%s.date_created), \'Hour Created\')'],
                    'day_week_resolved'                       => ['day of week resolved', 'DPQL_ALIAS(DPQL_DAYNAME(%s.date_resolved), \'Day of Week Resolved\')'],
                    'day_month_resolved'                      => ['day of month resolved', 'DPQL_ALIAS(DPQL_DAYOFMONTH(%s.date_resolved), \'Day of Month Resolved\')'],
                    'month_resolved'                          => ['month resolved', 'DPQL_ALIAS(DPQL_MONTHNAME(%s.date_resolved), \'Month Resolved\')'],
                    'year_resolved'                           => ['year resolved', 'DPQL_ALIAS(DPQL_YEAR(%s.date_resolved), \'Year Resolved\')'],
                    'date_resolved'                           => ['date resolved', 'DPQL_ALIAS(DPQL_DATE(%s.date_resolved), \'Date Resolved\')'],
                    DashboardWidgetManager::WIDGET_VALUE_FROM_REPORT => ['value from report', DashboardWidgetManager::WIDGET_VALUE_FROM_REPORT],
                ],
                'chats' => [
                    'department'                              => ['department', '%s.department'],
                    'agent'                                   => ['agent', '%s.agent'],
                    'agent_team'                              => ['agent team', '%s.agent_team'],
                    'person'                                  => ['person', '%s.person'],
                    'hour_created'                            => ['hour created', 'DPQL_ALIAS(DPQL_HOUR(%s.date_created), \'Hour Created\')'],
                    'day_week_created'                        => ['day of week created', 'DPQL_ALIAS(DPQL_DAYNAME(%s.date_created), \'Day of Week Created\')'],
                    'day_month_created'                       => ['day of month created', 'DPQL_ALIAS(DPQL_DAYOFMONTH(%s.date_created), \'Day of Month Created\')'],
                    'month_created'                           => ['month created', 'DPQL_ALIAS(DPQL_MONTHNAME(%s.date_created), \'Month Created\')'],
                    'year_created'                            => ['year created', 'DPQL_ALIAS(DPQL_YEAR(%s.date_created), \'Year Created\')'],
                    'date_created'                            => ['date created', 'DPQL_ALIAS(DPQL_DATE(%s.date_created), \'Date Created\')'],
                    'none'                                    => ['nothing', 'NULL'],
                    DashboardWidgetManager::WIDGET_VALUE_FROM_REPORT => ['value from report', DashboardWidgetManager::WIDGET_VALUE_FROM_REPORT],
                ],
                'articles' => [
                    'person'                                  => ['person', '%s.person'],
                    'hour_created'                            => ['hour created', 'DPQL_ALIAS(DPQL_HOUR(%s.date_created), \'Hour Created\')'],
                    'day_week_created'                        => ['day of week created', 'DPQL_ALIAS(DPQL_DAYNAME(%s.date_created), \'Day of Week Created\')'],
                    'day_month_created'                       => ['day of month created', 'DPQL_ALIAS(DPQL_DAYOFMONTH(%s.date_created), \'Day of Month Created\')'],
                    'month_created'                           => ['month created', 'DPQL_ALIAS(DPQL_MONTHNAME(%s.date_created), \'Month Created\')'],
                    'year_created'                            => ['year created', 'DPQL_ALIAS(DPQL_YEAR(%s.date_created), \'Year Created\')'],
                    'date_created'                            => ['date created', 'DPQL_ALIAS(DPQL_DATE(%s.date_created), \'Date Created\')'],
                    'none'                                    => ['nothing', 'NULL'],
                    DashboardWidgetManager::WIDGET_VALUE_FROM_REPORT => ['value from report', DashboardWidgetManager::WIDGET_VALUE_FROM_REPORT],
                ],
                'article_comments' => [
                    'hour_created'                            => ['hour created', 'DPQL_ALIAS(DPQL_HOUR(%s.date_created), \'Hour Created\')'],
                    'day_week_created'                        => ['day of week created', 'DPQL_ALIAS(DPQL_DAYNAME(%s.date_created), \'Day of Week Created\')'],
                    'day_month_created'                       => ['day of month created', 'DPQL_ALIAS(DPQL_DAYOFMONTH(%s.date_created), \'Day of Month Created\')'],
                    'month_created'                           => ['month created', 'DPQL_ALIAS(DPQL_MONTHNAME(%s.date_created), \'Month Created\')'],
                    'year_created'                            => ['year created', 'DPQL_ALIAS(DPQL_YEAR(%s.date_created), \'Year Created\')'],
                    'date_created'                            => ['date created', 'DPQL_ALIAS(DPQL_DATE(%s.date_created), \'Date Created\')'],
                    'none'                                    => ['nothing', 'NULL'],
                    DashboardWidgetManager::WIDGET_VALUE_FROM_REPORT => ['value from report', DashboardWidgetManager::WIDGET_VALUE_FROM_REPORT],
                ],
                'feedback' => [
                    'type'                                    => ['type', 'DPQL_ALIAS(%s.category, \'Type\')'],
                    'status'                                  => ['status', 'DPQL_ALIAS(%s.status_category, \'Status\')'],
                    'category'                                => ['category', 'DPQL_ALIAS(%s.custom_data[1], \'category\')'],
                    'person'                                  => ['person', '%s.person'],
                    'hour_created'                            => ['hour created', 'DPQL_ALIAS(DPQL_HOUR(%s.date_created), \'Hour Created\')'],
                    'day_week_created'                        => ['day of week created', 'DPQL_ALIAS(DPQL_DAYNAME(%s.date_created), \'Day of Week Created\')'],
                    'day_month_created'                       => ['day of month created', 'DPQL_ALIAS(DPQL_DAYOFMONTH(%s.date_created), \'Day of Month Created\')'],
                    'month_created'                           => ['month created', 'DPQL_ALIAS(DPQL_MONTHNAME(%s.date_created), \'Month Created\')'],
                    'year_created'                            => ['year created', 'DPQL_ALIAS(DPQL_YEAR(%s.date_created), \'Year Created\')'],
                    'date_created'                            => ['date created', 'DPQL_ALIAS(DPQL_DATE(%s.date_created), \'Date Created\')'],
                    'none'                                    => ['nothing', 'NULL'],
                    DashboardWidgetManager::WIDGET_VALUE_FROM_REPORT => ['value from report', DashboardWidgetManager::WIDGET_VALUE_FROM_REPORT],
                ],
                'feedback_comments' => [
                    'hour_created'                            => ['hour created', 'DPQL_ALIAS(DPQL_HOUR(%s.date_created), \'Hour Created\')'],
                    'day_week_created'                        => ['day of week created', 'DPQL_ALIAS(DPQL_DAYNAME(%s.date_created), \'Day of Week Created\')'],
                    'day_month_created'                       => ['day of month created', 'DPQL_ALIAS(DPQL_DAYOFMONTH(%s.date_created), \'Day of Month Created\')'],
                    'month_created'                           => ['month created', 'DPQL_ALIAS(DPQL_MONTHNAME(%s.date_created), \'Month Created\')'],
                    'year_created'                            => ['year created', 'DPQL_ALIAS(DPQL_YEAR(%s.date_created), \'Year Created\')'],
                    'date_created'                            => ['date created', 'DPQL_ALIAS(DPQL_DATE(%s.date_created), \'Date Created\')'],
                    'none'                                    => ['nothing', 'NULL'],
                    DashboardWidgetManager::WIDGET_VALUE_FROM_REPORT => ['value from report', DashboardWidgetManager::WIDGET_VALUE_FROM_REPORT],
                ],
            ],
            'dates' => [
                'today'                                   => ['today', '%TODAY%'],
                'yesterday'                               => ['yesterday', '%YESTERDAY%'],
                'this_week'                               => ['this week', '%THIS_WEEK%'],
                'this_month'                              => ['this month', '%THIS_MONTH%'],
                'this_year'                               => ['this year', '%THIS_DPQL_YEAR%'],
                'last_week'                               => ['last week', '%LAST_WEEK%'],
                'last_month'                              => ['last month', '%LAST_MONTH%'],
                'last_year'                               => ['last year', '%LAST_DPQL_YEAR%'],
                'past_24_hours'                           => ['in the past 24 hours', '%PAST_24_HOURS%'],
                'past_12_hours'                           => ['in the past 12 hours', '%PAST_12_HOURS%'],
                'past_hour'                               => ['in the past hour', '%PAST_DPQL_HOUR%'],
                'past_7_days'                             => ['in the past 7 days', '%PAST_7_DAYS%'],
                'past_30_days'                            => ['in the past 30 days', '%PAST_30_DAYS%'],
                'ever'                                    => ['any time', '%EVER%'],
                DashboardWidgetManager::WIDGET_VALUE_FROM_REPORT => ['value from report', DashboardWidgetManager::WIDGET_VALUE_FROM_REPORT],
            ],
            'statuses' => [
                'tickets' => [
                    'awaiting_user'                                  => ['awaiting user', '%s.status = \'awaiting_user\''],
                    'awaiting_agent'                                 => ['awaiting agent', '%s.status = \'awaiting_agent\''],
                    'unresolved'                                     => ['unresolved', '%s.status IN (\'awaiting_user\', \'awaiting_agent\')'],
                    'resolved'                                       => ['resolved', '%s.status IN (\'resolved\', \'archived\')'],
                    'hidden'                                         => ['hidden', '%s.status = \'hidden\''],
                    'any'                                            => ['with any status', '1'],
                    DashboardWidgetManager::WIDGET_VALUE_FROM_REPORT => ['value from report', DashboardWidgetManager::WIDGET_VALUE_FROM_REPORT],
                ],
            ],
            'orders' => [
                'tickets' => [
                    // todo: number of messages
                    'date_created_asc'                               => ['date created (ascending)', '%s.date_created ASC'],
                    'date_created_desc'                              => ['date created (descending)', '%s.date_created DESC'],
                    'last_agent_reply_asc'                           => ['last agent reply (ascending)', '%s.date_last_agent_reply ASC'],
                    'last_agent_reply_desc'                          => ['last agent reply (descending)', '%s.date_last_agent_reply DESC'],
                    'last_user_reply_asc'                            => ['last user reply (ascending)', '%s.date_last_user_reply ASC'],
                    'last_user_reply_desc'                           => ['last user reply (descending)', '%s.date_last_user_reply DESC'],
                    'total_waiting_asc'                              => ['total waiting time (ascending)', '%s.total_user_waiting ASC'],
                    'total_waiting_desc'                             => ['total waiting time (descending)', '%s.total_user_waiting DESC'],
                    DashboardWidgetManager::WIDGET_VALUE_FROM_REPORT => ['value from report', DashboardWidgetManager::WIDGET_VALUE_FROM_REPORT],
                ],
            ],
        ];

        /** @var CustomDefTicketRepository $customDefTicketRepository */
        $customDefTicketRepository = $this->getEntityManager()->getRepository(CustomDefTicketEntity::class);
        $fields                    = $customDefTicketRepository->getTopFields();
        foreach ($fields as $field) {
            $escaped                                               = addslashes($field->title);
            $return['fields']['tickets']['ticketfield'.$field->id] = [
                $field->title, 'DPQL_ALIAS(%s.custom_data['.$field->id.'], \''.$escaped.'\')',
            ];
        }

        /** @var CustomDefPersonRepository $customDefPersonRepository */
        $customDefPersonRepository = $this->getEntityManager()->getRepository(CustomDefPersonEntity::class);
        $fields                    = $customDefPersonRepository->getTopFields();
        foreach ($fields as $field) {
            $escaped                                               = addslashes($field->title);
            $return['fields']['tickets']['personfield'.$field->id] = [
                "creator's ".$field->title, 'DPQL_ALIAS(%s.person.custom_data['.$field->id.'], \''.$escaped.'\')',
            ];
        }

        /** @var CustomDefOrganizationRepository $customDefOrganizationRepository */
        $customDefOrganizationRepository = $this->getEntityManager()->getRepository(CustomDefOrganizationEntity::class);
        $fields                          = $customDefOrganizationRepository->getTopFields();
        foreach ($fields as $field) {
            $escaped                                            = addslashes($field->title);
            $return['fields']['tickets']['orgfield'.$field->id] = [
                "organizations's ".$field->title, 'DPQL_ALIAS(%s.organization.custom_data['.$field->id.'], \''.$escaped.'\')',
            ];
        }

        $return['fields']['tickets']['none'] = ['nothing', 'NULL'];

        return $return;
    }
}
