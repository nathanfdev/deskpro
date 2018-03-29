<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\Reports;

use Application\DeskPRO\App;
use Application\DeskPRO\Dpql\Compiler;
use Application\DeskPRO\Dpql\Exception as DpqlException;
use Application\DeskPRO\Entity\ReportWidget;
use Doctrine\ORM\EntityManager;

class Billing
{
    /**
     * @var \Doctrine\ORM\EntityManager
     */
    protected $em;

    /**
     * @var \Application\DeskPRO\EntityRepository\ReportWidget
     */
    protected $repository;

    public function __construct(EntityManager $em)
    {
        $this->em         = $em;
        $this->repository = $this->em->getRepository('DeskPRO:ReportWidget');
        $this->in         = App::getContainer()->getIn();
    }

    /**
     * @param string $id
     *
     * @return bool|string
     */
    public function getRenderedResult($id)
    {
        $report = $this->getById($id);
        $error  = false;

        $params = $this->getParamsInput('params');
        $query  = $report['query'];

        $tempReport            = new ReportWidget();
        $tempReport->title     = $report['title'];
        $report['title_final'] = $tempReport->getTitle('printable', $params);

        $results = $this->renderQuery($query, 'html', $error, $params);

        return $results;
    }

    /**
     * @param $id
     *
     * @return bool
     */
    public function getById($id)
    {
        $reports = $this->_getBillingReports();

        if (empty($reports[$id])) {
            return false;
        }

        return $reports[$id];
    }

    /**
     * @param       $query
     * @param       $renderer
     * @param bool  $error
     * @param array $params
     *
     * @return bool|string
     */
    public function renderQuery($query, $renderer, &$error = false, array $params = [])
    {
        $error = false;
        try {
            $compiler  = new Compiler();
            $statement = $compiler->compile($query, $params);

            return $statement->getRenderer($renderer)->render();
        } catch (DpqlException $e) {
            $error = $e->getMessage();

            return false;
        }
    }

    /**
     * @param string $name
     *
     * @return array
     */
    public function getParamsInput($name = 'params')
    {
        if (isset($_REQUEST[$name])) {
            $params = $_REQUEST[$name];
        } else {
            $params = $this->in->getRaw($name);
        }

        if (is_array($params)) {
            ksort($params);
        } elseif ($params) {
            $newParams = [];
            foreach (explode(',', $params) as $k => $v) {
                $newParams[$k + 1] = $v;
            }
            $params = $newParams;
        } else {
            $params = [];
        }

        return $params;
    }

