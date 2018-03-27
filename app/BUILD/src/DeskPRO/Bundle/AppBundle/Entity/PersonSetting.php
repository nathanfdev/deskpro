<?php

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace DeskPRO\Bundle\AppBundle\Entity;

use Application\DeskPRO\Entity\Person;
use Doctrine\Common\NotifyPropertyChanged;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @JMS\ExclusionPolicy("all")
 *
 * @ORM\Entity()
 * @ORM\Table(name="person_settings")
 * @ORM\ChangeTrackingPolicy("NOTIFY")
 */
class PersonSetting implements EntityInterface, NotifyPropertyChanged
{
    use NotifyPropertyChangedTrait;

    /**
     * @var Person
     * @ORM\Id()
     * @ORM\ManyToOne(targetEntity="Application\DeskPRO\Entity\Person")
     * @ORM\JoinColumn(name="person_id", referencedColumnName="id", onDelete="CASCADE")
     * @Assert\NotNull()
     */
    protected $person;

    /**
     * Setting name.
     *
     * @JMS\Expose()
     * @JMS\Type("string")
     *
     * @ORM\Id()
     * @ORM\Column(type="string")
     *
     * @Assert\NotBlank()
     */
    protected $name;

    /**
     * Setting value.
     *
     * @JMS\Expose()
     * @JMS\Type("array")
     *
     * @ORM\Column(type="json_array")
     *
     * @Assert\NotBlank()
     */
    protected $value;

    /**
     * Constructor.
     *
     * @param Person $person
     * @param string $name
     */
    public function __construct(Person $person, $name)
    {
        $this->person = $person;
        $this->name   = $name;
    }

    /**
     * Composite id (person_id + name).
     *
     * @JMS\VirtualProperty()
     * @JMS\SerializedName("id")
     *
     * @return array
     */
    public function getId()
    {
        return [$this->person->getId(), $this->getName()];
    }

    /**
     * @return Person
     */
    public function getPerson()
    {
        return $this->person;
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param $value
     *
     * @return $this
     */
    public function setValue($value)
    {
        $this->setModelField('value', $value);

        return $this;
    }
}
