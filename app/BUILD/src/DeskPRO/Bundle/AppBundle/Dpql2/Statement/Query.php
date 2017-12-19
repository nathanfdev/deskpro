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

/**
 * Class Query.
 */
class Query
{
    /**
     * @var [TYPE, SelectPart]
     */
    private $parts;

    /**
     * @var bool
     */
    private $isPrepared = false;

    /**
     * @var string
     */
    private $sql;

    /**
     * Query constructor.
     *
     * @param $parts
     */
    public function __construct(array $parts)
    {
        $this->parts = $parts;
    }

    public function prepare()
    {
        if ($this->isPrepared) {
            return;
        }

        $this->isPrepared = true;

        $sqlParts = [];

        if (count($this->parts) === 1) {
            $this->parts[0][1]->prepare();
            $this->sql = $this->parts[0][1]->toSql();
        } else {
            $first = true;
            foreach ($this->parts as $partInfo) {
                /** @var SelectPart $part */
                list($type, $part) = $partInfo;
                $part->prepare();

                if ($first) {
                    $sqlParts[] = '('.$part->toSql().')';
                } else {
                    $op         = 'UNION '.($type === 'DISTINCT' ? 'DISTINCT ' : '');
                    $sqlParts[] = "\n$op\n".'('.$part->toSql().')';
                }
                $first = false;
            }

            $this->sql = implode('', $sqlParts);
        }
    }

    /**
     * @return string
     */
    public function toSql()
    {
        $this->prepare();

        return $this->sql;
    }
}
