<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/  |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2014, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at https://www.deskpro.com/eula/                            |
|                                                                          |
| By using this software, you acknowledge having read the license          |
| and agree to be bound thereby.                                           |
|                                                                          |
| Please note that DeskPRO is not free software. We release the full       |
| source code for our software because we trust our users to pay us for    |
| the huge investment in time and energy that has gone into both creating  |
| this software and supporting our customers. By providing the source code |
| we preserve our customers' ability to modify, audit and learn from our   |
| work. We have been developing DeskPRO since 2001, please help us make it |
| another decade.                                                          |
|                                                                          |
| Like the work you see? Think you could make it better? We are always     |
| looking for great developers to join us: http://www.deskpro.com/jobs/    |
|                                                                          |
| ~ Thanks, Everyone at Team DeskPRO                                       |
\**************************************************************************/

/**
 * DeskPRO
 *
 * @package DeskPRO
 * @category Entities
 * @copyright Copyright (c) 2011 DeskPRO (http://www.deskpro.com/)
 */
namespace Application\DeskPRO\Entity;

use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Mapping\ClassMetadataInfo;

/**
 * History of slugs
 */
class FeedbackSlugHistory extends \Application\DeskPRO\Domain\DomainObject
{
    /**
     * The unique ID
     *
     * @var int
     *
     */
    protected $id;

    /**
     * The content
     *
     * @var Feedback
     */
    protected $feedback;

    /**
     * The slug
     *
     * @var string
     */
    protected $slug;

    /**
     * @var \DateTime
     */
    protected $date_created;

    /**
     * @param Feedback $feedback the content
     * @param string $old_slug the slug to put in history
     */
    public function __construct(Feedback $feedback, $old_slug)
    {
        $this->setFeedback($feedback);
        $this->setSlug($old_slug);
        $this->setModelField('date_created', new \DateTime());
    }

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
    public function getFeedback()
    {
        return $this->feedback;
    }

    /**
     * @param Feedback $feedback
     */
    public function setFeedback(Feedback $feedback)
    {
        $this->setModelField('feedback', $feedback);
    }

    /**
     * @return string
     */
    public function getSlug()
    {
        return $this->slug;
    }

    /**
     * @param string $slug
     */
    public function setSlug($slug)
    {
        $this->setModelField('slug', $slug);
    }

    ############################################################################
    # Doctrine Metadata
    ############################################################################

    public static function loadMetadata(ClassMetadata $metadata)
    {
        $metadata->setInheritanceType(ClassMetadataInfo::INHERITANCE_TYPE_NONE);
        $metadata->customRepositoryClassName = 'Application\DeskPRO\EntityRepository\ArticleSlugHistory';
        $metadata->setPrimaryTable(array( 'name' => 'feedback_slug_history'));
        $metadata->setChangeTrackingPolicy(ClassMetadataInfo::CHANGETRACKING_NOTIFY);
        $metadata->mapField(
            array(
                'fieldName' => 'date_created', 'type' => 'datetime', 'precision' => 0, 'scale' => 0, 'nullable' => false,
                'columnName' => 'date_created',
            )
        );
        $metadata->mapField(array( 'fieldName' => 'id', 'type' => 'integer', 'precision' => 0, 'scale' => 0, 'nullable' => false, 'columnName' => 'id', 'id' => true));
        $metadata->mapField(array( 'fieldName' => 'slug', 'type' => 'string', 'precision' => 0, 'scale' => 0, 'nullable' => false, 'columnName' => 'slug', 'unique' => true));
        $metadata->mapManyToOne(array(
            'fieldName' => 'feedback', 'targetEntity' => 'Application\DeskPRO\Entity\Feedback',
            'cascade' => array(0 => 'remove', 1 => 'persist', 3 => 'merge'), 'inversedBy' => 'slug_history'
        ));
        $metadata->setIdGeneratorType(ClassMetadataInfo::GENERATOR_TYPE_IDENTITY);
    }
}
