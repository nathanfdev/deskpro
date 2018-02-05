<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2018, DeskPRO Ltd.
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

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace Application\DeskPRO\Entity;

use Application\DeskPRO\App;
use Application\DeskPRO\Domain\DomainObject;
use Application\DeskPRO\Entity;
use Application\DeskPRO\Entity\Avatar\AvatarOwner;
use DeskPRO\Bundle\AppBundle\Entity\PersonList;
use DeskPRO\Bundle\AppBundle\Validator\Constraints as AppAssert;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Mapping\ClassMetadataInfo;
use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Mapping\ClassMetadata as ValidatorClassMetadata;

/**
 * An agent team is a group of agents. Similar to usergroups but for agents.
 *
 * @property string $name
 *
 * @JMS\ExclusionPolicy("ALL")
 */
class AgentTeam extends DomainObject implements PersonList, AvatarOwner
{
    /**
     * The unique ID.
     *
     * @var int
     * @JMS\Expose()
     * @JMS\Type("integer")
     */
    protected $id = null;

    /**
     * The name of agent team.
     *
     * @var string
     * @JMS\Expose()
     * @JMS\Type("string")
     */
    protected $name = '';

    /**
     * @Assert\All({
     *     @AppAssert\Person\PersonType(type="agent")
     * })
     *
     * @var ArrayCollection
     */
    protected $members = null;

    /**
     * @var Blob
     */
    protected $avatar;

    /**
     * @var ReportDashboardPermission
     */
    protected $report_dashboard_permissions;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    public function __construct()
    {
        $this->members = new ArrayCollection();
    }

    /**
     * @return ArrayCollection
     */
    public function getMembers()
    {
        return $this->members;
    }

    /**
     * @param Person $person
     */
    public function addPerson(Entity\Person $person)
    {
        if ($this->members->contains($person)) {
            return;
        }
        $this->members->add($person);
        $this->_onPropertyChanged('members', $this->members, $this->members);
    }

    /**
     * @param Person $person
     */
    public function removePerson(Entity\Person $person)
    {
        $this->members->removeElement($person);
        $this->_onPropertyChanged('members', $this->members, $this->members);
    }

    /**
     * @return bool
     */
    public function hasAvatar()
    {
        return $this->avatar && $this->avatar->isImage();
    }

    /**
     * @param int $size
     *
     * @return string
     */
    public function getAvatarUrl($size = 50)
    {
        if (!$this->hasAvatar()) {
            return App::get('router.default')->generate(
                'serve_default_picture',
                [
                    's'        => $size,
                    'size-fit' => 1,
                ],
                UrlGeneratorInterface::ABSOLUTE_URL
            );
        }

        return $this->avatar->getThumbnailUrl($size);
    }

    /**
     * @return ArrayCollection|Person[]
     */
    public function getPersonList()
    {
        return $this->members->filter(
            function ($member) {
                /* @var Person $member */
                return $member->isActiveAgent();
            });
    }

    /**
     * @param Person $person
     *
     * @return bool
     */
    public function hasMember(Person $person)
    {
        return $this->members->matching(new Criteria(Criteria::expr()->eq('id', $person->getId())))->count() > 0;
    }

    /**
     * @return Blob
     */
    public function getAvatarBlob()
    {
        return $this->avatar;
    }

    //###########################################################################
    // Validation Metadata
    //###########################################################################

    public static function loadValidatorMetadata(ValidatorClassMetadata $metadata)
    {
        $metadata->addPropertyConstraint('name', new NotBlank());
    }

    //###########################################################################
    // Doctrine Metadata
    //###########################################################################

    public static function loadMetadata(ClassMetadata $metadata)
    {
        $metadata->setInheritanceType(ClassMetadataInfo::INHERITANCE_TYPE_NONE);
        $metadata->customRepositoryClassName = 'Application\DeskPRO\EntityRepository\AgentTeam';
        $metadata->setPrimaryTable(['name' => 'agent_teams']);
        $metadata->setChangeTrackingPolicy(ClassMetadataInfo::CHANGETRACKING_NOTIFY);
        $metadata->mapField(
            [
                'fieldName'  => 'id',
                'type'       => 'integer',
                'precision'  => 0,
                'scale'      => 0,
                'nullable'   => false,
                'columnName' => 'id',
                'id'         => true,
            ]
        );
        $metadata->mapField(
            [
                'fieldName'  => 'name',
                'type'       => 'string',
                'length'     => 255,
                'precision'  => 0,
                'scale'      => 0,
                'nullable'   => false,
                'columnName' => 'name',
            ]
        );

        $metadata->setIdGeneratorType(ClassMetadataInfo::GENERATOR_TYPE_IDENTITY);

        $metadata->mapManyToMany([
            'fieldName'    => 'members',
            'inversedBy'   => 'teams',
            'targetEntity' => 'Application\\DeskPRO\\Entity\\Person',
            'joinTable'    => [
                'name'               => 'agent_team_members',
                'joinColumns'        => [['name' => 'team_id', 'onDelete' => 'CASCADE']],
                'inverseJoinColumns' => [['name' => 'person_id', 'onDelete' => 'CASCADE']],
            ],
            'orderBy' => ['name' => 'ASC'],
        ]);

        $metadata->mapManyToOne([
            'fieldName'    => 'avatar',
            'targetEntity' => 'Application\\DeskPRO\\Entity\\Blob',
            'mappedBy'     => null,
            'inversedBy'   => null,
            'joinColumns'  => [[
                'name'                 => 'avatar_blob_id',
                'referencedColumnName' => 'id',
                'nullable'             => true,
                'onDelete'             => 'set null',
            ]],
            'dpApi' => true,
        ]);
    }
}
