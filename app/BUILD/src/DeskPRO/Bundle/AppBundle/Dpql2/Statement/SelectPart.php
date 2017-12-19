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

namespace DeskPRO\Bundle\AppBundle\Dpql2\Statement;

use Application\DeskPRO\Entity\Person;
use DeskPRO\Bundle\AppBundle\Dpql2\Exception;
use DeskPRO\Bundle\AppBundle\Dpql2\Plugin\Hierarchy\HierarchyPlugin;
use DeskPRO\Bundle\AppBundle\Dpql2\Plugin\Hierarchy\HierarchySorting;
use DeskPRO\Bundle\AppBundle\Dpql2\ResultHandler;
use DeskPRO\Bundle\AppBundle\Dpql2\Results;
use DeskPRO\Bundle\AppBundle\Dpql2\SqlSelect;
use DeskPRO\Bundle\AppBundle\Dpql2\SqlSelectContext;
use DeskPRO\Bundle\AppBundle\Dpql2\Statement\Part\AbstractPart;
use DeskPRO\Bundle\AppBundle\Dpql2\Statement\Part\FunctionCall;
use DeskPRO\Bundle\AppBundle\Dpql2\Statement\Part\Prepared;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManager;
use Orb\Util\Strings;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;

/**
 * Object for a select statement in DPQL.
 */
class SelectPart
{
    /**
     * @var EntityManager
     */
    protected $em;

    /**
     * @var Connection
     */
    protected $reportsConnection;

    /**
     * @var TokenStorage
     */
    protected $tokenStorage;

    /**
     * @var DpqlStatementFactory
     */
    protected $statementFactory;

    /**
     * List of expressions in SELECT clause.
     *
     * @var \DeskPRO\Bundle\AppBundle\Dpql2\Statement\Part\AbstractPart[]
     */
    protected $_select = [];

    /**
     * Name of table to select from.
     *
     * @var string
     */
    protected $_from;

    /**
     * WHERE clause.
     *
     * @var \DeskPRO\Bundle\AppBundle\Dpql2\Statement\Part\AbstractPart|null
     */
    protected $_where = null;

    /**
     * SPLIT BY clause expressions.
     *
     * @var \DeskPRO\Bundle\AppBundle\Dpql2\Statement\Part\AbstractPart[]
     */
    protected $_splitBy = [];

    /**
     * GROUP BY clause expressions.
     *
     * @var \DeskPRO\Bundle\AppBundle\Dpql2\Statement\Part\AbstractPart[]
     */
    protected $_groupBy = [];

    /**
     * @var bool
     */
    protected $_withRollup = false;

    /**
     * ORDER BY clause expressions.
     *
     * @var \DeskPRO\Bundle\AppBundle\Dpql2\Statement\Part\AbstractPart[]
     */
    protected $_orderBy = [];

    /**
     * Number of rows to limit to. 0 or null for unlimited.
     *
     * @var int|null
     */
    protected $_limitAmount = null;

    /**
     * An implicit limit on number of rows returned. If specified, limits over this are ignored.
     *
     * @var int
     */
    protected $_implicitLimit = 2500;

    /**
     * Number of rows to offset results by. 0 or null for no offset.
     *
     * @var int|null
     */
    protected $_limitOffset = null;

    /**
     * SQL select object.
     *
     * @var \DeskPRO\Bundle\AppBundle\Dpql2\SqlSelect
     */
    protected $_sql;

    /**
     * SQL select option for splitting, if there is a split by.
     *
     * @var \DeskPRO\Bundle\AppBundle\Dpql2\SqlSelect|null
     */
    protected $_splitSql;

    /**
     * Maps SQL for splitting to the ID in the result set (1-based).
     *
     * @var array
     */
    protected $_splitColumnMap = [];

    /**
     * @var \DeskPRO\Bundle\AppBundle\Dpql2\ResultHandler
     */
    protected $_resultHandler;

    /**
     * Maps aliases (keys) to select field IDs (in the SQL).
     *
     * @var array
     */
    protected $_fieldMap = [];

    /**
     * Has this been prepared yet?
     *
     * @var bool
     */
    protected $_prepared = false;

    /**
     * List of group fill closures.
     *
     * @var \Closure[]
     */
    protected $_groupFills = [];

    /**
     * @var bool
     */
    protected $_isSubquery = false;

