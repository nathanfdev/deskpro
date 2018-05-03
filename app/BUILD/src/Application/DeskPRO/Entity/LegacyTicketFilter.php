<?php

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace Application\DeskPRO\Entity;

use Application\DeskPRO\App;
use Application\DeskPRO\Domain\DomainObject;
use Application\DeskPRO\Searcher\OrganizationSearch;
use Application\DeskPRO\Searcher\PersonSearch;
use Application\DeskPRO\Searcher\TicketSearch;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Mapping\ClassMetadataInfo;
use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Mapping\ClassMetadata as ValidatorClassMetadata;

/**
 * @property int       $id
 * @property Person    $person
 * @property AgentTeam $agent_team
 * @property bool      $is_global
 * @property string    $title
 * @property bool      $is_enabled
 * @property string    $sys_name
 * @property array     $terms
 * @property string    $group_by
 * @property string    $order_by
 * @property int       $display_order
 *
 * @JMS\ExclusionPolicy("all")
 */
class LegacyTicketFilter extends DomainObject
{
    /**
     * @var int
     *
     * @JMS\Expose()
     * @JMS\Type("integer")
     */
    protected $id = null;

    /**
     * Always who created the filter. Or if its not a team or global,
     * also means the person it belongs to.
     *
     * @var \Application\DeskPRO\Entity\Person
     */
    protected $person = null;

    /**
     * If this is a team filter, the team it belongs to.
     *
     * @var \Application\DeskPRO\Entity\AgentTeam
     */
    protected $agent_team = null;

    /**
     * @var bool
     */
    protected $is_global = false;

    /**
     * @JMS\Expose()
     * @JMS\Type("string")
     *
     * @var string
     */
    protected $title;

    /**
     * @var bool
     */
    protected $is_enabled = true;

    /**
     * System name for this filter.
     *
     * @var string
     */
    protected $sys_name = null;

    /**
     * @var array
     *
     * @JMS\Expose()
     * @JMS\SerializedName("term")
     */
    protected $terms = [];

    /**
     * @var string
     */
    protected $group_by = '';

    /**
     * @var string
     */
    protected $order_by = '';

    /**
     * @var int
     *
     * @JMS\Expose()
     * @JMS\Type("integer")
     */
    protected $display_order = 1000;

    /**
     * @var array
     *
     * @JMS\Expose()
     * @JMS\Type("array")
     */
    protected $filter_views = [];

    /**
     * @var array
     *
     * @JMS\Expose()
     * @JMS\Type("array")
     */
    protected $filter_preferences = [];

    /**
     * @var \DateTime
     *
     * @JMS\Expose()
     * @JMS\Type("DateTime")
     */
    protected $date_created = null;

    /**
     * @var \DateTime
     *
     * @JMS\Expose()
     * @JMS\Type("DateTime")
     */
    protected $date_updated = null;

    /**
     * Results from the last search.
     *
     * @var array
     */
    protected $_results = null;

    /**
     * Searcher for the filter.
     *
     * @var \Application\DeskPRO\Searcher\TicketSearch
     */
    protected $_searcher = null;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    public function getPersonId()
    {
        if ($this->person === null) {
            return 0;
        }

        return $this->person['id'];
    }

    public function setPersonId($id)
    {
        if ($id) {
            $this->setModelField('person', App::getEntityRepository('DeskPRO:Person')->find($id));
        } else {
            $this->setModelField('person', null);
        }
    }

    /**
     * @return Person
     */
    public function getPerson()
    {
        return $this->person;
    }

    /**
     * @return AgentTeam
     */
    public function getAgentTeam()
    {
        return $this->agent_team;
    }

    public function getAgentTeamId()
    {
        if (!$this->agent_team) {
            return 0;
        }

        return $this->agent_team['id'];
    }

    public function setAgentTeamId($id)
    {
        if ($id) {
            $agent_team         = App::getOrm()->getRepository('DeskPRO:AgentTeam')->find($id);
            $this['agent_team'] = $agent_team;
        } else {
            $this['agent_team'] = null;
        }
    }

    /**
     * @param array $terms
     */
    public function setTerms(array $terms = null)
    {
        if (!$terms) {
            $terms = [];
        }

        $this->setModelField('terms', $terms);
        $this->_searcher = null;
    }

    /**
     * Reset results so next calls will re-do the search.
     */
    public function resetResults()
    {
        $this->_results = null;
    }

    /**
     * Get the searcher for this.
     *
     * @param array $forceTerms
     *
     * @return TicketSearch
     */
    public function getSearcher(array $forceTerms = [])
    {
        if (!$forceTerms && $this->_searcher) {
            return $this->_searcher;
        }

        $searcher = self::createSearcher($this->sys_name, $this->terms, $forceTerms);
        if (!$forceTerms) {
            $this->_searcher = $searcher;
        }

        if ($this->id) {
            $note = 'filter:'.$this->id;
            if ($this->sys_name) {
                $note .= ' ('.$this->sys_name.')';
            }
            $this->_searcher->setQueryNote($note);
        }

        return $searcher;
    }

