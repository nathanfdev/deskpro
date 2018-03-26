<?php

namespace DeskPRO\Bundle\AppBundle\Model;

/**
 * Holds some property of a ticket (subject, department, custom field, etc).
 */
class TicketViewProperty
{
    /**
     * @var int
     */
    protected $id;

    /**
     * @var string
     */
    protected $type;

    /**
     * @var string
     */
    protected $label;

    /**
     * @var string
     */
    protected $value;

    /**
     * @var bool
     */
    private $alwaysVisible;

    /**
     * @var bool
     */
    private $linkify;

    /**
     * Constructor.
     *
     * @param int    $id
     * @param string $type
     * @param string $label
     * @param string $value
     * @param bool   $alwaysVisible
     * @param bool   $linkify
     *
     * @throws \Exception
     */
    public function __construct($id, $type, $label, $value, $alwaysVisible = false, $linkify = false)
    {
        if (!$id) {
            throw new \InvalidArgumentException('a TicketViewProperty cannot be instantiated without an ID');
        }

        $this->id            = $id;
        $this->type          = $type;
        $this->label         = $label;
        $this->value         = $value;
        $this->alwaysVisible = (bool) $alwaysVisible;
        $this->linkify       = (bool) $linkify;
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @return mixed
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @return bool
     */
    public function isVisible()
    {
        return $this->isAlwaysVisible() ? true : !$this->isEmpty();
    }

    /**
     * @return bool
     */
    public function isEmpty()
    {
        return empty($this->getValue());
    }

    /**
     * @return bool
     */
    public function isAlwaysVisible()
    {
        return $this->alwaysVisible;
    }

    /**
     * @return bool
     */
    public function isLinkify()
    {
        return $this->linkify;
    }
}