    /**
     * Maps available tables (keys) to Doctrine entity names (values).
     *
     * @var array
     */
    protected static $_tableEntityMap = [
        'agent_teams'                 => 'DeskPRO:AgentTeam',
        'articles'                    => 'DeskPRO:Article',
        'article_categories'          => 'DeskPRO:ArticleCategory',
        'article_attachments'         => 'DeskPRO:ArticleAttachment',
        'article_comments'            => 'DeskPRO:ArticleComment',
        'article_pending_create'      => 'DeskPRO:ArticlePendingCreate',
        'ban_emails'                  => 'DeskPRO:BanEmail',
        'ban_ips'                     => 'DeskPRO:BanIp',
        'blobs'                       => 'DeskPRO:Blob',
        'brands'                      => 'DeskPRO:Brand',
        'chat_conversations'          => 'DeskPRO:ChatConversation',
        'chat_messages'               => 'DeskPRO:ChatMessage',
        'custom_data_article'         => 'DeskPRO:CustomDataArticle',
        'custom_data_chat'            => 'DeskPRO:CustomDataChat',
        'custom_data_feedback'        => 'DeskPRO:CustomDataFeedback',
        'custom_data_organizations'   => 'DeskPRO:CustomDataOrganization',
        'custom_data_person'          => 'DeskPRO:CustomDataPerson',
        'custom_data_product'         => 'DeskPRO:CustomDataProduct',
        'custom_data_ticket'          => 'DeskPRO:CustomDataTicket',
        'custom_data_billing'         => 'DeskPRO:CustomDataBilling',
        'custom_def_article'          => 'DeskPRO:CustomDefArticle',
        'custom_def_chat'             => 'DeskPRO:CustomDefChat',
        'custom_def_feedback'         => 'DeskPRO:CustomDefFeedback',
        'custom_def_organizations'    => 'DeskPRO:CustomDefOrganization',
        'custom_def_people'           => 'DeskPRO:CustomDefPerson',
        'custom_def_products'         => 'DeskPRO:CustomDefProduct',
        'custom_def_ticket'           => 'DeskPRO:CustomDefTicket',
        'custom_def_billing'          => 'DeskPRO:CustomDefBilling',
        'custom_field_definition'     => 'DeskPRO:CustomFieldDefinition',
        'departments'                 => 'DeskPRO:Department',
        'downloads'                   => 'DeskPRO:Download',
        'download_categories'         => 'DeskPRO:DownloadCategory',
        'download_comments'           => 'DeskPRO:DownloadComment',
        'email_accounts'              => 'DeskPRO:EmailAccount',
        'email_sources'               => 'DeskPRO:EmailSource',
        'feedback'                    => 'DeskPRO:Feedback',
        'feedback_attachments'        => 'DeskPRO:FeedbackAttachment',
        'feedback_categories'         => 'DeskPRO:FeedbackCategory',
        'feedback_comments'           => 'DeskPRO:FeedbackComment',
        'glossary_words'              => 'DeskPRO:GlossaryWord',
        'glossary_word_definitions'   => 'DeskPRO:GlossaryWordDefinition',
        'labels_articles'             => 'DeskPRO:LabelArticle',
        'labels_chat_conversations'   => 'DeskPRO:LabelChatConversation',
        'labels_downloads'            => 'DeskPRO:LabelDownload',
        'labels_feedback'             => 'DeskPRO:LabelFeedback',
        'labels_news'                 => 'DeskPRO:LabelNews',
        'labels_organizations'        => 'DeskPRO:LabelOrganization',
        'labels_people'               => 'DeskPRO:LabelPerson',
        'labels_tasks'                => 'DeskPRO:LabelTask',
        'labels_tickets'              => 'DeskPRO:LabelTicket',
        'languages'                   => 'DeskPRO:Language',
        'news'                        => 'DeskPRO:News',
        'news_categories'             => 'DeskPRO:NewsCategory',
        'news_comments'               => 'DeskPRO:NewsComment',
        'object_lang'                 => 'DeskPRO:ObjectLang',
        'organizations'               => 'DeskPRO:Organization',
        'organization_email_domains'  => 'DeskPRO:OrganizationEmailDomain',
        'organization_files'          => 'DeskPRO:OrganizationFile',
        'organization_notes'          => 'DeskPRO:OrganizationNote',
        'organizations_contact_data'  => 'DeskPRO:OrganizationContactData',
        'page_view_log'               => 'DeskPRO:PageViewLog',
        'people'                      => 'DeskPRO:Person',
        'people_contact_data'         => 'DeskPRO:PersonContactData',
        'people_emails'               => 'DeskPRO:PersonEmail',
        'people_files'                => 'DeskPRO:PersonFile',
        'people_notes'                => 'DeskPRO:PersonNote',
        'phone_numbers'               => 'DeskPRO:PhoneNumber',
        'products'                    => 'DeskPRO:Product',
        'related_content'             => 'DeskPRO:RelatedContent',
        'searchlog'                   => 'DeskPRO:SearchLog',
        'sms_accounts'                => 'DeskPRO:SmsAccount',
        'tasks'                       => 'DeskPRO:Task',
        'task_comments'               => 'DeskPRO:TaskComment',
        'text_snippets'               => 'DeskPRO:TextSnippet',
        'text_snippet_categories'     => 'DeskPRO:TextSnippetCategory',
        'tickets'                     => 'DeskPRO:Ticket',
        'ticket_categories'           => 'DeskPRO:TicketCategory',
        'ticket_escalation_logs'      => 'DeskPRO:TicketEscalationLog',
        'ticket_escalations'          => 'DeskPRO:TicketEscalation',
        'ticket_filters'              => 'DeskPRO:LegacyTicketFilter',
        'ticket_filter_subscriptions' => 'DeskPRO:TicketFilterSubscription',
        'ticket_layouts'              => 'DeskPRO:TicketLayout',
        'ticket_macros'               => 'DeskPRO:TicketMacro',
        'ticket_object_use_logs'      => 'DeskPRO:TicketObjectUseLog',
        'ticket_priorities'           => 'DeskPRO:TicketPriority',
        'ticket_triggers'             => 'DeskPRO:TicketTrigger',
        'ticket_workflows'            => 'DeskPRO:TicketWorkflow',
        'tickets_attachments'         => 'DeskPRO:TicketAttachment',
        'tickets_deleted'             => 'DeskPRO:TicketDeleted',
        'tickets_flagged'             => 'DeskPRO:TicketFlagged',
        'tickets_participants'        => 'DeskPRO:TicketParticipant',
        'tickets_logs'                => 'DeskPRO:TicketLog',
        'tickets_messages'            => 'DeskPRO:TicketMessage',
        'ticket_attachments'          => 'DeskPRO:TicketAttachment',
        'ticket_charges'              => 'DeskPRO:TicketCharge',
        'ticket_feedback'             => 'DeskPRO:TicketFeedback',
        'ticket_slas'                 => 'DeskPRO:TicketSla',
        'user_rules'                  => 'DeskPRO:UserRule',
        'usergroups'                  => 'DeskPRO:Usergroup',
        'usersources'                 => 'DeskPRO:Usersource',
    ];

