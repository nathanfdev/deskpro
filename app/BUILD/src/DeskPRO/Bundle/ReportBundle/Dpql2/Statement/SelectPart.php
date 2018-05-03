<?php

namespace DeskPRO\Bundle\ReportBundle\Dpql2\Statement;

use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\Problem;
use Application\DeskPRO\Entity\Session;
use DeskPRO\Bundle\AppBundle\Entity\HitRecord;
use DeskPRO\Bundle\AppBundle\Entity\Snippet;
use DeskPRO\Bundle\AppBundle\Entity\SnippetUseLog;
use DeskPRO\Bundle\AppBundle\Entity\VoiceNumber;
use DeskPRO\Bundle\AppBundle\Entity\VoicePhoneCall;
use DeskPRO\Bundle\AppBundle\Entity\VoicePhoneCallLog;
use DeskPRO\Bundle\ReportBundle\Dpql2\DpqlContextStorage;
use DeskPRO\Bundle\ReportBundle\Dpql2\DpqlException;
use DeskPRO\Bundle\ReportBundle\Dpql2\Helper\CustomDataHelper;
use DeskPRO\Bundle\ReportBundle\Dpql2\Plugin\Hierarchy\HierarchyPlugin;
use DeskPRO\Bundle\ReportBundle\Dpql2\Plugin\Hierarchy\HierarchySorting;
use DeskPRO\Bundle\ReportBundle\Dpql2\SqlSelect;
use DeskPRO\Bundle\ReportBundle\Dpql2\SqlSelectContext;
use DeskPRO\Bundle\ReportBundle\Dpql2\Statement\Part\AbstractPart;
use DeskPRO\Bundle\ReportBundle\Dpql2\Statement\Part\Alias;
use DeskPRO\Bundle\ReportBundle\Dpql2\Statement\Part\FunctionCall;
use DeskPRO\Bundle\ReportBundle\Dpql2\Statement\Part\Prepared;
use DeskPRO\Bundle\ReportBundle\Reports\ResultMetadata;
use DeskPRO\Bundle\ReportBundle\Reports\Results;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManager;
use Orb\Util\Strings;

/**
 * Object for a select statement in DPQL.
 */
class SelectPart
{
    /**
     * @var EntityManager
     */
    private $em;

    /**
     * @var Connection
     */
    private $reportsConnection;

    /**
     * @var DpqlContextStorage
     */
    private $contextStorage;

    /**
     * @var CustomDataHelper
     */
    private $customDataHelper;

    /**
     * @var DpqlStatementFactory
     */
    private $statementFactory;

    /**
     * E.g. if part of a LAYER WITH <type>, a hint to the renderer
     * what type of graph we want.
     *
     * @var string|null
     */
    private $graphTypeHint;

    /**
     * List of expressions in SELECT clause.
     *
     * @var AbstractPart[]
     */
    private $select = [];

    /**
     * Name of table to select from.
     *
     * @var string
     */
    private $from;

    /**
     * WHERE clause.
     *
     * @var AbstractPart|null
     */
    private $where = null;

    /**
     * SPLIT BY clause expressions.
     *
     * @var AbstractPart[]
     */
    private $splitBy = [];

    /**
     * GROUP BY clause expressions.
     *
     * @var AbstractPart[]
     */
    private $groupBy = [];

    /**
     * @var bool
     */
    private $withRollup = false;

    /**
     * ORDER BY clause expressions.
     *
     * @var AbstractPart[]
     */
    private $orderBy = [];

    /**
     * Number of rows to limit to. 0 or null for unlimited.
     *
     * @var int|null
     */
    private $limitAmount = null;

    /**
     * An implicit limit on number of rows returned. If specified, limits over this are ignored.
     *
     * @var int
     */
    private $implicitLimit = 2500;

    /**
     * Number of rows to offset results by. 0 or null for no offset.
     *
     * @var int|null
     */
    private $limitOffset = null;

    /**
     * SQL select object.
     *
     * @var SqlSelect
     */
    private $sql;

    /**
     * SQL select option for splitting, if there is a split by.
     *
     * @var SqlSelect|null
     */
    private $splitSql;

    /**
     * Maps SQL for splitting to the ID in the result set (1-based).
     *
     * @var array
     */
    private $splitColumnMap = [];

    /**
     * @var ResultMetadata
     */
    private $resultMetadata;

    /**
     * Maps aliases (keys) to select field IDs (in the SQL).
     *
     * @var array
     */
    private $fieldMap = [];

