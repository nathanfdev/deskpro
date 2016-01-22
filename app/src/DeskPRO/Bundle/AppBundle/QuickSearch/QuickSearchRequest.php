<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2015, DeskPRO Ltd.
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
 */
namespace DeskPRO\Bundle\AppBundle\QuickSearch;

use Application\DeskPRO\Entity\Person;
use Orb\Util\Numbers;

/**
 * Class QuickSearchRequest.
 */
class QuickSearchRequest
{
    const TYPE_ARTICLE           = 'article';
    const TYPE_DOWNLOAD          = 'download';
    const TYPE_FEEDBACK          = 'feedback';
    const TYPE_NEWS              = 'news';
    const TYPE_TICKET            = 'ticket';
    const TYPE_PERSON            = 'person';
    const TYPE_ORGANIZATION      = 'organization';
    const TYPE_CHAT_CONVERSATION = 'chat_conversation';

    /**
     * @var string
     */
    private $query;

    /**
     * @var string|null
     */
    private $sort;

    /**
     * @var Person
     */
    private $person;

    /**
     * Constructor.
     *
     * @param Person $person
     * @param string $query
     * @param string $sort
     */
    public function __construct(Person $person, $query, $sort = null)
    {
        $this->person = $person;
        $this->query  = $query;
        $this->sort   = $sort;
    }

    /**
     * @return string
     */
    public function getQuery()
    {
        return $this->query;
    }

    /**
     * @return bool
     */
    public function isTicketRef()
    {
        return (bool) preg_match('#^[0-9A-Z\-_\.]+$#', $this->query);
    }

    /**
     * @return bool
     */
    public function isId()
    {
        return Numbers::isInteger($this->query);
    }

    /**
     * @return string|null
     */
    public function getSort()
    {
        return $this->sort;
    }

    /**
     * @return Person
     */
    public function getPerson()
    {
        return $this->person;
    }

    /**
     * @return string[]
     */
    public function getTypes()
    {
        $types = [
            self::TYPE_ARTICLE,
            self::TYPE_DOWNLOAD,
            self::TYPE_FEEDBACK,
            self::TYPE_NEWS,
            self::TYPE_TICKET,
            self::TYPE_PERSON,
            self::TYPE_ORGANIZATION,
            self::TYPE_CHAT_CONVERSATION,
        ];

        if (!$this->person->hasPerm('agent_people.use')) {
            $types = array_diff($types, [self::TYPE_PERSON, self::TYPE_ORGANIZATION]);
        }

        return $types;
    }
}