    /**
     * @var SqlSelectContext
     */
    private $_sqlSelectContext;

    /**
     * Constructor.
     *
     * @param EntityManager        $em
     * @param Connection           $reportsConnection
     * @param TokenStorage         $tokenStorage
     * @param DpqlStatementFactory $statementFactory
     * @param array                $select            Fields to select
     * @param string               $from              Table to select from
     */
    public function __construct(
        EntityManager        $em,
        Connection           $reportsConnection,
        TokenStorage         $tokenStorage,
        DpqlStatementFactory $statementFactory,
        array                $select,
        $from
    ) {
        $this->em                = $em;
        $this->reportsConnection = $reportsConnection;
        $this->tokenStorage      = $tokenStorage;
        $this->statementFactory  = $statementFactory;

        $this->setSelect($select);
        $this->setFrom($from);

        $this->_sql              = new SqlSelect($this->em->getConnection());
        $this->_resultHandler    = new ResultHandler();
        $this->_sqlSelectContext = new SqlSelectContext($this->reportsConnection, $this->_resultHandler, [
            new HierarchyPlugin(
                $this->reportsConnection,
                new HierarchySorting($this->reportsConnection),
                $this
            ),
        ]);
    }

    /**
     * Returns statement as SQL.
     *
     * @return string
     */
    public function toSql()
    {
        if (!$this->_prepared) {
            $this->prepare();
        }

        return $this->_sql->toSql();
    }

    /**
     * @return bool
     */
    public function isSubquery()
    {
        return $this->_isSubquery;
    }

    /**
     * @param bool $isSubquery
     */
    public function setIsSubquery($isSubquery)
    {
        $this->_isSubquery = $isSubquery;
    }

