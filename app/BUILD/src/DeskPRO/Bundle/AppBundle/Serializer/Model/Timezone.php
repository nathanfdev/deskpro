<?php

namespace DeskPRO\Bundle\AppBundle\Serializer\Model;

use JMS\Serializer\Annotation as JMS;

/**
 * Class Timezone.
 */
class Timezone
{
    /**
     * Timezone ID.
     *
     * @JMS\Type("integer")
     *
     * @var int
     */
    protected $id;

    /**
     * Timezone Title.
     *
     * @JMS\Type("string")
     *
     * @var string
     */
    protected $title;

    /**
     * Timezone constructor.
     *
     * @param int    $id
     * @param string $title
     */
    public function __construct($id, $title)
    {
        $this->id    = $id;
        $this->title = $title;
    }
}
