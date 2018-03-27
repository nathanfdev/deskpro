<?php

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\AppBundle\Entity;

use Doctrine\Common\NotifyPropertyChanged;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Main representation of a ticket filter definition.
 *
 * Describes a criterion or set of criteria that make up a ticket filter.
 *
 * @ORM\Entity(repositoryClass="DeskPRO\Bundle\AppBundle\Entity\Repository\TicketFilterRepository")
 * @ORM\Table(name="ticket_filters2")
 *
 * @JMS\ExclusionPolicy("ALL")
 * @ORM\ChangeTrackingPolicy("NOTIFY")
 */
class TicketFilter implements EntityInterface, NotifyPropertyChanged
{
    use NotifyPropertyChangedTrait;

    /**
     * The unique filter ID.
     *
     * @ORM\Id()
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue()
     *
     * @JMS\Expose()
     * @JMS\Type("integer")
     *
     * @var int
     */
    protected $id;

    /**
     * The filter`s title.
     *
     * @ORM\Column(name="title", type="string")
     *
     * @Assert\NotBlank()
     *
     * @JMS\Expose()
     * @JMS\Type("string")
     *
     * @var string
     */
    protected $title;

    /**
     * FQL query.
     *
     * @ORM\Column(name="query", type="string")
     *
     * @Assert\NotNull()
     * @Assert\Valid()
     *
     * @JMS\Expose()
     * @JMS\Type("string")
     *
     * @var string
     */
    protected $query;

    /**
     * @ORM\Column(name="is_enabled", type="boolean")
     *
     * @JMS\Expose()
     * @JMS\Type("boolean")
     *
     * @var bool
     */
    protected $isEnabled = true;

    /**
     * @return int|null
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param string $title
     *
     * @return $this
     */
    public function setTitle($title)
    {
        $this->setModelField('title', $title);

        return $this;
    }

    /**
     * @return string
     */
    public function getQuery()
    {
        return $this->query;
    }

    /**
     * @param string $query
     */
    public function setQuery($query)
    {
        $this->setModelField('query', $query);

        return $this;
    }

    /**
     * @return bool
     */
    public function isEnabled()
    {
        return $this->isEnabled;
    }

    /**
     * Enable the filter.
     */
    public function enable()
    {
        $this->setModelField('isEnabled', true);

        return $this;
    }

    /**
     * Disable the filter.
     */
    public function disable()
    {
        $this->setModelField('isEnabled', false);

        return $this;
    }
}