    /**
     * Gets the results from the database that match.
     *
     * @throws \DeskPRO\Bundle\AppBundle\Dpql2\Exception
     *
     * @return \DeskPRO\Bundle\AppBundle\Dpql2\Results
     */
    public function getResults()
    {
        $results = new Results();
        $this->reportsConnection->query("SET time_zone = '+0:00'");

        try {
            if ($this->_splitColumnMap) {
                $this->_splitSql->setTable($this->_sql->getTable());
                $this->_splitSql->setJoins($this->_sql->getJoins());
                $this->_splitSql->setConditions($this->_sql->getConditions());

                $splitResults = $this->reportsConnection->executeQuery($this->_splitSql->toSql())->fetchAll(\PDO::FETCH_NUM);
                foreach ($splitResults as $splitResult) {
                    $sql = clone $this->_sql;
                    foreach ($this->_splitColumnMap as $splitCondition => $splitColumn) {
                        $splitValue = $splitResult[$splitColumn - 1];
                        if ($splitValue === null) {
                            $sql->addCondition("$splitCondition IS NULL");
                        } else {
                            $sql->addCondition("$splitCondition = ".$this->reportsConnection->quote($splitValue));
                        }
                    }

                    $queryResults = $this->_sqlSelectContext->execute($sql);
                    $results->addSplitResults($this->_fillResults($queryResults), $splitResult);
                }
            } else {
                $queryResults = $this->_sqlSelectContext->execute($this->_sql);
                $results->setResults($this->_fillResults($queryResults));
            }
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        } catch (\Exception $e) {
            throw new Exception('This DPQL statement generated an invalid MySQL query. Please try a different query.');
        }

        return $results;
    }

    protected function _fillResults(array $results)
    {
        if (!$this->_groupFills) {
            return $results;
        }

        if (!$results) {
            return $results;
        }

        $first = reset($results);
        $last  = end($results);

        $base = [];
        foreach ($this->_sql->getSelectFields() as $key => $sel) {
            $base[$key] = null;
        }

        foreach ($this->_groupFills as $fill) {
            $closure = $fill['fill'];
            $print   = $fill['print'] - 1;
            $sql     = $fill['sql'] - 1;
            $order   = $fill['order'] - 1;

            $firstValue    = $first[$order];
            $lastValue     = $last[$order];
            $ascending     = ($lastValue > $firstValue);
            $previousValue = null;
            $startRowValue = null;
            $startRow      = 0;
            $rowSets       = [];

            foreach ($results as $rowKey => $row) {
                if ($previousValue !== null) {
                    if (($ascending && (int) ($row[$order]) < $previousValue) ||
                        (!$ascending && (int) ($row[$order]) > $previousValue)
                    ) {
                        if ($rowKey - 1 > $startRow) {
                            $rowSets[] = [
                                'start'      => $startRow,
                                'end'        => $rowKey - 1,
                                'startValue' => $startRowValue,
                                'endValue'   => $previousValue,
                            ];
                        }
                        $previousValue = null;
                    } else {
                        $previousValue = (int) $row[$order];
                    }
                }

                if ($previousValue === null) {
                    $previousValue = (int) $row[$order];
                    $startRowValue = (int) $row[$order];
                    $startRow      = $rowKey;
                }
            }

            if ($startRow < $rowKey || !$rowSets) {
                $rowSets[] = [
                    'start'      => $startRow,
                    'end'        => $rowKey,
                    'startValue' => $startRowValue,
                    'endValue'   => $previousValue,
                ];
            }

            $newResults = [];
            $seenRow    = 0;
            foreach ($rowSets as $set) {
                if ($set['start'] > $seenRow) {
                    $newResults = array_merge($newResults, array_slice($results, $seenRow, $set['start'] - $seenRow));
                }

                $rows     = array_slice($results, $set['start'], $set['end'] - $set['start'] + 1);
                $setFirst = reset($rows);
                $setLast  = end($rows);

                if ($ascending) {
                    $min = $setFirst[$order];
                    $max = $setLast[$order];
                } else {
                    $min = $setLast[$order];
                    $max = $setFirst[$order];
                }

                if ($first[$order] == $last[$order]) {
                    $newResults = array_merge($newResults, $rows);
                } else {
                    $fills = $closure($min, $max);
                    if (!$ascending) {
                        $fills = array_reverse($fills);
                    }

                    if ($fills) {
                        $fillRow = array_shift($fills);

                        foreach ($rows as $row) {
                            while ($fillRow && (
                                ($ascending && $fillRow[2] < $row[$print]) || (!$ascending && $fillRow[2] > $row[$print])
                            )) {
                                $copyRow         = $base;
                                $copyRow[$print] = $fillRow[0];
                                $copyRow[$sql]   = $fillRow[1];
                                $copyRow[$order] = $fillRow[2];
                                $newResults[]    = $copyRow;

                                $fillRow = array_shift($fills);
                            }
                            while ($fillRow && $fillRow[2] == $row[$print]) {
                                $fillRow = array_shift($fills);
                            }
                            $newResults[] = $row;
                        }

                        if ($fillRow) {
                            array_unshift($fills, $fillRow);
                        }
                        while ($fillRow = array_shift($fills)) {
                            $copyRow         = $base;
                            $copyRow[$print] = $fillRow[0];
                            $copyRow[$sql]   = $fillRow[1];
                            $copyRow[$order] = $fillRow[2];
                            $newResults[]    = $copyRow;
                        }
                    } else {
                        $newResults = array_merge($newResults, $rows);
                    }
                }

                $seenRow = $set['end'];
            }

            $results = $newResults;
        }

        return $results;
    }

