<?php

namespace Application\InstallBundle\Upgrade\Build;

use Application\DeskPRO\People\PersonGuest;
use DeskPRO\Bundle\ReportBundle\Dpql2\DpqlContext;
use DeskPRO\Component\Util\DebugUtils;
use DeskPRO\Component\Util\ListUtils;
use DeskPRO\Component\Util\MapUtils;
use DeskPRO\Component\Util\RegexUtils;
use DeskPRO\Component\Util\StringUtils;

class Build1525426643 extends AbstractBuild implements OnlineBuildInterface
{
    private static $fnMap = [
        'ALIAS'                   => 'DPQL_ALIAS',
        'CONCAT'                  => 'DPQL_CONCAT',
        'COUNT'                   => 'DPQL_COUNT',
        'COUNT_DISTINCT'          => 'DPQL_COUNT_DISTINCT',
        'CUR_DATE'                => 'DPQL_CURDATE',
        'CURDATE'                 => 'DPQL_CURDATE',
        'CUR_TIME'                => 'DPQL_CURTIME',
        'CURTIME'                 => 'DPQL_CURTIME',
        'DATE'                    => 'DPQL_DATE',
        'DATE_OFFSET_GROUP'       => 'DPQL_DATE_OFFSET_GROUP',
        'DAY_NAME'                => 'DPQL_DAYNAME',
        'DAYNAME'                 => 'DPQL_DAYNAME',
        'DAY_OF_MONTH'            => 'DPQL_DAYOFMONTH',
        'DAYOFMONTH'              => 'DPQL_DAYOFMONTH',
        'DAY_OF_WEEK'             => 'DPQL_DAYOFWEEK',
        'DAYOFWEEK'               => 'DPQL_DAYOFWEEK',
        'FORMAT'                  => 'DPQL_FORMAT',
        'HIERARCHY'               => 'DPQL_HIERARCHY',
        'HIERARCHY_DESCENDS_FROM' => 'DPQL_HIERARCHY_DESCENDS_FROM',
        'HOUR'                    => 'DPQL_HOUR',
        'JSON_EXTRACT'            => 'DPQL_JSON_EXTRACT',
        'LINK'                    => 'DPQL_LINK',
        'MATRIX'                  => 'DPQL_MATRIX',
        'MINUTE'                  => 'DPQL_MINUTE',
        'MONTH'                   => 'DPQL_MONTH',
        'MONTH_NAME'              => 'DPQL_MONTHNAME',
        'MONTHNAME'               => 'DPQL_MONTHNAME',
        'NOW'                     => 'DPQL_NOW',
        'OBJ_LANG'                => 'DPQL_OBJ_LANG',
        'PERCENT'                 => 'DPQL_PERCENT',
        'PRINT'                   => 'DPQL_PRINT',
        'STACK_GROUP'             => 'DPQL_STACK_GROUP',
        'TIME_LENGTH'             => 'DPQL_TIME_LENGTH',
        'TO_UTC'                  => 'DPQL_TO_UTC',
        'TOTAL'                   => 'DPQL_TOTAL',
        'UTC'                     => 'DPQL_UTC',
        'X'                       => 'DPQL_X',
        'Y'                       => 'DPQL_Y',
        'YEAR'                    => 'DPQL_YEAR',
    ];

    public function addNewTables()
    {
    }

    public function runAlters()
    {
    }

    public function run()
    {
        $this->out('Upgrade custom stats to Reports v2');
        $db          = $this->getDbConnection();
        $dpqlQueries = $db->fetchAll('SELECT * FROM report_builder WHERE is_custom = 1');

        foreach ($dpqlQueries as $dpqlQuery) {
            $this->out("Rewrite query {$dpqlQuery['id']} {$dpqlQuery['title']}");
            try {
                $newQuery = $this->rewriteQuery($dpqlQuery);
                $this->verifyQuery($newQuery);

                $db->insert('report_widget', [
                    'title'         => $newQuery['title'],
                    'description'   => '',
                    'query'         => $newQuery['query'],
                    'is_custom'     => 1,
                    'labels'        => $dpqlQuery['category'],
                    'display_order' => $dpqlQuery['display_order'],
                    'display_types' => implode(',', $newQuery['displayTypes']),
                    'variables'     => json_encode($newQuery['variables']),
                ]);
            } catch (\Exception $e) {
                $this->out("\tFailed: [{$e->getCode()}] {$e->getMessage()} (line {$e->getLine()} of {$e->getFile()})");
                $this->out("\tDPQL: ".preg_replace("/[\r\n]+/", ' ', $dpqlQuery['query']));
                $this->out(StringUtils::reformatLines(DebugUtils::getExceptionSummary($e, true), "\t{.}"));
            }
        }
    }

    private function verifyQuery(array $newQuery)
    {
        $compiler = $this->container->get('dpql.compiler');
        $compiler->compile($newQuery['query'], [], new DpqlContext(new PersonGuest()));
    }

