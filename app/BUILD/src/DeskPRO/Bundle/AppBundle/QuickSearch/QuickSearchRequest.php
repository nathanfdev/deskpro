<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2016, DeskPRO Ltd.
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
use Orb\Util\Arrays;
use Orb\Util\Numbers;
use Orb\Util\Strings;
use Orb\Validator\StringEmail;

/**
 * Class QuickSearchRequest.
 */
class QuickSearchRequest
{
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
     *
     * @deprecated Use Token storage instead
     */
    private $person;

    /**
     * @var string[]
     */
    private $words;

    /**
     * @var string[];
     */
    private $types;

    /**
     * @var bool
     */
    private $enable_sideloads = true;

    /**
     * @var int
     */
    private $limit = 100;

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
     * @return bool
     */
    public function isValidEmail()
    {
        return StringEmail::isValueValid($this->query);
    }

    /**
     * @return bool
     */
    public function isEmailPart()
    {
        return strpos($this->query, '@') !== false;
    }

    /**
     * @return bool
     */
    public function isEmailDomain()
    {
        return strpos($this->query, '@') === 0;
    }

    /**
     * @return null|string
     */
    public function getEmailDomain()
    {
        return substr($this->query, strpos($this->query, '@') + 1);
    }

    /**
     * @return string
     */
    public function getLabel()
    {
        if (preg_match('#^\[(.*?)\]$#', $this->query, $matches)) {
            return $matches[1];
        }

        return '';
    }

    /**
     * @return bool
     */
    public function isLabel()
    {
        return (bool) $this->getLabel();
    }

    /**
     * @return $this
     */
    public function disableSideloads()
    {
        $this->enable_sideloads = false;

        return $this;
    }

    /**
     * @return bool
     */
    public function enabledSideloads()
    {
        return $this->enable_sideloads;
    }

    /**
     * @return string[]
     */
    public function getWords()
    {
        if (!is_array($this->words)) {
            $words = Strings::utf8_strtolower($this->query);
            $words = explode(' ', $words);
            $words = Arrays::removeFalsey($words);
            $words = array_unique($words);
            $words = array_filter($words, function ($s) {
                return strlen($s) >= 3;
            });

            $this->words = $words;
        }

        return $this->words;
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
     *
     * @deprecated Use Token storage instead
     */
    public function getPerson()
    {
        return $this->person;
    }

    /**
     * @param array $types
     *
     * @return $this
     */
    public function setTypes(array $types)
    {
        $this->types = array_intersect($types, array_keys(QuickSearchContext::getDoctrineMapping()));

        return $this;
    }

    /**
     * @return string[]
     */
    public function getTypes()
    {
        if (!$this->types) {
            $types = array_keys(QuickSearchContext::getDoctrineMapping());
        } else {
            $types = $this->types;
        }

        if (!$this->person->hasPerm('agent_people.use')) {
            $types = array_diff($types, [QuickSearchContext::TYPE_PERSON, QuickSearchContext::TYPE_ORGANIZATION]);
        }

        return $types;
    }

    /**
     * @return int
     */
    public function getLimit()
    {
        return $this->limit;
    }

    /**
     * @param int $limit
     */
    public function setLimit($limit)
    {
        $this->limit = $limit;
    }
}
