<?php

namespace Application\DeskPRO\NewSearch\SearchEngine;

use Orb\Util\OptionsArray;

class SearchContext implements SearchContextInterface
{
    /**
     * @var array
     */
    private $article_category_ids = [];

    /**
     * @var array
     */
    private $download_category_ids = [];

    /**
     * @var array
     */
    private $news_category_ids = [];

    /**
     * @var array
     */
    private $feedback_category_ids = [];

    /**
     * @var array
     */
    private $guide_ids = [];

    /**
     * @var \Application\DeskPRO\Entity\Person
     */
    private $person;

    /**
     * @var \Application\DeskPRO\Entity\Brand
     */
    private $brand;

    /**
     * @var OptionsArray
     */
    private $options;

    /**
     * @var array
     */
    private $allowedFields;

    /**
     * SearchContext constructor.
     */
    public function __construct()
    {
        $this->options = new OptionsArray();
    }

    /**
     * @param $name
     * @param $value
     *
     * @return $this
     */
    public function setOption($name, $value)
    {
        $this->options->set($name, $value);

        return $this;
    }

    /**
     * @param $name
     *
     * @return mixed
     */
    public function getOption($name)
    {
        return $this->options->get($name);
    }

    /**
     * @return \Application\DeskPRO\Entity\Person
     */
    public function getPerson()
    {
        return $this->person;
    }

    /**
     * @param \Application\DeskPRO\Entity\Person $person
     */
    public function setPerson($person)
    {
        $this->person = $person;
    }

    /**
     * @return \Application\DeskPRO\Entity\Brand
     */
    public function getBrand()
    {
        return $this->brand;
    }

    /**
     * @param \Application\DeskPRO\Entity\Brand $brand
     */
    public function setBrand($brand)
    {
        $this->brand = $brand;
    }

    /**
     * @return array
     */
    public function getArticleCategoryIds()
    {
        return $this->article_category_ids;
    }

    /**
     * @param array $article_category_ids
     */
    public function setArticleCategoryIds($article_category_ids)
    {
        $this->article_category_ids = $article_category_ids;
    }

    /**
     * @return array
     */
    public function getDownloadCategoryIds()
    {
        return $this->download_category_ids;
    }

    /**
     * @param array $download_category_ids
     */
    public function setDownloadCategoryIds($download_category_ids)
    {
        $this->download_category_ids = $download_category_ids;
    }

    /**
     * @return array
     */
    public function getFeedbackCategoryIds()
    {
        return $this->feedback_category_ids;
    }

    /**
     * @param array $feedback_category_ids
     */
    public function setFeedbackCategoryIds($feedback_category_ids)
    {
        $this->feedback_category_ids = $feedback_category_ids;
    }

    /**
     * @return array
     */
    public function getNewsCategoryIds()
    {
        return $this->news_category_ids;
    }

    /**
     * @param array $news_category_ids
     */
    public function setNewsCategoryIds($news_category_ids)
    {
        $this->news_category_ids = $news_category_ids;
    }

    /**
     * @return array
     */
    public function getGuideIds()
    {
        return $this->guide_ids;
    }

    /**
     * @param array $guide_ids
     */
    public function setGuideIds($guide_ids)
    {
        $this->guide_ids = $guide_ids;
    }

    /**
     * @return array
     */
    public function getAllowedFields()
    {
        return $this->allowedFields;
    }

    /**
     * @param array $allowedFields
     *
     * @return $this
     */
    public function setAllowedFields($allowedFields)
    {
        $this->allowedFields = $allowedFields;

        return $this;
    }
}
