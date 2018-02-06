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

namespace DeskPRO\Bundle\ImportBundle\Model;

use Application\DeskPRO\Entity\ContentAbstract;
use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Basic properties on content.
 *
 * Class AbstractContentEntity
 */
abstract class AbstractContentModel implements ContentAwareInterface
{
    use PrimaryImportModelTrait;

    /**
     * @var string
     *
     * @JMS\Type("string")
     *
     * @Assert\NotBlank()
     */
    protected $title;

    /**
     * @var string
     *
     * @JMS\Type("string")
     *
     * @Assert\NotBlank()
     */
    protected $content;

    /**
     * @var string
     *
     * @JMS\Type("string")
     */
    protected $language;

    /**
     * @var string
     *
     * @JMS\Type("string")
     *
     * @Assert\NotBlank()
     * @Assert\Choice(choices={
     *   "published",
     *   "archived",
     *   "hidden",
     *   "hidden.unpublished",
     *   "hidden.deleted",
     *   "hidden.spam",
     *   "hidden.draft"
     * })
     */
    protected $status;

    /**
     * @var int
     *
     * @JMS\Type("integer")
     */
    protected $view_count = 0;

    /**
     * @var \DateTime
     *
     * @JMS\Type("DateTime")
     */
    protected $date_created;

    /**
     * @var \DateTime
     *
     * @JMS\Type("DateTime")
     */
    protected $date_published;

    /**
     * {@inheritdoc}
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * {@inheritdoc}
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * {@inheritdoc}
     */
    public function setContent($content)
    {
        $this->content = $content;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getLanguage()
    {
        return $this->language;
    }

    /**
     * {@inheritdoc}
     */
    public function setLanguage($language)
    {
        $this->language = $language;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getStatus()
    {
        if ($this->date_published) {
            return ContentAbstract::STATUS_PUBLISHED;
        }

        return $this->status;
    }

    /**
     * {@inheritdoc}
     */
    public function setStatus($status)
    {
        $this->status = $status ?: ContentAbstract::STATUS_HIDDEN.'.'.ContentAbstract::HIDDEN_STATUS_UNPUBLISHED;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getViewCount()
    {
        return $this->view_count;
    }

    /**
     * {@inheritdoc}
     */
    public function setViewCount($view_count)
    {
        $this->view_count = (int) $view_count;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getDateCreated()
    {
        return $this->date_created;
    }

    /**
     * {@inheritdoc}
     */
    public function setDateCreated(\DateTime $date_created)
    {
        $this->date_created = $date_created;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getDatePublished()
    {
        return $this->date_published;
    }

    /**
     * {@inheritdoc}
     */
    public function setDatePublished(\DateTime $date_published = null)
    {
        $this->date_published = $date_published;

        return $this;
    }
}
