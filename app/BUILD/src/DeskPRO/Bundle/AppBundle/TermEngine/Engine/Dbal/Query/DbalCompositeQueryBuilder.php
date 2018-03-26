<?php

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\AppBundle\TermEngine\Engine\Dbal\Query;

/**
 * Class DbalCompositeQueryBuilder.
 */
class DbalCompositeQueryBuilder extends DbalQueryBuilder
{
    /**
     * @var array|null
     */
    protected $where_strings;

    /**
     * {@inheritdoc}
     */
    public function setWhereString($new_where_string)
    {
        $this->where_strings[] = $new_where_string;
    }

    /**
     * @return array
     */
    public function getWhereStrings()
    {
        return $this->where_strings ?: [];
    }
}
