<?php

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
