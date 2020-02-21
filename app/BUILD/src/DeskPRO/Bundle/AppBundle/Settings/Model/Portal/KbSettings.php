<?php

namespace DeskPRO\Bundle\AppBundle\Settings\Model\Portal;

use JMS\Serializer\Annotation as JMS;

/**
 * Class KbSettings.
 */
class KbSettings extends AbstractAppSettings
{
    /**
     * @var bool
     *
     * @JMS\Type("boolean")
     */
    protected $knowledgebaseDeepTree;

    /**
     * @var bool
     *
     * @JMS\Type("boolean")
     */
    protected $requireReviewDate;

    /**
     * @var bool
     *
     * @JMS\Type("boolean")
     */
    protected $minReviewDate;

    /**
     * @var int
     *
     * @JMS\Type("integer")
     */
    protected $minReviewDateInterval;

    /**
     * @var string
     *
     * @JMS\Type("string")
     */
    protected $minReviewDateUnit;

    /**
     * @var bool
     *
     * @JMS\Type("boolean")
     */
    protected $maxReviewDate;

    /**
     * @var int
     *
     * @JMS\Type("integer")
     */
    protected $maxReviewDateInterval;

    /**
     * @var string
     *
     * @JMS\Type("string")
     */
    protected $maxReviewDateUnit;

    /**
     * @var bool
     *
     * @JMS\Type("boolean")
     */
    protected $defaultReviewDate;

    /**
     * @var int
     *
     * @JMS\Type("integer")
     */
    protected $defaultReviewDateInterval;

    /**
     * @var string
     *
     * @JMS\Type("string")
     */
    protected $defaultReviewDateUnit;

    /**
     * @var bool
     *
     * @JMS\Type("boolean")
     */
    protected $autoUnpublishReview;

    /**
     * @var int
     *
     * @JMS\Type("integer")
     */
    protected $autoUnpublishReviewInterval;

    /**
     * @var string
     *
     * @JMS\Type("string")
     */
    protected $autoUnpublishReviewUnit;

    /**
     * @return bool
     */
    public function isKnowledgebaseDeepTree()
    {
        return $this->knowledgebaseDeepTree;
    }

    /**
     * @param bool $knowledgebaseDeepTree
     *
     * @return $this
     */
    public function setKnowledgebaseDeepTree($knowledgebaseDeepTree)
    {
        $this->knowledgebaseDeepTree = $knowledgebaseDeepTree;

        return $this;
    }

    /**
     * @return bool
     */
    public function isRequireReviewDate()
    {
        return $this->requireReviewDate;
    }

    /**
     * @param bool $requireReviewDate
     *
     * @return $this
     */
    public function setRequireReviewDate($requireReviewDate)
    {
        $this->requireReviewDate = $requireReviewDate;

        return $this;
    }

    /**
     * @return bool
     */
    public function isMinReviewDate()
    {
        return $this->minReviewDate;
    }

    /**
     * @param bool $minReviewDate
     *
     * @return $this
     */
    public function setMinReviewDate($minReviewDate)
    {
        $this->minReviewDate = $minReviewDate;

        return $this;
    }

    /**
     * @return int
     */
    public function getMinReviewDateInterval()
    {
        return $this->minReviewDateInterval;
    }

    /**
     * @param int $minReviewDateInterval
     *
     * @return $this
     */
    public function setMinReviewDateInterval($minReviewDateInterval)
    {
        $this->minReviewDateInterval = $minReviewDateInterval;

        return $this;
    }

    /**
     * @return string
     */
    public function getMinReviewDateUnit()
    {
        return $this->minReviewDateUnit;
    }

    /**
     * @param string $minReviewDateUnit
     *
     * @return $this
     */
    public function setMinReviewDateUnit($minReviewDateUnit)
    {
        $this->minReviewDateUnit = $minReviewDateUnit;

        return $this;
    }

    /**
     * @return bool
     */
    public function isMaxReviewDate()
    {
        return $this->maxReviewDate;
    }

    /**
     * @param bool $maxReviewDate
     *
     * @return $this
     */
    public function setMaxReviewDate($maxReviewDate)
    {
        $this->maxReviewDate = $maxReviewDate;

        return $this;
    }

    /**
     * @return int
     */
    public function getMaxReviewDateInterval()
    {
        return $this->maxReviewDateInterval;
    }

    /**
     * @param int $maxReviewDateInterval
     *
     * @return $this
     */
    public function setMaxReviewDateInterval($maxReviewDateInterval)
    {
        $this->maxReviewDateInterval = $maxReviewDateInterval;

        return $this;
    }

    /**
     * @return string
     */
    public function getMaxReviewDateUnit()
    {
        return $this->maxReviewDateUnit;
    }

    /**
     * @param string $maxReviewDateUnit
     *
     * @return $this
     */
    public function setMaxReviewDateUnit($maxReviewDateUnit)
    {
        $this->maxReviewDateUnit = $maxReviewDateUnit;

        return $this;
    }

    /**
     * @return bool
     */
    public function isDefaultReviewDate()
    {
        return $this->defaultReviewDate;
    }

    /**
     * @param bool $defaultReviewDate
     *
     * @return $this
     */
    public function setDefaultReviewDate($defaultReviewDate)
    {
        $this->defaultReviewDate = $defaultReviewDate;

        return $this;
    }

    /**
     * @return int
     */
    public function getDefaultReviewDateInterval()
    {
        return $this->defaultReviewDateInterval;
    }

    /**
     * @param int $defaultReviewDateInterval
     *
     * @return $this
     */
    public function setDefaultReviewDateInterval($defaultReviewDateInterval)
    {
        $this->defaultReviewDateInterval = $defaultReviewDateInterval;

        return $this;
    }

    /**
     * @return string
     */
    public function getDefaultReviewDateUnit()
    {
        return $this->defaultReviewDateUnit;
    }

    /**
     * @param string $defaultReviewDateUnit
     *
     * @return $this
     */
    public function setDefaultReviewDateUnit($defaultReviewDateUnit)
    {
        $this->defaultReviewDateUnit = $defaultReviewDateUnit;

        return $this;
    }

    /**
     * @return bool
     */
    public function isAutoUnpublishReview()
    {
        return $this->autoUnpublishReview;
    }

    /**
     * @param bool $autoUnpublishReview
     *
     * @return $this
     */
    public function setAutoUnpublishReview($autoUnpublishReview)
    {
        $this->autoUnpublishReview = $autoUnpublishReview;

        return $this;
    }

    /**
     * @return int
     */
    public function getAutoUnpublishReviewInterval()
    {
        return $this->autoUnpublishReviewInterval;
    }

    /**
     * @param int $autoUnpublishReviewInterval
     *
     * @return $this
     */
    public function setAutoUnpublishReviewInterval($autoUnpublishReviewInterval)
    {
        $this->autoUnpublishReviewInterval = $autoUnpublishReviewInterval;

        return $this;
    }

    /**
     * @return string
     */
    public function getAutoUnpublishReviewUnit()
    {
        return $this->autoUnpublishReviewUnit;
    }

    /**
     * @param string $autoUnpublishReviewUnit
     *
     * @return $this
     */
    public function setAutoUnpublishReviewUnit($autoUnpublishReviewUnit)
    {
        $this->autoUnpublishReviewUnit = $autoUnpublishReviewUnit;

        return $this;
    }
}
