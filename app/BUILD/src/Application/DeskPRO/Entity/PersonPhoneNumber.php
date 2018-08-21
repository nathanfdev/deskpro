<?php

namespace Application\DeskPRO\Entity;

use Doctrine\ORM\Mapping\ClassMetadata;
use JMS\Serializer\Annotation as JMS;

/**
 * Class PersonPhoneNumber.
 *
 * @JMS\ExclusionPolicy("ALL")
 */
class PersonPhoneNumber extends AbstractPhoneNumber
{
    /**
     * @var \Application\DeskPRO\Entity\Person
     *
     * @JMS\Expose()
     * @JMS\Type("entity<Application\DeskPRO\Entity\Person>")
     */
    protected $person;

    /**
     * @param Person $person
     *
     * @return $this
     */
    public function setPerson(Person $person = null)
    {
        $this->setModelField('person', $person);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setOwner($owner)
    {
        return $this->setPerson($owner);
    }

    /**
     * @return Person
     */
    public function getPerson()
    {
        return $this->person;
    }

    /**
     * {@inheritdoc}
     */
    public static function loadMetadata(ClassMetadata $metadata)
    {
        $metadata->customRepositoryClassName = 'Application\DeskPRO\EntityRepository\PersonPhoneNumber';
        $metadata->mapManyToOne(
            [
                'fieldName'    => 'person',
                'targetEntity' => 'Application\\DeskPRO\\Entity\\Person',
                'mappedBy'     => null,
                'inversedBy'   => 'phone_numbers',
                'cascade'      => ['persist'],
                'joinColumns'  => [
                    0 => [
                        'name'                 => 'person_id',
                        'referencedColumnName' => 'id',
                        'nullable'             => true,
                        'onDelete'             => 'cascade',
                        'columnDefinition'     => null,
                    ],
                ],
            ]
        );
    }
}
