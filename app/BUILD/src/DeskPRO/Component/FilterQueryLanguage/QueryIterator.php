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

namespace DeskPRO\Component\FilterQueryLanguage;

/**
 * Use this iterator to iterate over every node of a query.
 */
class QueryIterator extends \ArrayIterator implements \RecursiveIterator
{
    public function __construct(array $query)
    {
        parent::__construct($query['query']);
    }

    public function hasChildren()
    {
        $c = $this->current();

        return !empty($c['type']) && $c['type'] === 'TERM_GROUP';
    }

    /**
     * {@inheritdoc}
     */
    public function getChildren()
    {
        $c = $this->current();

        return !empty($c['type']) && $c['type'] === 'TERM_GROUP'
            ? $c['terms']
            : [];
    }

    /**
     * Iterate through a query and collect the identities of all
     * referenced fields.
     *
     * @param array $query
     *
     * @return string[]
     */
    public static function collectFieldIds(array $query)
    {
        $fields = [];

        foreach (new self($query) as $n) {
            if ($n['type'] === 'TERM') {
                $fields[] = $n['field']['identity'];
            }
        }

        return $fields;
    }
}