    /**
     * Has this been prepared yet?
     *
     * @var bool
     */
    private $prepared = false;

    /**
     * List of group fill closures.
     *
     * @var \Closure[]
     */
    private $groupFills = [];

    /**
     * @var bool
     */
    private $isSubQuery = false;

    /**
     * @var bool
     */
    private $isUnionPart = false;

    /**
     * Maps available tables (keys) to Doctrine entity names (values).
     *
     * @var array
     */
    private static $tableEntityMap = [
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
        'snippets'                    => Snippet::class,
        'snippet_use_log'             => SnippetUseLog::class,
        'problems'                    => Problem::class,
        'sessions'                    => Session::class,
        'hit_record'                  => HitRecord::class,
        'voice_numbers'               => VoiceNumber::class,
        'voice_phone_calls'           => VoicePhoneCall::class,
        'voice_phone_call_logs'       => VoicePhoneCallLog::class,
    ];

    /**
     * @var SqlSelectContext
     */
    private $sqlSelectContext;

    /**
     * Constructor.
     *
     * @param EntityManager        $em
     * @param Connection           $reportsConnection
     * @param DpqlContextStorage   $contextStorage
     * @param CustomDataHelper     $customDataHelper
     * @param DpqlStatementFactory $statementFactory
     * @param array                $select            Fields to select
     * @param string               $from              Table to select from
     */
    public function __construct(
        EntityManager        $em,
        Connection           $reportsConnection,
        DpqlContextStorage   $contextStorage,
        CustomDataHelper     $customDataHelper,
        DpqlStatementFactory $statementFactory,
        array                $select,
        $from
    ) {
        $this->em                = $em;
        $this->reportsConnection = $reportsConnection;
        $this->contextStorage    = $contextStorage;
        $this->customDataHelper  = $customDataHelper;
        $this->statementFactory  = $statementFactory;

        $this->setSelect($select);
        $this->setFrom($from);

        $context = $this->contextStorage->getContext();
        $person  = $context && $context->getPerson() instanceof Person ? $context->getPerson() : null;

        $this->sql              = new SqlSelect($this->em->getConnection());
        $this->resultMetadata   = new ResultMetadata($person);
        $this->sqlSelectContext = new SqlSelectContext($this->reportsConnection, $this->resultMetadata, [
            new HierarchyPlugin(
                $this->reportsConnection,
                new HierarchySorting($this->reportsConnection),
                $this,
                $this->customDataHelper
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
        if (!$this->prepared) {
            $this->prepare();
        }

        return $this->sql->toSql();
    }

    /**
     * @return bool
     */
    public function isSubQuery()
    {
        return $this->isSubQuery;
    }

    /**
     * @param bool $isSubQuery
     */
    public function setIsSubQuery($isSubQuery)
    {
        $this->isSubQuery = $isSubQuery;
    }

    /**
     * @return bool
     */
    public function isUnionPart()
    {
        return $this->isUnionPart;
    }

    /**
     * @param bool $isUnionPart
     */
    public function setIsUnionPart($isUnionPart)
    {
        $this->isUnionPart = $isUnionPart;
    }

    /**
     * Gets the results from the database that match.
     *
     * @throws DpqlException
     *
     * @return Results
     */
    public function getResults()
    {
        $results = new Results();
        $results->setMetadata($this->resultMetadata);
        $this->reportsConnection->query("SET time_zone = '+0:00'");

        try {
            if ($this->splitColumnMap) {
                $this->splitSql->setTable($this->sql->getTable());
                $this->splitSql->setJoins($this->sql->getJoins());
                $this->splitSql->setConditions($this->sql->getConditions());

                $splitResults = $this->reportsConnection->executeQuery($this->splitSql->toSql())->fetchAll(\PDO::FETCH_NUM);
                foreach ($splitResults as $splitResult) {
                    $sql = clone $this->sql;
                    foreach ($this->splitColumnMap as $splitCondition => $splitColumn) {
                        $splitValue = $splitResult[$splitColumn - 1];
                        if ($splitValue === null) {
                            $sql->addCondition("$splitCondition IS NULL");
                        } else {
                            $sql->addCondition("$splitCondition = ".$this->reportsConnection->quote($splitValue));
                        }
                    }

                    $queryResults = $this->sqlSelectContext->execute($sql, $this->resultMetadata);
                    $results->addSplitResults($this->fillResults($queryResults), $splitResult);
                }
            } else {
                $queryResults = $this->sqlSelectContext->execute($this->sql, $this->resultMetadata);
                $results->setResults($this->fillResults($queryResults));
            }
        } catch (DpqlException $e) {
            throw new DpqlException($e->getMessage());
        } catch (\Exception $e) {
            throw new DpqlException('This DPQL statement generated an invalid MySQL query. Please try a different query.', 0, $e);
        }

        return $results;
    }

    /**
     * @param array $results
     *
     * @return array
     */
    protected function fillResults(array $results)
    {
        if (!$this->groupFills) {
            return $results;
        }

        if (!$results) {
            return $results;
        }

        $first = reset($results);
        $last  = end($results);

        $base = [];
        foreach ($this->sql->getSelectFields() as $key => $sel) {
            $base[$key] = null;
        }

        foreach ($this->groupFills as $fill) {
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
     * @return \DeskPRO\Bundle\ReportBundle\Reports\ResultMetadata
     */
    public function getResultMetadata()
    {
        if (!$this->prepared) {
            $this->prepare();
        }

        return $this->resultMetadata;
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
        foreach ($this->select as $field) {
            $selectFields[] = $field->toDpql($this, 'select', []);
        }

        $splitFields = [];
        foreach ($this->splitBy as $field) {
            $splitFields[] = $field->toDpql($this, 'split', []);
        }

        $groupFields = [];
        foreach ($this->groupBy as $field) {
            $groupFields[] = $field->toDpql($this, 'group', []);
        }

        $orderFields = [];
        foreach ($this->orderBy as $field) {
            $orderFields[] = $field->toDpql($this, 'order', []);
        }

        $from = $this->from;
        if ($from instanceof AbstractPart) {
            $from = $from->toDpql($this, 'from', []);
        }

        return [
            'SELECT'      => implode(', ', $selectFields),
            'FROM'        => $from,
            'WHERE'       => ($this->where ? $this->where->toDpql($this, 'where', []) : ''),
            'SPLIT'       => implode(', ', $splitFields),
            'GROUP'       => implode(', ', $groupFields),
            'ORDER'       => implode(', ', $orderFields),
            'LIMIT'       => $this->limitAmount,
            'OFFSET'      => $this->limitOffset,
            'WITH_ROLLUP' => $this->withRollup,
        ];
    }

    /**
     * @return array
     */
    public function getDpqlPartsForInput()
    {
        $parts = $this->getDpqlParts();

        return [
            'select'      => $parts['SELECT'],
            'from'        => $parts['FROM'],
            'where'       => $parts['WHERE'],
            'split_by'    => $parts['SPLIT'],
            'group_by'    => $parts['GROUP'],
            'order_by'    => $parts['ORDER'],
            'with_rollup' => $parts['WITH_ROLLUP'],
            'limit'       => $parts['LIMIT'] ?: '',
            'offset'      => $parts['OFFSET'] ?: '',
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
     * @throws \DeskPRO\Bundle\ReportBundle\Dpql2\DpqlException
     */
    public function prepare()
    {
        if ($this->prepared) {
            return;
        }
        $this->prepared = true;

        if ($this->from instanceof Alias) {
            $prepared = $this->from->prepare($this, 'from', [], $this->sql, $this->resultMetadata);
            $this->sql->setTable([$prepared->sql(), $this->from->alias]);
        } else {
            $repository = $this->getFromEntityRepository();
            if ($repository) {
                $this->sql->setTable($repository->getTableName());
            } else {
                throw new DpqlException("Unknown table $this->from in FROM clause.");
            }
        }

        $this->prepareSelect();
        $this->prepareGroupBy(); // prepare early as it may have aliases
        $this->prepareWhere();
        $this->prepareSplitBy();
        $this->prepareOrderBy();

        $this->setSqlLimit();
    }

    /**
     * Sets the SQL limit based on the implicit and explicit amounts.
     */
    protected function setSqlLimit()
    {
        if ($this->limitAmount) {
            $limit = ($this->implicitLimit ? min($this->implicitLimit, $this->limitAmount) : $this->limitAmount);
        } else {
            $limit = $this->implicitLimit;
        }
        $this->sql->setLimit($limit, $this->limitOffset);
    }

    /**
     * Prepares the SELECT clause.
     */
    protected function prepareSelect()
    {
        $sql = $this->sql;

        foreach ($this->select as $field) {
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

            if ($field instanceof Part\FunctionCall && $field->name === 'DPQL_CONCAT') {
                $concatIds = [];
                foreach ($field->arguments as $concatField) {
                    $select = $concatField->prepare($this, 'select', [], $sql, $this->resultMetadata);
                    if ($select->hasValue()) {
                        $concatIds[] = $this->addSqlSelectField($select->printed());
                    }
                }

                $select      = $field->prepare($this, 'select', [], $sql, $this->resultMetadata);
                $resultTitle = ($alias !== false ? $alias : $select->name());

                $this->resultMetadata->addSelectColumn($resultTitle, $concatIds, $select->renderer());
            } else {
                $select = $field->prepare($this, 'select', [], $sql, $this->resultMetadata);
                $this->addPreparedSelectField($select, $alias);
            }
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
            $this->resultMetadata->addSelectColumn($resultTitle, $id, $select->renderer());

            if ($select->total()) {
                $this->resultMetadata->addTotalColumn($id);
            }
        }
    }

    /**
     * Prepares the WHERE clause.
     */
    protected function prepareWhere()
    {
        if ($this->where) {
            $where = $this->where->prepare($this, 'where', [], $this->sql, $this->resultMetadata);
            if ($where->hasValue()) {
                $this->sql->addCondition($where->sql());
            }
        }
    }

    /**
     * Prepares the SPLIT BY clause.
     */
    protected function prepareSplitBy()
    {
        if (!$this->splitBy) {
            return;
        }
        if ($this->isUnionPart) {
            throw new DpqlException('Unable to split UNION query part');
        }

        $splitSql       = new SqlSelect($this->em->getConnection());
        $this->splitSql = $splitSql;

        foreach ($this->splitBy as $group) {
            $groupBy = $group->prepare($this, 'split', [], $this->sql, $this->resultMetadata);
            if ($groupBy->hasValue()) {
                $splitSql->addGroupBy($groupBy->sql());

                $this->splitColumnMap[$groupBy->sql()] = $splitSql->addSelectField($groupBy->sql());

                $id = $splitSql->addSelectField($groupBy->printed());
                $this->resultMetadata->addSplitColumn($id, $groupBy->renderer());
            }
        }

        if (!$this->splitColumnMap) {
            $this->splitSql = null;
        }
    }

    /**
     * @return SqlSelect|null
     */
    public function getSplitSql()
    {
        return $this->splitSql;
    }

    /**
     * Prepares the GROUP BY clause.
     */
    protected function prepareGroupBy()
    {
        $sql = $this->sql;

        foreach ($this->groupBy as $group) {
            if ($group instanceof Part\Alias) {
                $alias = $group->alias;
                $group = $group->value;
            } else {
                $alias = false;
            }

            $groupBy = $group->prepare($this, 'group', [], $sql, $this->resultMetadata);
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
                    if (count($this->resultMetadata->getGroupYColumns()) > 1) {
                        return $value;
                    }

                    return array_key_exists('hierarchy_title', $row) ? $row['hierarchy_title'] : $value;
                };
                $this->resultMetadata->addGroupYColumn($resultTitle, $groupId, $printId, $renderer);
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
        if (!$this->orderBy) {
            $this->sql->addOrderBy($sql);

            return true;
        } else {
            return false;
        }
    }

    /**
     * Prepares the ORDER BY clause.
     */
    protected function prepareOrderBy()
    {
        $sql = $this->sql;

        foreach ($this->orderBy as $order) {
            if ($order instanceof Part\OrderDir) {
                $direction = ' '.$order->orderDir;
                $order     = $order->order;
            } else {
                $direction = false;
            }

            $orderSql = $order->prepare($this, 'order', [], $sql, $this->resultMetadata);
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
        if (count($this->sql->getGroupBy()) > 1) {
            return false;
        }

        $this->groupFills[] = [
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
        $selectFieldId = $this->sql->addSelectField($select);

        if ($alias !== false) {
            $this->fieldMap[$alias] = $selectFieldId;
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
        if (isset($this->fieldMap[$key])) {
            return $this->fieldMap[$key];
        } else {
            return false;
        }
    }

    /**
     * Gets the entity repository for the from table.
     *
     * @throws \DeskPRO\Bundle\ReportBundle\Dpql2\DpqlException
     *
     * @return \Application\DeskPRO\EntityRepository\AbstractEntityRepository|bool
     */
    public function getFromEntityRepository()
    {
        return $this->getRepositoryByTable($this->from);
    }

    /**
     * @param $table
     *
     * @throws DpqlException
     *
     * @return bool|\Application\DeskPRO\EntityRepository\AbstractEntityRepository
     */
    public function getRepositoryByTable($table)
    {
        $table = strtolower($table);
        if (!isset(self::$tableEntityMap[$table])) {
            return false;
        }

        $repositoryName = self::$tableEntityMap[$table];
        $repository     = $this->em->getRepository($repositoryName);

        if (!method_exists($repository, 'getTableName')) {
            throw new DpqlException("$repositoryName does not extend AbstractEntityRepository so cannot be queried.");
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

        $context = $this->contextStorage->getContext();
        if (!$context) {
            return 0;
        }

        $user = $context->getPerson();
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
     * @param AbstractPart[] $select
     */
    public function setSelect(array $select)
    {
        $this->select = $select;
    }

    /**
     * @param AbstractPart $select
     */
    public function addSelect(AbstractPart $select)
    {
        $this->select[] = $select;
    }

    /**
     * @return AbstractPart[]
     */
    public function getSelect()
    {
        return $this->select;
    }

    /**
     * @param string $from
     */
    public function setFrom($from)
    {
        $this->from = $from;
    }

    /**
     * @return string
     */
    public function getFrom()
    {
        return $this->from;
    }

    /**
     * @param AbstractPart|null $where
     */
    public function setWhere(AbstractPart $where = null)
    {
        $this->where = $where;
    }

    /**
     * @return AbstractPart|null
     */
    public function getWhere()
    {
        return $this->where;
    }

    /**
     * @param AbstractPart[] $splitBy
     */
    public function setSplitBy(array $splitBy)
    {
        $this->splitBy = $splitBy;
    }

    /**
     * @return AbstractPart[]
     */
    public function getSplitBy()
    {
        return $this->splitBy;
    }

    /**
     * @param AbstractPart[] $groupBy
     */
    public function setGroupBy(array $groupBy)
    {
        $this->groupBy = $groupBy;
    }

    /**
     * @return AbstractPart[]
     */
    public function getGroupBy()
    {
        return $this->groupBy;
    }

    /**
     * @param AbstractPart[] $orderBy
     */
    public function setOrderBy(array $orderBy)
    {
        $this->orderBy = $orderBy;
    }

    /**
     * @return AbstractPart[]
     */
    public function getOrderBy()
    {
        return $this->orderBy;
    }

    /**
     * @param int|null $amount
     */
    public function setLimitAmount($amount)
    {
        $this->limitAmount = $amount;
    }

    /**
     * @return int|null
     */
    public function getLimitAmount()
    {
        return $this->limitAmount;
    }

    /**
     * @param int $amount
     */
    public function setImplicitLimit($amount)
    {
        $this->implicitLimit = $amount;
        $this->setSqlLimit();
    }

    /**
     * @param int|null $offset
     */
    public function setLimitOffset($offset)
    {
        $this->limitOffset = $offset;
    }

    /**
     * @return int|null
     */
    public function getLimitOffset()
    {
        return $this->limitOffset;
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
                .(!empty($parts['split_by']) ? "\nSPLIT BY $parts[split_by]" : '')
                .(!empty($parts['group_by']) ? "\nGROUP BY $parts[group_by]" : '')
                .(!empty($parts['with_rollup']) ? "\nWITH ROLLUP" : '')
                .(!empty($parts['order_by']) ? "\nORDER BY $parts[order_by]" : '')
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
        return self::$tableEntityMap;
    }

    /**
     * @return SqlSelectContext
     */
    public function getSqlSelectContext()
    {
        return $this->sqlSelectContext;
    }

    /**
     * @param bool $withRollup
     */
    public function setWithRollup($withRollup)
    {
        $this->withRollup = $withRollup;
        if ($this->withRollup) {
            $this->resultMetadata->addFlag(ResultMetadata::FLAG_WITH_ROLLUP);
        }
    }

    /**
     * @return bool
     */
    public function withRollup()
    {
        return $this->withRollup;
    }

    /**
     * @return null|string
     */
    public function getGraphTypeHint()
    {
        return $this->graphTypeHint;
    }

    /**
     * @param null|string $graphTypeHint
     */
    public function setGraphTypeHint($graphTypeHint)
    {
        $this->graphTypeHint = $graphTypeHint;
    }
}