    /**
     * @return array
     */
    protected function _getBillingReports()
    {
        $currency = addslashes(App::getSetting('core_tickets.billing_currency'));

        $fields      = App::getContainer()->getBillingFieldManager()->getFields();
        $select_bits = [];
        foreach ($fields as $f) {
            $select_bits[] = 'ticket_charges.custom_data['.$f->getId().'] AS \''.addslashes($f->getTitle()).'\'';
        }

        $select_bits = implode(', ', $select_bits);
        if ($select_bits) {
            $select_bits = $select_bits.', ';
        }

        $output = [
            'list-charges-date' => [
                'title' => 'List of charges <1:date group, default: today>',
                'query' => "
                        DISPLAY TABLE
                        SELECT TOTAL(TIME_LENGTH(ticket_charges.charge_time)) AS 'Time', TOTAL(FORMAT(ticket_charges.amount, 'number', 2)) AS 'Amount ($currency)', $select_bits ticket_charges.agent, ticket_charges.date_created, ticket_charges.ticket
                        FROM ticket_charges
                        WHERE ticket_charges.date_created = %1:DATE_GROUP%
                        ORDER BY ticket_charges.date_created
                    ",
            ],
            'total-charges-per-day-date' => [
                'title' => 'Total [charges] per day <1:date group, default: this_month>',
                'query' => "
                        DISPLAY TABLE
                        SELECT TOTAL(COUNT()) AS 'Number of Charges', TOTAL(TIME_LENGTH(SUM(ticket_charges.charge_time))) AS 'Total Time', TOTAL(FORMAT(SUM(ticket_charges.amount), 'number', 2)) AS 'Total Amount ($currency)'
                        FROM ticket_charges
                        WHERE ticket_charges.date_created = %1:DATE_GROUP%
                        GROUP BY DATE(ticket_charges.date_created) AS 'Date'
                    ",
            ],
            'total-amount-charges-per-day-date' => [
                'title' => 'Total [amount charges] per day <1:date group, default: this_month>',
                'query' => "
                        DISPLAY TABLE, BAR
                        SELECT TOTAL(FORMAT(SUM(ticket_charges.amount), 'number', 2)) AS 'Total Amount ($currency)'
                        FROM ticket_charges
                        WHERE ticket_charges.date_created = %1:DATE_GROUP% AND ticket_charges.amount > 0
                        GROUP BY DATE(ticket_charges.date_created) AS 'Date'
                    ",
            ],
            'total-time-charges-per-day-date' => [
                'title' => 'Total [time charges] per day <1:date group, default: this_month>',
                'query' => "
                        DISPLAY TABLE, BAR
                        SELECT TOTAL(TIME_LENGTH(SUM(ticket_charges.charge_time))) AS 'Total Time'
                        FROM ticket_charges
                        WHERE ticket_charges.date_created = %1:DATE_GROUP% AND ticket_charges.charge_time > 0
                        GROUP BY DATE(ticket_charges.date_created) AS 'Date'
                    ",
            ],
            'total-charges-person-date' => [
                'title' => 'Total [charges] per person <1:date group, default: this_month>',
                'query' => "
                        DISPLAY TABLE
                        SELECT TOTAL(COUNT()) AS 'Number of Charges', TOTAL(TIME_LENGTH(SUM(ticket_charges.charge_time))) AS 'Total Time', TOTAL(FORMAT(SUM(ticket_charges.amount), 'number', 2)) AS 'Total Amount ($currency)'
                        FROM ticket_charges
                        WHERE ticket_charges.date_created = %1:DATE_GROUP%
                        GROUP BY ticket_charges.person
                    ",
            ],
            'total-amount-charges-person-date' => [
                'title' => 'Total [amount charges] per person <1:date group, default: this_month>',
                'query' => "
                        DISPLAY TABLE, BAR
                        SELECT TOTAL(FORMAT(SUM(ticket_charges.amount), 'number', 2)) AS 'Total Amount ($currency)'
                        FROM ticket_charges
                        WHERE ticket_charges.date_created = %1:DATE_GROUP% AND ticket_charges.amount > 0
                        GROUP BY ticket_charges.person
                    ",
            ],
            'total-time-charges-person-date' => [
                'title' => 'Total [time charges] per person <1:date group, default: this_month>',
                'query' => "
                        DISPLAY TABLE, BAR
                        SELECT TOTAL(TIME_LENGTH(SUM(ticket_charges.charge_time))) AS 'Total Time'
                        FROM ticket_charges
                        WHERE ticket_charges.date_created = %1:DATE_GROUP% AND ticket_charges.charge_time > 0
                        GROUP BY ticket_charges.person
                    ",
            ],
            'list-charges-person-date' => [
                'title' => 'List of charges per person <1:date group, default: this_month>',
                'query' => "
                        DISPLAY TABLE
                        SELECT TOTAL(TIME_LENGTH(ticket_charges.charge_time)) AS 'Time', TOTAL(FORMAT(ticket_charges.amount, 'number', 2)) AS 'Amount ($currency)', $select_bits ticket_charges.agent, ticket_charges.date_created, ticket_charges.ticket
                        FROM ticket_charges
                        WHERE ticket_charges.date_created = %1:DATE_GROUP%
                        SPLIT BY ticket_charges.person
                        ORDER BY ticket_charges.date_created
                    ",
            ],
            'total-charges-organization-date' => [
                'title' => 'Total [charges] per organization <1:date group, default: this_month>',
                'query' => "
                        DISPLAY TABLE
                        SELECT TOTAL(COUNT()) AS 'Number of Charges', TOTAL(TIME_LENGTH(SUM(ticket_charges.charge_time))) AS 'Total Time', TOTAL(FORMAT(SUM(ticket_charges.amount), 'number', 2)) AS 'Total Amount ($currency)'
                        FROM ticket_charges
                        WHERE ticket_charges.date_created = %1:DATE_GROUP% AND ticket_charges.organization_id <> NULL
                        GROUP BY ticket_charges.organization
                    ",
            ],
            'total-amount-charges-organization-date' => [
                'title' => 'Total [amount charges] per organization <1:date group, default: this_month>',
                'query' => "
                        DISPLAY TABLE, BAR
                        SELECT TOTAL(FORMAT(SUM(ticket_charges.amount), 'number', 2)) AS 'Total Amount ($currency)'
                        FROM ticket_charges
                        WHERE ticket_charges.date_created = %1:DATE_GROUP% AND ticket_charges.organization_id <> NULL AND ticket_charges.amount > 0
                        GROUP BY ticket_charges.organization
                    ",
            ],
            'total-time-charges-organization-date' => [
                'title' => 'Total [time charges] per organization <1:date group, default: this_month>',
                'query' => "
                        DISPLAY TABLE, BAR
                        SELECT TOTAL(TIME_LENGTH(SUM(ticket_charges.charge_time))) AS 'Total Time'
                        FROM ticket_charges
                        WHERE ticket_charges.date_created = %1:DATE_GROUP% AND ticket_charges.organization_id <> NULL AND ticket_charges.charge_time > 0
                        GROUP BY ticket_charges.organization
                    ",
            ],
            'list-charges-organization-date' => [
                'title' => 'List of charges per organization <1:date group, default: this_month>',
                'query' => "
                        DISPLAY TABLE
                        SELECT TOTAL(TIME_LENGTH(ticket_charges.charge_time)) AS 'Time', TOTAL(FORMAT(ticket_charges.amount, 'number', 2)) AS 'Amount ($currency)', $select_bits ticket_charges.agent, ticket_charges.date_created, ticket_charges.ticket
                        FROM ticket_charges
                        WHERE ticket_charges.date_created = %1:DATE_GROUP% AND ticket_charges.organization_id <> NULL
                        SPLIT BY ticket_charges.organization
                        ORDER BY ticket_charges.date_created
                    ",
            ],
            'total-charges-agent-date' => [
                'title' => 'Total [charges] per agent <1:date group, default: this_month>',
                'query' => "
                        DISPLAY TABLE
                        SELECT TOTAL(COUNT()) AS 'Number of Charges', TOTAL(TIME_LENGTH(SUM(ticket_charges.charge_time))) AS 'Total Time', TOTAL(FORMAT(SUM(ticket_charges.amount), 'number', 2)) AS 'Total Amount ($currency)'
                        FROM ticket_charges
                        WHERE ticket_charges.date_created = %1:DATE_GROUP%
                        GROUP BY ticket_charges.agent
                    ",
            ],
            'total-amount-charges-agent-date' => [
                'title' => 'Total [amount charges] per agent <1:date group, default: this_month>',
                'query' => "
                        DISPLAY TABLE, BAR
                        SELECT TOTAL(FORMAT(SUM(ticket_charges.amount), 'number', 2)) AS 'Total Amount ($currency)'
                        FROM ticket_charges
                        WHERE ticket_charges.date_created = %1:DATE_GROUP% AND ticket_charges.amount > 0
                        GROUP BY ticket_charges.agent
                    ",
            ],
            'total-time-charges-agent-date' => [
                'title' => 'Total [time charges] per agent <1:date group, default: this_month>',
                'query' => "
                        DISPLAY TABLE, BAR
                        SELECT TOTAL(TIME_LENGTH(SUM(ticket_charges.charge_time))) AS 'Total Time'
                        FROM ticket_charges
                        WHERE ticket_charges.date_created = %1:DATE_GROUP% AND ticket_charges.charge_time > 0
                        GROUP BY ticket_charges.agent
                    ",
            ],
            'list-charges-agent-date' => [
                'title' => 'List of charges per agent <1:date group, default: this_month>',
                'query' => "
                        DISPLAY TABLE
                        SELECT TOTAL(TIME_LENGTH(ticket_charges.charge_time)) AS 'Time', TOTAL(FORMAT(ticket_charges.amount, 'number', 2)) AS 'Amount ($currency)', $select_bits ticket_charges.date_created, ticket_charges.ticket
                        FROM ticket_charges
                        WHERE ticket_charges.date_created = %1:DATE_GROUP%
                        SPLIT BY ticket_charges.agent
                        ORDER BY ticket_charges.date_created
                    ",
            ],
        ];

        $tempReport = new ReportWidget();

        foreach ($output as $id => &$report) {
            $report['id'] = $id;

            $tempReport->title           = $report['title'];
            $report['title_placeholder'] = $tempReport->getTitle('placeholder');
        }

        return $output;
    }
}
