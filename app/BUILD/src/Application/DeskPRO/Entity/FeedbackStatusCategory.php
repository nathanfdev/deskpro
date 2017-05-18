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

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace Application\DeskPRO\Entity;

use Application\DeskPRO\Domain\DomainObject;
use Application\DeskPRO\Translate\HasPhraseName;
use Application\DeskPRO\Translate\Translate;
use Application\DeskPRO\Validator\HasValidationMetadataInterface;
use DeskPRO\Bundle\AppBundle\ObjectRouter\Configuration\PortalLinkCustom;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Mapping\ClassMetadataInfo;
use JMS\Serializer\Annotation as JMS;
use Orb\Util\Util;
use Symfony\Component\Validator\Constraints\Choice;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Mapping\ClassMetadata as ValidatorClassMetadata;

/**
 * Feedback status types for accepted/declined statuses.
 *
 * @JMS\ExclusionPolicy("all")
 * @PortalLinkCustom()
 */
class FeedbackStatusCategory extends DomainObject implements HasPhraseName, HasValidationMetadataInterface
{
    const STATUS_ACTIVE = 'active';
    const STATUS_CLOSED = 'closed';

    /**
     * The unique ID.
     *
     * @JMS\Expose()
     * @JMS\Type("integer")
     *
     * @var int
     */
    protected $id = null;

    /**
     * Category status type.
     *
     * @JMS\Expose()
     * @JMS\Type("string")
     *
     * @var string
     */
    protected $status_type;

    /**
     * Category title.
     *
     * @JMS\Expose()
     * @JMS\Type("string")
     *
     * @var string
     */
    protected $title;

    /**
     * @var int
     */
    protected $display_order = 0;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return FeedBackStatusCategory
     */
    public static function createFeedbackStatusCategory()
    {
        $status_category = new self();

        return $status_category;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    public function setTitle($string)
    {
        $this->setModelField('title', $string);

        return $this;
    }

    /**
     * @return string
     */
    public function getStatusType()
    {
        return $this->status_type;
    }

    public function setStatusType($string)
    {
        $this->setModelField('status_type', $string);

        return $this;
    }

    /**
     * @return int
     */
    public function getDisplayOrder()
    {
        return $this->display_order;
    }

    /**
     * @param $order
     *
     * @return $this
     */
    public function setDisplayOrder($order)
    {
        $this->setModelField('display_order', (int) $order);

        return $this;
    }

    public function getStatusCode()
    {
        return $this->status_type.'.'.$this->id;
    }

    public function getRealTitle()
    {
        return $this->title;
    }

    /**
     * {@inheritdoc}
     */
    public function getPhraseName($property)
    {
        if (!$property) {
            $property = 'title';
        }
        $name        = strtolower(Util::getBaseClassname($this));
        $phrase_name = 'obj_'.$name.'.'.$this->id.'_'.$property;

        return $phrase_name;
    }

    /**
     * {@inheritdoc}
     */
    public function getPhraseDefault($property, Translate $translate)
    {
        return $this->title;
    }

    public function getSelectTitle()
    {
        return $this->title;
    }

    public function __toString()
    {
        return $this->title;
    }

    //###########################################################################
    // Validation Metadata
    //###########################################################################

    public static function loadValidatorMetadata(ValidatorClassMetadata $metadata)
    {
        $metadata->addPropertyConstraint('title', new NotBlank());
        $metadata->addPropertyConstraint('title', new Length(['min' => 2]));
        $metadata->addPropertyConstraint(
            'status_type',
            new Choice(
                [
                    'choices' => ['active', 'closed'],
                ]
            )
        );
    }

    //###########################################################################
    // Doctrine Metadata
    //###########################################################################

    public static function loadMetadata(ClassMetadata $metadata)
    {
        $metadata->setInheritanceType(ClassMetadataInfo::INHERITANCE_TYPE_NONE);
        $metadata->customRepositoryClassName = 'Application\DeskPRO\EntityRepository\FeedbackStatusCategory';
        $metadata->setPrimaryTable(['name' => 'feedback_status_categories']);
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
                'fieldName'  => 'status_type',
                'type'       => 'string',
                'length'     => 255,
                'precision'  => 0,
                'scale'      => 0,
                'nullable'   => false,
                'columnName' => 'status_type',
            ]
        );
        $metadata->mapField(
            [
                'fieldName'  => 'title',
                'type'       => 'string',
                'length'     => 255,
                'precision'  => 0,
                'scale'      => 0,
                'nullable'   => false,
                'columnName' => 'title',
            ]
        );
        $metadata->mapField(
            [
                'fieldName'  => 'display_order',
                'type'       => 'integer',
                'precision'  => 0,
                'scale'      => 0,
                'nullable'   => false,
                'columnName' => 'display_order',
            ]
        );
        $metadata->setIdGeneratorType(ClassMetadataInfo::GENERATOR_TYPE_IDENTITY);
    }
}