    /**
     * @return \DeskPRO\Bundle\AppBundle\Dpql2\ResultHandler
     */
    public function getResultHandler()
    {
        if (!$this->_prepared) {
            $this->prepare();
        }

        return $this->_resultHandler;
    }

    /**
     * Gets the specified renderer object.
     *
     * @param string     $rendererType Type of renderer needed
     * @param array|null $results      If null, gets results
     *
     * @return \DeskPRO\Bundle\AppBundle\Dpql2\Renderer\AbstractRenderer
     */
    public function getRenderer($rendererType, array $results = null)
    {
        if ($results === null) {
            $results = $this->getResults();
        }

        $handler = $this->getResultHandler();

        return \DeskPRO\Bundle\AppBundle\Dpql2\Renderer\AbstractRenderer::create(
            $rendererType, $handler, $results
        );
    }

    /**
     * Gets the statement back as a string of DPQL parts. Keys are:
     * SELECT, FROM, WHERE, SPLIT, GROUP, ORDER, LIMIT, OFFSET.
     *
     * @return array
     */
    public function getDpqlParts()
    {
        $selectFields = [];
        foreach ($this->_select as $field) {
            $selectFields[] = $field->toDpql($this, 'select', []);
        }

        $splitFields = [];
        foreach ($this->_splitBy as $field) {
            $splitFields[] = $field->toDpql($this, 'split', []);
        }

        $groupFields = [];
        foreach ($this->_groupBy as $field) {
            $groupFields[] = $field->toDpql($this, 'group', []);
        }

        $orderFields = [];
        foreach ($this->_orderBy as $field) {
            $orderFields[] = $field->toDpql($this, 'order', []);
        }

        return [
            'SELECT'      => implode(', ', $selectFields),
            'FROM'        => $this->_from,
            'WHERE'       => ($this->_where ? $this->_where->toDpql($this, 'where', []) : ''),
            'SPLIT'       => implode(', ', $splitFields),
            'GROUP'       => implode(', ', $groupFields),
            'ORDER'       => implode(', ', $orderFields),
            'LIMIT'       => $this->_limitAmount,
            'OFFSET'      => $this->_limitOffset,
            'WITH_ROLLUP' => $this->_withRollup,
        ];
    }

    /**
     * @return string
     */
    public function toDpql()
    {
        $parts = [];
        foreach ($this->getDpqlParts() as $key => $value) {
            $parts[Strings::dashToCamelCase($key)] = $value;
        }

        return self::getQueryStringFromParts($parts);
    }

    /**
     * Prepares the statement for use.
     *
     * @throws \DeskPRO\Bundle\AppBundle\Dpql2\Exception
     */
    public function prepare()
    {
        if ($this->_prepared) {
            return;
        }
        $this->_prepared = true;

        $repository = $this->getFromEntityRepository();
        if ($repository) {
            $this->_sql->setTable($repository->getTableName());
        } else {
            throw new Exception("Unknown table $this->_from in FROM clause.");
        }

        $this->_prepareSelect();
        $this->_prepareGroupBy(); // prepare early as it may have aliases
        $this->_prepareWhere();
        $this->_prepareSplitBy();
        $this->_prepareOrderBy();

        $this->_setSqlLimit();
    }

    /**
     * Sets the SQL limit based on the implicit and explicit amounts.
     */
    protected function _setSqlLimit()
    {
        if ($this->_limitAmount) {
            $limit = ($this->_implicitLimit ? min($this->_implicitLimit, $this->_limitAmount) : $this->_limitAmount);
        } else {
            $limit = $this->_implicitLimit;
        }
        $this->_sql->setLimit($limit, $this->_limitOffset);
    }

