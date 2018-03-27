<?php

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace Application\DeskPRO\Entity;

use Application\DeskPRO\App;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Mapping\ClassMetadataInfo;

/**
 * Twitter Account Search.
 */
class TwitterAccountSearch extends \Application\DeskPRO\Domain\DomainObject
{
    const CACHE_LENGTH   = 60;
    const SEARCH_RESULTS = 100;

    /**
     * @var int
     */
    protected $id;

    /**
     * @var \Application\DeskPRO\Entity\TwitterAccount
     */
    protected $account;

    /**
     * @var string
     */
    protected $term;

    /**
     * @var \DateTime|null
     */
    protected $date_updated = null;

    /**
     * @var int
     */
    protected $max_id;

    /**
     * @var int
     */
    protected $min_id;

    /**
     * @var \Doctrine\Common\Collections\ArrayCollection
     */
    protected $search_statuses;

    public function __construct()
    {
        $this->search_statuses = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * @return int
     */
    public function getAccountId()
    {
        if (null !== $this->account) {
            return $this->account->getId();
        }

        return 0;
    }

    /**
     * @param int $id
     */
    public function setAccountId($id)
    {
        if ($id && $account = App::getOrm()->getRepository('DeskPRO:TwitterAccount')->find($id)) {
            $this->setModelField('account', $account);
        } else {
            $this->setModelField('account', null);
        }
    }

    public function updateSearch($do_write = true, $since_id = 0)
    {
        $em = App::getOrm();

        try {
            $api     = $this->account->getTwitterApi();
            $results = $api->get_searchTweets([
                'q'                => $this->term,
                'result_type'      => 'recent',
                'count'            => self::SEARCH_RESULTS,
                'since_id'         => $since_id,
                'include_entities' => true,
            ]);
        } catch (\EpiTwitterException $e) {
            return [];
        }

        $new_statuses = [];

        if (!empty($results->statuses)) {
            $twitter = new \Application\DeskPRO\Service\Twitter();
            $lookups = [];
            foreach ($results->statuses as $status) {
                $lookups[$status->id_str] = $twitter->processStatus($api, $status, $do_write, 1);
                if ($do_write) {
                    $em->persist($lookups[$status->id_str]);
                }
            }

            $account_statuses = App::getEntityRepository('DeskPRO:TwitterAccountStatus')->getByTwitterIdsAndAccount(
                array_keys($lookups), $this->account
            );

            foreach ($lookups as $tweet_id => $status) {
                if (isset($account_statuses[$tweet_id])) {
                    $account_status = $account_statuses[$tweet_id];
                } else {
                    $account_status              = new TwitterAccountStatus();
                    $account_status->status      = $status;
                    $account_status->account     = $this->account;
                    $account_status->status_type = null; // this ensures it only appears by search

                    if ($do_write) {
                        $em->persist($account_status);
                        $em->flush();
                    }
                }

                $new_statuses[] = $account_status;

                if (!App::getOrm()->getRepository('DeskPRO:TwitterAccountSearch')->getExistingSearchStatus($this, $account_status)) {
                    $search_status                 = new TwitterAccountSearchStatus();
                    $search_status->search         = $this;
                    $search_status->account_status = $account_status;

                    if ($do_write) {
                        $em->persist($search_status);
                    }

                    $this->search_statuses->add($search_status);
                }
            }
        }

        if (!empty($results->search_metadata)) {
            $this->setModelField('max_id', $results->search_metadata->max_id_str);
            /*if (!$this->min_id || $results->search_metadata->since_id_str + 0 < $this->min_id) {
                $this->setModelField('min_id', $results->search_metadata->since_id_str);
            }*/
        }
        $this->setModelField('date_updated', new \DateTime());

        if ($do_write) {
            $em->persist($this);
            $em->flush();
        }

        return $new_statuses;
    }

    public function getAccountStatuses($includeArchived = false, $page = 1, $per_page = 50, $auto_update = true)
    {
        if ($auto_update) {
            if (!$this->date_updated || $this->date_updated->getTimestamp() < time() - self::CACHE_LENGTH) {
                $this->updateSearch();
            }
        }

        $page   = max(1, intval($page));
        $offset = ($page - 1) * $per_page;

        $output  = [];
        $results = App::getOrm()->createQuery('
            SELECT s,
                a, account, action_agent, agent, agent_team, retweeted,
                notes, replies,
                t, u, ret, recip, long, in_reply
            FROM DeskPRO:TwitterAccountSearchStatus s
            INNER JOIN s.account_status a
            INNER JOIN a.account account
            LEFT JOIN a.action_agent action_agent
            LEFT JOIN a.agent agent
            LEFT JOIN a.agent_team agent_team
            LEFT JOIN a.retweeted retweeted
            LEFT JOIN a.notes notes
            LEFT JOIN a.replies replies
            INNER JOIN a.status t
            INNER JOIN t.user u
            LEFT JOIN t.retweet ret
            LEFT JOIN t.recipient recip
            LEFT JOIN t.long long
            LEFT JOIN t.in_reply_to_status in_reply
            WHERE s.search = ?0
                '.($includeArchived ? '' : 'AND a.is_archived = false').'
            ORDER BY s.date_created DESC
        ')->setParameters([$this])->setMaxResults($per_page)->setFirstResult($offset)->execute();
        foreach ($results as $result) {
            $output[] = $result->account_status;
        }

        return $output;
    }

    public function countAccountStatuses($includeArchived = false, $auto_update = true)
    {
        if ($auto_update) {
            if (!$this->date_updated || $this->date_updated->getTimestamp() < time() - self::CACHE_LENGTH) {
                $this->updateSearch();
            }
        }

        return App::getDb()->fetchColumn('
            SELECT COUNT(*)
            FROM twitter_accounts_searches_statuses AS ss
            INNER JOIN twitter_accounts_statuses AS a ON (ss.account_status_id = a.id)
            WHERE ss.search_id = ?
                '.($includeArchived ? '' : 'AND a.is_archived = 0').'
        ', [$this->id]);
    }

    //###########################################################################
    // Doctrine Metadata
    //###########################################################################

    public static function loadMetadata(ClassMetadata $metadata)
    {
        $metadata->setInheritanceType(ClassMetadataInfo::INHERITANCE_TYPE_NONE);
        $metadata->customRepositoryClassName = 'Application\DeskPRO\EntityRepository\TwitterAccountSearch';
        $metadata->setPrimaryTable(['name' => 'twitter_accounts_searches']);
        $metadata->setChangeTrackingPolicy(ClassMetadataInfo::CHANGETRACKING_NOTIFY);
        $metadata->mapField([
            'fieldName'  => 'id',
            'type'       => 'integer',
            'precision'  => 0,
            'scale'      => 0,
            'nullable'   => false,
            'columnName' => 'id',
            'id'         => true,
        ]);
        $metadata->mapField([
            'fieldName'  => 'term',
            'type'       => 'string',
            'length'     => 255,
            'precision'  => 0,
            'scale'      => 0,
            'nullable'   => false,
            'columnName' => 'term',
        ]);
        $metadata->mapField([
            'fieldName'  => 'date_updated',
            'type'       => 'datetime',
            'precision'  => 0,
            'scale'      => 0,
            'nullable'   => true,
            'columnName' => 'date_updated',
        ]);
        $metadata->mapField([
            'fieldName'  => 'max_id',
            'type'       => 'bigint',
            'precision'  => 0,
            'scale'      => 0,
            'nullable'   => true,
            'columnName' => 'max_id',
        ]);
        $metadata->mapField([
            'fieldName'  => 'min_id',
            'type'       => 'bigint',
            'precision'  => 0,
            'scale'      => 0,
            'nullable'   => true,
            'columnName' => 'min_id',
        ]);
        $metadata->setIdGeneratorType(ClassMetadataInfo::GENERATOR_TYPE_IDENTITY);
        $metadata->mapManyToOne([
            'fieldName'    => 'account',
            'targetEntity' => 'Application\\DeskPRO\\Entity\\TwitterAccount',
            'mappedBy'     => null,
            'inversedBy'   => 'searches',
            'joinColumns'  => [
                0 => [
                    'name'                 => 'account_id',
                    'referencedColumnName' => 'id',
                    'nullable'             => false,
                    'onDelete'             => 'cascade',
                    'columnDefinition'     => null,
                ],
            ],
        ]);
        $metadata->mapOneToMany([
            'fieldName'    => 'search_statuses',
            'targetEntity' => 'Application\\DeskPRO\\Entity\\TwitterAccountSearchStatus',
            'mappedBy'     => 'search',
        ]);
    }
}
