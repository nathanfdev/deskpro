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
 *
 * @category Entities
 */

namespace Application\DeskPRO\EntityRepository;

use Application\DeskPRO\App;

class ReportBuilder extends AbstractEntityRepository
{
    /**
     * Gets all reports.
     *
     * @return \Application\DeskPRO\Entity\ReportBuilder[]
     */
    public function getAllReports()
    {
        return $this->getEntityManager()->createQuery('
            SELECT rb
            FROM DeskPRO:ReportBuilder rb
            ORDER BY rb.display_order, rb.title
        ')->execute();
    }

    public function getByUniqueKey($key)
    {
        return $this->getEntityManager()->createQuery('
            SELECT rb
            FROM DeskPRO:ReportBuilder rb
            WHERE rb.unique_key = ?0
        ')->setParameters([$key])->getOneOrNullResult();
    }

    public function findFavorite(
        \Application\DeskPRO\Entity\ReportBuilder $report,
        \Application\DeskPRO\Entity\Person $person = null,
        array $params = []
    ) {
        if (!$person) {
            $person = App::getCurrentPerson();
        }

        ksort($params);

        return $this->getEntityManager()->createQuery('
            SELECT f
            FROM DeskPRO:ReportBuilderFavorite f
            WHERE f.report_builder = ?0 AND f.person = ?1 AND f.params = ?2
        ')->setParameters([$report, $person, $params ? implode(',', $params) : ''])->getOneOrNullResult();
    }

    public function getFavoritesForPerson(\Application\DeskPRO\Entity\Person $person = null)
    {
        if (!$person) {
            $person = App::getCurrentPerson();
        }

        return $this->getEntityManager()->createQuery('
            SELECT f, r
            FROM DeskPRO:ReportBuilderFavorite f
            JOIN f.report_builder r
            WHERE f.person = ?0
            ORDER BY r.title
        ')->execute([$person]);
    }

    public function getFavoritesSimplified(array $favorites)
    {
        $output = [];
        foreach ($favorites as $fav) {
            $output[] = ['id' => $fav->report_builder->id, 'params' => $fav->params];
        }

        return $output;
    }

    /**
     * Groups a list of reports for use in the reports list. Returns lists of
     * reports in these keys:
     *  - custom: list of custom reports
     *  - builtIn: grouped list of built-in reports. Grouped by printable name of the group.
     *
     * @return array
     */
    public function groupReportsList()
    {
        $reports = $this->getAllReports();

        $custom     = [];
        $builtIn    = [];
        $categories = $this->getBuiltInCategories();

        foreach ($reports as $report) {
            if ($report->is_custom) {
                $custom[] = $report;
            } else {
                if (isset($categories[$report->category])) {
                    $categoryId = $report->category;
                } else {
                    $categoryId = '';
                }
                $builtIn[$categoryId][] = $report;
            }
        }

        $builtInOrdered = [];
        foreach ($categories as $categoryId => $categoryName) {
            if (isset($builtIn[$categoryId])) {
                $builtInOrdered[$categoryName] = $builtIn[$categoryId];
            }
        }

        return [
            'custom'  => $custom,
            'builtIn' => $builtInOrdered,
        ];
    }

    /**
     * @return array
     */
    public function getCustomReports()
    {
        $reports = $this->getAllReports();
        $custom  = [];

        foreach ($reports as $report) {
            if ($report->is_custom) {
                $custom[] = $report->toApiData();
            }
        }

        return $custom;
    }

    /**
     * @return array
     */
    public function getBuiltInReports()
    {
        $reports    = $this->getAllReports();
        $builtIn    = [];
        $categories = $this->getBuiltInCategories();

        foreach ($reports as $report) {
            if (!$report->is_custom) {
                {
                    if (isset($categories[$report->category])) {
                        $categoryId = $report->category;
                    } else {
                        $categoryId = '';
                    }
                    $builtIn[$categoryId][] = $report->toApiData();
                }
            }
        }

        $builtInOrdered = [];

        foreach ($categories as $categoryId => $categoryName) {
            if (isset($builtIn[$categoryId])) {
                $builtInOrdered[$categoryName] = $builtIn[$categoryId];
            }
        }

        return $builtInOrdered;
    }

    /**
     * Gets the list of built-in report grouping categories.
     *
     * @return array
     */
    public function getBuiltInCategories()
    {
        return [
            'ticket'    => 'Tickets',
            'chat'      => 'Chats',
            'idea'      => 'Ideas',
            'person'    => 'People & Organizations',
            'kb'        => 'Knowledgebase',
            'news'      => 'News',
            'downloads' => 'Downloads',
            'feedback'  => 'Feedback',
            'tasks'     => 'Tasks',
            'twitter'   => 'Twitter',
        ];
    }

    /**
     * @return bool
     */
    public function canManageBuiltInReports()
    {
        return (bool) App::getConfig('debug.dev');
    }

    public function getReportGroupParams()
    {
        $return = [
            'fields' => [
                'tickets' => [
                    'brand'         => ['brand', '%s.brand'],
                    'department'    => ['department', 'ALIAS(STACK_GROUP(%1$s.department, COALESCE(%1$s.department.parent.title, %1$s.department.title)), \'Department\')'],
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
                    'agent_replies' => ['number of agent replies', 'ALIAS(%s.count_agent_replies, \'Agent Replies\')'],
                    'user_replies'  => ['number of user replies', 'ALIAS(%s.count_user_replies, \'User Replies\')'],
                    'replies'       => ['number of replies', 'ALIAS(%1$s.count_user_replies + %1$s.count_agent_replies, \'Total Replies\')'],
                    // todo: ticket rating
                    'hour_created'       => ['hour created', 'ALIAS(HOUR(%s.date_created), \'Hour Created\')'],
                    'day_week_created'   => ['day of week created', 'ALIAS(DAYNAME(%s.date_created), \'Day of Week Created\')'],
                    'day_month_created'  => ['day of month created', 'ALIAS(DAYOFMONTH(%s.date_created), \'Day of Month Created\')'],
                    'month_created'      => ['month created', 'ALIAS(MONTHNAME(%s.date_created), \'Month Created\')'],
                    'year_created'       => ['year created', 'ALIAS(YEAR(%s.date_created), \'Year Created\')'],
                    'date_created'       => ['date created', 'ALIAS(DATE(%s.date_created), \'Date Created\')'],
                    'hour_resolved'      => ['hour created', 'ALIAS(HOUR(%s.date_created), \'Hour Created\')'],
                    'day_week_resolved'  => ['day of week resolved', 'ALIAS(DAYNAME(%s.date_resolved), \'Day of Week Resolved\')'],
                    'day_month_resolved' => ['day of month resolved', 'ALIAS(DAYOFMONTH(%s.date_resolved), \'Day of Month Resolved\')'],
                    'month_resolved'     => ['month resolved', 'ALIAS(MONTHNAME(%s.date_resolved), \'Month Resolved\')'],
                    'year_resolved'      => ['year resolved', 'ALIAS(YEAR(%s.date_resolved), \'Year Resolved\')'],
                    'date_resolved'      => ['date resolved', 'ALIAS(DATE(%s.date_resolved), \'Date Resolved\')'],
                ],
                'chats' => [
                    'department'        => ['department', '%s.department'],
                    'agent'             => ['agent', '%s.agent'],
                    'agent_team'        => ['agent team', '%s.agent_team'],
                    'person'            => ['person', '%s.person'],
                    'hour_created'      => ['hour created', 'ALIAS(HOUR(%s.date_created), \'Hour Created\')'],
                    'day_week_created'  => ['day of week created', 'ALIAS(DAYNAME(%s.date_created), \'Day of Week Created\')'],
                    'day_month_created' => ['day of month created', 'ALIAS(DAYOFMONTH(%s.date_created), \'Day of Month Created\')'],
                    'month_created'     => ['month created', 'ALIAS(MONTHNAME(%s.date_created), \'Month Created\')'],
                    'year_created'      => ['year created', 'ALIAS(YEAR(%s.date_created), \'Year Created\')'],
                    'date_created'      => ['date created', 'ALIAS(DATE(%s.date_created), \'Date Created\')'],
                    'none'              => ['nothing', 'NULL'],
                ],
                'articles' => [
                    'person'            => ['person', '%s.person'],
                    'hour_created'      => ['hour created', 'ALIAS(HOUR(%s.date_created), \'Hour Created\')'],
                    'day_week_created'  => ['day of week created', 'ALIAS(DAYNAME(%s.date_created), \'Day of Week Created\')'],
                    'day_month_created' => ['day of month created', 'ALIAS(DAYOFMONTH(%s.date_created), \'Day of Month Created\')'],
                    'month_created'     => ['month created', 'ALIAS(MONTHNAME(%s.date_created), \'Month Created\')'],
                    'year_created'      => ['year created', 'ALIAS(YEAR(%s.date_created), \'Year Created\')'],
                    'date_created'      => ['date created', 'ALIAS(DATE(%s.date_created), \'Date Created\')'],
                    'none'              => ['nothing', 'NULL'],
                ],
                'article_comments' => [
                    'hour_created'      => ['hour created', 'ALIAS(HOUR(%s.date_created), \'Hour Created\')'],
                    'day_week_created'  => ['day of week created', 'ALIAS(DAYNAME(%s.date_created), \'Day of Week Created\')'],
                    'day_month_created' => ['day of month created', 'ALIAS(DAYOFMONTH(%s.date_created), \'Day of Month Created\')'],
                    'month_created'     => ['month created', 'ALIAS(MONTHNAME(%s.date_created), \'Month Created\')'],
                    'year_created'      => ['year created', 'ALIAS(YEAR(%s.date_created), \'Year Created\')'],
                    'date_created'      => ['date created', 'ALIAS(DATE(%s.date_created), \'Date Created\')'],
                    'none'              => ['nothing', 'NULL'],
                ],
                'feedback' => [
                    'type'              => ['type', 'ALIAS(%s.category, \'Type\')'],
                    'status'            => ['status', 'ALIAS(%s.status_category, \'Status\')'],
                    'category'          => ['category', 'ALIAS(%s.custom_data[1], \'category\')'],
                    'person'            => ['person', '%s.person'],
                    'hour_created'      => ['hour created', 'ALIAS(HOUR(%s.date_created), \'Hour Created\')'],
                    'day_week_created'  => ['day of week created', 'ALIAS(DAYNAME(%s.date_created), \'Day of Week Created\')'],
                    'day_month_created' => ['day of month created', 'ALIAS(DAYOFMONTH(%s.date_created), \'Day of Month Created\')'],
                    'month_created'     => ['month created', 'ALIAS(MONTHNAME(%s.date_created), \'Month Created\')'],
                    'year_created'      => ['year created', 'ALIAS(YEAR(%s.date_created), \'Year Created\')'],
                    'date_created'      => ['date created', 'ALIAS(DATE(%s.date_created), \'Date Created\')'],
                    'none'              => ['nothing', 'NULL'],
                ],
                'feedback_comments' => [
                    'hour_created'      => ['hour created', 'ALIAS(HOUR(%s.date_created), \'Hour Created\')'],
                    'day_week_created'  => ['day of week created', 'ALIAS(DAYNAME(%s.date_created), \'Day of Week Created\')'],
                    'day_month_created' => ['day of month created', 'ALIAS(DAYOFMONTH(%s.date_created), \'Day of Month Created\')'],
                    'month_created'     => ['month created', 'ALIAS(MONTHNAME(%s.date_created), \'Month Created\')'],
                    'year_created'      => ['year created', 'ALIAS(YEAR(%s.date_created), \'Year Created\')'],
                    'date_created'      => ['date created', 'ALIAS(DATE(%s.date_created), \'Date Created\')'],
                    'none'              => ['nothing', 'NULL'],
                ],
            ],
            'dates' => [
                'today'         => ['today', '%TODAY%'],
                'yesterday'     => ['yesterday', '%YESTERDAY%'],
                'this_week'     => ['this week', '%THIS_WEEK%'],
                'this_month'    => ['this month', '%THIS_MONTH%'],
                'this_year'     => ['this year', '%THIS_YEAR%'],
                'last_week'     => ['last week', '%LAST_WEEK%'],
                'last_month'    => ['last month', '%LAST_MONTH%'],
                'last_year'     => ['last year', '%LAST_YEAR%'],
                'past_24_hours' => ['in the past 24 hours', '%PAST_24_HOURS%'],
                'past_12_hours' => ['in the past 12 hours', '%PAST_12_HOURS%'],
                'past_hour'     => ['in the past hour', '%PAST_HOUR%'],
                'past_7_days'   => ['in the past 7 days', '%PAST_7_DAYS%'],
                'past_30_days'  => ['in the past 30 days', '%PAST_30_DAYS%'],
                'ever'          => ['any time', '%EVER%'],
            ],
            'statuses' => [
                'tickets' => [
                    'awaiting_user'  => ['awaiting user', '%s.status = \'awaiting_user\''],
                    'awaiting_agent' => ['awaiting agent', '%s.status = \'awaiting_agent\''],
                    'unresolved'     => ['unresolved', '%s.status IN (\'awaiting_user\', \'awaiting_agent\')'],
                    'resolved'       => ['resolved', '%s.status IN (\'resolved\', \'archived\')'],
                    'hidden'         => ['hidden', '%s.status = \'hidden\''],
                    'any'            => ['with any status', '1'],
                ],
            ],
            'orders' => [
                'tickets' => [
                    // todo: number of messages
                    'date_created_asc'      => ['date created (ascending)', '%s.date_created ASC'],
                    'date_created_desc'     => ['date created (descending)', '%s.date_created DESC'],
                    'last_agent_reply_asc'  => ['last agent reply (ascending)', '%s.date_last_agent_reply ASC'],
                    'last_agent_reply_desc' => ['last agent reply (descending)', '%s.date_last_agent_reply DESC'],
                    'last_user_reply_asc'   => ['last user reply (ascending)', '%s.date_last_user_reply ASC'],
                    'last_user_reply_desc'  => ['last user reply (descending)', '%s.date_last_user_reply DESC'],
                    'total_waiting_asc'     => ['total waiting time (ascending)', '%s.total_user_waiting ASC'],
                    'total_waiting_desc'    => ['total waiting time (descending)', '%s.total_user_waiting DESC'],
                ],
            ],
        ];

        $fields = $this->getEntityManager()->getRepository('DeskPRO:CustomDefTicket')->getTopFields();
        foreach ($fields as $field) {
            $escaped                                               = addslashes($field->title);
            $return['fields']['tickets']['ticketfield'.$field->id] = [
                $field->title, 'ALIAS(%s.custom_data['.$field->id.'], \''.$escaped.'\')',
            ];
        }

        $fields = $this->getEntityManager()->getRepository('DeskPRO:CustomDefPerson')->getTopFields();
        foreach ($fields as $field) {
            $escaped                                               = addslashes($field->title);
            $return['fields']['tickets']['personfield'.$field->id] = [
                "creator's ".$field->title, 'ALIAS(%s.person.custom_data['.$field->id.'], \''.$escaped.'\')',
            ];
        }

        $fields = $this->getEntityManager()->getRepository('DeskPRO:CustomDefOrganization')->getTopFields();
        foreach ($fields as $field) {
            $escaped                                            = addslashes($field->title);
            $return['fields']['tickets']['orgfield'.$field->id] = [
                "organizations's ".$field->title, 'ALIAS(%s.organization.custom_data['.$field->id.'], \''.$escaped.'\')',
            ];
        }

        $return['fields']['tickets']['none'] = ['nothing', 'NULL'];

        return $return;
    }
}