    /**
     * Prepares the SELECT clause.
     */
    protected function _prepareSelect()
    {
        $sql = $this->_sql;

        foreach ($this->_select as $field) {
            if ($field instanceof Part\Alias) {
                $alias = $field->alias;
                $field = $field->value;
            } else {
                $alias = false;
            }

            if ($field instanceof self) {
                $field->prepare();
                $field = $this->statementFactory->createSubSelect($field->toSql());
            }

            $select = $field->prepare($this, 'select', [], $sql, $this->_resultHandler);
            $this->addPreparedSelectField($select, $alias);
        }
    }

    /**
     * @param Prepared $select
     * @param bool     $alias
     */
    public function addPreparedSelectField(Prepared $select, $alias = false)
    {
        if ($select->hasValue()) {
            $id = $this->addSqlSelectField($select->printed(), $alias);

            $resultTitle = ($alias !== false ? $alias : $select->name());
            $this->_resultHandler->addSelectColumn($resultTitle, $id, $select->renderer());

            if ($select->total()) {
                $this->_resultHandler->addTotalColumn($id);
            }
        }
    }

    /**
     * Prepares the WHERE clause.
     */
    protected function _prepareWhere()
    {
        if ($this->_where) {
            $where = $this->_where->prepare($this, 'where', [], $this->_sql, $this->_resultHandler);
            if ($where->hasValue()) {
                $this->_sql->addCondition($where->sql());
            }
        }
    }

    /**
     * Prepares the SPLIT BY clause.
     */
    protected function _prepareSplitBy()
    {
        if (!$this->_splitBy) {
            return;
        }

        $splitSql        = new SqlSelect($this->em->getConnection());
        $this->_splitSql = $splitSql;

        foreach ($this->_splitBy as $group) {
            $groupBy = $group->prepare($this, 'split', [], $this->_sql, $this->_resultHandler);
            if ($groupBy->hasValue()) {
                $splitSql->addGroupBy($groupBy->sql());

                $this->_splitColumnMap[$groupBy->sql()] = $splitSql->addSelectField($groupBy->sql());

                $id = $splitSql->addSelectField($groupBy->printed());
                $this->_resultHandler->addSplitColumn($id, $groupBy->renderer());
            }
        }

        if (!$this->_splitColumnMap) {
            $this->_splitSql = null;
        }
    }

    /**
     * @return SqlSelect|null
     */
    public function getSplitSql()
    {
        return $this->_splitSql;
    }

    /**
     * Prepares the GROUP BY clause.
     */
    protected function _prepareGroupBy()
    {
        $sql = $this->_sql;

        foreach ($this->_groupBy as $group) {
            if ($group instanceof Part\Alias) {
                $alias = $group->alias;
                $group = $group->value;
            } else {
                $alias = false;
            }

            $groupBy = $group->prepare($this, 'group', [], $sql, $this->_resultHandler);
            if ($groupBy->hasValue()) {
                $printId = $this->addSqlSelectField($groupBy->printed(), $alias);
                $sql->addGroupBy($groupBy->sql());
                $defaultOrder = $this->addDefaultOrder($groupBy->ordered());

                if ($groupBy->printed() === $groupBy->sql()) {
                    $groupId = $printId;
                } else {
                    $groupId = $sql->addSelectField($groupBy->sql());
                }

                if ($groupBy->ordered() == $groupBy->printed()) {
                    $orderId = $printId;
                } else {
                    $orderId = $sql->addSelectField($groupBy->ordered());
                }

                if ($defaultOrder && $groupBy->groupFill()) {
                    $this->addGroupFill($groupBy->groupFill(), $printId, $groupId, $orderId);
                }

                $resultTitle = ($alias !== false ? $alias : $groupBy->name());
                $renderer    = $groupBy->renderer() ?: function ($valueRenderer, $value, $row) {
                    return array_key_exists('hierarchy_title', $row) ? $row['hierarchy_title'] : $value;
                };
                $this->_resultHandler->addGroupYColumn($resultTitle, $groupId, $printId, $renderer);
            }
        }
    }

    /**
     * Adds an order condition if there are no explicitly entered orders.
     *
     * @param string $sql
     *
     * @return bool
     */
    public function addDefaultOrder($sql)
    {
        if (!$this->_orderBy) {
            $this->_sql->addOrderBy($sql);

            return true;
        } else {
            return false;
        }
    }

