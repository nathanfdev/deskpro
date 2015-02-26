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

namespace Application\ImportBundle\Entity;

use Symfony\Component\Validator\Mapping\ClassMetadata;
use DateTime;

/**
 * Exporting article entity
 *
 * Class Article
 * @package Application\ImportBundle\Entity
 */
final class Article extends AbstractContentEntity implements PersonAwareInterface, LabelAwareInterface
{
    /**
     * @var string
     */
    private $person_email;

    /**
     * @var string
     */
    private $end_action;

    /**
     * @var DateTime
     */
    private $date_end;

    /**
     * @var array
     */
    private $categories = array();

    /**
     * @var array
     */
    private $labels = array();

    /**
     * {@inheritdoc}
     */
    public function getType()
    {
        return self::TYPE_ARTICLE;
    }

    /**
     * {@inheritdoc}
     */
    public function getPersonEmail()
    {
        return $this->person_email;
    }

    /**
     * {@inheritdoc}
     */
    public function setPersonEmail($person_email)
    {
        $this->person_email = $person_email;

        return $this;
    }

    /**
     * @return string
     */
    public function getEndAction()
    {
        return $this->end_action;
    }

    /**
     * @param  string $end_action
     * @return $this
     */
    public function setEndAction($end_action)
    {
        $this->end_action = $end_action;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getStatus()
    {
        if ($this->date_end) {
            return 'archived';
        }

        return parent::getStatus();
    }

    /**
     * @return DateTime
     */
    public function getDateEnd()
    {
        return $this->date_end;
    }

    /**
     * @param  DateTime $date_end
     * @return $this
     */
    public function setDateEnd(DateTime $date_end)
    {
        $this->date_end = $date_end;

        return $this;
    }

    /**
     * @return array
     */
    public function getCategories()
    {
        return $this->categories;
    }

    /**
     * @param  string $category
     * @return $this
     */
    public function addCategory($category)
    {
        $this->categories[] = (string) $category;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getLabels()
    {
        return $this->labels;
    }

    /**
     * {@inheritdoc}
     */
    public function addLabel($label)
    {
        $this->labels[] = (string) $label;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function toArray()
    {
        if (! $this->date_created) {
            throw new \Exception('Date created is not set up');
        }

        return array(
            'oid'            => $this->oid,
            'person'         => $this->person_email,
            'title'          => $this->title,
            'content'        => $this->content,
            'language'       => $this->language,
            'end_action'     => $this->end_action,
            'slug'           => $this->slug,
            'total_rating'   => $this->total_rating,
            'num_comments'   => $this->num_comments,
            'num_ratings'    => $this->num_ratings,
            'view_count'     => $this->view_count,
            'status'         => $this->status,
            'date_created'   => $this->date_created->format('Y-m-d H:i:s'),
            'date_published' => $this->date_published ? $this->date_published->format('Y-m-d H:i:s') : null,
            'date_end'       => $this->date_end ? $this->date_end->format('Y-m-d H:i:s') : null,
            'categories'     => $this->categories,
            'labels'         => $this->labels,
        );
    }

    /**
     * Validator class metadata
     *
     * @param ClassMetadata $metadata
     */
    public static function loadValidatorMetadata(ClassMetadata $metadata)
    {
        AbstractContentEntity::loadValidatorMetadata($metadata);
    }
}
