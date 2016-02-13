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

namespace DeskPRO\Bundle\InstallBundle\Schema;

class SchemaArray implements SchemaInterface
{
    /**
     * @var array
     */
    private $creates;

    /**
     * @var array
     */
    private $alters;

    /**
     * @var array
     */
    private $triggers;

    /**
     * @var int
     */
    private $count;

    /**
     * @param string $path
     *
     * @return SchemaArray
     */
    public static function createFromFile($path)
    {
        require $path;

        return new self(
            $queries['create'],
            $queries['alter'],
            $queries['trigger']
        );
    }

    /**
     * SchemaFile constructor.
     *
     * @param array|null $creates
     * @param array|null $alters
     * @param array|null $triggers
     */
    public function __construct(array $creates = null, array $alters = null, array $triggers = null)
    {
        $this->creates  = $creates ?: [];
        $this->alters   = $alters ?: [];
        $this->triggers = $triggers ?: [];

        $this->count = count($creates) + count($alters) + count($triggers);
    }

    /**
     * {@inheritdoc}
     */
    public function getCreates()
    {
        return $this->creates;
    }

    /**
     * {@inheritdoc}
     */
    public function getAlters()
    {
        return $this->alters;
    }

    /**
     * {@inheritdoc}
     */
    public function getTriggers()
    {
        return $this->getTriggers();
    }

    /**
     * {@inheritdoc}
     */
    public function count()
    {
        return $this->count;
    }
}