    /**
     * Prepares the ORDER BY clause.
     */
    protected function _prepareOrderBy()
    {
        $sql = $this->_sql;

        foreach ($this->_orderBy as $order) {
            if ($order instanceof Part\OrderDir) {
                $direction = ' '.$order->orderDir;
                $order     = $order->order;
            } else {
                $direction = false;
            }

            $orderSql = $order->prepare($this, 'order', [], $sql, $this->_resultHandler);
            if ($orderSql->hasValue()) {
                $sql->addOrderBy($orderSql->ordered().$direction);
            }
        }
    }

    /**
     * Adds a group fill handler.
     *
     * @param \Closure $fill
     * @param $printId
     * @param $sqlId
     *
     * @return bool
     */
    public function addGroupFill(\Closure $fill, $printId, $sqlId, $orderId)
    {
        if (count($this->_sql->getGroupBy()) > 1) {
            return false;
        }

        $this->_groupFills[] = [
            'fill'  => $fill,
            'print' => $printId,
            'sql'   => $sqlId,
            'order' => $orderId,
        ];

        return true;
    }

    /**
     * Adds a select field to the SQL result.
     *
     * @param string      $select
     * @param string|bool $alias  If available, the name this column is aliased under
     *
     * @return int
     */
    public function addSqlSelectField($select, $alias = false)
    {
        $selectFieldId = $this->_sql->addSelectField($select);

        if ($alias !== false) {
            $this->_fieldMap[$alias] = $selectFieldId;
        }

        return $selectFieldId;
    }

    /**
     * Gets the SQL select field ID for the specified key. Used for alias lookup.
     *
     * @param string $key
     *
     * @return bool|int
     */
    public function getSqlSelectFieldId($key)
    {
        if (isset($this->_fieldMap[$key])) {
            return $this->_fieldMap[$key];
        } else {
            return false;
        }
    }

    /**
     * Gets the entity repository for the from table.
     *
     * @throws \DeskPRO\Bundle\AppBundle\Dpql2\Exception
     *
     * @return \Application\DeskPRO\EntityRepository\AbstractEntityRepository|bool
     */
    public function getFromEntityRepository()
    {
        return $this->getRepositoryByTable($this->_from);
    }

    /**
     * @param $table
     *
     * @throws Exception
     *
     * @return bool|\Application\DeskPRO\EntityRepository\AbstractEntityRepository
     */
    public function getRepositoryByTable($table)
    {
        $table = strtolower($table);
        if (!isset(self::$_tableEntityMap[$table])) {
            return false;
        }

        $repositoryName = self::$_tableEntityMap[$table];
        $repository     = $this->em->getRepository($repositoryName);

        if (!method_exists($repository, 'getTableName')) {
            throw new Exception("$repositoryName does not extend AbstractEntityRepository so cannot be queried.");
        } else {
            return $repository;
        }
    }

    /**
     * Returns true if the value is non-empty (represents something printable to SQL).
     *
     * @param string $input
     *
     * @return bool
     */
    public function isSqlValue($input)
    {
        return strval($input) !== '';
    }

