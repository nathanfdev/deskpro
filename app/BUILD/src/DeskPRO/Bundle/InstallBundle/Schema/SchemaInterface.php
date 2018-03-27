<?php

namespace DeskPRO\Bundle\InstallBundle\Schema;

interface SchemaInterface extends \Countable
{
    /**
     * @return array
     */
    public function getCreates();

    /**
     * @return array
     */
    public function getAlters();

    /**
     * @return array
     */
    public function getTriggers();

    /**
     * Counts all creates, alters and triggers for a grand total
     * of schema artefacts.
     *
     * @return int
     */
    public function count();
}