    private function rewriteQuery(array $dpqlQuery)
    {
        $dpql = $dpqlQuery['query'];

        $dpql = preg_replace("/[\r\n]+/", ' ', $dpql);
        $dpql = preg_replace('/[ ]+/', ' ', $dpql);
        $dpql = trim($dpql);

        //-----
        // Display types
        //-----

        $displayRe = 'TABLE|BAR|AREA|PIE|LINE';
        $m         = RegexUtils::getMatches("/^DISPLAY ($displayRe)(?:,\s*($displayRe))?\s+/i", $dpql);
        if (!$m) {
            throw new \InvalidArgumentException('Could not parse DISPLAY part');
        }

        $displayTypes = [$m[1]];
        if (!empty($m[2])) {
            $displayTypes[] = $m[2];
        }
        $displayTypes = ListUtils::map($displayTypes, function ($s) {
            switch (strtolower($s)) {
                case 'table': return 'table';
                case 'bar':   return 'simple_bars';
                case 'pie':   return 'pie';
                case 'area':  return 'simple_area';
                case 'line':  return 'simple_lines';
                default: return null;
            }
        });
        $displayTypes = ListUtils::filterOutFalsey($displayTypes);
        if (empty($displayTypes)) {
            $displayTypes = ['table'];
        }

        // strip out display part
        $dpql = str_replace($m[0], '', $dpql);

        //-----
        // Function rename
        //-----

        // Replace functionc alls
        foreach (self::$fnMap as $fromName => $toName) {
            // e.g. HOUR (foo) => DPQL_HOUR(foo)
            $dpql = preg_replace("/\b$fromName\s*\(/i", "$toName(", $dpql);
        }

        //-----
        // Table rename
        //-----

        // wrong name in old reports
        $dpql = preg_replace('/\btickets_log\b/', 'tickets_logs', $dpql);

        //-----
        // Variables
        //-----

        $newTitle = $dpqlQuery['title'];
        $newVars  = [];

        // titles contain defaults: Average time to resolve tickets <1:date group, default: this_month> ...
        $titleVars = RegexUtils::getAllMatchSets('/<(?P<id>\d+):\s*(?P<type>(?:date|field|order:status))\s+group\s*default:\s*(?P<default>\w+)\s*>/i', $dpql);
        if (!$titleVars) {
            $titleVars = [];
        }

        // Query contains var usage: ... GROUP BY %2:FIELD_GROUP:tickets% ...
        $queryVars = RegexUtils::getAllMatchSets('/%(?P<id>\d+):(?P<type>DATE_GROUP|FIELD_GROUP|ORDER_GROUP|STATUS_GROUP)(?::(?P<fieldType>\w+))?(?::(?P<fieldName>\w+))?%/i', $dpql);
        if (!$queryVars) {
            $queryVars = [];
        }

        // This sorts the vars in both arrays to use the id as a mapping key
        $titleVars = MapUtils::rekeyByKey($titleVars, 'id');
        $queryVars = MapUtils::rekeyByKey($queryVars, 'id');

        foreach ($queryVars as $var) {
            $id = $var['id'];

            $default = null;
            if (isset($titleVars[$var['id']])) {
                $titlePart = $titleVars[$var['id']];
                $default   = $titlePart['default'];
            } else {
                $titlePart = null;
            }

            if (empty($var['fieldType'])) {
                $var['fieldType'] = 'tickets';
            }
            if (empty($var['fieldName'])) {
                $var['fieldName'] = '';
            }

            switch (strtoupper($var['type'])) {
                case 'DATE_GROUP':
                    $varName   = 'date_'.$id;
                    $newVars[] = [
                        'name'    => $varName,
                        'type'    => 'dates',
                        'default' => $default ?: 'today',
                    ];
                    break;

                case 'STATUS_GROUP':
                    $varName   = 'status_'.$id;
                    $newVars[] = [
                        'name'       => $varName,
                        'type'       => 'statuses',
                        'field_type' => $var['fieldType'],
                        'table'      => $var['fieldName'],
                        'default'    => $default ?: 'awaiting_agent',
                    ];
                    break;

                case 'ORDER_GROUP':
                    if ($var['fieldType'] !== 'tickets') {
                        throw new \InvalidArgumentException('ORDER_GROUP: Only valid type is tickets');
                    }
                    $varName   = 'order_'.$id;
                    $newVars[] = [
                        'name'       => $varName,
                        'type'       => 'orders',
                        'field_type' => $var['fieldType'],
                        'table'      => $var['fieldName'],
                    ];
                    break;

                case 'FIELD_GROUP':
                    $varName   = 'field_'.$id;
                    $newVars[] = [
                        'name'       => $varName,
                        'type'       => 'orders',
                        'field_type' => $var['fieldType'],
                        'table'      => $var['fieldName'],
                    ];
                    break;
                default:
                    throw new \RuntimeException();
            }

            $dpql = str_replace($var[0], '${'.$varName.'}', $dpql);

            if ($titlePart) {
                $newTitle = str_replace($titlePart[0], '${'.$varName.'}', $newTitle);
            }
        }

        return [
            'query'        => $dpql,
            'title'        => $newTitle,
            'variables'    => $newVars,
            'displayTypes' => $displayTypes,
        ];
    }
}