    /**
     * Returns true if the stack of parent parts has forced date calculations to UTC.
     *
     * @param array $stack
     *
     * @return bool
     */
    public function stackForcedUtc(array $stack)
    {
        foreach ($stack as $element) {
            if ($element instanceof FunctionCall
                && strtoupper($element->name) == 'UTC'
            ) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get the timezone offset for a function/column reference.
     *
     * @param array $stack
     *
     * @return int
     */
    public function getTimezoneOffsetForFunction(array $stack)
    {
        if ($this->stackForcedUtc($stack)) {
            return 0;
        }

        $token = $this->tokenStorage->getToken();
        if (!$token) {
            return 0;
        }

        $user = $token->getUser();
        if (!$user instanceof Person) {
            return 0;
        }

        return $user->getTimezoneOffset() * 3600;
    }

    /**
     * Quotes a string as a DPQL literal.
     *
     * @param string $string
     *
     * @return string
     */
    public function quoteDpqlString($string)
    {
        $string = strtr($string, ['\\' => '\\\\', "'" => "\\'"]);

        return "'$string'";
    }

    /**
     * @param \DeskPRO\Bundle\AppBundle\Dpql2\Statement\Part\AbstractPart[] $select
     */
    public function setSelect(array $select)
    {
        $this->_select = $select;
    }

    /**
     * @param \DeskPRO\Bundle\AppBundle\Dpql2\Statement\Part\AbstractPart $select
     */
    public function addSelect(AbstractPart $select)
    {
        $this->_select[] = $select;
    }

    /**
     * @return \DeskPRO\Bundle\AppBundle\Dpql2\Statement\Part\AbstractPart[]
     */
    public function getSelect()
    {
        return $this->_select;
    }

    /**
     * @param string $from
     */
    public function setFrom($from)
    {
        $this->_from = $from;
    }

    /**
     * @return string
     */
    public function getFrom()
    {
        return $this->_from;
    }

    /**
     * @param \DeskPRO\Bundle\AppBundle\Dpql2\Statement\Part\AbstractPart|null $where
     */
    public function setWhere(AbstractPart $where = null)
    {
        $this->_where = $where;
    }

    /**
     * @return \DeskPRO\Bundle\AppBundle\Dpql2\Statement\Part\AbstractPart|null
     */
    public function getWhere()
    {
        return $this->_where;
    }

    /**
     * @param \DeskPRO\Bundle\AppBundle\Dpql2\Statement\Part\AbstractPart[] $splitBy
     */
    public function setSplitBy(array $splitBy)
    {
        $this->_splitBy = $splitBy;
    }

    /**
     * @return \DeskPRO\Bundle\AppBundle\Dpql2\Statement\Part\AbstractPart[]
     */
    public function getSplitBy()
    {
        return $this->_splitBy;
    }

    /**
     * @param \DeskPRO\Bundle\AppBundle\Dpql2\Statement\Part\AbstractPart[] $groupBy
     */
    public function setGroupBy(array $groupBy)
    {
        $this->_groupBy = $groupBy;
    }

    /**
     * @return \DeskPRO\Bundle\AppBundle\Dpql2\Statement\Part\AbstractPart[]
     */
    public function getGroupBy()
    {
        return $this->_groupBy;
    }

    /**
     * @param \DeskPRO\Bundle\AppBundle\Dpql2\Statement\Part\AbstractPart[] $orderBy
     */
    public function setOrderBy(array $orderBy)
    {
        $this->_orderBy = $orderBy;
    }

    /**
     * @return \DeskPRO\Bundle\AppBundle\Dpql2\Statement\Part\AbstractPart[]
     */
    public function getOrderBy()
    {
        return $this->_orderBy;
    }

    /**
     * @param int|null $amount
     */
    public function setLimitAmount($amount)
    {
        $this->_limitAmount = $amount;
    }

    /**
     * @return int|null
     */
    public function getLimitAmount()
    {
        return $this->_limitAmount;
    }

    /**
     * @param int $amount
     */
    public function setImplicitLimit($amount)
    {
        $this->_implicitLimit = $amount;
        $this->_setSqlLimit();
    }

    /**
     * @param int|null $offset
     */
    public function setLimitOffset($offset)
    {
        $this->_limitOffset = $offset;
    }

    /**
     * @return int|null
     */
    public function getLimitOffset()
    {
        return $this->_limitOffset;
    }

    /**
     * Gets a DPQL query string from a list of parts.
     *
     * @param array $parts
     *
     * @return string
     */
    public static function getQueryStringFromParts(array $parts)
    {
        if (empty($parts['from'])) {
            return '';
        } else {
            $offset = (!empty($parts['offset']) ? " OFFSET {$parts['offset']}" : '');
            if ($parts['select'] === '') {
                $parts['select'] = 'COUNT()';
            }

            return "SELECT {$parts['select']}"
                ."\nFROM {$parts['from']}"
                .(!empty($parts['where']) ? "\nWHERE $parts[where]" : '')
                .(!empty($parts['splitBy']) ? "\nSPLIT BY $parts[splitBy]" : '')
                .(!empty($parts['groupBy']) ? "\nGROUP BY $parts[groupBy]" : '')
                .(!empty($parts['withRollup']) ? "\nWITH ROLLUP" : '')
                .(!empty($parts['orderBy']) ? "\nORDER BY $parts[orderBy]" : '')
                .(!empty($parts['limit']) ? "\nLIMIT $parts[limit]$offset" : '');
        }
    }

    /**
     * Gets the map from tables to entities.
     *
     * @return array
     */
    public static function getTableEntityList()
    {
        return self::$_tableEntityMap;
    }

    /**
     * @return SqlSelectContext
     */
    public function getSqlSelectContext()
    {
        return $this->_sqlSelectContext;
    }

    /**
     * @param bool $withRollup
     */
    public function setWithRollup($withRollup)
    {
        if ($this->_withRollup = $withRollup) {
            $this->_resultHandler->addFlag(ResultHandler::FLAG_WITH_ROLLUP);
        }
    }

    /**
     * @return bool
     */
    public function withRollup()
    {
        return $this->_withRollup;
    }
}