    /**
     * @param string $sysName
     * @param array  $terms
     * @param array  $forceTerms
     *
     * @return TicketSearch
     */
    public static function createSearcher($sysName, array $terms, array $forceTerms = [])
    {
        $searcher = new TicketSearch();

        if (!$sysName || strpos($sysName, 'archive_') !== 0) {
            $searcher->enableFilterSearch();
        }

        $user_searcher  = new PersonSearch();
        $org_searcher   = new OrganizationSearch();
        $has_user_terms = false;
        $has_org_terms  = false;

        $force_term_types = [];
        foreach ($forceTerms as $term) {
            if ($term['op'] != 'ignore') {
                if (strpos($term['type'], 'person_') === 0) {
                    $user_searcher->addTerm($term['type'], $term['op'], $term['options']);
                    $has_user_terms = true;
                } elseif (strpos($term['type'], 'org_') === 0) {
                    $org_searcher->addTerm($term['type'], $term['op'], $term['options']);
                    $has_org_terms = true;
                } else {
                    $searcher->addTerm($term['type'], $term['op'], $term['options']);
                }
            }

            $force_term_types[] = $term['type'];
        }

        foreach ($terms as $term) {
            if (in_array($term['type'], $force_term_types)) {
                continue;
            }

            if (strpos($term['type'], 'person_') === 0) {
                $user_searcher->addTerm($term['type'], $term['op'], $term['options']);
                $has_user_terms = true;
            } elseif (strpos($term['type'], 'org_') === 0) {
                $org_searcher->addTerm($term['type'], $term['op'], $term['options']);
                $has_org_terms = true;
            } else {
                $searcher->addTerm($term['type'], $term['op'], $term['options']);
            }
        }

        if ($has_user_terms) {
            $searcher->setPersonSearch($user_searcher);
        }
        if ($has_org_terms) {
            $searcher->setOrganizationSearch($org_searcher);
        }

        return $searcher;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        if ($this->sys_name) {
            if (defined('DP_BOOT_MODE') && DP_BOOT_MODE == 'testing') {
                return $this->sys_name;
            }

            $tr = App::getTranslator();

            switch ($this->sys_name) {
                case 'agent':
                case 'agent_w_hold':
                    return $tr->phrase('agent.tickets.filter_agent');

                case 'agent_team':
                case 'agent_team_w_hold':

                    $person = App::getCurrentPerson();
                    if ($person && $person->isAgent()) {
                        $person->loadHelper('Agent');

                        if (count($person->getTeams()) > 1) {
                            return $tr->phrase('agent.tickets.filter_agent_teams');
                        }
                    }

                    return $tr->phrase('agent.tickets.filter_agent_team');

                case 'participant':
                case 'participant_w_hold':
                    return $tr->phrase('agent.tickets.filter_participant');

                case 'unassigned':
                case 'unassigned_w_hold':
                    return $tr->phrase('agent.tickets.filter_unassigned');

                case 'all':
                case 'all_w_hold':
                    return $tr->phrase('agent.tickets.filter_all');
                case 'archive_awaiting_user':
                    return $tr->phrase('agent.tickets.status_awaiting_user');
                case 'archive_resolved':
                    return $tr->phrase('agent.tickets.status_resolved');
                case 'archive_archived':
                    return $tr->phrase('agent.tickets.status_archived');
                case 'archive_validating':
                    return $tr->phrase('agent.general.awaiting_validation');
                case 'archive_spam':
                    return $tr->phrase('agent.general.spam');
                case 'archive_deleted':
                    return $tr->phrase('agent.general.recycle_bin');
            }
        }

        if (!$this->title && $this->sys_name) {
            return $this->sys_name;
        }

        return $this->title;
    }

    /**
     * Gets the actual value in the title field.
     *
     * @return string
     */
    public function getRawTitle()
    {
        return $this->title;
    }

    /**
     * @return string
     */
    public function getSysName()
    {
        return $this->sys_name;
    }

    /**
     * @return bool
     */
    public function isGlobal()
    {
        return $this->is_global;
    }

    /**
     * Get an array of criteria phrases.
     *
     * @return array
     */
    public function getSummaryParts()
    {
        return $this->getSearcher()->getSummary();
    }

    /**
     * Explain criteria in the filter. Ex: Agent is Unassigned, Category is None.
     *
     * @return string
     */
    public function getSummaryPhrase()
    {
        return implode(', ', $this->getSummaryParts());
    }

    public function getResults(Person $person = null)
    {
        if ($this->_results !== null) {
            return $this->_results;
        }

        $searcher = $this->getSearcher();

        if (!$person) {
            $person = App::getCurrentPerson();
        }
        $searcher->setPerson($person);
        $this->_results = $searcher->getMatches();

        return $this->_results;
    }

    public function isArchiveTableFilter()
    {
        return self::checkFilterNameForArchiveTable($this->sys_name);
    }

    public static function checkFilterNameForArchiveTable($name)
    {
        return in_array($name, self::getArchiveTableFilterNames());
    }

    public static function getArchiveTableFilterNames()
    {
        return [
            'archive_archived',
            'archive_validating',
            'archive_spam',
            'archive_deleted',
        ];
    }

    public function getResultsCount()
    {
        return count($this->getResults());
    }

    /**
     * @return int
     */
    public function getDisplayOrder()
    {
        return $this->display_order;
    }

    /**
     * @return array
     */
    public function getTerms()
    {
        return $this->terms;
    }

    /**
     * @return bool
     */
    public function isProblemFilter()
    {
        return (bool) preg_match('/^problem_\d+$/', $this->getSysName());
    }

    public function __toString()
    {
        return (string) $this->id;
    }

    /**
     * {@inheritdoc}
     */
    public function toApiData($primary = true, $deep = true, array $visited = [])
    {
        $data = parent::toApiData($primary, $deep, $visited);

        return $data;
    }

    //###########################################################################
    // Validation Metadata
    //###########################################################################

    public static function loadValidatorMetadata(ValidatorClassMetadata $metadata)
    {
        $metadata->addPropertyConstraint('title', new NotBlank());
    }

    //###########################################################################
    // Doctrine Metadata
    //###########################################################################

    public static function loadMetadata(ClassMetadata $metadata)
    {
        $metadata->inheritanceType           = ClassMetadataInfo::INHERITANCE_TYPE_NONE;
        $metadata->customRepositoryClassName = 'Application\DeskPRO\EntityRepository\TicketFilter';
        $metadata->changeTrackingPolicy      = ClassMetadataInfo::CHANGETRACKING_NOTIFY;
        $metadata->generatorType             = ClassMetadataInfo::GENERATOR_TYPE_IDENTITY;

        $metadata->setPrimaryTable([
            'name'              => 'ticket_filters',
            'uniqueConstraints' => ['sys_name_unique' => ['columns' => ['sys_name']]],
        ]);

        $metadata->mapField([
            'id'         => true,
            'columnName' => 'id',
            'fieldName'  => 'id',
            'type'       => 'integer',
            'nullable'   => false,
        ]);
        $metadata->mapField([
            'columnName' => 'is_global',
            'fieldName'  => 'is_global',
            'type'       => 'boolean',
            'nullable'   => false,
        ]);
        $metadata->mapField([
            'columnName' => 'title',
            'fieldName'  => 'title',
            'type'       => 'string',
            'length'     => 255,
            'nullable'   => false,
        ]);
        $metadata->mapField([
            'columnName' => 'is_enabled',
            'fieldName'  => 'is_enabled',
            'type'       => 'boolean',
            'nullable'   => false,
        ]);
        $metadata->mapField([
            'columnName' => 'sys_name',
            'fieldName'  => 'sys_name',
            'type'       => 'string',
            'length'     => 50,
            'nullable'   => true,
        ]);
        $metadata->mapField([
            'columnName' => 'terms',
            'fieldName'  => 'terms',
            'type'       => 'json_array',
            'nullable'   => false,
        ]);
        $metadata->mapField([
            'columnName' => 'group_by',
            'fieldName'  => 'group_by',
            'type'       => 'string',
            'length'     => 255,
            'nullable'   => false,
        ]);
        $metadata->mapField([
            'columnName' => 'order_by',
            'fieldName'  => 'order_by',
            'type'       => 'string',
            'length'     => 255,
            'nullable'   => false,
        ]);
        $metadata->mapField([
            'columnName' => 'display_order',
            'fieldName'  => 'display_order',
            'type'       => 'integer',
            'nullable'   => false,
        ]);
        $metadata->mapManyToOne([
            'fieldName'    => 'person',
            'targetEntity' => Person::class,
            'dpApi'        => true,
            'joinColumns'  => [
                [
                    'name'                 => 'person_id',
                    'referencedColumnName' => 'id',
                    'nullable'             => true,
                    'onDelete'             => 'cascade',
                ],
            ],
        ]);
        $metadata->mapManyToOne([
            'fieldName'    => 'agent_team',
            'targetEntity' => AgentTeam::class,
            'dpApi'        => true,
            'joinColumns'  => [
                [
                    'name'                 => 'agent_team_id',
                    'referencedColumnName' => 'id',
                    'nullable'             => true,
                    'onDelete'             => 'cascade',
                ],
            ],
        ]);
    }
}
